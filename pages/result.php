<?php
// pages/result.php
require_once '../config/database.php';
startSession();

// Cek login
if (!isLoggedIn()) {
    redirect('../index.php');
}

$score = isset($_GET['score']) ? intval($_GET['score']) : 0;
$total = isset($_GET['total']) ? intval($_GET['total']) : 10;
$percentage = ($score / $total) * 100;

// Tentukan jumlah bintang
$stars = 0;
if ($percentage >= 30) $stars = 1;
if ($percentage >= 60) $stars = 2;
if ($percentage >= 90) $stars = 3;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hasil - Earabic</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .result-container {
            width: 100%;
            max-width: 400px;
            background-color: white;
            padding: 2.5rem 2rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            text-align: center;
            position: relative;
            z-index: 10;
        }
        
        .result-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .result-subtitle {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        .stars-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .star {
            font-size: 3rem;
            color: #d1d5db;
            transition: color 0.3s;
        }
        
        .star.filled {
            color: #facc15;
        }
        
        .star:nth-child(2) {
            font-size: 4rem;
        }
        
        .score-card {
            background-color: #f3f4f6;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        
        .score-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .score-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn-primary {
            width: 100%;
            padding: 1rem 1.5rem;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .btn-secondary {
            width: 100%;
            padding: 1rem 1.5rem;
            background-color: #e5e7eb;
            color: #374151;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            background-color: #d1d5db;
        }
        
        .bg-char {
            position: fixed;
            font-size: 6rem;
            opacity: 0.3;
            font-family: 'Amiri', serif;
            pointer-events: none;
        }
        
        @keyframes starPop {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .star.filled {
            animation: starPop 0.5s ease-out forwards;
        }
        
        @media (min-width: 640px) {
            .result-container {
                padding: 3rem 2.5rem;
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

    <!-- Result Container -->
    <div class="result-container">
        <h1 class="result-title">Latihan Selesai!</h1>
        <p class="result-subtitle">Kerja bagus, teruslah berlatih!</p>

        <div class="stars-container">
            <i class="fas fa-star star" id="star1"></i>
            <i class="fas fa-star star" id="star2"></i>
            <i class="fas fa-star star" id="star3"></i>
        </div>

        <div class="score-card">
            <p class="score-label">Skor Kamu</p>
            <p class="score-value"><?php echo $score; ?>/<?php echo $total; ?></p>
        </div>

        <div class="button-group">
            <a href="progress.php" class="btn-primary">Pilih Tema Lain</a>
            <a href="progress.php" class="btn-secondary">Kembali ke Peta</a>
        </div>
    </div>

    <script>
        // Animate stars
        const starsToFill = <?php echo $stars; ?>;
        
        setTimeout(() => {
            if (starsToFill >= 1) {
                document.getElementById('star1').classList.add('filled');
            }
        }, 300);
        
        setTimeout(() => {
            if (starsToFill >= 2) {
                document.getElementById('star2').classList.add('filled');
            }
        }, 600);
        
        setTimeout(() => {
            if (starsToFill >= 3) {
                document.getElementById('star3').classList.add('filled');
            }
        }, 900);
    </script>
</body>
</html>