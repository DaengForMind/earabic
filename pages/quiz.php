<?php
// pages/quiz.php
require_once '../config/database.php';
startSession();

// Cek login
if (!isLoggedIn()) {
    redirect('../index.php');
}

$user = getCurrentUser();

// Get parameters
$level = isset($_GET['level']) ? intval($_GET['level']) : 1;
$theme = isset($_GET['theme']) ? $_GET['theme'] : 'التعرف';

// Get quiz data
$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM quiz_data WHERE theme = ? AND level = ? ORDER BY question_number");
$stmt->bind_param("si", $theme, $level);
$stmt->execute();
$result = $stmt->get_result();
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();
$conn->close();

// Tentukan jenis quiz berdasarkan level
$quiz_types = [
    1 => 'mc',      // Multiple Choice
    2 => 'write',   // Writing
    3 => 'fill',    // Fill in the blank
    4 => 'story'    // Story comprehension
];
$quiz_type = $quiz_types[$level];

$level_titles = [
    1 => 'Pilih Tulisan yang Tepat',
    2 => 'Tulis Apa yang Kamu Dengar',
    3 => 'Lengkapi Kalimat dengan Menulis',
    4 => 'Pahami Isi Cerita'
];
$level_descriptions = [
    1 => 'Dengarkan audio dan pilih tulisan yang sesuai.',
    2 => 'Dengarkan audio lalu tulis tanpa harakat.',
    3 => 'Dengarkan audio lalu tulis kata yang rumpang.',
    4 => 'Dengarkan cerita, lalu pilih kesimpulan yang paling tepat.'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quiz - Earabic</title>
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
            padding-bottom: 140px;
        }

        .quiz-wrapper {
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
            gap: 1rem;
            margin-bottom: 2rem;
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

        .progress-container {
            flex: 1;
        }

        .progress-bar-outer {
            width: 100%;
            height: 10px;
            background-color: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar-inner {
            height: 100%;
            background-color: #4f46e5;
            border-radius: 999px;
            transition: width 0.5s ease;
        }

        .progress-text {
            font-weight: 700;
            color: #4b5563;
            margin-left: 1rem;
            white-space: nowrap;
        }

        .quiz-content {
            margin-top: 2rem;
        }

        .quiz-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            text-align: center;
            margin-bottom: 1rem;
        }

        .quiz-description {
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }

        .audio-btn-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2.5rem;
        }

        .audio-btn {
            width: 96px;
            height: 96px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            border-radius: 50%;
            color: #4f46e5;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .audio-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
        }

        .audio-btn i {
            font-size: 3rem;
        }

        .audio-btn.playing {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        /* Multiple Choice Options */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .option-btn {
            background-color: white;
            border-radius: 1rem;
            padding: 1.5rem 1rem;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
            min-height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .option-btn:hover {
            border-color: #4f46e5;
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .option-btn.correct {
            border-color: #22c55e;
            background-color: #f0fdf4;
        }

        .option-btn.incorrect {
            border-color: #ef4444;
            background-color: #fef2f2;
            animation: shake 0.5s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            10%,
            30%,
            50%,
            70%,
            90% {
                transform: translateX(-5px);
            }

            20%,
            40%,
            60%,
            80% {
                transform: translateX(5px);
            }
        }

        .option-text {
            font-family: 'Amiri', serif;
            font-size: 2rem;
            color: #1f2937;
            text-align: center;
        }

        /* Write Input */
        .write-form {
            position: relative;
        }

        .write-input {
            width: 100%;
            font-family: 'Amiri', serif;
            font-size: 2rem;
            padding: 1.25rem 4rem 1.25rem 1.5rem;
            background-color: white;
            border: 2px solid #d1d5db;
            border-radius: 1rem;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .write-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .write-input.correct {
            border-color: #22c55e;
            background-color: #f0fdf4;
        }

        .write-input.incorrect {
            border-color: #ef4444;
            background-color: #fef2f2;
            animation: shake 0.5s;
        }

        .submit-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            padding: 0.75rem;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            background-color: #4338ca;
        }

        .submit-btn i {
            font-size: 1.5rem;
        }

        /* Fill in the blank */
        .sentence-container {
            font-family: 'Amiri', serif;
            font-size: 2rem;
            color: #1f2937;
            text-align: center;
            margin-bottom: 2rem;
            background-color: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            direction: rtl;
            line-height: 1.8;
        }

        .blank-space {
            color: #4f46e5;
            font-weight: 700;
        }

        /* Story Question */
        .story-question {
            font-family: 'Amiri', serif;
            font-size: 1.5rem;
            color: #1f2937;
            text-align: center;
            margin-bottom: 2rem;
            background-color: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            direction: rtl;
        }

        .story-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .story-option {
            background-color: white;
            border-radius: 1rem;
            padding: 1.25rem;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .story-option:hover {
            border-color: #4f46e5;
            transform: translateX(-4px);
        }

        .story-option-text {
            font-family: 'Amiri', serif;
            font-size: 1.25rem;
            color: #1f2937;
            direction: rtl;
            text-align: right;
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
    <div class="quiz-wrapper">
        <!-- Header -->
        <div class="header-section">
            <a href="themes.php?level=<?php echo $level; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="progress-container">
                <div class="progress-bar-outer">
                    <div class="progress-bar-inner" id="progressBar" style="width: 10%;"></div>
                </div>
            </div>
            <span class="progress-text" id="progressText">1/10</span>
        </div>

        <!-- Quiz Content -->
        <div class="quiz-content">
            <h1 class="quiz-title"><?php echo $level_titles[$level]; ?></h1>
            <p class="quiz-description"><?php echo $level_descriptions[$level]; ?></p>

            <!-- Audio Button -->
            <div class="audio-btn-container">
                <button class="audio-btn" id="audioBtn">
                    <i class="fas fa-volume-up"></i>
                </button>
            </div>

            <!-- Question Container (dynamically filled by JavaScript) -->
            <div id="questionContainer"></div>
        </div>
    </div>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        const quizType = '<?php echo $quiz_type; ?>';
        const userId = <?php echo $user['id']; ?>;
        const theme = '<?php echo addslashes($theme); ?>';
        const level = <?php echo $level; ?>;

        let currentQuestion = 0;
        let score = 0;
        let currentAudio = null;

        function updateProgress() {
            const progress = ((currentQuestion + 1) / questions.length) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('progressText').textContent = `${currentQuestion + 1}/${questions.length}`;
        }

        function playAudio(url) {
            const audioBtn = document.getElementById('audioBtn');

            if (currentAudio) {
                currentAudio.pause();
                currentAudio = null;
            }

            currentAudio = new Audio(url);
            audioBtn.classList.add('playing');

            currentAudio.play();

            currentAudio.onended = () => {
                audioBtn.classList.remove('playing');
            };
        }

        document.getElementById('audioBtn').addEventListener('click', function() {
            if (questions[currentQuestion]) {
                playAudio(questions[currentQuestion].audio_url);
            }
        });

        function loadQuestion() {
            if (currentQuestion >= questions.length) {
                saveProgress();
                return;
            }

            updateProgress();
            const q = questions[currentQuestion];
            const container = document.getElementById('questionContainer');

            if (quizType === 'mc') {
                loadMultipleChoice(q, container);
            } else if (quizType === 'write') {
                loadWriteQuestion(q, container);
            } else if (quizType === 'fill') {
                loadFillQuestion(q, container);
            } else if (quizType === 'story') {
                loadStoryQuestion(q, container);
            }
        }

        function loadMultipleChoice(q, container) {
            const options = [q.option_a, q.option_b, q.option_c, q.option_d];

            container.innerHTML = `
                <div class="options-grid" id="optionsGrid">
                    ${options.map((opt, i) => `
                        <button class="option-btn" data-index="${i}">
                            <span class="option-text">${opt}</span>
                        </button>
                    `).join('')}
                </div>
            `;

            document.querySelectorAll('.option-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const selectedIndex = parseInt(this.dataset.index);
                    const correctAnswer = q.correct_answer;
                    const selectedAnswer = options[selectedIndex];

                    document.querySelectorAll('.option-btn').forEach(b => b.style.pointerEvents = 'none');

                    if (selectedAnswer === correctAnswer) {
                        this.classList.add('correct');
                        score++;
                        unlockMufrodat(q.audio_word, q.translation);
                    } else {
                        this.classList.add('incorrect');
                        // Show correct answer
                        document.querySelectorAll('.option-btn').forEach(b => {
                            if (options[parseInt(b.dataset.index)] === correctAnswer) {
                                b.classList.add('correct');
                            }
                        });
                    }

                    setTimeout(() => {
                        currentQuestion++;
                        loadQuestion();
                    }, 1500);
                });
            });
        }

        function loadWriteQuestion(q, container) {
            container.innerHTML = `
                <form class="write-form" id="writeForm">
                    <input type="text" class="write-input" id="writeInput" 
                           placeholder="اكتب هنا..." dir="rtl" required>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </form>
            `;

            document.getElementById('writeForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const input = document.getElementById('writeInput');
                const userAnswer = input.value.trim();
                const correctAnswer = q.correct_answer;

                input.disabled = true;

                if (userAnswer === correctAnswer) {
                    input.classList.add('correct');
                    score++;
                    unlockMufrodat(q.audio_word, q.translation);
                } else {
                    input.classList.add('incorrect');
                }

                setTimeout(() => {
                    currentQuestion++;
                    loadQuestion();
                }, 1500);
            });
        }

        function loadFillQuestion(q, container) {
            container.innerHTML = `
                <div class="sentence-container">
                    <span>${q.sentence_part1}</span>
                    <span class="blank-space">.....</span>
                    <span>${q.sentence_part2 || ''}</span>
                </div>
                <form class="write-form" id="fillForm">
                    <input type="text" class="write-input" id="fillInput" 
                           placeholder="اكتب الكلمة المفقودة..." dir="rtl" required>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </form>
            `;

            document.getElementById('fillForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const input = document.getElementById('fillInput');
                const userAnswer = input.value.trim();
                const correctAnswer = q.correct_answer;

                input.disabled = true;

                if (userAnswer === correctAnswer) {
                    input.classList.add('correct');
                    score++;
                    unlockMufrodat(q.correct_answer, q.translation);
                } else {
                    input.classList.add('incorrect');
                }

                setTimeout(() => {
                    currentQuestion++;
                    loadQuestion();
                }, 1500);
            });
        }

        function loadStoryQuestion(q, container) {
            const options = [q.option_a, q.option_b, q.option_c, q.option_d];

            container.innerHTML = `
                <div class="story-question">${q.question_text}</div>
                <div class="story-options">
                    ${options.map((opt, i) => `
                        <button class="story-option" data-index="${i}">
                            <span class="story-option-text">${opt}</span>
                        </button>
                    `).join('')}
                </div>
            `;

            document.querySelectorAll('.story-option').forEach(btn => {
                btn.addEventListener('click', function() {
                    const selectedIndex = parseInt(this.dataset.index);
                    const correctAnswer = q.correct_answer;
                    const selectedAnswer = options[selectedIndex];

                    document.querySelectorAll('.story-option').forEach(b => b.style.pointerEvents = 'none');

                    if (selectedAnswer === correctAnswer) {
                        this.classList.add('correct');
                        this.style.borderColor = '#22c55e';
                        this.style.backgroundColor = '#f0fdf4';
                        score++;
                    } else {
                        this.classList.add('incorrect');
                        this.style.borderColor = '#ef4444';
                        this.style.backgroundColor = '#fef2f2';
                        // Show correct answer
                        document.querySelectorAll('.story-option').forEach(b => {
                            if (options[parseInt(b.dataset.index)] === correctAnswer) {
                                b.style.borderColor = '#22c55e';
                                b.style.backgroundColor = '#f0fdf4';
                            }
                        });
                    }

                    setTimeout(() => {
                        currentQuestion++;
                        loadQuestion();
                    }, 1500);
                });
            });
        }

        function unlockMufrodat(word, translation) {
            const audioUrl = questions[currentQuestion].audio_url;
            fetch('save_mufrodat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `word=${encodeURIComponent(word)}&translation=${encodeURIComponent(translation)}&audio_url=${encodeURIComponent(audioUrl)}`
            });
        }

        function saveProgress() {
            fetch('save_progress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `theme=${encodeURIComponent(theme)}&level=${level}&score=${score}&total=${questions.length}`
                })
                .then(() => {
                    window.location.href = `result.php?score=${score}&total=${questions.length}`;
                });
        }

        // Start quiz
        loadQuestion();
    </script>
</body>

</html>