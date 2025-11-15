<?php
// pages/progress.php
require_once '../config/database.php';
startSession();

// Cek login
if (!isLoggedIn()) {
    redirect('../index.php');
}

$user = getCurrentUser();
$conn = getConnection();

// Hitung total progress user
$user_id = $user['id'];
$stmt = $conn->prepare("SELECT COUNT(*) as total_completed FROM user_progress WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$progress_result = $stmt->get_result()->fetch_assoc();
$total_completed = $progress_result['total_completed'];
$cumulative_progress = min(100, ($total_completed / 40) * 100); // 40 = total semua level & tema
$stmt->close();

// Get leaderboard data
$leaderboard_query = "
    SELECT u.id, u.nama, u.avatar_initial, u.motivasi, 
           COUNT(up.id) * 25 as xp,
           (COUNT(up.id) / 40) * 100 as progress
    FROM users u
    LEFT JOIN user_progress up ON u.id = up.user_id
    GROUP BY u.id
    ORDER BY xp DESC
    LIMIT 10
";
$leaderboard_result = $conn->query($leaderboard_query);
$leaderboard = [];
while ($row = $leaderboard_result->fetch_assoc()) {
    $leaderboard[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Progress - Earabic</title>
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
        
        .progress-wrapper {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
            position: relative;
            z-index: 10;
        }
        
        .header-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .user-info h2 {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .user-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .progress-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .progress-card h2 {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
        }
        
        .circular-progress {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
        }
        
        .circular-progress svg {
            transform: rotate(-90deg);
        }
        
        .progress-bg {
            fill: none;
            stroke: #e5e7eb;
            stroke-width: 10;
        }
        
        .progress-bar-circle {
            fill: none;
            stroke: #4f46e5;
            stroke-width: 10;
            stroke-linecap: round;
            transition: stroke-dashoffset 1.5s cubic-bezier(0.25, 1, 0.5, 1);
        }
        
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: 700;
            color: #4f46e5;
        }
        
        .progress-card p {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .map-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .level-path {
            position: relative;
        }
        
        .level-line {
            position: absolute;
            left: 28px;
            top: 60px;
            width: 2px;
            height: calc(100% - 120px);
            background: linear-gradient(to bottom, #d1d5db 0%, #d1d5db 100%);
            background-size: 2px 8px;
            background-repeat: repeat-y;
        }
        
        .level-item {
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            z-index: 10;
            text-decoration: none;
            color: inherit;
        }
        
        .level-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 2rem;
            transition: transform 0.3s;
        }
        
        .level-item:hover .level-icon {
            transform: scale(1.05);
        }
        
        .level-icon.completed {
            background-color: #22c55e;
            color: white;
            box-shadow: 0 0 0 4px #dcfce7;
        }
        
        .level-icon.active {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 0 0 4px #e0e7ff;
            animation: pulse 2s infinite;
        }
        
        .level-icon.locked {
            background-color: #d1d5db;
            color: #9ca3af;
            box-shadow: 0 0 0 4px #f3f4f6;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .level-info {
            flex: 1;
        }
        
        .level-status {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .level-status.completed { color: #22c55e; }
        .level-status.active { color: #4f46e5; }
        .level-status.locked { color: #6b7280; }
        
        .level-title {
            font-weight: 700;
            font-size: 1rem;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .level-desc {
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.4;
        }
        
        .leaderboard-item {
            background-color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .leaderboard-item.current-user {
            background-color: #4338ca;
            color: white;
            box-shadow: 0 4px 12px rgba(67, 56, 202, 0.3);
        }
        
        .rank-badge {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.25rem;
        }
        
        .user-avatar-small {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        
        .leaderboard-info {
            flex: 1;
            min-width: 0;
        }
        
        .leaderboard-name {
            font-weight: 700;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .leaderboard-quote {
            font-size: 0.75rem;
            font-style: italic;
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }
        
        .leaderboard-xp {
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .progress-bar-mini {
            width: 100%;
            height: 6px;
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 999px;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        
        .progress-fill-mini {
            height: 100%;
            background-color: white;
            border-radius: 999px;
            transition: width 1s ease;
        }
        
        .leaderboard-item:not(.current-user) .progress-bar-mini {
            background-color: #e5e7eb;
        }
        
        .leaderboard-item:not(.current-user) .progress-fill-mini {
            background-color: #4f46e5;
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
    <div class="progress-wrapper">
        <!-- Header -->
        <div class="header-section">
            <div class="user-info">
                <h2>Selamat datang kembali,</h2>
                <h1><?php echo htmlspecialchars($user['nama']); ?>!</h1>
            </div>
            <img src="https://placehold.co/48x48/312e81/ffffff?text=<?php echo $user['avatar_initial']; ?>" alt="Avatar" class="user-avatar">
        </div>

        <!-- Progress Card -->
        <div class="progress-card">
            <h2>Level Kemahiran Menyimak</h2>
            <div class="circular-progress">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="50" class="progress-bg"></circle>
                    <circle cx="60" cy="60" r="50" class="progress-bar-circle" id="progressCircle"
                            style="stroke-dasharray: 314; stroke-dashoffset: 314;"></circle>
                </svg>
                <span class="progress-text" id="progressText">0%</span>
            </div>
            <p>Tingkatkan progresmu!</p>
        </div>

        <!-- Level Map -->
        <h2 class="section-title">Peta Perjalanan Istima'</h2>
        <div class="map-card">
            <div class="level-path">
                <div class="level-line"></div>
                
                <a href="themes.php?level=1" class="level-item">
                    <div class="level-icon completed">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="level-info">
                        <p class="level-status completed">SELESAI</p>
                        <h3 class="level-title">Tingkat 1</h3>
                        <p class="level-desc">Menyimak lalu memilih pilihan ganda sesuai dengan apa yang di dengar</p>
                    </div>
                </a>

                <a href="themes.php?level=2" class="level-item">
                    <div class="level-icon active">
                        <i class="fas fa-pencil-alt"></i>
                    </div>
                    <div class="level-info">
                        <p class="level-status active">LANJUTKAN</p>
                        <h3 class="level-title">Tingkat 2</h3>
                        <p class="level-desc">Menyimak lalu menulis apa yang didengar</p>
                    </div>
                </a>

                <a href="#" class="level-item" style="pointer-events: none; opacity: 0.7;">
                    <div class="level-icon locked">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="level-info">
                        <p class="level-status locked">TERKUNCI</p>
                        <h3 class="level-title">Tingkat 3</h3>
                        <p class="level-desc">Menyimak lalu melengkapi kalimat yang rumpang</p>
                    </div>
                </a>

                <a href="#" class="level-item" style="pointer-events: none; opacity: 0.7;">
                    <div class="level-icon locked">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="level-info">
                        <p class="level-status locked">TERKUNCI</p>
                        <h3 class="level-title">Tingkat 4</h3>
                        <p class="level-desc">Menyimak dan memahami isi cerita</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Leaderboard -->
        <h2 class="section-title">Papan Peringkat</h2>
        <div id="leaderboardContainer">
            <?php 
            $medals = ['🥇', '🥈', '🥉'];
            foreach ($leaderboard as $index => $leader): 
                $is_current = ($leader['id'] == $user['id']);
                $rank_display = $index < 3 ? $medals[$index] : ($index + 1);
            ?>
            <div class="leaderboard-item <?php echo $is_current ? 'current-user' : ''; ?>">
                <div class="rank-badge"><?php echo $rank_display; ?></div>
                <img src="https://placehold.co/48x48/e2e8f0/64748b?text=<?php echo $leader['avatar_initial']; ?>" 
                     alt="Avatar" class="user-avatar-small">
                <div class="leaderboard-info">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4 class="leaderboard-name"><?php echo htmlspecialchars($leader['nama']); ?></h4>
                        <span class="leaderboard-xp"><?php echo $leader['xp']; ?> XP</span>
                    </div>
                    <p class="leaderboard-quote">"<?php echo htmlspecialchars($leader['motivasi'] ?: 'Belajar dengan semangat!'); ?>"</p>
                    <div class="progress-bar-mini">
                        <div class="progress-fill-mini" style="width: <?php echo round($leader['progress']); ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
            <div class="nav-icon-bg">
                <i class="fas fa-home nav-icon"></i>
            </div>
        </a>
        <a href="mufrodat.php" class="nav-item">
            <div class="nav-icon-bg">
                <i class="fas fa-search nav-icon"></i>
            </div>
        </a>
        <a href="profile.php" class="nav-item active">
            <div class="nav-icon-bg">
                <i class="fas fa-user nav-icon"></i>
            </div>
        </a>
    </nav>

    <script>
        // Animate circular progress
        window.addEventListener('load', function() {
            const progress = <?php echo round($cumulative_progress); ?>;
            const circle = document.getElementById('progressCircle');
            const text = document.getElementById('progressText');
            const circumference = 2 * Math.PI * 50;
            
            setTimeout(() => {
                const offset = circumference - (progress / 100) * circumference;
                circle.style.strokeDashoffset = offset;
                
                let current = 0;
                const interval = setInterval(() => {
                    current++;
                    text.textContent = current + '%';
                    if (current >= progress) clearInterval(interval);
                }, 15);
            }, 100);
        });
    </script>
</body>
</html>