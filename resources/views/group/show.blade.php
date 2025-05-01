<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group Video Call</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: sans-serif;
            background: #f0f2f5;
        }
        
        #header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        #user-count {
            background: #4CAF50;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        #video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .video-container {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        video {
            width: 100%;
            border-radius: 4px;
            background: #222;
            transform: scaleX(-1);
        }
        
        .user-name {
            font-weight: bold;
            margin: 10px 0 5px;
        }
        
        .status {
            font-size: 13px;
            color: #666;
        }
        
        #controls {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .control-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 16px;
        }
        
        #start-btn {
            background: #4CAF50;
            color: white;
        }
        
        #stop-btn {
            background: #f44336;
            color: white;
            display: none;
        }
        
        .connection-status {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div id="header">
        <h1>Group Video Call</h1>
        <div id="user-count">Users: {{ count($users) }}</div>
    </div>
    
    <div id="video-grid">
        @foreach($users as $user)
        <div class="video-container" id="box-{{ $user->id }}">
            <div class="user-name">{{ $user->id == auth()->id() ? 'You' : $user->name }}</div>
            <video autoplay playsinline id="video-{{ $user->id }}"></video>
            <div class="status" id="status-{{ $user->id }}">
                {{ $user->id == auth()->id() ? 'Ready' : 'Offline' }}
            </div>
            <div class="connection-status" id="connection-{{ $user->id }}">-</div>
        </div>
        @endforeach
    </div>
    
    <div id="controls">
        <button class="control-btn" id="start-btn">START VIDEO</button>
        <button class="control-btn" id="stop-btn">STOP VIDEO</button>
    </div>

    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    
    <script>
        // 1. CONFIGURATION ======================================
        const GROUP_ID = 8;
        const MY_USER_ID = String(localStorage.getItem("my_user_id"));
        const OTHER_USERS = ['1', '16'].filter(id => id !== MY_USER_ID);
        const processedSignals = new Set();

        console.log(`MY_USER_ID: ${MY_USER_ID}, type: ${typeof MY_USER_ID}`);
        console.log(`OTHER_USERS: ${JSON.stringify(OTHER_USERS)}`);

        // 2. STATE MANAGEMENT ===================================
        let localStream = null;
        const peerConnections = {};

        // 3. WEBRTC CONFIGURATION ===============================
        const rtcConfig = {
            iceServers: [
                { urls: "stun:stun.l.google.com:19302" },
                { urls: "stun:stun1.l.google.com:19302" },
                {
                    urls: "turn:openrelay.metered.ca:80",
                    username: "openrelayproject",
                    credential: "openrelayproject"
                }
            ]
        };

        // SDP Sanitization Function - More conservative approach
        function sanitizeSdp(sdp) {
            if (!sdp) return sdp;
            
            // Remove problematic lines but keep essential ones
            const lines = sdp.split('\r\n');
            const allowedLines = lines.filter(line => {
                // Keep important lines
                if (line.startsWith('v=') || 
                    line.startsWith('o=') || 
                    line.startsWith('s=') || 
                    line.startsWith('t=') || 
                    line.startsWith('m=') || 
                    line.startsWith('c=') || 
                    line.startsWith('a=')) {
                    
                    // Filter out specific problematic attributes
                    if (line.includes('ulpfec') || 
                        line.includes('flexfec') || 
                        line.includes('rtx') ||
                        line.includes('apt=')) {
                        return false;
                    }
                    return true;
                }
                return false;
            });
            
            return allowedLines.join('\r\n') + '\r\n';
        }

        // 4. PUSHER/ECHO SETUP =================================
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '6a43af93d7b7735f2b06',
            cluster: 'eu',
            forceTLS: true
        });

        // 5. MAIN FUNCTIONS =====================================
        async function startVideo() {
            try {
                localStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 1280 }, height: { ideal: 720 }, facingMode: "user" },
                    audio: false
                });

                console.log("Local stream tracks:", localStream.getTracks());
                console.log("Local stream active:", localStream.active, localStream.getVideoTracks()[0].enabled);

                const myVideo = document.getElementById(`video-${MY_USER_ID}`);
                if (myVideo) {
                    myVideo.srcObject = localStream;
                    myVideo.muted = true;
                    myVideo.play().catch(e => console.error("Local video play error:", e));
                }

                document.getElementById('start-btn').style.display = 'none';
                document.getElementById('stop-btn').style.display = 'block';

                updateStatus(MY_USER_ID, "Camera active");

                console.log(`Starting peer connections for OTHER_USERS: ${JSON.stringify(OTHER_USERS)}`);
                OTHER_USERS.forEach(userId => {
                    if (String(userId) !== String(MY_USER_ID)) {
                        createPeerConnection(userId);
                    } else {
                        console.log(`Skipping peer connection for self: ${userId}`);
                    }
                });
                setupSignaling();

            } catch (error) {
                console.error("Camera error:", error);
                alert("Could not access camera: " + error.message);
                updateStatus(MY_USER_ID, "Camera error");
            }
        }

        function stopVideo() {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                const myVideo = document.getElementById(`video-${MY_USER_ID}`);
                if (myVideo) myVideo.srcObject = null;

                Object.values(peerConnections).forEach(pc => pc.close());
                Object.keys(peerConnections).forEach(key => delete peerConnections[key]);

                document.getElementById('start-btn').style.display = 'block';
                document.getElementById('stop-btn').style.display = 'none';

                updateStatus(MY_USER_ID, "Camera off");

                OTHER_USERS.forEach(userId => {
                    updateStatus(userId, "Offline");
                    updateConnectionStatus(userId, "disconnected");
                    const video = document.getElementById(`video-${userId}`);
                    if (video) video.srcObject = null;
                });
            }
        }

        function createPeerConnection(userId) {
            if (peerConnections[userId]) {
                console.log(`Peer connection for ${userId} already exists`);
                return;
            }

            console.log(`Creating peer connection for user ${userId}`);
            const pc = new RTCPeerConnection(rtcConfig);
            peerConnections[userId] = pc;

            if (localStream) {
                localStream.getTracks().forEach(track => {
                    pc.addTrack(track, localStream);
                    console.log(`Added track to ${userId}:`, track);
                });
            }

            pc.ontrack = (event) => {
                console.log(`Received track from user ${userId}`, event.streams, event.streams[0].getTracks());
                const video = document.getElementById(`video-${userId}`);
                if (video && !video.srcObject) {
                    video.srcObject = event.streams[0];
                    video.play().catch(e => console.error(`Video play error for ${userId}:`, e));
                    video.addEventListener('click', () => video.play());
                    updateStatus(userId, "Connected");
                    updateConnectionStatus(userId, "connected");
                }
            };

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    console.log(`Sending ICE candidate to ${userId}`, event.candidate);
                    sendSignal({
                        type: 'candidate',
                        candidate: event.candidate.toJSON(),
                        from: MY_USER_ID,
                        to: userId,
                        group_id: GROUP_ID
                    });
                }
            };

            pc.onconnectionstatechange = () => {
                console.log(`Connection state with ${userId}: ${pc.connectionState}`);
                updateConnectionStatus(userId, pc.connectionState);
                if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed') {
                    const video = document.getElementById(`video-${userId}`);
                    if (video) video.srcObject = null;
                    updateStatus(userId, "Disconnected");
                }
            };

            pc.onnegotiationneeded = async () => {
                if (pc.signalingState !== 'stable') {
                    console.log(`Negotiation needed with ${userId}, but signaling state is ${pc.signalingState}. Deferring.`);
                    return;
                }
                try {
                    console.log(`Negotiation needed with ${userId}`);
                    const offer = await pc.createOffer({
                        offerToReceiveVideo: true
                    });
                    
                    // Use original SDP first
                    await pc.setLocalDescription(offer);
                    
                    // Then send the sanitized version
                    const sanitizedSdp = sanitizeSdp(offer.sdp);
                    console.log(`Sanitized offer SDP for ${userId}:`, sanitizedSdp);
                    
                    sendSignal({
                        type: 'offer',
                        offer: { type: 'offer', sdp: sanitizedSdp },
                        from: MY_USER_ID,
                        to: userId,
                        group_id: GROUP_ID
                    });
                } catch (error) {
                    console.error("Negotiation error:", error);
                }
            };
        }

        function setupSignaling() {
            console.log(`Subscribing to group.${GROUP_ID} for user ${MY_USER_ID}`);
            window.Echo.channel(`group.${GROUP_ID}`)
                .listen('WebRtcOfferEvent', async (event) => {
                    const signalKey = `${event.from}-${event.to}-${event.type}-${JSON.stringify(event.offer || event.answer || event.candidate)}`;
                    if (processedSignals.has(signalKey)) {
                        console.log(`Ignoring duplicate signal: ${signalKey}`);
                        return;
                    }
                    processedSignals.add(signalKey);

                    console.log(`Signal received by ${MY_USER_ID} from ${event.from}:`, event);
                    if (event.from === MY_USER_ID || event.to !== MY_USER_ID) {
                        console.log(`Ignoring signal: from=${event.from}, to=${event.to}, MY_USER_ID=${MY_USER_ID}`);
                        return;
                    }

                    if (!peerConnections[event.from]) {
                        createPeerConnection(event.from);
                    }

                    const pc = peerConnections[event.from];

                    try {
                        switch (event.type) {
                            case 'offer':
                                console.log("Received offer from", event.from, "Original SDP:", event.offer.sdp);
                                if (!event.offer || !event.offer.type || !event.offer.sdp) {
                                    console.error("Invalid offer received:", event.offer);
                                    return;
                                }
                                
                                // Use the sanitized SDP for setting remote description
                                const sanitizedOfferSdp = sanitizeSdp(event.offer.sdp);
                                console.log("Sanitized offer SDP:", sanitizedOfferSdp);
                                
                                await pc.setRemoteDescription(new RTCSessionDescription({
                                    type: event.offer.type,
                                    sdp: sanitizedOfferSdp
                                }));
                                
                                const answer = await pc.createAnswer();
                                await pc.setLocalDescription(answer);
                                
                                // Send sanitized answer
                                const sanitizedAnswerSdp = sanitizeSdp(answer.sdp);
                                console.log("Sanitized answer SDP:", sanitizedAnswerSdp);
                                
                                sendSignal({
                                    type: 'answer',
                                    answer: { type: 'answer', sdp: sanitizedAnswerSdp },
                                    from: MY_USER_ID,
                                    to: event.from,
                                    group_id: GROUP_ID
                                });
                                break;

                            case 'answer':
                                console.log("Received answer from", event.from, "Original SDP:", event.answer.sdp);
                                if (!event.answer || !event.answer.type || !event.answer.sdp) {
                                    console.error("Invalid answer received:", event.answer);
                                    return;
                                }
                                
                                // const sanitizedAnswerSdp = sanitizeSdp(event.answer.sdp);
                                console.log("Sanitized answer SDP:", sanitizedAnswerSdp);
                                
                                await pc.setRemoteDescription(new RTCSessionDescription({
                                    type: event.answer.type,
                                    sdp: sanitizedAnswerSdp
                                }));
                                break;

                            case 'candidate':
                                console.log("Received ICE candidate from", event.from, "Candidate:", event.candidate);
                                if (pc.remoteDescription && event.candidate && event.candidate.candidate) {
                                    try {
                                        await pc.addIceCandidate(new RTCIceCandidate(event.candidate));
                                    } catch (e) {
                                        console.error("Error adding ICE candidate:", e);
                                    }
                                } else {
                                    console.log("Skipping candidate: remoteDescription not set or candidate invalid");
                                }
                                break;
                        }
                    } catch (error) {
                        console.error("Signal handling error:", error);
                    }
                });
        }

        function sendSignal(data) {
            console.log("Sending signal:", data);
            fetch(`/api/send-signal/${data.to}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => console.log("Signal sent:", data))
            .catch(err => console.error("Signal send error:", err));
        }

        function updateStatus(userId, message) {
            const element = document.getElementById(`status-${userId}`);
            if (element) {
                element.textContent = message;
                element.style.color = message.includes("Connected") || message.includes("active") ? "#4CAF50" :
                                     message.includes("Error") || message.includes("Failed") ? "#f44336" : "#666";
            }
        }

        function updateConnectionStatus(userId, status) {
            const element = document.getElementById(`connection-${userId}`);
            if (element) {
                element.textContent = status;
                element.style.background = status === "connected" ? "rgba(76, 175, 80, 0.7)" :
                                          status === "disconnected" ? "rgba(244, 67, 54, 0.7)" :
                                          "rgba(0, 0, 0, 0.7)";
            }
        }

        // 6. INITIALIZE EVENT LISTENERS =========================
        document.addEventListener('DOMContentLoaded', () => {
            const startBtn = document.getElementById('start-btn');
            const stopBtn = document.getElementById('stop-btn');

            if (startBtn) {
                startBtn.addEventListener('click', startVideo);
            } else {
                console.error("Start button not found!");
            }

            if (stopBtn) {
                stopBtn.addEventListener('click', stopVideo);
            } else {
                console.error("Stop button not found!");
            }
        });

        // 7. CLEANUP ON EXIT ====================================
        window.addEventListener('beforeunload', () => {
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }
            Object.values(peerConnections).forEach(pc => pc.close());
        });
    </script>
</body>
</html>