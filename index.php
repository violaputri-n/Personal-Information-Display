<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Information Display - AR Face Recognition</title>
    <script src="js/face-api.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }

        body, html {
            margin: 0; padding: 0;
            width: 100%; height: 100%;
            overflow: hidden;
            background-color: #1a001a;
            font-family: 'Nunito', 'Segoe UI', sans-serif;
        }

        /* ====== LOADING SCREEN ====== */
        #loading-screen {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(135deg, #ff6eb4 0%, #ff9ed2 40%, #ffcce8 70%, #ffe0f4 100%);
            z-index: 99;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #7a0050;
        }

        /* Stiker dekoratif loading */
        #loading-screen::before {
            content: '🎀 ✨ 🌸 💕 🌷 ✨ 🎀';
            position: absolute;
            top: 30px;
            font-size: 22px;
            letter-spacing: 8px;
            opacity: 0.6;
            animation: floatSticker 2s ease-in-out infinite alternate;
        }
        #loading-screen::after {
            content: '💅 🦋 🌙 🍓 🌺 🦋 💅';
            position: absolute;
            bottom: 30px;
            font-size: 22px;
            letter-spacing: 8px;
            opacity: 0.6;
            animation: floatSticker 2.4s ease-in-out infinite alternate-reverse;
        }

        @keyframes floatSticker {
            from { transform: translateY(0); }
            to { transform: translateY(-8px); }
        }

        .loading-box {
            background: rgba(255,255,255,0.5);
            border: 2.5px solid #ff80bf;
            border-radius: 28px;
            padding: 40px 50px;
            text-align: center;
            box-shadow: 0 8px 40px rgba(255, 100, 180, 0.3);
            max-width: 380px;
            width: 90%;
        }

        .loading-emoji {
            font-size: 52px;
            margin-bottom: 12px;
            display: block;
            animation: bounceEmoji 1.2s ease-in-out infinite alternate;
        }
        @keyframes bounceEmoji {
            from { transform: scale(1); }
            to { transform: scale(1.15) rotate(-5deg); }
        }

        .loading-title {
            font-size: 22px;
            font-weight: 800;
            color: #c2006a;
            margin-bottom: 6px;
        }

        #loading-text {
            font-size: 14px;
            font-weight: 600;
            color: #7a0050;
            margin-bottom: 24px;
            min-height: 20px;
        }

        /* Spinner girly */
        .spinner-wrap {
            position: relative;
            width: 60px; height: 60px;
            margin: 0 auto 10px;
        }
        .spinner {
            width: 60px; height: 60px;
            border: 5px solid rgba(255, 160, 210, 0.3);
            border-top: 5px solid #ff4daa;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }
        .spinner-center {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-size: 18px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Progress dots */
        .loading-dots {
            display: flex; gap: 8px;
            justify-content: center;
            margin-top: 18px;
        }
        .loading-dots span {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #ff80bf;
            animation: dotPulse 1.2s ease-in-out infinite;
        }
        .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loading-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes dotPulse {
            0%, 80%, 100% { transform: scale(0.7); opacity: 0.5; }
            40% { transform: scale(1.2); opacity: 1; }
        }

        /* ====== VIDEO CONTAINER ====== */
        #webcam-container {
            position: relative;
            width: 100%; height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        video {
            position: absolute;
            width: 100%; height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        canvas {
            position: absolute;
            width: 100%; height: 100%;
            object-fit: cover;
            z-index: 2;
            pointer-events: none;
        }

        /* Stiker pojok dekoratif */
        .corner-sticker {
            position: absolute;
            z-index: 4;
            font-size: 28px;
            pointer-events: none;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
            animation: floatSticker 3s ease-in-out infinite alternate;
        }
        .corner-sticker.tl { top: 16px; left: 16px; animation-delay: 0s; }
        .corner-sticker.tr { top: 16px; right: 16px; animation-delay: 0.5s; }
        .corner-sticker.bl { bottom: 16px; left: 16px; animation-delay: 1s; }
        .corner-sticker.br { bottom: 16px; right: 16px; animation-delay: 1.5s; }

        /* Watermark cute di atas */
        .top-bar {
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 4;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 20px;
            background: linear-gradient(180deg, rgba(255,105,180,0.55) 0%, transparent 100%);
            pointer-events: none;
        }
        .top-bar-title {
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 2px;
            text-shadow: 0 1px 8px rgba(200,0,100,0.5);
        }

        /* ====== AR OVERLAY LAYER ====== */
        #ar-overlay-layer {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 3;
            pointer-events: none;
        }

        /* ====== AR CARD GIRLY ====== */
        .ar-card {
            position: absolute;
            z-index: 9999;
            background: rgba(255, 235, 248, 0.92);
            border: 2.5px solid #ff69b4;
            border-radius: 22px;
            padding: 16px 18px;
            color: #7a004a;
            width: 270px;
            pointer-events: auto;
            transition: opacity 0.25s ease, transform 0.15s ease-out;
            opacity: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            box-shadow:
                0 0 0 3px rgba(255, 105, 180, 0.18),
                0 8px 30px rgba(255, 80, 160, 0.22);
        }
        .ar-card.active { opacity: 1; }

        /* Stiker di pojok card */
        .ar-card::before {
            content: '✨';
            position: absolute;
            top: -14px; right: -10px;
            font-size: 22px;
            filter: drop-shadow(0 1px 3px rgba(255,100,180,0.6));
            animation: floatSticker 2s ease-in-out infinite alternate;
        }
        .ar-card::after {
            content: '🌸';
            position: absolute;
            bottom: -12px; left: -8px;
            font-size: 20px;
            filter: drop-shadow(0 1px 3px rgba(255,100,180,0.6));
            animation: floatSticker 2.5s ease-in-out infinite alternate-reverse;
        }

        /* Header card */
        .ar-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 1.5px dashed rgba(255, 105, 180, 0.45);
            position: relative;
        }

        .ar-card-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .ar-card-avatar {
            width: 56px; height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ff69b4;
            box-shadow: 0 0 0 3px rgba(255,105,180,0.2);
            display: block;
        }
        /* Ring animasi di avatar */
        .ar-card-avatar-wrap::after {
            content: '';
            position: absolute;
            top: -4px; left: -4px;
            width: calc(100% + 8px);
            height: calc(100% + 8px);
            border-radius: 50%;
            border: 2px solid rgba(255, 105, 180, 0.5);
            border-top-color: #ff4daa;
            animation: spin 2.5s linear infinite;
        }

        .ar-card-name-area { flex: 1; min-width: 0; }
        .ar-card-name {
            font-weight: 800;
            font-size: 15px;
            color: #c2006a;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ar-nim-badge {
            display: inline-block;
            background: linear-gradient(90deg, #ffb3d9, #ff80bf);
            color: #fff;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        /* Body card */
        .ar-card-body {
            font-size: 12.5px;
            color: #7a004a;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .ar-card-row {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 182, 215, 0.18);
            border-radius: 10px;
            padding: 5px 9px;
        }
        .ar-card-icon {
            font-size: 15px;
            flex-shrink: 0;
        }
        .ar-card-label {
            color: #c0558a;
            font-weight: 700;
            font-size: 11px;
            white-space: nowrap;
            min-width: 36px;
        }
        .ar-card-value {
            color: #7a004a;
            font-size: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Footer card */
        .ar-card-footer {
            text-align: center;
            font-size: 10.5px;
            color: #d46fa0;
            font-weight: 700;
            letter-spacing: 1px;
            padding-top: 4px;
            border-top: 1.5px dashed rgba(255, 105, 180, 0.35);
        }
    </style>
</head>
<body>

<!-- ===== LOADING SCREEN ===== -->
<div id="loading-screen">
    <div class="loading-box">
        <span class="loading-emoji">🌸</span>
        <div class="loading-title">AI Face ✨ Scan</div>
        <p id="loading-text">Menyiapkan AI Engine...</p>
        <div class="spinner-wrap">
            <div class="spinner"></div>
            <span class="spinner-center">💕</span>
        </div>
        <div class="loading-dots">
            <span></span><span></span><span></span>
        </div>
    </div>
</div>

<!-- ===== MAIN APP ===== -->
<div id="webcam-container">
    <video id="webcam" autoplay muted playsinline></video>
    <canvas id="overlay-canvas"></canvas>

    <!-- Stiker pojok dekoratif -->
    <div class="corner-sticker tl">🎀</div>
    <div class="corner-sticker tr">💫</div>
    <div class="corner-sticker bl">🌷</div>
    <div class="corner-sticker br">🍓</div>

    <!-- Top bar -->
    <div class="top-bar">
        <span class="top-bar-title">🌸 AR FACE ID 💕</span>
    </div>

    <!-- AR Card Layer -->
    <div id="ar-overlay-layer">
        <div id="personal-ar-card" class="ar-card">
            <div class="ar-card-header">
                <div class="ar-card-avatar-wrap">
                    <img id="ar-avatar" src="" class="ar-card-avatar" alt="User">
                </div>
                <div class="ar-card-name-area">
                    <div id="ar-nama" class="ar-card-name">Memuat Wajah...</div>
                    <span id="ar-nim" class="ar-nim-badge">NIM: -</span>
                </div>
            </div>

            <div class="ar-card-body">
                <div class="ar-card-row">
                    <span class="ar-card-icon">🎓</span>
                    <span class="ar-card-label">Prodi</span>
                    <span id="ar-prodi" class="ar-card-value">-</span>
                </div>
                <div class="ar-card-row">
                    <span class="ar-card-icon">💌</span>
                    <span class="ar-card-label">Email</span>
                    <span id="ar-email" class="ar-card-value">-</span>
                </div>
                <div class="ar-card-row">
                    <span class="ar-card-icon">📱</span>
                    <span class="ar-card-label">No HP</span>
                    <span id="ar-nohp" class="ar-card-value">-</span>
                </div>
            </div>

            <div class="ar-card-footer">✨ terverifikasi ✨</div>
        </div>
    </div>
</div>

<script>
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('overlay-canvas');
    const arCard = document.getElementById('personal-ar-card');
    let faceMatcher = null;

    async function startApplication() {
        try {
            document.getElementById('loading-text').innerText = "Memuat Neural Network Wajah...";
            await faceapi.nets.ssdMobilenetv1.loadFromUri('models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('models');

            document.getElementById('loading-text').innerText = "Mengunduh Database Pengguna...";
            await loadUsersFromDatabase();

            document.getElementById('loading-text').innerText = "Membuka Akses Kamera...";
            startVideo();
        } catch (error) {
            alert("Gagal menginisialisasi sistem: " + error.message);
            console.error(error);
        }
    }

    async function loadUsersFromDatabase() {
        const response = await fetch('api/get_users.php');
        const result = await response.json();

        if (result.status !== 'success' || result.data.length === 0) {
            throw new Error("Gagal memuat dataset wajah atau database kosong.");
        }

        const labeledDescriptors = [];

        result.data.forEach(user => {
            if (user.descriptor && user.descriptor.length === 128) {
                const floatArray = new Float32Array(user.descriptor);
                const labelInfo = JSON.stringify({
                    nama: user.nama,
                    nim: user.nim,
                    prodi: user.prodi,
                    email: user.email,
                    no_hp: user.no_hp,
                    foto: user.foto
                });
                labeledDescriptors.push(new faceapi.LabeledFaceDescriptors(labelInfo, [floatArray]));
            }
        });

        if (labeledDescriptors.length > 0) {
            faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);
        }
    }

    function startVideo() {
        navigator.mediaDevices.getUserMedia({ video: { width: 1280, height: 720 } })
            .then(stream => {
                video.srcObject = stream;
                video.addEventListener('playing', onVideoPlaying);
            })
            .catch(err => {
                alert("Kamera diblokir atau tidak ditemukan.");
                console.error(err);
            });
    }

    function onVideoPlaying() {
        document.getElementById('loading-screen').style.display = 'none';

        const displaySize = { width: video.videoWidth, height: video.videoHeight };
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
            const detections = await faceapi.detectAllFaces(video)
                                            .withFaceLandmarks()
                                            .withFaceDescriptors();

            const resizedDetections = faceapi.resizeResults(detections, displaySize);

            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (resizedDetections.length > 0 && faceMatcher) {
                const bestMatch = resizedDetections[0];
                const matchResult = faceMatcher.findBestMatch(bestMatch.descriptor);

                const { x, y, width, height } = bestMatch.detection.box;

                /* Kotak wajah girly: sudut bulat + warna pink */
                const r = 14;
                ctx.strokeStyle = '#ff69b4';
                ctx.lineWidth = 3;
                ctx.shadowColor = 'rgba(255,105,180,0.7)';
                ctx.shadowBlur = 10;
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.lineTo(x + width - r, y);
                ctx.quadraticCurveTo(x + width, y, x + width, y + r);
                ctx.lineTo(x + width, y + height - r);
                ctx.quadraticCurveTo(x + width, y + height, x + width - r, y + height);
                ctx.lineTo(x + r, y + height);
                ctx.quadraticCurveTo(x, y + height, x, y + height - r);
                ctx.lineTo(x, y + r);
                ctx.quadraticCurveTo(x, y, x + r, y);
                ctx.closePath();
                ctx.stroke();
                ctx.shadowBlur = 0;

                try {
    console.log(matchResult);

    if (matchResult.label !== "unknown") {

        let userData;

        try {
            userData = JSON.parse(matchResult.label);
        } catch {
            userData = {
                nama: matchResult.label,
                nim: "-",
                prodi: "-",
                email: "-",
                no_hp: "-",
                foto: "unknown.png"
            };
        }

        updateArCard(userData, x, y, width);

    } else {

        updateArCard({
            nama: "Unknown Person",
            nim: "-",
            prodi: "-",
            email: "-",
            no_hp: "-",
            foto: "unknown.png"
        }, x, y, width);

    }

} catch(err) {
    console.log("ERROR CARD:", err);
}
            } else {
                arCard.classList.remove('active');
            }
        }, 100);
    }

    function updateArCard(data, faceX, faceY, faceWidth) {

    document.getElementById('ar-nama').innerText = data.nama;
    document.getElementById('ar-nim').innerText =
        "NIM: " + data.nim;

    document.getElementById('ar-prodi').innerText =
        data.prodi;

    document.getElementById('ar-email').innerText =
        data.email;

    document.getElementById('ar-nohp').innerText =
        data.no_hp;

    if (data.foto === "unknown.png") {

        document.getElementById('ar-avatar').src =
            "https://ui-avatars.com/api/?name=Unknown";

    } else {

        document.getElementById('ar-avatar').src =
            "uploads/" + data.foto;

    }

    const cardWidth = 270;

    let cardX = faceX + faceWidth + 15;
    let cardY = faceY;

    if (cardX + cardWidth > window.innerWidth) {
        cardX = faceX - cardWidth - 15;
    }

    arCard.style.left = `${cardX}px`;
    arCard.style.top = `${cardY}px`;

    arCard.style.display = "flex";
    arCard.style.opacity = "1";
    arCard.style.visibility = "visible";

    arCard.classList.add("active");

    console.log("CARD MUNCUL", data);
}

    window.onload = startApplication;
</script>
</body>
</html>