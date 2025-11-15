<?php
// pages/themes.php
require_once '../config/database.php';
startSession();

// Cek login
if (!isLoggedIn()) {
    redirect('../index.php');
}

$user = getCurrentUser();

// Get level dari URL
$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
$level = max(1, min(4, $level)); // Batasi antara 1-4

// Tentukan judul berdasarkan level
$level_titles = [
    1 => 'Pilih Tema Belajar - Tingkat 1',
    2 => 'Pilih Tema Belajar - Tingkat 2',
    3 => 'Pilih Tema Belajar - Tingkat 3',
    4 => 'Pilih Tema Belajar - Tingkat 4'
];
$page_title = $level_titles[$level];

// Data tema
$themes = [
    [
        'name_ar' => 'التعرف',
        'name_id' => 'Perkenalan',
        'icon' => 'fa-user',
        'color' => 'purple'
    ],
    [
        'name_ar' => 'المدرسة',
        'name_id' => 'Sekolah',
        'icon' => 'fa-school',
        'color' => 'yellow'
    ],
    [
        'name_ar' => 'البيت',
        'name_id' => 'Rumah',
        'icon' => 'fa-home',
        'color' => 'blue'
    ],
    [
        'name_ar' => 'الأسرة',
        'name_id' => 'Keluarga',
        'icon' => 'fa-users',
        'color' => 'red'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tema - Earabic</title>
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
        
        .themes-wrapper {
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
            margin-bottom: 2rem;
            gap: 1rem;
        }
        
        .back-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: white;
            color: #4b5563;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            flex-shrink: 0;
        }
        
        .back-btn:hover {
            background-color: #f3f4f6;
            transform: translateX(-2px);
        }
        
        .page-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            flex: 1;
            text-align: center;
            margin-right: 40px;
        }
        
        .themes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }
        
        .theme-card {
            background-color: white;
            border-radius: 1.5rem;
            padding: 1.5rem 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            min-height: 160px;
        }
        
        .theme-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .theme-icon-bg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            transition: transform 0.3s;
        }
        
        .theme-card:hover .theme-icon-bg {
            transform: scale(1.1);
        }
        
        .theme-icon-bg.purple {
            background-color: #f3e8ff;
        }
        
        .theme-icon-bg.yellow {
            background-color: #fef3c7;
        }
        
        .theme-icon-bg.blue {
            background-color: #dbeafe;
        }
        
        .theme-icon-bg.red {
            background-color: #fee2e2;
        }
        
        .theme-icon {
            font-size: 2.5rem;
        }
        
        .theme-icon.purple {
            color: #9333ea;
        }
        
        .theme-icon.yellow {
            color: #eab308;
        }
        
        .theme-icon.blue {
            color: #3b82f6;
        }
        
        .theme-icon.red {
            color: #ef4444;
        }
        
        .theme-name-ar {
            font-family: 'Amiri', serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .theme-name-id {
            font-size: 0.875rem;
            color: #6b7280;
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
            .themes-grid {
                gap: 1.5rem;
            }
            
            .theme-card {
                min-height: 180px;
            }
            
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
    <div class="themes-wrapper">
        <!-- Header -->
        <div class="header-section">
            <a href="progress.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="page-title"><?php echo $page_title; ?></h1>
        </div>

        <!-- Themes Grid -->
        <div class="themes-grid">
            <?php foreach ($themes as $theme): ?>
            <a href="quiz.php?level=<?php echo $level; ?>&theme=<?php echo urlencode($theme['name_ar']); ?>" class="theme-card">
                <div class="theme-icon-bg <?php echo $theme['color']; ?>">
                    <i class="fas <?php echo $theme['icon']; ?> theme-icon <?php echo $theme['color']; ?>"></i>
                </div>
                <h3 class="theme-name-ar"><?php echo $theme['name_ar']; ?></h3>
                <p class="theme-name-id"><?php echo $theme['name_id']; ?></p>
            </a>
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
</body>
</html>