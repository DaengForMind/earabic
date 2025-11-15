<?php
// pages/home.php
require_once '../config/database.php';
startSession();

// Cek login
if (!isLoggedIn()) {
    redirect('../index.php');
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Home - Earabic</title>
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
        }
        
        .home-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            padding-bottom: 100px;
            position: relative;
            z-index: 10;
            text-align: center;
        }
        
        .arabic-title {
            font-family: 'Amiri', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 3rem;
            letter-spacing: 2px;
        }
        
        .mascot-container {
            margin: 3rem 0;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .mascot-container:hover {
            transform: scale(1.05);
        }
        
        .mascot-circle {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto;
        }
        
        .mascot-bg {
            position: absolute;
            inset: 0;
            background-color: white;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 4px solid #e5e7eb;
        }
        
        .mascot-inner {
            position: absolute;
            inset: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .mascot-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            padding: 8px;
        }
        
        .quote-container {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .arabic-quote {
            font-family: 'Amiri', serif;
            font-size: 1.5rem;
            color: #4b5563;
            margin-bottom: 0.75rem;
            line-height: 1.8;
        }
        
        .quote-translation {
            font-size: 0.875rem;
            color: #6b7280;
            font-style: italic;
            line-height: 1.6;
        }
        
        .bg-char {
            position: fixed;
            font-size: 6rem;
            opacity: 0.3;
            font-family: 'Amiri', serif;
            pointer-events: none;
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
        
        @media (min-width: 640px) {
            .arabic-title {
                font-size: 3rem;
            }
            
            .mascot-circle {
                width: 220px;
                height: 220px;
            }
            
            .arabic-quote {
                font-size: 1.75rem;
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
        <span class="bg-char" style="bottom: 20%; left: 50%; transform: rotate(5deg);">ب</span>
        <span class="bg-char" style="top: 70%; right: -5%; transform: rotate(-30deg);">ل</span>
    </div>

    <!-- Main Content -->
    <main class="home-wrapper">
        <h1 class="arabic-title">أَهْلًا وَسَهْلًا</h1>
        
        <a href="progress.php" class="mascot-container">
            <div class="mascot-circle">
                <div class="mascot-bg"></div>
                <div class="mascot-inner">
                    <img src="../assets/images/maskot.png" alt="Maskot Earabic" class="mascot-img">
                </div>
            </div>
        </a>
        
        <div class="quote-container">
            <p class="arabic-quote">تَعَلَّمُوا الْعَرَبِيَّةَ فَإِنَّهَا مِنْ دِينِكُمْ</p>
            <p class="quote-translation">"Pelajarilah bahasa Arab karena ia bagian dari agama kalian."</p>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="home.php" class="nav-item active">
            <div class="nav-icon-bg">
                <i class="fas fa-home nav-icon"></i>
            </div>
        </a>
        <a href="mufrodat.php" class="nav-item">
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
</body>
</html>