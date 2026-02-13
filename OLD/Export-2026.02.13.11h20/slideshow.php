<?php
// slideshow.php
// Displays a fullscreen slideshow of images in photos/slides_optimized/

$slideDir = __DIR__ . '/photos/slides_optimized/';
$images = [];

if (is_dir($slideDir)) {
    $files = scandir($slideDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {

            // 1. Handling the Display Name (Text)
            // The filename format is: Participant Name___OriginalName.jpg

            if (strpos($file, '___') !== false) {
                $parts = explode('___', $file);
                $rawName = $parts[0];
            } else {
                // Fallback for old naming convention: first_last_original.jpg
                $parts = explode('_', $file);
                if (count($parts) >= 2) {
                    $rawName = $parts[0] . ' ' . $parts[1];
                } else {
                    $rawName = $parts[0];
                }
            }

            // Ensure we have correct UTF-8 display
            if (mb_check_encoding($rawName, 'UTF-8')) {
                $displayName = $rawName;
            } else {
                // Fallback: Windows usually uses Windows-1252 or CP1252
                $displayName = mb_convert_encoding($rawName, 'UTF-8', 'Windows-1252');
            }

            // Cleanup name
            $displayName = str_replace(['_', '-'], ' ', $displayName);

            // Professional Title Case display: "FRANÇOIS TRONEL" -> "François Tronel"
            // We use MB_CASE_TITLE if available, or just keeping it as is since bank names are usually correct
            if (mb_check_encoding($displayName, 'UTF-8')) {
                $displayName = mb_convert_case($displayName, MB_CASE_TITLE, "UTF-8");
            }


            // 2. Handling the File Path (URL)
            // We MUST use the exact filename as it exists on the disk for the URL resources.
            // But we must URL-encode special chars (spaces, accents) for the HTML src attribute.
            // On Windows, filenames from scandir might be in system encoding (1252).
            // We convert to UTF-8 before URL encoding to match browser expectations.

            $filenameForUrl = $file;
            if (!mb_check_encoding($filenameForUrl, 'UTF-8')) {
                $filenameForUrl = mb_convert_encoding($filenameForUrl, 'UTF-8', 'Windows-1252');
            }
            $urlEncodedName = rawurlencode($filenameForUrl);

            $images[] = [
                'src' => 'photos/slides_optimized/' . $urlEncodedName,
                'name' => $displayName
            ];
        }
    }
}

// Randomize slideshow order
shuffle($images);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diaporama - Concours Photo CFBR</title>
    <?php include __DIR__ . '/includes/pwa_loader.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            overflow: hidden;
            font-family: 'Montserrat', sans-serif;
        }

        #slide-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .slide {
            position: absolute;
            max-width: 100%;
            max-height: 100%;
            opacity: 0;
            transition: opacity 1.5s ease-in-out;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
        }

        .slide.active {
            opacity: 1;
        }

        #watermark {
            position: absolute;
            bottom: 30px;
            right: 40px;
            /* Align Right for subtle look */
            background-color: rgba(0, 0, 0, 0.6);
            color: rgba(255, 255, 255, 0.9);
            padding: 10px 25px;
            border-radius: 30px;
            font-size: 1.2rem;
            letter-spacing: 2px;
            opacity: 0;
            transition: opacity 1s;
        }

        #watermark.visible {
            opacity: 1;
        }

        /* Controls */
        #controls {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 50;
            opacity: 0;
            transition: opacity 0.5s;
        }

        /* body:hover #controls handled by JS now */

        #logo-container {
            position: absolute;
            bottom: 30px;
            left: 40px;
            z-index: 50;
            opacity: 1;
        }

        #logo-cfbr {
            height: 56px;
            /* Reduced by 30% (80px * 0.7) */
            width: auto;
            filter: drop-shadow(0 0 10px rgba(0, 0, 0, 0.5));
        }
    </style>
</head>

<body>

    <div id="controls">
        <a href="index.php" class="text-white hover:text-orange-500 text-2xl"><i class="fas fa-times"></i> Aller à
            l'accueil</a>
    </div>

    <!-- CFBR Logo -->
    <div id="logo-container">
        <img id="logo-cfbr" src="assets/logo_cfbr_100_ans.png" alt="Logo CFBR">
    </div>

    <div id="slide-container">
        <!-- Images injected via JS to control loading -->
    </div>

    <div id="watermark"></div>

    <script>
        const images = <?php echo json_encode($images); ?>;

        // Controls Auto-Hide Logic
        const controls = document.getElementById('controls');
        let controlsTimeout;

        function showControls() {
            controls.style.opacity = '1';
            clearTimeout(controlsTimeout);
            controlsTimeout = setTimeout(() => {
                controls.style.opacity = '0';
            }, 2000);
        }

        // Show on move
        document.addEventListener('mousemove', showControls);
        // Show on click/touch
        document.addEventListener('click', showControls);

        // Init
        showControls();
        let currentIndex = 0;
        const container = document.getElementById('slide-container');
        const watermark = document.getElementById('watermark');
        const duration = 5000; // 5 seconds per slide

        if (images.length === 0) {
            container.innerHTML = '<h1 class="text-white text-2xl">Aucune photo trouvée. Veuillez lancer le script d\'optimisation.</h1>';
        } else {
            // Preload first image
            createSlide(0, true);
            updateWatermark(0);

            // Start Loop
            setInterval(nextSlide, duration);
        }

        function createSlide(index, isActive) {
            const img = document.createElement('img');
            img.src = images[index].src;
            img.className = 'slide' + (isActive ? ' active' : '');
            img.id = 'slide-' + index;

            // Error Handling: Skip broken images
            img.onerror = () => {
                console.warn("Failed to load image: " + images[index].src + ". Skipping...");
                img.remove();
                // If the current first image fails, try the next one immediately
                if (index === currentIndex) {
                    nextSlide();
                }
            };

            container.appendChild(img);
        }

        function updateWatermark(index) {
            watermark.classList.remove('visible');
            setTimeout(() => {
                watermark.innerText = images[index].name;
                watermark.classList.add('visible');
            }, 500); // Wait for fade out
        }

        function nextSlide() {
            const nextIndex = (currentIndex + 1) % images.length;

            // Create next slide if not exists (lazy load)
            let nextImg = document.getElementById('slide-' + nextIndex);
            if (!nextImg) {
                createSlide(nextIndex, false);
                nextImg = document.getElementById('slide-' + nextIndex);
            }

            // Transition
            const currentImg = document.getElementById('slide-' + currentIndex);

            // Fade In Next
            // Small delay to ensure DOM render/load
            setTimeout(() => {
                if (nextImg && nextImg.parentElement) {
                    nextImg.classList.add('active');
                    updateWatermark(nextIndex);

                    // Fade Out Current
                    if (currentImg) currentImg.classList.remove('active');

                    // Cleanup old slides
                    let prevIndex = (currentIndex - 1 + images.length) % images.length;
                    let prevImg = document.getElementById('slide-' + prevIndex);
                    if (prevImg) {
                        setTimeout(() => { if (prevImg.parentElement) prevImg.remove(); }, 2000);
                    }

                    currentIndex = nextIndex;
                } else {
                    // If nextImg was removed by onerror, try again
                    currentIndex = nextIndex;
                    nextSlide();
                }
            }, 50);
        }

    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>

</html>