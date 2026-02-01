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
            // The filename format is: Name___OriginalName.jpg
            // We need to ensure the Name is valid UTF-8 for display.

            // Extract the "Name" part from the raw filename first
            $parts = explode('___', $file);
            $rawName = (count($parts) > 1) ? $parts[0] : 'Participant';

            // Check encoding of the Raw Name
            if (mb_check_encoding($rawName, 'UTF-8')) {
                $displayName = $rawName; // It's already UTF-8 (Linux servers usually)
            } else {
                // Fallback: It might be ISO-8859-1 or Windows-1252
                $displayName = mb_convert_encoding($rawName, 'UTF-8', 'ISO-8859-1');
            }

            // Clean up name (underscores/dashes to spaces)
            $displayName = str_replace(['_', '-'], ' ', $displayName);


            // 2. Handling the File Path (URL)
            // We MUST use the exact filename as it exists on the disk for the URL resources.
            // But we must URL-encode special chars (spaces, accents) for the HTML src attribute.
            // Solution: URL encode each segment of the filename.

            // We use the raw $file here, unchanged.
            $urlEncodedName = rawurlencode($file);

            $images[] = [
                'src' => 'photos/slides_optimized/' . $urlEncodedName,
                'name' => $displayName
            ];
        }
    }
}

// Shuffle optionally? Let's keep alphabetical for now or shuffle. 
// User didn't specify, but random is usually better for slideshows.
// Let's shuffle.
shuffle($images);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diaporama - Concours Photo CFBR</title>
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
            text-transform: uppercase;
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
    </style>
</head>

<body>

    <div id="controls">
        <a href="index.php" class="text-white hover:text-orange-500 text-2xl"><i class="fas fa-times"></i> Aller à
            l'accueil</a>
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
            container.appendChild(img);

            // Cleanup old slides to save memory (keep only current and prev)
            // Actually for smooth transition we need 2 at a time.
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
            // Small delay to ensure DOM render
            setTimeout(() => {
                nextImg.classList.add('active');
                updateWatermark(nextIndex);
            }, 50);

            // Fade Out Current
            setTimeout(() => {
                if (currentImg) currentImg.classList.remove('active');

                // Cleanup potentially really old slides? 
                // Simple version: just keep adding to DOM might crash if 1000s of photos.
                // Better: Remove (currentIndex - 1)
                let prevIndex = (currentIndex - 1 + images.length) % images.length;
                let prevImg = document.getElementById('slide-' + prevIndex);
                if (prevImg) {
                    // Keep it for a bit for transition then remove? 
                    // Actually CSS transition takes 1.5s.
                    setTimeout(() => { prevImg.remove(); }, 2000);
                }

                currentIndex = nextIndex;
            }, 100); // 
        }

    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>

</html>