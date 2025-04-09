<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $group->title }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            font-family: sans-serif;
        }

        .container {
            display: flex;
            width: 100%;
        }

        .user-box {
            flex: 1;
            border: 1px solid #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        video {
            width: 80%;
            border-radius: 10px;
        }

        .start-camera-btn,
        .stop-camera-btn {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        .start-camera-btn:disabled,
        .stop-camera-btn:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    @if(count($users) === 2)
        <div class="container">
            @foreach($users as $user)
                <div class="user-box">
                    <h2>{{ $user->name }}</h2>

                    <button class="start-camera-btn" data-user-id="{{ $user->id }}" id="start-{{ $user->id }}">Turn On
                        Camera</button>
                    <button class="stop-camera-btn" data-user-id="{{ $user->id }}" id="stop-{{ $user->id }}" disabled>Stop
                        Camera</button>

                    <video autoplay playsinline id="video-{{ $user->id }}" muted></video>
                </div>
            @endforeach
        </div>
    @else
        <div style="margin: auto; text-align: center;">
            <h1>Waiting for more users to join...</h1>
            <p>Current users in group: {{ count($users) }}</p>
        </div>
    @endif

    <script>

        const urlParams = new URLSearchParams(window.location.search);
        const userId = urlParams.get('user');

        console.log("Extracted userId from URL:", userId);


        if (userId) {
            localStorage.setItem('my_user_id', userId);
            console.log("Stored userId in localStorage:", localStorage.getItem('my_user_id'));
        } else {
            console.log("No user ID found in URL");
        }

        const myUserId = localStorage.getItem('my_user_id');
        console.log("My User ID from localStorage:", myUserId);

        document.querySelectorAll('.start-camera-btn').forEach(button => {
            const userId = button.getAttribute('data-user-id');
            if (userId !== myUserId) {
                button.style.display = 'none';
                document.getElementById(`stop-${userId}`).style.display = 'none';
            }
        });

        const cameraButtons = document.querySelectorAll('.start-camera-btn');
        const stopButtons = document.querySelectorAll('.stop-camera-btn');
        let currentStream = null;

        cameraButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-user-id');
                if (userId == myUserId) {
                    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                        .then(stream => {
                            const myVideo = document.getElementById(`video-${userId}`);
                            if (myVideo) {
                                myVideo.srcObject = stream;
                                currentStream = stream;
                            }

                            this.disabled = true;
                            const stopButton = document.querySelector(`#stop-${userId}`);
                            if (stopButton) {
                                stopButton.disabled = false;
                            }
                        })
                        .catch(error => {
                            alert('Camera access denied: ' + error.message);
                        });
                }
            });
        });

        stopButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-user-id');
                if (userId == myUserId && currentStream) {
                    const tracks = currentStream.getTracks();
                    tracks.forEach(track => track.stop());

                    const myVideo = document.getElementById(`video-${userId}`);
                    if (myVideo) {
                        myVideo.srcObject = null;
                    }

                    const startButton = document.querySelector(`#start-${userId}`);
                    if (startButton) {
                        startButton.disabled = false;
                    }

                    this.disabled = true;
                }
            });
        });
    </script>

</body>

</html>