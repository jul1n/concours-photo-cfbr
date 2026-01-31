<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépôt Candidature – Concours Photo CFBR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #F8F8F8;
        }

        h1,
        h2,
        h3 {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="bg-[#F8F8F8] text-[#0A2240]">

    <header class="bg-[#0A2240] text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="flex items-center space-x-2">
                <img src="https://www.barrages-cfbr.eu/IMG/logo/siteon0.png?1572394244" alt="Logo"
                    class="h-10 bg-white rounded p-1">
                <span class="font-bold text-lg hidden md:block">Concours CFBR</span>
            </a>
            <a href="index.php" class="hover:text-[#FF9900]">Retour Accueil</a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-3xl">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold mb-6 text-center border-b pb-4">Dépôt de Candidature</h1>

            <form action="process_upload.php" method="POST" enctype="multipart/form-data" id="uploadForm"
                class="space-y-6">

                <!-- Identité -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#FF9900]">1. Vos Coordonnées</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Nom Complet</label>
                            <input type="text" name="name" required
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-[#0A2240]">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Email Professionnel</label>
                            <input type="email" name="email" required
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-[#0A2240]">
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#FF9900]">2. Vos Photos (Max 5)</h2>
                    <div class="bg-blue-50 p-4 rounded text-sm text-[#0A2240]">
                        <ul class="list-disc list-inside">
                            <li>Format : JPEG ou PNG</li>
                            <li>Résolution recommandée : <strong>4960px</strong> (grand côté) pour impression A3.</li>
                            <li>Poids max par fichier : 20 Mo.</li>
                        </ul>
                    </div>

                    <div id="drop-zone"
                        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:bg-gray-50 transition cursor-pointer">
                        <p class="text-gray-500 mb-2">Glissez vos fichiers ici ou cliquez pour sélectionner</p>
                        <input type="file" name="photos[]" multiple accept="image/*" class="hidden" id="fileInput"
                            max="5">
                        <button type="button" onclick="document.getElementById('fileInput').click()"
                            class="bg-[#0A2240] text-white px-4 py-2 rounded">Choisir des fichiers</button>
                    </div>

                    <!-- Preview List -->
                    <div id="fileList" class="space-y-2"></div>
                </div>

                <!-- Signature -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#FF9900]">3. Signature Électronique</h2>
                    <div class="border p-4 rounded bg-gray-50 text-sm h-40 overflow-y-auto mb-4">
                        <p class="font-bold">Accord de cession de droits :</p>
                        <p>Je certifie être l'auteur des photographies déposées...</p>
                        <p>J'autorise le CFBR à utiliser ces images...</p>
                        <!-- (Texte complet à insérer si fourni) -->
                    </div>

                    <div class="flex items-start space-x-3">
                        <input type="checkbox" name="signature" id="signature" required
                            class="mt-1 w-5 h-5 text-[#0A2240]">
                        <label for="signature" class="text-sm">
                            Je reconnais avoir lu le règlement et je signe numériquement cette cession de droits.
                            <br><span class="text-xs text-gray-500">Un log technique (IP + Date) sera conservé pour
                                valeur légale.</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 text-center">
                    <button type="submit"
                        class="bg-[#FF9900] text-[#0A2240] px-8 py-4 rounded-full font-bold text-xl hover:bg-[#0A2240] hover:text-white transition shadow-lg w-full md:w-auto">
                        <i class="fas fa-paper-plane mr-2"></i> Valider ma participation
                    </button>
                </div>

            </form>
        </div>
    </main>

    <script>
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const dropZone = document.getElementById('drop-zone');

        // Drag & Drop visual feedback
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('bg-blue-100'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('bg-blue-100'); });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-blue-100');
            fileInput.files = e.dataTransfer.files;
            updateFileList();
        });

        fileInput.addEventListener('change', updateFileList);

        function updateFileList() {
            fileList.innerHTML = '';
            const files = fileInput.files;
            if (files.length > 5) {
                alert("Maximum 5 photos autorisées !");
                fileInput.value = "";
                return;
            }

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const div = document.createElement('div');
                div.className = "flex justify-between items-center bg-white p-3 rounded shadow-sm border";

                // Analyse JS pré-upload (taille)
                let sizeCheck = "";
                if (file.size > 20 * 1024 * 1024) sizeCheck = '<span class="text-red-600 font-bold ml-2">TROP LOURD (>20Mo)</span>';

                // Tentative lecture dimensions (async mais basique)
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = new Image();
                    img.onload = function () {
                        let resWarning = "";
                        const maxSide = Math.max(this.width, this.height);
                        if (maxSide < 3900) {
                            resWarning = '<span class="text-orange-500 text-xs block"><i class="fas fa-exclamation-triangle"></i> Résolution faible (' + maxSide + 'px)</span>';
                        }
                        div.querySelector('.meta').innerHTML += resWarning;
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);

                div.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-image text-gray-400 text-xl"></i>
                        <div>
                            <div class="font-semibold text-sm truncate max-w-[200px]">${file.name}</div>
                            <div class="text-xs text-gray-500 meta">${(file.size / 1024 / 1024).toFixed(2)} Mo ${sizeCheck}</div>
                        </div>
                    </div>
                    <span class="text-green-600"><i class="fas fa-check-circle"></i></span>
                `;
                fileList.appendChild(div);
            }
        }
    </script>
</body>

</html>