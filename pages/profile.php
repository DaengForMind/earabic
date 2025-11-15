<?php
// pages/profile.php
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
    <title>Profile - Earabic</title>
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
        
        .profile-wrapper {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            padding: 2rem 1rem;
            position: relative;
            z-index: 10;
        }
        
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .avatar-section {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .avatar-img {
            width: 128px;
            height: 128px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            object-fit: cover;
        }
        
        .profile-card {
            width: 100%;
            background-color: white;
            border-radius: 1.5rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            padding: 2rem 1.5rem;
            margin-bottom: 1rem;
            position: relative;
        }
        
        .edit-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
        }
        
        .edit-btn:hover {
            background-color: #4338ca;
            transform: scale(1.1);
        }
        
        .profile-row {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            color: #374151;
        }
        
        .profile-row:last-child {
            margin-bottom: 0;
        }
        
        .profile-label {
            width: 110px;
            font-weight: 600;
            color: #6b7280;
            flex-shrink: 0;
        }
        
        .profile-value {
            flex: 1;
            word-break: break-word;
        }
        
        .logout-btn {
            width: 100%;
            padding: 1rem 1.5rem;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            margin-top: 1rem;
        }
        
        .logout-btn:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
        }
        
        .logout-btn:active {
            transform: translateY(0);
        }
        
        .logout-btn i {
            margin-right: 0.5rem;
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 1.5rem;
            padding: 2rem 1.5rem;
            width: 100%;
            max-width: 400px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .close-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f3f4f6;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .close-btn:hover {
            background-color: #e5e7eb;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
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
        
        .form-textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            resize: vertical;
            min-height: 80px;
            font-family: 'Inter', sans-serif;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .save-btn {
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
            margin-top: 0.5rem;
        }
        
        .save-btn:hover {
            background-color: #4338ca;
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
    <div class="profile-wrapper">
        <div class="profile-container">
            <!-- Avatar -->
            <div class="avatar-section">
                <img src="https://placehold.co/128x128/312e81/ffffff?text=<?php echo $user['avatar_initial']; ?>" 
                     alt="Profile Picture" 
                     class="avatar-img">
            </div>

            <!-- Profile Card -->
            <div class="profile-card">
                <button class="edit-btn" onclick="openEditModal()">
                    <i class="fas fa-edit"></i>
                </button>

                <div class="profile-row">
                    <span class="profile-label">Nama</span>
                    <span class="profile-value">: <?php echo htmlspecialchars($user['nama']); ?></span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">Instansi</span>
                    <span class="profile-value" id="displayInstansi">: <?php echo htmlspecialchars($user['instansi']); ?></span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">Email</span>
                    <span class="profile-value" style="word-break: break-all;">: <?php echo htmlspecialchars($user['email']); ?></span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">Tanggal Lahir</span>
                    <span class="profile-value" id="displayTanggalLahir">: <?php echo $user['tanggal_lahir'] ? date('d F Y', strtotime($user['tanggal_lahir'])) : 'Belum diisi'; ?></span>
                </div>

                <div class="profile-row">
                    <span class="profile-label">Motivasi</span>
                    <p class="profile-value" id="displayMotivasi" style="margin: 0;">: <?php echo htmlspecialchars($user['motivasi'] ?: 'Belajar dengan semangat!'); ?></p>
                </div>
            </div>

            <!-- Logout Button -->
            <button class="logout-btn" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </button>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Edit Profile</h2>
                <button class="close-btn" onclick="closeEditModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="editForm">
                <div class="form-group">
                    <label for="instansi" class="form-label">Instansi</label>
                    <input type="text" id="instansi" name="instansi" class="form-input" 
                           value="<?php echo htmlspecialchars($user['instansi']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" class="form-input" 
                           value="<?php echo $user['tanggal_lahir']; ?>">
                </div>

                <div class="form-group">
                    <label for="motivasi" class="form-label">Motivasi</label>
                    <textarea id="motivasi" name="motivasi" class="form-textarea" 
                              placeholder="Tulis motivasi Anda..."><?php echo htmlspecialchars($user['motivasi'] ?: ''); ?></textarea>
                </div>

                <button type="submit" class="save-btn">
                    <i class="fas fa-save" style="margin-right: 0.5rem;"></i>
                    Simpan Perubahan
                </button>
            </form>
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
        function openEditModal() {
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Handle form submission
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile berhasil diupdate!');
                    
                    // Update displayed values
                    document.getElementById('displayInstansi').textContent = ': ' + formData.get('instansi');
                    
                    const tanggalLahir = formData.get('tanggal_lahir');
                    if (tanggalLahir) {
                        const date = new Date(tanggalLahir);
                        const options = { day: 'numeric', month: 'long', year: 'numeric' };
                        document.getElementById('displayTanggalLahir').textContent = ': ' + date.toLocaleDateString('id-ID', options);
                    }
                    
                    const motivasi = formData.get('motivasi') || 'Belajar dengan semangat!';
                    document.getElementById('displayMotivasi').textContent = ': ' + motivasi;
                    
                    closeEditModal();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        });

        function logout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>
</html>