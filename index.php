<?php
// Random Hero Image Logic
$heroImage = 'https://www.barrages-cfbr.eu/IMG/jpg/villefort-frc0480005-101_photo_edf_coubard.jpg'; // Default Fallback
$slideDir = __DIR__ . '/photos/slides_optimized/';
$randomCredit = 'Photo EDF - Coubard'; // Default Credit

if (is_dir($slideDir)) {
    $files = glob($slideDir . '*.jpg');
    if ($files && count($files) > 0) {
        $randomFile = $files[array_rand($files)];
        $heroImage = 'photos/slides_optimized/' . basename($randomFile);

        // Extract Credit from Filename (Name___Title.jpg)
        $filename = pathinfo($randomFile, PATHINFO_FILENAME);
        $parts = explode('___', $filename);
        if (count($parts) >= 1) {
            // Reformat name: "Firstname Lastname" -> "Firstname Lastname"
            $name = str_replace(['_', '-'], ' ', $parts[0]);
            $randomCredit = 'Photo Participant - ' . ucwords($name);
        }
    }
}
?>
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

        /* Style pour la bannière principale */
        .hero-banner {
            background-image: linear-gradient(rgba(10, 34, 64, 0.6), rgba(10, 34, 64, 0.8)), url('<?= $heroImage ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            transition: background-image 0.5s ease-in-out;
        }

        .animate-fade-in-up {
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-[#F8F8F8]">

    <header
        class="relative hero-banner h-screen min-h-[600px] flex flex-col items-center justify-center text-white p-4">
        <div class="animate-fade-in-up text-center">
            <img src="https://www.barrages-cfbr.eu/IMG/logo/siteon0.png?1572394244" alt="Logo CFBR"
                class="h-24 w-auto mb-8 bg-white/90 rounded p-2 mx-auto shadow-lg">

            <h1 class="text-4xl md:text-6xl font-bold mb-4">Concours Photo 2026</h1>
            <h2 class="text-xl md:text-3xl font-light italic">« Barrages : Entre nature et architecture »</h2>
            <p
                class="mt-6 text-lg md:text-xl text-[#FF9900] font-bold tracking-widest uppercase bg-[#0A2240]/50 px-4 py-2 rounded-full inline-block backdrop-blur-sm">
                Ouvert au Grand Public
            </p>

            <div class="mt-12 flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="upload.php"
                    class="bg-[#FF9900] text-[#0A2240] px-8 py-4 rounded-full text-lg font-bold hover:bg-white hover:text-[#0A2240] transition-all duration-300 shadow-[0_0_20px_rgba(255,153,0,0.5)] transform md:hover:scale-105">
                    <i class="fas fa-camera mr-2"></i> Participer Maintenant
                </a>

            </div>
        </div>
        <div class="absolute bottom-4 right-4 bg-black/60 text-white text-xs px-3 py-1 rounded-full backdrop-blur-sm">
            <i class="fas fa-camera mr-1"></i> <?= htmlspecialchars(str_replace('Photo Participant - ', '', $randomCredit)) ?>
        </div>
    </header>

    <main id="details">
        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-6">

                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-[#0A2240] mb-4">Catégories & Prix</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">Deux grandes catégories pour célébrer le
                        patrimoine hydraulique sous toutes ses formes.</p>
                </div>

                <div class="grid lg:grid-cols-2 gap-12">

                    <!-- Catégorie A -->
                    <div
                        class="bg-gray-50 rounded-2xl shadow-lg transform transition md:hover:scale-105 duration-300 overflow-hidden">
                        <!-- Image Left -->
                        <div class="relative group rounded-2xl overflow-hidden shadow-2xl h-80 lg:h-96 order-1">
                            <img src="photos/slides_optimized/Patrice MERIAUX___MÃriauxPatrice03.jpg"
                                alt="Intégration Environnementale"
                                class="w-full h-full object-cover transform transition duration-700 md:group-hover:scale-110">
                            <div
                                class="absolute bottom-4 right-4 bg-black/60 text-white text-xs px-3 py-1 rounded-full">
                                <i class="fas fa-camera mr-1"></i> P. MÉRIAUX
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="flex items-center mb-6">
                                <h3 class="text-2xl font-bold text-[#0A2240]">Intégration Environnementale</h3>
                            </div>
                            <p class="text-gray-600 mb-6 italic">Comment les ouvrages s'inscrivent-ils dans leur
                                environnement naturel ?</p>

                            <div class="space-y-6">
                                <!-- Prix Individuel -->
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <span class="text-xs font-bold text-[#FF9900] uppercase tracking-wide">Prix
                                        "Individuel"</span>
                                    <h4 class="text-lg font-bold text-[#0A2240] mt-1">Le Prix du Paysage & de
                                        l'Architecture</h4>
                                    <p class="text-gray-700 text-sm mt-2"><i
                                            class="fas fa-camera text-[#FF9900] mr-2"></i>Récompense la plus belle
                                        <strong>photo unique</strong> (esthétisme, harmonie, majesté).
                                    </p>
                                </div>

                                <!-- Prix Organisme -->
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <span class="text-xs font-bold text-[#0A2240] uppercase tracking-wide">Prix "Spécial
                                        Organisme"</span>
                                    <h4 class="text-lg font-bold text-[#0A2240] mt-1">Le Trophée du Patrimoine</h4>
                                    <p class="text-gray-700 text-sm mt-2"><i
                                            class="fas fa-images text-[#0A2240] mr-2"></i>Récompense la meilleure
                                        <strong>série de 5 photos</strong> montrant la diversité et l'insertion des
                                        ouvrages d'un même organisme.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catégorie B -->
                    <div
                        class="bg-gray-50 rounded-2xl shadow-lg transform transition md:hover:scale-105 duration-300 overflow-hidden">
                        <!-- Image -->
                        <div class="relative group rounded-2xl overflow-hidden shadow-2xl h-80 lg:h-96">
                            <img src="data/slide/François TRONEL/Las_Bambas_03_300dpi.jpg" alt="Savoir-Faire"
                                class="w-full h-full object-cover transition duration-500 md:group-hover:scale-110">
                            <div
                                class="absolute bottom-4 right-4 bg-black/60 text-white text-xs px-3 py-1 rounded-full">
                                <i class="fas fa-camera mr-1"></i> F. TRONEL
                            </div>
                        </div>

                        <div class="p-8">
                            <div class="flex items-center mb-6">
                                <h3 class="text-2xl font-bold text-[#0A2240]">Hommes & Femmes de l'Art</h3>
                            </div>
                            <p class="text-gray-600 mb-6 italic">Valoriser ceux qui conçoivent, construisent et
                                exploitent ces ouvrages.</p>

                            <div class="space-y-6">
                                <!-- Prix Individuel -->
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <span class="text-xs font-bold text-[#FF9900] uppercase tracking-wide">Prix
                                        "Individuel"</span>
                                    <h4 class="text-lg font-bold text-[#0A2240] mt-1">Le Prix du Geste Professionnel
                                    </h4>
                                    <p class="text-gray-700 text-sm mt-2"><i
                                            class="fas fa-camera text-[#FF9900] mr-2"></i>Récompense la plus belle
                                        <strong>photo unique</strong> valorisant l'humain au travail (expertise,
                                        sécurité, passion).
                                    </p>
                                </div>

                                <!-- Prix Organisme -->
                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                    <span class="text-xs font-bold text-[#0A2240] uppercase tracking-wide">Prix "Spécial
                                        Organisme"</span>
                                    <h4 class="text-lg font-bold text-[#0A2240] mt-1">Le Trophée du Savoir-Faire</h4>
                                    <p class="text-gray-700 text-sm mt-2"><i
                                            class="fas fa-images text-[#0A2240] mr-2"></i>Récompense le meilleur
                                        <strong>portfolio (5 photos)</strong> illustrant la richesse des métiers au sein
                                        d'une organisation.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </section>

        <!-- NOUVEAU: SECTION LAUREATS INTERNE -->
        <section class="py-16 lg:py-24 bg-[#0A2240] text-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <span class="text-[#FF9900] font-bold tracking-widest uppercase mb-2 block">Concours Interne
                        2026</span>
                    <h2 class="text-3xl lg:text-4xl font-bold mb-4">Les Lauréats de l'Édition Interne</h2>
                    <p class="text-gray-300 max-w-2xl mx-auto">
                        Découvrez les clichés primés lors de la remise des trophées du 29 janvier 2026.
                        Une source d'inspiration pour le grand public !
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 items-end">

                    <!-- Lauréat 1 -->
                    <div
                        class="group relative bg-[#051120] rounded-xl overflow-hidden shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                        <div class="aspect-w-4 aspect-h-3 overflow-hidden">
                            <img src="data/interne/01.PHOTO_MEMBRE_001.jpg" alt="Emergence"
                                class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-700">
                        </div>
                        <div class="p-6">
                            <div
                                class="absolute top-4 right-4 bg-[#FF9900] text-[#0A2240] font-bold px-3 py-1 rounded-full text-xs shadow-lg">
                                <i class="fas fa-trophy mr-1"></i> 1er Prix
                            </div>
                            <h3 class="text-xl font-bold mb-1 font-['Montserrat']">Emergence</h3>
                            <p class="text-sm text-[#FF9900] font-semibold mb-3">Mme Adelise LAFRIQUE</p>
                            <p class="text-xs text-gray-400 leading-relaxed italic border-l-2 border-[#FF9900] pl-3">
                                "Le barrage apparait progressivement partiellement enveloppé par la brume. L’ouvrage ne
                                domine pas le paysage, il s’en dégage."
                            </p>
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                                <span><i class="fas fa-map-marker-alt mr-1"></i> Barrage de Cap-de-Long</span>
                                <span>10/09/2024</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lauréat 2 -->
                    <div
                        class="group relative bg-[#051120] rounded-xl overflow-hidden shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                        <div class="aspect-w-4 aspect-h-3 overflow-hidden">
                            <img src="data/interne/02.PHOTO_MEMBRE_101.jpg" alt="Balcun de Campam"
                                class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-700">
                        </div>
                        <div class="p-6">
                            <div
                                class="absolute top-4 right-4 bg-gray-600 text-white font-bold px-3 py-1 rounded-full text-xs shadow-lg">
                                2ème Prix
                            </div>
                            <h3 class="text-xl font-bold mb-1 font-['Montserrat']">Balcun de Campam</h3>
                            <p class="text-sm text-[#FF9900] font-semibold mb-3">M. Daniel SANTIN</p>
                            <p class="text-xs text-gray-400 leading-relaxed italic border-l-2 border-[#FF9900] pl-3">
                                "Vue plongeante de la retenue des Laquets depuis le barrage de Gréziolles avec les
                                Pyrénées en arrière plan."
                            </p>
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                                <span><i class="fas fa-map-marker-alt mr-1"></i> Barrage des Laquets</span>
                                <span>09/07/2015</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lauréat 3 -->
                    <div
                        class="group relative bg-[#051120] rounded-xl overflow-hidden shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                        <div class="aspect-w-4 aspect-h-3 overflow-hidden">
                            <img src="data/interne/03.PHOTO_MEMBRE_087.jpg" alt="Le barrage du Mont-Cenis"
                                class="object-cover w-full h-full transform group-hover:scale-110 transition-transform duration-700">
                        </div>
                        <div class="p-6">
                            <div
                                class="absolute top-4 right-4 bg-blue-800 text-white font-bold px-3 py-1 rounded-full text-xs shadow-lg">
                                3ème Prix
                            </div>
                            <h3 class="text-xl font-bold mb-1 font-['Montserrat']">Mont-Cenis</h3>
                            <p class="text-sm text-[#FF9900] font-semibold mb-3">M. Stephan AIGOUY</p>
                            <p class="text-xs text-gray-400 leading-relaxed italic border-l-2 border-[#FF9900] pl-3">
                                "Barrage du Mont-Cenis, en Savoie. Photo prise depuis le versant du Lamet, qui domine la
                                rive gauche."
                            </p>
                            <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                                <span><i class="fas fa-map-marker-alt mr-1"></i> Mont-Cenis</span>
                                <span>01/09/2021</span>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-12 text-center">
                    <a href="slideshow.php" target="_blank"
                        class="inline-block bg-[#FF9900] text-[#0A2240] px-8 py-4 rounded-full text-lg font-bold hover:bg-white hover:text-[#0A2240] transition-all duration-300 shadow-lg transform hover:scale-105">
                        <i class="fas fa-images mr-2"></i> Voir l'intégralité des candidatures 2026
                    </a>
                    <p class="text-gray-400 text-sm mt-3 italic">Découvrez toute la richesse du concours interne en
                        diaporama plein écran.</p>
                </div>

            </div>


        </section>

        <!-- SECTION CALENDRIER (RETRO PLANNING) -->
        <section class="py-16 lg:py-24 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-[#0A2240] mb-4">Calendrier du Concours</h2>
                    <p class="text-gray-600 max-w-2xl mx-auto">Les étapes clés à retenir pour votre participation.</p>
                </div>

                <div class="relative max-w-4xl mx-auto">
                    <!-- Ligne verticale -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-1 bg-[#0A2240] hidden md:block">
                    </div>

                    <div class="space-y-12">
                        <!-- Etape 1 -->
                        <div class="flex flex-col md:flex-row items-center justify-between w-full">
                            <div
                                class="w-full md:w-5/12 order-2 md:order-1 md:text-right bg-blue-50 p-6 rounded-lg shadow-md border-l-4 border-[#0A2240] md:border-l-0 md:border-r-4">
                                <h3 class="font-bold text-[#0A2240] text-xl">Lancement</h3>
                                <div class="text-[#FF9900] font-bold mb-2">Printemps 2026</div>
                                <p class="text-gray-600 text-sm">Ouverture officielle du concours au Grand Public.
                                    Préparez vos objectifs !</p>
                            </div>
                            <div
                                class="z-10 bg-[#0A2240] text-white w-8 h-8 rounded-full flex items-center justify-center font-bold order-1 md:order-2 border-4 border-white shadow-lg mb-4 md:mb-0">
                                1</div>
                            <div class="w-full md:w-5/12 order-3 md:order-3"></div>
                        </div>

                        <!-- Etape 2 -->
                        <div class="flex flex-col md:flex-row items-center justify-between w-full">
                            <div class="w-full md:w-5/12 order-3 md:order-1"></div>
                            <div
                                class="z-10 bg-[#FF9900] text-[#0A2240] w-8 h-8 rounded-full flex items-center justify-center font-bold order-1 md:order-2 border-4 border-white shadow-lg mb-4 md:mb-0">
                                2</div>
                            <div
                                class="w-full md:w-5/12 order-2 md:order-3 bg-orange-50 p-6 rounded-lg shadow-md border-l-4 border-[#FF9900]">
                                <h3 class="font-bold text-[#0A2240] text-xl">Clôture des envois</h3>
                                <div class="text-red-500 font-bold mb-2">1er Octobre 2026</div>
                                <p class="text-gray-600 text-sm">Date limite impérative pour soumettre vos 5 meilleures
                                    photos.</p>
                            </div>
                        </div>

                        <!-- Etape 3 -->
                        <div class="flex flex-col md:flex-row items-center justify-between w-full">
                            <div
                                class="w-full md:w-5/12 order-2 md:order-1 md:text-right bg-blue-50 p-6 rounded-lg shadow-md border-l-4 border-[#0A2240] md:border-l-0 md:border-r-4">
                                <h3 class="font-bold text-[#0A2240] text-xl">Délibération du Jury</h3>
                                <div class="text-[#FF9900] font-bold mb-2">Octobre 2026</div>
                                <p class="text-gray-600 text-sm">Sélection des lauréats par notre jury d'experts et de
                                    photographes.</p>
                            </div>
                            <div
                                class="z-10 bg-[#0A2240] text-white w-8 h-8 rounded-full flex items-center justify-center font-bold order-1 md:order-2 border-4 border-white shadow-lg mb-4 md:mb-0">
                                3</div>
                            <div class="w-full md:w-5/12 order-3 md:order-3"></div>
                        </div>

                        <!-- Etape 4 -->
                        <div class="flex flex-col md:flex-row items-center justify-between w-full">
                            <div class="w-full md:w-5/12 order-3 md:order-1"></div>
                            <div
                                class="z-10 bg-[#FF9900] text-[#0A2240] w-10 h-10 rounded-full flex items-center justify-center font-bold order-1 md:order-2 border-4 border-white shadow-lg animate-bounce mb-4 md:mb-0">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div
                                class="w-full md:w-5/12 order-2 md:order-3 bg-[#0A2240] text-white p-6 rounded-lg shadow-xl border-l-4 border-[#FF9900]">
                                <h3 class="font-bold text-white text-xl">Remise des Prix</h3>
                                <div class="text-[#FF9900] font-bold mb-2">18-19 Novembre 2026</div>
                                <p class="text-gray-300 text-sm">Annonce des résultats lors du <strong>Colloque
                                        Prospective Eau</strong> à Aix-les-Bains.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-[#F8F8F8]">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold text-[#0A2240] mb-4">Comment Participer ?</h2>
                <p class="text-gray-600 mb-12 max-w-xl mx-auto">Une procédure simple et entièrement numérique pour
                    faciliter vos envois.</p>

                <div class="grid md:grid-cols-3 gap-8">
                    <div
                        class="bg-white p-8 rounded-xl shadow-lg border-b-4 border-[#0A2240] transition hover:-translate-y-2 lg:hover:scale-105 duration-300">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-camera text-3xl text-[#0A2240]"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0A2240] mb-4">1. Photographiez</h3>
                        <p class="text-gray-600">Sélectionnez vos 5 meilleures photos (JPEG). Soyez créatif et
                            respectez
                            le thème.</p>
                    </div>

                    <div
                        class="bg-white p-8 rounded-xl shadow-lg border-b-4 border-[#FF9900] transition hover:-translate-y-2 lg:hover:scale-105 duration-300">
                        <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-clipboard-check text-3xl text-[#FF9900]"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0A2240] mb-4">2. Préparez</h3>
                        <p class="text-gray-600">Notez les titres de vos œuvres et préparez une courte note
                            d'intention
                            pour chacune.</p>
                    </div>

                    <div
                        class="bg-white p-8 rounded-xl shadow-lg border-b-4 border-[#0A2240] transition hover:-translate-y-2 lg:hover:scale-105 duration-300 relative overflow-hidden">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-cloud-upload-alt text-3xl text-[#0A2240]"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0A2240] mb-4">3. Déposez</h3>
                        <p class="text-gray-600 mb-6">Utilisez notre formulaire en ligne sécurisé avant le 1er
                            Octobre.</p>
                        <a href="upload.php"
                            class="inline-block bg-[#0A2240] text-white px-6 py-2 rounded-full font-bold hover:bg-[#FF9900] transition-colors">
                            Accéder au formulaire
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-white relative overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-[#FF9900] opacity-5"></div>

            <div class="container mx-auto px-6 text-center relative z-10">
                <h2 class="text-3xl lg:text-4xl font-bold text-[#0A2240] mb-6">Prix & Récompenses</h2>
                <p class="mb-12 text-gray-600">Des récompenses exceptionnelles pour célébrer le centenaire.</p>

                <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    <!-- Top 3 -->
                    <div
                        class="bg-white border border-gray-100 rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-shadow">
                        <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-ticket-alt text-3xl text-[#0A2240]"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0A2240] mb-2">Les 3 Premiers<br><span
                                class="text-lg font-normal text-gray-500">de chaque catégorie</span></h3>
                        <div class="w-12 h-1 bg-[#FF9900] mx-auto mb-4"></div>
                        <p class="text-gray-600 mb-4">Recevront une <strong>invitation officielle avec pass</strong>
                            pour assister au prestigieux :</p>
                        <div class="bg-[#0A2240] text-white py-2 px-4 rounded-lg font-bold">
                            Colloque Prospective Eau (18-19 Nov) à Aix-les-Bains
                        </div>
                    </div>

                    <!-- 1er Prix -->
                    <div
                        class="bg-white border-2 border-[#FF9900] rounded-2xl p-8 shadow-2xl transform hover:-translate-y-2 transition-transform relative">
                        <div
                            class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-[#FF9900] text-white px-4 py-1 rounded-full text-sm font-bold shadow-md">
                            BONUS 1er PRIX
                        </div>
                        <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-crown text-3xl text-[#FF9900]"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-[#0A2240] mb-2">Le Grand Gagnant<br><span
                                class="text-lg font-normal text-gray-500">de chaque catégorie</span></h3>
                        <div class="w-12 h-1 bg-[#FF9900] mx-auto mb-4"></div>
                        <p class="text-gray-600 mb-4">En plus du pass, vivez une expérience inoubliable :</p>
                        <div
                            class="bg-gradient-to-r from-[#FF9900] to-[#FFCC00] text-[#0A2240] py-3 px-4 rounded-lg font-bold shadow-md">
                            <i class="fas fa-hard-hat mr-2"></i> Visite VIP d'un barrage remarquable en France
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 lg:py-24 bg-[#0A2240] text-white">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-3xl lg:text-4xl font-bold mb-8">Le temps presse !</h2>

                <div
                    class="bg-white/10 backdrop-blur-md max-w-4xl mx-auto p-8 rounded-2xl shadow-2xl mb-12 border border-white/20">
                    <div id="countdown" class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
                        <div>
                            <div class="bg-white text-[#0A2240] rounded-lg p-4 shadow-lg">
                                <span id="days" class="text-3xl md:text-5xl font-bold tabular-nums">00</span>
                            </div>
                            <p class="text-xs md:text-sm uppercase tracking-widest mt-2 font-semibold">Jours</p>
                        </div>
                        <div>
                            <div class="bg-white text-[#0A2240] rounded-lg p-4 shadow-lg">
                                <span id="hours" class="text-3xl md:text-5xl font-bold tabular-nums">00</span>
                            </div>
                            <p class="text-xs md:text-sm uppercase tracking-widest mt-2 font-semibold">Heures</p>
                        </div>
                        <div>
                            <div class="bg-white text-[#0A2240] rounded-lg p-4 shadow-lg">
                                <span id="minutes" class="text-3xl md:text-5xl font-bold tabular-nums">00</span>
                            </div>
                            <p class="text-xs md:text-sm uppercase tracking-widest mt-2 font-semibold">Minutes</p>
                        </div>
                        <div>
                            <div class="bg-white text-[#0A2240] rounded-lg p-4 shadow-lg">
                                <span id="seconds" class="text-3xl md:text-5xl font-bold tabular-nums">00</span>
                            </div>
                            <p class="text-xs md:text-sm uppercase tracking-widest mt-2 font-semibold">Secondes</p>
                        </div>
                    </div>
                    <p class="mt-8 text-[#FF9900] font-bold text-lg animate-pulse">Clôture : 1er Octobre 2026 à
                        23h59
                    </p>
                </div>

                <a href="upload.php"
                    class="bg-[#FF9900] text-[#0A2240] px-10 py-5 rounded-full text-xl font-bold hover:bg-white transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1 inline-flex items-center">
                    <i class="fas fa-paper-plane mr-3"></i> Déposer mes photos
                </a>
            </div>
        </section>

    </main>

    <footer class="bg-[#051120] text-white py-12 border-t border-gray-800">
        <div class="container mx-auto px-6 text-center">
            <img src="https://www.barrages-cfbr.eu/IMG/logo/siteon0.png?1572394244" alt="Logo CFBR"
                class="h-12 w-auto mb-6 mx-auto bg-white rounded p-1">
            <p class="text-gray-400 mb-4">© 2026 Comité Français des Barrages et Réservoirs</p>

            <div class="flex flex-wrap justify-center gap-6 text-gray-400 mb-8 text-sm md:text-base">
                <a href="#" class="hover:text-[#FF9900] transition-colors">Règlement complet</a>
                <span class="text-gray-700 hidden md:inline">|</span>
                <a href="https://www.barrages-cfbr.eu/Mentions-legales.html"
                    class="hover:text-[#FF9900] transition-colors">Mentions légales</a>
                <span class="text-gray-700 hidden md:inline">|</span>
                <a href="https://www.barrages-cfbr.eu/spip.php?page=plan"
                    class="hover:text-[#FF9900] transition-colors">Plan du site</a>
                <span class="text-gray-700 hidden md:inline">|</span>
                <a href="https://www.barrages-cfbr.eu/Contactez-nous.html"
                    class="hover:text-[#FF9900] transition-colors">Contact</a>
                <span class="text-gray-700 hidden md:inline">|</span>
                <a href="jury_login.php" class="hover:text-[#FF9900] transition-colors font-semibold">Espace Jury</a>
            </div>

            <div class="flex justify-center space-x-6">
                <a href="https://www.linkedin.com/company/cfbr/"
                    class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#0077b5] hover:text-white transition-colors"><i
                        class="fab fa-linkedin-in"></i></a>
                <a href="https://www.instagram.com/dam_nature100/"
                    class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-[#E1306C] hover:text-white transition-colors"><i
                        class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    <script>
        // DATE CLÔTURE : 1er Octobre 2026
        const countdownDate = new Date("Oct 1, 2026 23:59:59").getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = countdownDate - now;

            if (distance < 0) {
                document.getElementById("countdown").innerHTML = "<div class='col-span-full text-2xl font-bold text-white'>Les soumissions sont closes !</div>";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("days").innerText = days < 10 ? "0" + days : days;
            document.getElementById("hours").innerText = hours < 10 ? "0" + hours : hours;
            document.getElementById("minutes").innerText = minutes < 10 ? "0" + minutes : minutes;
            document.getElementById("seconds").innerText = seconds < 10 ? "0" + seconds : seconds;
        }

        setInterval(updateCountdown, 1000);
        updateCountdown(); // Init immédiat
    </script>

</body>

</html>