<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Concours Photo 2026 – CFBR</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        :root {
            --font-title: 'Montserrat', sans-serif;
            --font-body: 'Open Sans', sans-serif;
            --deep-blue: #0A2240;
            --accent-gold: #FF9900;
        }

        body {
            font-family: var(--font-body);
            background-color: #F8F8F8;
            color: #333;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: var(--font-title);
        }

        .hero-banner {
            background-image: linear-gradient(rgba(10, 34, 64, 0.7), rgba(10, 34, 64, 0.7)), url('https://www.barrages-cfbr.eu/IMG/jpg/villefort-frc0480005-101_photo_edf_coubard.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
</head>

<body class="bg-[#F8F8F8] flex flex-col min-h-screen">

    <!-- Header / Hero -->
    <header class="relative hero-banner min-h-[500px] flex flex-col items-center justify-center text-white p-4">
        <img src="https://www.barrages-cfbr.eu/IMG/logo/siteon0.png?1572394244" alt="Logo CFBR"
            class="h-20 w-auto mb-6 bg-white rounded p-2">

        <h1 class="text-3xl md:text-5xl font-bold text-center">Concours Photo 2026</h1>
        <h2 class="text-lg md:text-2xl mt-4 text-center font-light">« Barrages : Entre nature et architecture »</h2>
        <p class="mt-4 text-base text-[#FF9900] font-semibold tracking-wide uppercase">Ouvert à tous les adultes</p>

        <div class="mt-10">
            <a href="upload.php"
                class="bg-[#FF9900] text-[#0A2240] px-8 py-3 rounded-full text-lg font-semibold hover:bg-white hover:text-[#0A2240] transition-colors duration-300 shadow-lg transform hover:scale-105 inline-block">
                <i class="fas fa-camera mr-2"></i> Déposer ma candidature
            </a>
        </div>
        <div class="absolute bottom-4 right-4 text-xs text-white opacity-75 font-light">Photo EDF - Coubard</div>
    </header>

    <main class="flex-grow">
        <!-- Intro & Rules Summary -->
        <section class="py-16 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center max-w-3xl mx-auto">
                    <h2 class="text-3xl font-bold text-[#0A2240] mb-6">Participez au concours du Centenaire</h2>
                    <p class="text-lg text-gray-700 leading-relaxed mb-8">
                        Révélez l'intégration environnementale et la majesté architecturale des barrages.
                        Chaque participant peut proposer jusqu'à <strong>5 photographies</strong>.
                    </p>

                    <div class="grid md:grid-cols-3 gap-6 text-left">
                        <div class="p-4 border border-gray-200 rounded hover:shadow-md transition">
                            <h3 class="font-bold text-[#0A2240] mb-2"><i class="fas fa-check text-[#FF9900]"></i> Haute
                                Résolution</h3>
                            <p class="text-sm text-gray-600">Format A3 visé (min 4960px sur le grand côté). Pas
                                d'upscaling artificiel.</p>
                        </div>
                        <div class="p-4 border border-gray-200 rounded hover:shadow-md transition">
                            <h3 class="font-bold text-[#0A2240] mb-2"><i class="fas fa-check text-[#FF9900]"></i>
                                Anonymat</h3>
                            <p class="text-sm text-gray-600">Les photos sont jugées anonymement lors du premier tour.
                            </p>
                        </div>
                        <div class="p-4 border border-gray-200 rounded hover:shadow-md transition">
                            <h3 class="font-bold text-[#0A2240] mb-2"><i class="fas fa-check text-[#FF9900]"></i> Droit
                                d'auteur</h3>
                            <p class="text-sm text-gray-600">Vous devez être l'auteur des photos et accepter la cession
                                de droits.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Jury Login Link (Discret) -->
        <section class="py-8 bg-[#F8F8F8] border-t">
            <div class="container mx-auto px-6 text-center">
                <p class="text-sm text-gray-500">Membre du Jury ? <a href="jury_tour1.php"
                        class="text-[#0A2240] font-bold hover:underline">Accéder à l'espace de vote</a></p>
            </div>
        </section>
    </main>

    <footer class="bg-[#0A2240] text-white py-8 border-t border-gray-700">
        <div class="container mx-auto px-6 text-center">
            <div class="flex flex-col md:flex-row justify-center items-center space-y-4 md:space-y-0 md:space-x-8">
                <img src="https://www.barrages-cfbr.eu/IMG/logo/siteon0.png?1572394244" alt="Logo CFBR"
                    class="h-10 w-auto bg-white rounded p-1">
                <p class="text-gray-400 text-sm">© 2026 Comité Français des Barrages et Réservoirs</p>
            </div>

            <div class="flex justify-center space-x-6 mt-6">
                <a href="https://www.linkedin.com/company/cfbr/" target="_blank"
                    class="text-white hover:text-[#FF9900] transition-colors"><i class="fab fa-linkedin fa-lg"></i></a>
                <a href="https://www.instagram.com/dam_nature100/" target="_blank"
                    class="text-white hover:text-[#FF9900] transition-colors"><i class="fab fa-instagram fa-lg"></i></a>
            </div>
        </div>
    </footer>

</body>

</html>