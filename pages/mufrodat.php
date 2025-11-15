<?php
// pages/mufrodat.php
require_once '../config/database.php';
startSession();

// Cek login
if (!isLoggedIn()) {
    redirect('../index.php');
}

$user = getCurrentUser();
$user_id = $user['id'];

// Get mufrodat user
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM mufrodat WHERE user_id = ? ORDER BY unlocked_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$mufrodat_list = [];
while ($row = $result->fetch_assoc()) {
    $mufrodat_list[] = $row;
}
$stmt->close();
$conn->close();

$colors = ['red', 'blue', 'green', 'yellow', 'purple', 'pink', 'indigo', 'teal'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Arena Mufrodat - Earabic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Amiri:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #F8F6F2;
            padding-bottom: 100px;
        }
        
        .mufrodat-wrapper {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            padding: 2rem 1rem;
            position: relative;
            z-index: 10;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header-title {
            display: inline-block;
            position: relative;
            padding: 0.75rem 2rem;
            border-top: 2px solid #d1d5db;
            border-bottom: 2px solid #d1d5db;
        }
        
        .header-title h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: 2px;
            margin: 0;
        }
        
        .mufrodat-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .mufrodat-item {
            background-color: white;
            border-radius: 999px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            padding: 0.5rem;
            padding-right: 1rem;
            transition: all 0.3s;
        }
        
        .mufrodat-item:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }
        
        .mufrodat-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .icon-red { background-color: #fee2e2; color: #ef4444; }
        .icon-blue { background-color: #dbeafe; color: #3b82f6; }
        .icon-green { background-color: #dcfce7; color: #22c55e; }
        .icon-yellow { background-color: #fef3c7; color: #eab308; }
        .icon-purple { background-color: #f3e8ff; color: #a855f7; }
        .icon-pink { background-color: #fce7f3; color: #ec4899; }
        .icon-indigo { background-color: #e0e7ff; color: #6366f1; }
        .icon-teal { background-color: #ccfbf1; color: #14b8a6; }
        
        .mufrodat-icon span {
            font-family: 'Amiri', serif;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .mufrodat-content {
            flex: 1;
            margin: 0 1rem;
            text-align: right;
        }
        
        .arabic-word {
            font-family: 'Amiri', serif;
            font-size: 1.5rem;
            color: #1f2937;
            margin-bottom: 0.125rem;
        }
        
        .translation {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .audio-btn-small {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f3f4f6;
            border-radius: 50%;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            flex-shrink: 0;
        }
        
        .audio-btn-small:hover {
            background-color: #e0e7ff;
            color: #4f46e5;
            transform: scale(1.1);
        }
        
        .audio-btn-small.playing {
            background-color: #4f46e5;
            color: white;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .audio-btn-small i {
            font-size: 1.25rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }
        
        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 80px;
            border-radius: 1.5rem 1.5rem 0 0;
            z-index: 50;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            text-decoration: none;
            width: 33.333%;
            padding: 0.5rem;
            transition: color 0.3s;
        }
        
        .nav-item:hover {
            color: #4f46e5;
        }
        
        .nav-item.active {
            color: #4f46e5;
        }
        
        .nav-icon-bg {
            padding: 0.75rem;
            border-radius: 50%;
            transition: background-color 0.3s;
        }
        
        .nav-item.active .nav-icon-bg {
            background-color: #eef2ff;
        }
        
        .nav-icon {
            font-size: 1.75rem;
        }
        
        .bg-char {
            position: fixed;
            font-size: 6rem;
            opacity: 0.3;
            font-family: 'Amiri', serif;
            pointer-events: none;
        }
        
        @media (min-width: 640px) {
            .bg-char {
                font-size: 8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background Decorative Characters -->
    <div style="position: fixed; inset: 0; pointer-events: none; overflow: hidden; z-index: 0;">
        <span class="bg-char" style="top: 5%; left: 5%; transform: rotate(-15deg);">س</span>
        <span class="bg-char" style="top: 10%; right: 10%; transform: rotate(20deg);">ش</span>
        <span class="bg-char" style="top: 30%; right: -5%; transform: rotate(-10deg);">ق</span>
        <span class="bg-char" style="top: 50%; left: -10%; transform: rotate(15deg);">ف</span>
        <span class="bg-char" style="bottom: 30%; right: 5%; transform: rotate(25deg);">ع</span>
        <span class="bg-char" style="bottom: 10%; left: 20%; transform: rotate(-20deg);">ك</span>
    </div>

    <!-- Main Content -->
    <div class="mufrodat-wrapper">
        <div class="header-section">
            <div class="header-title">
                <h1>ARENA MUFRODAT</h1>
            </div>
        </div>

        <?php if (empty($mufrodat_list)): ?>
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <p>Belum ada mufrodat.</p>
                <p style="font-size: 0.875rem;">Selesaikan latihan untuk menambah koleksi!</p>
            </div>
        <?php else: ?>
            <div class="mufrodat-list">
                <?php foreach ($mufrodat_list as $index => $item): 
                    $color = $colors[$index % count($colors)];
                    $firstChar = mb_substr($item['arabic_word'], 0, 1);
                ?>
                <div class="mufrodat-item">
                    <div class="mufrodat-icon icon-<?php echo $color; ?>">
                        <span><?php echo $firstChar; ?></span>
                    </div>
                    <div class="mufrodat-content">
                        <p class="arabic-word"><?php echo htmlspecialchars($item['arabic_word']); ?></p>
                        <p class="translation"><?php echo htmlspecialchars($item['translation']); ?></p>
                    </div>
                    <button class="audio-btn-small" 
                            data-audio="<?php echo htmlspecialchars($item['audio_url']); ?>"
                            onclick="playAudio(this)">
                        <i class="fas fa-volume-up"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
            <div class="nav-icon-bg">
                <i class="fas fa-home nav-icon"></i>
            </div>
        </a>
        <a href="mufrodat.php" class="nav-item active">
            <div class="nav-icon-bg">
                <i class="fas fa-search nav-icon"></i>
            </div>
        </a>
        <a href="profile.php" class="nav-item">
            <div class="nav-icon-bg">
                <i class="fas fa-user nav-icon"></i>
            </div>
        </a>
    </nav>

    <script>
        let currentAudio = null;
        let currentButton = null;

        function playAudio(button) {
            const audioUrl = button.dataset.audio;
            
            // Jika tidak ada audio URL, skip
            if (!audioUrl || audioUrl === '') {
                alert('Audio tidak tersedia untuk mufrodat ini');
                return;
            }
            
            // Stop audio sebelumnya jika ada
            if (currentAudio) {
                currentAudio.pause();
                currentAudio = null;
                if (currentButton) {
                    currentButton.classList.remove('playing');
                }
            }
            
            // Play audio baru
            currentAudio = new Audio(audioUrl);
            currentButton = button;
            
            button.classList.add('playing');
            
            currentAudio.play().catch(error => {
                console.error('Error playing audio:', error);
                button.classList.remove('playing');
                alert('Gagal memutar audio. Coba lagi.');
            });
            
            currentAudio.onended = () => {
                button.classList.remove('playing');
                currentAudio = null;
                currentButton = null;
            };
            
            currentAudio.onerror = () => {
                button.classList.remove('playing');
                currentAudio = null;
                currentButton = null;
                alert('Audio tidak dapat dimuat');
            };
        }
    </script>
</body>
</html>