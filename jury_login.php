<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Jury - Concours Photo CFBR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
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
        h3 {
            font-family: var(--font-title);
        }
    </style>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <div class="text-center mb-6">
            <img src="https://www.barrages-cfbr.eu/IMG/logo/siteon0.png?1572394244" alt="Logo CFBR"
                class="h-16 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-[#0A2240]">Espace Jury</h1>
            <p class="text-gray-600 text-sm mt-2">Veuillez renseigner votre email pour recevoir votre lien de connexion.
            </p>
        </div>

        <form action="jury_process_login.php" method="POST" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Adresse Email</label>
                <input type="email" id="email" name="email" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-[#FF9900] focus:border-[#FF9900]">
            </div>

            <button type="submit"
                class="w-full bg-[#0A2240] text-white py-2 px-4 rounded-md font-bold hover:bg-[#FF9900] hover:text-[#0A2240] transition-colors duration-300">
                Recevoir le lien
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="index.php" class="text-sm text-gray-500 hover:text-[#0A2240] underline">Retour à l'accueil</a>
        </div>
    </div>

</body>

</html>