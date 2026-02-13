<!DOCTYPE html>
<?php require_once __DIR__ . '/includes/analytics.php'; ?>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Dossier - Concours Photo CFBR</title>
    <?php include __DIR__ . '/includes/pwa_loader.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        h2 {
            font-family: var(--font-title);
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 text-[#0A2240] rounded-full mb-4">
                <i class="fas fa-folder-open text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-[#0A2240]">Mon Dossier</h1>
            <p class="text-gray-500 text-sm mt-2">Retrouvez votre candidature, vos photos et votre reçu PDF.</p>
        </div>

        <form action="dossier_auth.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Adresse Email utilisée lors du
                    dépôt</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" required placeholder="votre@email.com"
                        class="pl-10 block w-full px-4 py-3 border border-gray-200 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#FF9900] focus:border-transparent transition-all">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-[#0A2240] text-white py-4 px-6 rounded-xl font-bold hover:bg-[#FF9900] hover:text-[#0A2240] shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                Recevoir mon lien d'accès <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
            <a href="index.php"
                class="text-sm text-gray-400 hover:text-[#0A2240] transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-chevron-left text-[10px]"></i> Retour à l'accueil
            </a>
        </div>
    </div>

</body>

</html>