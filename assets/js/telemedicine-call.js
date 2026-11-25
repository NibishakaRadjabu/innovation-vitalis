(() => {
    const root = document.querySelector('[data-call-ui]');
    if (!root) return;

    const role = root.dataset.callUi;
    let room = root.dataset.room || '';
    let key = root.dataset.key || '';
    let peer = null;
    let localStream = null;
    let remoteStream = null;
    let dataChannel = null;
    let displayStream = null;
    let cursor = 0;
    let polling = false;
    let active = false;
    let startedAt = 0;
    let timerHandle = 0;
    let swapped = false;
    let pendingCandidates = [];
    let incomingFile = null;

    const $ = (selector) => root.querySelector(selector) || document.querySelector(selector);
    const csrf = root.dataset.callCsrf || '';
    const status = $('[data-patient-call-status]') || $('.session-indicator');
    const timer = $('[data-call-timer]') || $('[data-session-time]');
    const localVideo = $('[data-local-video]');
    const remoteVideo = $('[data-remote-video]');
    const stage = $('[data-video-stage]');
    const callHint = $('[data-call-hint]');
    const controls = {
        mic: $('[data-toggle-mic]'),
        camera: $('[data-toggle-camera]'),
        share: $('[data-toggle-share]'),
        end: $('[data-end-session]'),
        start: $('[data-start-call]'),
        join: $('[data-join-call]'),
    };

    const setStatus = (message) => {
        const label = $('[data-patient-call-status]');
        if (label) label.textContent = message;
        const indicator = $('.session-indicator, [data-patient-indicator]');
        if (indicator) {
            const dot = indicator.querySelector('i');
            indicator.textContent = message;
            if (dot) indicator.prepend(dot);
        }
    };

    const setHint = (message) => { if (callHint) callHint.textContent = message; };

    function displayPatientInfo(info) {
        const patient = info.patient;
        const details = $('[data-info-details]');
        const name = $('[data-info-name]');
        if (!patient || !details || !name) return;

        name.textContent = patient.name || 'Patient account';
        const avatar = $('[data-info-avatar]');
        if (avatar) {
            avatar.textContent = (patient.name || '?').trim().split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
        }
        const setValue = (selector, value) => {
            const element = $(selector);
            if (element) element.textContent = value || 'Not provided';
        };
        setValue('[data-info-email]', patient.email);
        setValue('[data-info-phone]', patient.phone);
        setValue('[data-info-dob]', patient.date_of_birth
            ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(`${patient.date_of_birth}T00:00:00`))
            : '');
        setValue('[data-info-gender]', patient.gender ? patient.gender.replaceAll('_', ' ') : '');

        const appointment = info.latest_appointment;
        let appointmentText = 'No appointment request on file';
        if (appointment) {
            const parts = [
                appointment.service,
                appointment.type?.replaceAll('_', ' '),
                appointment.status,
            ].filter(Boolean);
            if (appointment.preferred_at) {
                const appointmentDate = new Date(`${appointment.preferred_at.replace(' ', 'T')}`);
                if (!Number.isNaN(appointmentDate.getTime())) {
                    parts.push(new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(appointmentDate));
                }
            }
            appointmentText = parts.join(' · ');
        }
        setValue('[data-info-appointment]', appointmentText);
        details.hidden = false;
        const infoStatus = $('[data-info-status]');
        if (infoStatus) infoStatus.textContent = 'Patient account details loaded for this call.';
    }

    async function api(action, extra = {}) {
        const headers = { 'Content-Type': 'application/json' };
        if (csrf) headers['X-CSRF-Token'] = csrf;
        const response = await fetch('telemedicine-api.php', {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers,
            body: JSON.stringify({ action, room, key, ...extra }),
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(result.error || `Call service error (${response.status}).`);
        return result;
    }

    async function sendSignal(type, payload = null) {
        await api('send', { type, payload });
    }

    function formatDuration(seconds) {
        const minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
        const remainder = (seconds % 60).toString().padStart(2, '0');
        return `${minutes}:${remainder}`;
    }

    function startTimer() {
        if (timerHandle) return;
        startedAt = Date.now();
        timerHandle = window.setInterval(() => {
            if (timer) timer.textContent = formatDuration(Math.floor((Date.now() - startedAt) / 1000));
        }, 1000);
    }

    function appendChat(message, incoming = false) {
        const box = $('[data-chat-messages]');
        if (!box) return;
        box.querySelector('.chat-empty')?.remove();
        const bubble = document.createElement('p');
        bubble.className = 'chat-message';
        bubble.textContent = message;
        if (incoming) bubble.classList.add('is-incoming');
        box.appendChild(bubble);
        box.scrollTop = box.scrollHeight;
    }

    function appendFile(name, blob) {
        const box = $('[data-chat-messages]');
        if (!box) return;
        box.querySelector('.chat-empty')?.remove();
        const bubble = document.createElement('p');
        bubble.className = 'chat-message';
        const link = document.createElement('a');
        const objectUrl = URL.createObjectURL(blob);
        link.href = objectUrl;
        link.download = name;
        link.textContent = `Download shared file: ${name}`;
        bubble.appendChild(link);
        box.appendChild(bubble);
        box.scrollTop = box.scrollHeight;
        window.setTimeout(() => URL.revokeObjectURL(objectUrl), 10 * 60 * 1000);
    }

    function updateChatAvailability(enabled) {
        const form = $('[data-chat-form]');
        if (!form) return;
        form.querySelectorAll('input, button').forEach((control) => { control.disabled = !enabled; });
    }

    function handleDataChannel(channel) {
        dataChannel = channel;
        channel.binaryType = 'arraybuffer';
        channel.bufferedAmountLowThreshold = 128 * 1024;
        channel.onopen = () => updateChatAvailability(true);
        channel.onclose = () => updateChatAvailability(false);
        channel.onmessage = (event) => {
            if (event.data instanceof ArrayBuffer) {
                if (!incomingFile) return;
                if (incomingFile.received + event.data.byteLength > incomingFile.size) {
                    incomingFile = null;
                    return;
                }
                incomingFile.chunks.push(event.data);
                incomingFile.received += event.data.byteLength;
                return;
            }
            try {
                const data = JSON.parse(event.data);
                if (data.type === 'chat' && typeof data.message === 'string') appendChat(data.message, true);
                if (data.type === 'file-start' && Number(data.size) <= 8 * 1024 * 1024) {
                    incomingFile = { name: String(data.name || 'shared-file').replace(/[\\/]/g, '_'), type: String(data.mime || 'application/octet-stream'), size: Number(data.size), received: 0, chunks: [] };
                }
                if (data.type === 'file-end' && incomingFile) {
                    appendFile(incomingFile.name, new Blob(incomingFile.chunks, { type: incomingFile.type || 'application/octet-stream' }));
                    incomingFile = null;
                }
            } catch { /* Ignore malformed peer messages. */ }
        };
    }

    window.telemedicineSendChat = (message) => {
        const clean = String(message || '').trim().slice(0, 240);
        if (!clean || dataChannel?.readyState !== 'open') return false;
        dataChannel.send(JSON.stringify({ type: 'chat', message: clean }));
        appendChat(clean);
        return true;
    };

    function showStream(stream, video) {
        if (!stream || !video) return;
        video.srcObject = stream;
        video.play().catch(() => {});
        if (stage) stage.classList.add(video === remoteVideo ? 'has-remote-video' : 'has-local-video');
    }

    function swapViews() {
        swapped = !swapped;
        const main = swapped ? localStream : remoteStream;
        const thumb = swapped ? remoteStream : localStream;
        if (main) showStream(main, remoteVideo);
        if (thumb) showStream(thumb, localVideo);
        if (remoteVideo) remoteVideo.muted = Boolean(swapped);
        if (localVideo) localVideo.muted = !swapped;
        const mainLabel = $('[data-main-video-label]');
        const thumbLabel = $('[data-thumb-video-label]');
        if (mainLabel) mainLabel.textContent = swapped ? 'Your camera view' : (role === 'doctor' ? 'Patient video window' : 'Doctor video feed');
        if (thumbLabel) thumbLabel.textContent = swapped ? (role === 'doctor' ? 'Patient video preview' : 'Doctor video preview') : 'Your camera preview';
        const button = $('[data-swap-views]');
        button?.setAttribute('aria-pressed', String(swapped));
    }

    window.telemedicineControls = {
        mic() {
            const track = localStream?.getAudioTracks()[0];
            if (!track) return false;
            track.enabled = !track.enabled;
            return !track.enabled;
        },
        camera() {
            const track = localStream?.getVideoTracks()[0];
            if (!track) return false;
            track.enabled = !track.enabled;
            return !track.enabled;
        },
        async share() {
            if (displayStream) {
                const cameraTrack = localStream?.getVideoTracks()[0];
                if (cameraTrack) await peer?.getSenders().find((sender) => sender.track?.kind === 'video')?.replaceTrack(cameraTrack);
                displayStream.getTracks().forEach((track) => track.stop());
                displayStream = null;
                return false;
            }
            if (!peer || !navigator.mediaDevices?.getDisplayMedia) throw new Error('Screen sharing is not available in this browser.');
            displayStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
            const displayTrack = displayStream.getVideoTracks()[0];
            const sender = peer.getSenders().find((item) => item.track?.kind === 'video');
            await sender?.replaceTrack(displayTrack);
            displayTrack.addEventListener('ended', async () => {
                try {
                    await window.telemedicineControls.share();
                    const button = controls.share;
                    button?.setAttribute('aria-pressed', 'false');
                    const label = button?.querySelector('[data-share-label]');
                    if (label) label.textContent = 'Share screen';
                } catch { /* The call can continue if restoring the camera fails. */ }
            }, { once: true });
            return true;
        },
        swap: swapViews,
    };

    async function requestMedia() {
        if (!navigator.mediaDevices?.getUserMedia) throw new Error('Camera and microphone require HTTPS or localhost in a supported browser.');
        try {
            localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
        } catch (error) {
            if (error.name === 'NotFoundError' || error.name === 'OverconstrainedError') {
                localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                setHint('Camera unavailable. Joining with microphone audio.');
            } else {
                throw error;
            }
        }
        showStream(localStream, localVideo);
    }

    function setupPeer() {
        if (!window.RTCPeerConnection || !window.MediaStream) throw new Error('This browser does not support WebRTC video calls.');
        peer = new RTCPeerConnection({
            iceServers: [{ urls: 'stun:stun.l.google.com:19302' }],
        });
        remoteStream = new MediaStream();
        if (remoteVideo) remoteVideo.srcObject = remoteStream;
        localStream.getTracks().forEach((track) => peer.addTrack(track, localStream));
        peer.ontrack = (event) => {
            const incoming = event.streams[0];
            if (incoming) {
                remoteStream = incoming;
            } else {
                remoteStream.addTrack(event.track);
            }
            showStream(swapped ? localStream : remoteStream, swapped ? localVideo : remoteVideo);
        };
        peer.onicecandidate = (event) => {
            if (event.candidate) sendSignal('candidate', event.candidate.toJSON()).catch((error) => setStatus(error.message));
        };
        peer.onconnectionstatechange = () => {
            if (peer.connectionState === 'connected') {
                active = true;
                startTimer();
                setStatus('Connected');
                setHint('Live peer to peer video call. This call is not recorded.');
                $('[data-patient-connection]') && ($('[data-patient-connection]').innerHTML = '<i></i> Connection secure');
                if (role === 'doctor') $('[data-patient-name]') && ($('[data-patient-name]').textContent = 'Patient connected');
            } else if (['failed', 'disconnected'].includes(peer.connectionState)) {
                setStatus(peer.connectionState === 'failed' ? 'Connection failed' : 'Connection interrupted');
                setHint('Check the network and camera permissions. A TURN server may be needed on restricted networks.');
            }
        };
        if (role === 'doctor') handleDataChannel(peer.createDataChannel('consultation-chat'));
        else peer.ondatachannel = (event) => handleDataChannel(event.channel);
        controls.mic && (controls.mic.disabled = false);
        controls.camera && (controls.camera.disabled = false);
        controls.share && (controls.share.disabled = role !== 'doctor');
        controls.end && (controls.end.disabled = false);
    }

    async function flushCandidates() {
        for (const candidate of pendingCandidates.splice(0)) {
            try { await peer.addIceCandidate(candidate); } catch { /* Ignore expired or duplicate ICE candidates. */ }
        }
    }

    async function processSignal(signal) {
        if (signal.type === 'offer' && role === 'patient') {
            await peer.setRemoteDescription(signal.payload);
            await flushCandidates();
            const answer = await peer.createAnswer();
            await peer.setLocalDescription(answer);
            await sendSignal('answer', peer.localDescription.toJSON());
            setStatus('Connecting');
            return;
        }
        if (signal.type === 'answer' && role === 'doctor') {
            await peer.setRemoteDescription(signal.payload);
            await flushCandidates();
            setStatus('Connecting');
            return;
        }
        if (signal.type === 'candidate' && signal.payload) {
            if (!peer.remoteDescription) pendingCandidates.push(signal.payload);
            else {
                try { await peer.addIceCandidate(signal.payload); } catch { /* Ignore candidates from a stale connection. */ }
            }
            return;
        }
        if (signal.type === 'hangup') finishCall(false);
    }

    async function poll() {
        if (peer) {
            try {
                const result = await api('poll', { after: cursor });
                for (const signal of result.signals) {
                    cursor = Math.max(cursor, signal.id);
                    await processSignal(signal);
                }
                if (result.status === 'ended') finishCall(false);
            } catch (error) {
                setStatus(error.message);
                if (/expired|ended/i.test(error.message)) finishCall(false);
            }
        }
        if (peer) window.setTimeout(poll, 800);
    }

    async function startDoctorCall() {
        const patientEmail = $('[data-patient-email]')?.value.trim() || '';
        if (!patientEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(patientEmail)) {
            setStatus('Enter the email address on the patient account.');
            $('[data-patient-email]')?.focus();
            return;
        }
        controls.start.disabled = true;
        try {
            setStatus('Requesting camera and microphone…');
            await requestMedia();
            const created = await api('create', { patient_email: patientEmail });
            room = created.room;
            key = '';
            const inviteUrl = new URL(created.join_path, window.location.href);
            const invite = inviteUrl.href;
            const patientName = $('[data-patient-name]');
            if (patientName) patientName.textContent = created.patient_name;
            const infoStatus = $('[data-info-status]');
            if (infoStatus) infoStatus.textContent = 'Loading patient account details…';
            try {
                displayPatientInfo(await api('patient-info'));
            } catch (error) {
                if (infoStatus) infoStatus.textContent = error.message || 'Patient information could not be loaded.';
            }
            const inviteBox = $('[data-patient-invite]');
            const inviteInput = $('[data-invite-link]');
            if (inviteInput) inviteInput.value = invite;
            if (inviteBox) inviteBox.hidden = false;
            setupPeer();
            const offer = await peer.createOffer();
            await peer.setLocalDescription(offer);
            await sendSignal('offer', peer.localDescription.toJSON());
            setStatus('Waiting for patient to join');
            setHint('Share the private invite link with your patient.');
            poll();
        } catch (error) {
            localStream?.getTracks().forEach((track) => track.stop());
            localStream = null;
            controls.start.disabled = false;
            setStatus(error.message || 'Could not start the call.');
            setHint('Allow camera and microphone access, and confirm the telemedicine database migration has been installed.');
        }
    }

    async function joinPatientCall() {
        controls.join.disabled = true;
        try {
            await api('poll', { after: 0 });
            setStatus('Requesting camera and microphone…');
            await requestMedia();
            setupPeer();
            setStatus('Joining call');
            setHint('Connecting to your clinician.');
            updateChatAvailability(true);
            poll();
            window.history.replaceState({}, document.title, window.location.pathname);
        } catch (error) {
            controls.join.disabled = false;
            setStatus(error.message || 'Could not join the call.');
            setHint('Allow camera and microphone access. If the link expired, ask your clinician for a new one.');
        }
    }

    function finishCall(notifyPeer = true) {
        if (!peer && !localStream) return;
        if (notifyPeer && room && (key || role === 'patient')) {
            sendSignal('hangup').catch(() => {});
        }
        peer?.close();
        peer = null;
        localStream?.getTracks().forEach((track) => track.stop());
        displayStream?.getTracks().forEach((track) => track.stop());
        localStream = null;
        displayStream = null;
        active = true;
        if (timerHandle) window.clearInterval(timerHandle);
        timerHandle = 0;
        setStatus('Call ended');
        setHint('This consultation has ended.');
        if (controls.start) controls.start.disabled = true;
        if (controls.join) controls.join.disabled = true;
        Object.values(controls).filter(Boolean).forEach((button) => { if (button !== controls.start && button !== controls.join) button.disabled = true; });
    }

    controls.start?.addEventListener('click', startDoctorCall);
    controls.join?.addEventListener('click', joinPatientCall);
    controls.end?.addEventListener('click', () => {
        if (window.confirm('End this video consultation?')) finishCall(true);
    });
    $('[data-swap-views]')?.addEventListener('click', (event) => {
        if (!peer) return;
        event.stopImmediatePropagation();
        swapViews();
    }, true);

    const connectionControls = [
        ['mic', controls.mic, '[data-mic-label]', 'Mute mic', 'Unmute mic'],
        ['camera', controls.camera, '[data-camera-label]', 'Turn camera off', 'Turn camera on'],
        ['share', controls.share, '[data-share-label]', 'Share screen', 'Stop sharing'],
    ];
    connectionControls.forEach(([name, button, labelSelector, normal, toggled]) => {
        button?.addEventListener('click', async (event) => {
            if (!peer) return;
            event.stopImmediatePropagation();
            const label = button.querySelector(labelSelector);
            try {
                const state = name === 'share' ? await window.telemedicineControls.share() : window.telemedicineControls[name]();
                button.setAttribute('aria-pressed', String(state));
                if (label) label.textContent = state ? toggled : normal;
            } catch (error) {
                setHint(error.message);
            }
        }, true);
    });

    if (role === 'patient') {
        updateChatAvailability(false);
        const params = new URLSearchParams(window.location.search);
        room = room || params.get('room') || '';
        const fragment = new URLSearchParams(window.location.hash.replace(/^#/, ''));
        key = key || fragment.get('key') || '';
        window.history.replaceState({}, document.title, window.location.pathname + window.location.search);
        if (!room) setStatus('Invalid or expired consultation link');
    } else {
        updateChatAvailability(false);
        $('[data-copy-invite]')?.addEventListener('click', async () => {
            const input = $('[data-invite-link]');
            if (!input?.value) return;
            try {
                await navigator.clipboard.writeText(input.value);
                $('[data-copy-invite]').textContent = 'Copied';
            } catch {
                input.select();
                document.execCommand('copy');
                $('[data-copy-invite]').textContent = 'Copied';
            }
        });
    }

    $('[data-file-selection]')?.closest('.telemedicine-panel')?.querySelector('#telemedicine-file')?.addEventListener('change', async (event) => {
        const fileInput = event.currentTarget;
        const file = fileInput.files?.[0];
        const message = $('[data-file-selection]');
        if (!file) return;
        if (file.size > 8 * 1024 * 1024) {
            if (message) message.textContent = 'Files must be 8 MB or smaller.';
            fileInput.value = '';
            return;
        }
        if (dataChannel?.readyState !== 'open') {
            if (message) message.textContent = 'Join the call before sharing files.';
            fileInput.value = '';
            return;
        }
        try {
            if (message) message.textContent = `Sending ${file.name}…`;
            dataChannel.send(JSON.stringify({ type: 'file-start', name: file.name, mime: file.type, size: file.size }));
            const bytes = new Uint8Array(await file.arrayBuffer());
            const chunkSize = 16 * 1024;
            for (let offset = 0; offset < bytes.length; offset += chunkSize) {
                if (dataChannel.bufferedAmount > 512 * 1024) {
                    await new Promise((resolve) => dataChannel.addEventListener('bufferedamountlow', resolve, { once: true }));
                }
                dataChannel.send(bytes.slice(offset, offset + chunkSize));
            }
            dataChannel.send(JSON.stringify({ type: 'file-end' }));
            if (message) message.textContent = `${file.name} sent directly to the other participant.`;
            appendFile(file.name, file);
        } catch {
            if (message) message.textContent = 'File transfer failed. Try a smaller file.';
        } finally {
            fileInput.value = '';
        }
    });
})();
