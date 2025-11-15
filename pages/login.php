<?php
// pages/login.php
require_once '../config/database.php';
startSession();

// Jika sudah login, redirect ke home
if (isLoggedIn()) {
    redirect('home.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Earabic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Amiri:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .page-wrapper {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            z-index: 10;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 2rem 1.5rem;
        }
        
        .form-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .form-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn-submit {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background-color: #1f2937;
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-top: 0.5rem;
        }
        
        .btn-submit:hover {
            background-color: #111827;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }
        
        .register-link {
            text-align: center;
            font-size: 0.875rem;
            color: #4b5563;
            margin-top: 2rem;
        }
        
        .register-link a {
            font-weight: 500;
            color: #4f46e5;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .bg-char {
            font-size: 6rem;
            opacity: 0.5;
        }
        
        @media (min-width: 640px) {
            .bg-char {
                font-size: 8rem;
            }
            
            .login-container {
                padding: 3rem 2rem;
            }
            
            .form-title {
                font-size: 2.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background Decorative Characters -->
    <div style="position: fixed; inset: 0; pointer-events: none; overflow: hidden; z-index: 0;">
        <span class="bg-char font-amiri" style="position: absolute; top: 5%; left: 5%; transform: rotate(-15deg);">س</span>
        <span class="bg-char font-amiri" style="position: absolute; top: 10%; right: 10%; transform: rotate(20deg);">ش</span>
        <span class="bg-char font-amiri" style="position: absolute; top: 30%; right: -5%; transform: rotate(-10deg);">ق</span>
        <span class="bg-char font-amiri" style="position: absolute; top: 50%; left: -10%; transform: rotate(15deg);">ف</span>
        <span class="bg-char font-amiri" style="position: absolute; bottom: 30%; right: 5%; transform: rotate(25deg);">ع</span>
        <span class="bg-char font-amiri" style="position: absolute; bottom: 10%; left: 20%; transform: rotate(-20deg);">ك</span>
        <span class="bg-char font-amiri" style="position: absolute; bottom: 20%; left: 50%; transform: rotate(5deg);">ب</span>
        <span class="bg-char font-amiri" style="position: absolute; top: 70%; right: -5%; transform: rotate(-30deg);">ل</span>
    </div>

    <!-- Main Content -->
    <div class="page-wrapper">
        <div class="login-container">
            <h1 class="form-title">Login</h1>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="telepon" class="form-label">No. Telepon</label>
                    <input type="tel" id="telepon" name="telepon" class="form-input" placeholder="08xxx" required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="contoh@gmail.com" required>
                </div>

                <button type="submit" class="btn-submit">Masuk</button>
            </form>

            <p class="register-link">
                Belum punya akun? <a href="../index.php">Daftar</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'home.php';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        });
    </script>
</body>
</html>
