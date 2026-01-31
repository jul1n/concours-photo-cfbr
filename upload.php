<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépôt Candidature – Concours Photo CFBR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

            <form action="process_upload.php" method="POST" enctype="multipart/form-data" id="uploadForm" class="space-y-6">

                <!-- Identité -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#FF9900]">1. Vos Coordonnées</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Prénom</label>
                            <input type="text" name="firstname" required
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-[#0A2240]">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Nom</label>
                            <input type="text" name="lastname" required
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-[#0A2240]">
                        </div>
                        <div class="col-span-2">
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
                            <li>Format : JPEG ou PNG (Max 20 Mo/photo)</li>
                            <li>Résolution recommandée : <strong>4960px</strong> (grand côté).</li>
                            <li>Donnez un titre à chaque photo.</li>
                        </ul>
                    </div>

                    <div id="drop-zone"
                        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:bg-gray-50 transition cursor-pointer">
                        <p class="text-gray-500 mb-2">Glissez vos fichiers ici ou cliquez pour sélectionner</p>
                        <!-- Input file caché standard -->
                         <input type="file" id="fileInput" name="photos[]" multiple accept="image/*" class="hidden">
                        <button type="button" onclick="document.getElementById('fileInput').click()"
                            class="bg-[#0A2240] text-white px-4 py-2 rounded">Choisir des fichiers</button>
                    </div>

                    <!-- Container pour les inputs générés dynamiquement -->
                    <div id="photosContainer" class="space-y-4 mt-4">
                        <!-- Les items photo seront injectés ici -->
                    </div>
                </div>

                <!-- Signature & Règlements -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#FF9900]">3. Règlements et Autorisations</h2>
                    
                    <!-- Box Scrollable Règlement (Texte complet) -->
                    <div id="rulesBox" class="border p-4 rounded bg-gray-50 text-sm h-60 overflow-y-auto mb-4 border-l-4 border-[#0A2240] text-justify space-y-2">
                        <h3 class="font-bold text-[#0A2240] text-center mb-2">Règlement Complet du Concours Photo 2026</h3>
                        
                       <div class="whitespace-pre-line text-xs text-gray-700 mt-4 leading-relaxed">
<strong>Règlement du Concours Photo Interne 2026 – CFBR</strong>
« Barrages : Entre nature et architecture »

<strong>Préambule</strong>
Le Comité Français des Barrages et Réservoirs (CFBR) invite ses membres à porter leur regard d'artiste sur ces géants que sont les digues, les canaux et les barrages qu’ils soient en remblais ou en béton ; et à révéler leur intégration environnementale, leur majesté architecturale et leur rôle pour l’homme et la nature. Montrez-nous comment les barrages et autres ouvrages hydrauliques coexistent avec la nature, s'opposent ou s'allient.

<strong>Article 1 – Organisateur et Objet du Concours</strong>
Le Comité Français des Barrages et Réservoirs (CFBR) organise en 2026 un concours photo gratuit réservé aux membres du CFBR. 
L'objectif est de valoriser les barrages et autres ouvrages hydrauliques (digues, canaux, …), en mettant en lumière leur intégration dans l'environnement naturel et paysager, leur dimension architecturale et les multiples usages de l’eau qu'ils permettent (production hydroélectrique, soutien d'étiage, eau potable, irrigation, protection contre les crues, loisirs, biodiversité, etc.).

<strong>Article 2 – Calendrier Prévisionnel</strong>
Le concours se déroulera selon le calendrier prévisionnel suivant :
•	Lancement du concours : Le concours sera ouvert à partir du 1er décembre 2025, avec une annonce officielle sur le site cfbr.eu, ainsi que par mail auprès de ses membres.
•	Clôture des soumissions : La date limite pour l'envoi des photographies est fixée au 6 janvier 2026 à 23h59 (CET, heure de Paris). L'heure de réception du courriel de participation fera foi.
•	Délibération : Au cours du mois de janvier 2026, le jury procèdera à la sélection des finalistes et à leur classement.
•	Remise des prix : La cérémonie de remise des prix et le vernissage de l'exposition se tiendront le 29 janvier 2026 lors du symposium CFBR.

<strong>Article 3 – Conditions de Participation</strong>
•	Être membre du CFBR.
•	La participation est gratuite.
•	Chaque participant(e) peut soumettre un maximum de cinq (5) photographies.

<strong>Article 4 – Caractéristiques Techniques des Photographies</strong>
•	Format : Fichiers JPEG haute qualité ou TIFF non compressé.
•	Résolution : 300 dpi minimum, avec au moins 3 000 pixels sur le plus grand côté.
•	Taille de fichier : Entre 3 Mo et 25 Mo.
•	Nommage du fichier : Pour assurer une bonne gestion des soumissions, chaque fichier doit être impérativement nommé comme suit : NomPrénom_NuméroDeLaPhoto.jpg.
Le numéro (de 1 à 5) doit correspondre à l'ordre de présentation de la photographie dans votre document texte d'accompagnement.
Exemple : Pour une soumission de trois photos, les fichiers seront nommés : DupontChloe_01.jpg, DupontChloe_02.jpg, et DupontChloe_03.jpg.
Authenticité : Les retouches légères (luminosité, contraste, colorimétrie, recadrage) sont autorisées. Les photomontages, les manipulations profondes de l'image et l'utilisation d'intelligences artificielles génératives sont interdits et entraîneront la disqualification.

<strong>Article 5 – Modalités de Soumission</strong>
Pour être valide, la participation doit être envoyée par courriel à l’adresse concoursphoto2026@barrages-cfbr.eu avant la date de clôture. Le courriel devra contenir :
•	Les photographies en pièces jointes ou via un lien de téléchargement.
•	Un document texte (DOCX, TXT PDF) listant pour chaque photo :
o	Le numéro du fichier (ex: 01) et le titre de l’œuvre (70 caractères max.).
o	Le nom de l’ouvrage, le lieu précis et la date de la prise de vue.
o	Une note d’intention (optionnel, 500 caractères max.) expliquant la démarche ou le contexte, les aspects mis en avant, le message....
•	Le présent règlement daté et signé, précédé de la mention « Lu et approuvé ».
•	Le formulaire de cession de droits (Annexe A) dûment complété et signé.
•	Le cas échéant, le formulaire de renonciation au droit à l’image (Annexe B) pour toute personne identifiable sur une photo.
Tout dossier incomplet ou non conforme sera rejeté.

<strong>Article 6 – Jury et Critères de Sélection</strong>
Le jury sera désigné par la Commission Exécutive du CFBR. Il sera en partie composé de professionnels des barrages. Pour garantir une impartialité totale, les photographies seront présentées aux membres du jury de manière anonyme. Le nom des auteurs ne sera révélé qu’après la délibération finale. Ses décisions sont souveraines et sans appel. Les critères de sélection sont :
•	Pertinence au thème : Force du lien entre l’ouvrage et son environnement.
•	Originalité et créativité : Vision artistique unique et approche personnelle.
•	Qualité esthétique et technique : Composition, lumière, maitrise de la mise au point.
•	Portée documentaire ou pédagogique : Capacité de la photo à informer, à faire comprendre un enjeu.

<strong>Article 7 – Prix et Récompenses</strong>
Les lauréats du concours recevront les dotations financières et les récompenses suivantes :
•	Le premier prix est doté d'une somme de 300 €.
•	Le deuxième prix est d'une valeur de 200 €.
•	Le troisième prix s'élève à 100 €.
Récompenses additionnelles pour tous les lauréats :
En plus de la dotation financière, les lauréats recevront un tirage d'art de leur œuvre. Pour les lauréats du 1er prix, ce tirage d'art prendra la forme d'une impression sur support métallique.

<strong>Article 8 – Droits de Propriété Intellectuelle et Droit à l'Image</strong>
Le participant déclare et garantit être l’auteur exclusif des photographies soumises, qu'elles sont issues de prises de vues réelles et ne sont pas le produit d'une intelligence artificielle générative, et qu'elles ne portent atteinte à aucun droit de tiers.
Le participant est seul responsable de l'obtention des autorisations nécessaires, notamment en ce qui concerne le droit à l'image des personnes éventuellement représentées et les autorisations d'accès à des propriétés privées ou réglementées.
En participant, chaque photographe accepte de céder au CFBR, à titre gratuit, une licence d'utilisation non exclusive des droits patrimoniaux sur les œuvres primées et présélectionnées. Cette licence est valable pour le monde entier et pour la durée légale de protection du droit d'auteur.
Cette licence autorise le CFBR à reproduire, représenter, adapter et diffuser les photos sur tous ses supports de communication (site web, réseaux sociaux, rapports, newsletters, expositions, publications papier, etc.), à des fins non commerciales de promotion de ses activités et du patrimoine hydraulique.
Le crédit photo (Nom de l'auteur) sera systématiquement et lisiblement mentionné à chaque utilisation. L'auteur conserve la pleine propriété de son œuvre et reste libre de l'utiliser par ailleurs.
Les photographies non présélectionnées resteront la pleine propriété de leurs auteurs, et le CFBR ne disposera d'aucun droit d'utilisation sur celles-ci.

<strong>Article 9 – Expositions et Communication</strong>
Les photographies finalistes et lauréates feront l'objet d'une large diffusion, incluant :
•	Une exposition inaugurale au colloque des 100 ans du Cfbr.
•	Une exposition itinérante potentielle en 2027 dans des musées, espaces publics ou lors d'événements partenaires.
•	Des publications sur les supports de communication du CFBR.

<strong>Article 10 – Données Personnelles</strong>
Conformément au RGPD, les données personnelles collectées sont uniquement destinées à la gestion du concours. Elles ne seront pas transmises à des tiers. Chaque participant dispose d'un droit d’accès, de rectification et de suppression en contactant : dpo@barrages-cfbr.eu 

<strong>Article 11 – Responsabilités et Annulation</strong>
Le CFBR ne pourra être tenu responsable en cas de problème de transmission, de perte ou de piratage des fichiers. Le CFBR se réserve le droit de modifier, reporter ou annuler le concours en cas de force majeure, sans qu'aucune indemnité ne puisse être réclamée.

<strong>Article 12 – Acceptation du Règlement</strong>
La participation à ce concours photo implique l'acceptation pleine, entière et sans réserve du présent règlement. Tout litige non résolu à l'amiable sera tranché souverainement par le CFBR.
                        </div>
                    </div>

                    <!-- Instagram Option -->
                    <div class="flex items-start space-x-3 bg-white p-3 rounded border border-gray-200">
                        <input type="checkbox" name="instagram" id="instagram" value="1"
                            class="mt-1 w-5 h-5 text-[#FF9900] focus:ring-[#FF9900]">
                        <label for="instagram" class="text-sm cursor-pointer select-none">
                            <strong>Option Instagram :</strong> J'autorise le CFBR à publier mes photos sur leur compte Instagram officiel (@dam_nature100) en me créditant.
                        </label>
                    </div>

                    <!-- Signature Check -->
                    <div class="flex items-start space-x-3 mt-4">
                        <input type="checkbox" name="signature" id="signature" required disabled
                            class="mt-1 w-5 h-5 text-gray-400 focus:ring-[#0A2240] cursor-not-allowed">
                        <label for="signature" id="lblSignature" class="text-sm text-gray-500 select-none">
                            Je reconnais avoir lu le règlement et les annexes, et je signe numériquement cette cession de droits.
                            <br><span class="text-xs">Veuillez prendre le temps de lire le règlement ci-dessus pour activer cette case.</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 text-center">
                    <button type="submit" id="submitBtn" disabled
                        class="bg-gray-400 text-white px-8 py-4 rounded-full font-bold text-xl transition shadow-lg w-full md:w-auto cursor-not-allowed">
                        <i class="fas fa-paper-plane mr-2"></i> Valider ma participation
                    </button>
                </div>

            </form>
        </div>
    </main>

    <script>
        const fileInput = document.getElementById('fileInput');
        const photosContainer = document.getElementById('photosContainer');
        const dropZone = document.getElementById('drop-zone');
        const submitBtn = document.getElementById('submitBtn');
        const signatureCheckbox = document.getElementById('signature');
        const lblSignature = document.getElementById('lblSignature');
        const rulesBox = document.getElementById('rulesBox');

        // DataTransfer object to manage files
        const dt = new DataTransfer();

        // 1. Handle file selection via button
        fileInput.addEventListener('change', function() {
            for(let i=0; i<this.files.length; i++){
                if(dt.items.length < 5) dt.items.add(this.files[i]);
            }
            // Do NOT update fileInput.files here immediately if you want to keep adding.
            // But standard behavior is fileInput replace. 
            // We use dt to aggregate.
            renderPhotos();
        });

        // 2. Handle Drag & Drop
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('bg-blue-100'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('bg-blue-100'); });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-blue-100');
            for(let i=0; i<e.dataTransfer.files.length; i++){
                if(dt.items.length < 5) dt.items.add(e.dataTransfer.files[i]);
            }
            renderPhotos();
        });
        
        // 3. Sync files before submit
        document.getElementById('uploadForm').addEventListener('submit', function() {
            fileInput.files = dt.files;
        });

        function renderPhotos() {
            photosContainer.innerHTML = '';
            const files = dt.files;

            if (files.length === 0) {
                 photosContainer.innerHTML = '<p class="text-gray-400 text-sm italic text-center">Aucune photo sélectionnée.</p>';
                 // Reset fileInput value so change event triggers even if same file selected again (optional)
                 // fileInput.value = ''; 
                 return;
            }

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const div = document.createElement('div');
                div.className = "bg-white p-4 rounded shadow border border-gray-200 animate-fade-in-down";
                
                div.innerHTML = `
                    <div class="flex flex-col md:flex-row md:items-start gap-4">
                        <div class="w-full md:w-1/4 flex-shrink-0 text-center bg-gray-100 p-2 rounded">
                            <i class="fas fa-image text-4xl text-gray-300 mb-2 block"></i>
                            <span class="text-xs text-gray-600 break-all line-clamp-2" title="${file.name}">${file.name}</span>
                            <span class="text-xs text-gray-400 block">${(file.size / 1024 / 1024).toFixed(2)} Mo</span>
                            <button type="button" class="mt-2 text-red-500 text-xs underline hover:text-red-700 font-bold" onclick="removeFile(${i})">Supprimer</button>
                        </div>
                        <div class="flex-grow space-y-3 w-full">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Titre de l'œuvre <span class="text-red-500">*</span></label>
                                <input type="text" name="titles[]" required placeholder="Ex: Barrage au crépuscule" 
                                    class="w-full border-b-2 border-gray-200 focus:border-[#0A2240] outline-none py-1 transition bg-transparent text-[#0A2240] font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Note d'intention (Facultatif)</label>
                                <textarea name="descriptions[]" rows="2" placeholder="Quelques mots sur votre démarche, le lieu, l'instant..."
                                    class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-[#0A2240]"></textarea>
                            </div>
                        </div>
                    </div>
                `;
                photosContainer.appendChild(div);
            }
        }

        window.removeFile = function(index) {
            dt.items.remove(index);
            renderPhotos();
        }

        // --- Logic Lecture Règlement ---
        let startTime = Date.now();
        let minReadTime = 5000; // 5 sec min for safety
        let hasScrolled = false;

        rulesBox.addEventListener('scroll', function() {
            if(this.scrollTop + this.clientHeight >= this.scrollHeight - 50) {
                hasScrolled = true;
                checkReadStatus();
            }
        });

        function checkReadStatus() {
            let timeElapsed = Date.now() - startTime;
            if (timeElapsed > minReadTime && hasScrolled) {
                enableSignature();
            }
        }
        
        function enableSignature() {
            signatureCheckbox.disabled = false;
            signatureCheckbox.classList.remove('text-gray-400', 'cursor-not-allowed');
            signatureCheckbox.classList.add('text-[#0A2240]', 'cursor-pointer');
            lblSignature.classList.remove('text-gray-500');
            lblSignature.classList.add('text-black', 'cursor-pointer');
            lblSignature.innerHTML = `Je reconnais avoir lu le règlement et les annexes, et je signe numériquement cette cession de droits.<br><span class="text-xs text-green-600 font-bold"><i class="fas fa-check"></i> Lecture confirmée</span>`;
        }

        signatureCheckbox.addEventListener('change', function() {
            if(this.checked) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.add('bg-[#FF9900]', 'text-[#0A2240]', 'hover:bg-[#0A2240]', 'hover:text-white', 'cursor-pointer');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.remove('bg-[#FF9900]', 'text-[#0A2240]', 'hover:bg-[#0A2240]', 'hover:text-white', 'cursor-pointer');
            }
        });
        
        // Timer Check Loop pour les "rapides"
        // On écoute sur le parent pour capturer le clic même si disabled
        signatureCheckbox.parentElement.addEventListener('click', function(e) {
            if(signatureCheckbox.disabled) {
                // Prevent default header click behaviors if any
                // e.preventDefault(); 
                
                let timeElapsed = Date.now() - startTime;
                if(!hasScrolled) {
                    alert("⚠️ Veuillez faire défiler le règlement jusqu'en bas pour confirmer que vous l'avez parcouru.");
                } else if (timeElapsed < minReadTime) {
                    let remaining = Math.ceil((minReadTime - timeElapsed)/1000);
                    alert("⏳ Prenez encore " + remaining + " secondes pour bien lire les points importants !");
                }
            }
        });

    </script>
</body>

</html>