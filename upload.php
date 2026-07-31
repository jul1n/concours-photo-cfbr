<?php require_once __DIR__ . '/core/auth.php'; require_once __DIR__ . '/includes/analytics.php'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dépôt Candidature – Concours Photo CFBR</title>
    <?php include __DIR__ . '/includes/pwa_loader.php'; ?>
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
                <img src="assets/logo_cfbr_100_ans.png" alt="Logo" class="h-10 bg-white rounded p-1">
                <span class="font-bold text-lg hidden md:block">Concours CFBR</span>
            </a>
            <a href="index.php" class="hover:text-[#FF9900]">Retour Accueil</a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-3xl">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-3xl font-bold mb-6 text-center border-b pb-4">Dépôt de Candidature</h1>

            <form action="core/process_upload.php" method="POST" enctype="multipart/form-data" id="uploadForm"
                class="space-y-6">
                <?php csrf_field(); ?>

                <!-- Identité & Catégorie -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#FF9900]">1. Catégorie & Coordonnées</h2>

                    <!-- Choix Catégorie -->
                    <div class="bg-gray-50 p-4 rounded border border-gray-200 mb-4 space-y-4">
                        <div id="categoryFieldContainer">
                            <label class="block font-bold mb-2 text-[#0A2240]">Dans quelle catégorie participez-vous ?
                                <span class="text-red-500">*</span></label>
                            <select name="category" id="category"
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-[#0A2240] bg-white cursor-pointer"
                                required>
                                <option value="" disabled selected>-- Sélectionnez une catégorie --</option>
                                <option value="cat1">Catégorie 1 : Intégration Environnementale</option>
                                <option value="cat2">Catégorie 2 : Femmes & Hommes de l'Art</option>
                            </select>
                        </div>

                        <div id="categoryCorporateContainer" class="hidden">
                            <label class="block font-bold mb-2 text-[#0A2240]">Catégorie de participation</label>
                            <div class="p-3 bg-blue-50 border border-blue-100 text-[#0A2240] font-bold rounded-lg flex items-center">
                                <i class="fas fa-building mr-2 text-[#FF9900]"></i> Prix Spécial Organisme
                            </div>
                        </div>

                        <!-- Choix Type Candidature -->
                        <div>
                            <label class="block font-bold mb-2 text-[#0A2240]">Type de candidature <span
                                    class="text-red-500">*</span></label>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="candidacy_type" value="individual"
                                        class="form-radio text-[#FF9900] focus:ring-[#0A2240]" checked
                                        onchange="toggleCompanyField()">
                                    <span class="ml-2 font-semibold">Candidature Individuelle</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="candidacy_type" value="corporate"
                                        class="form-radio text-[#0A2240] focus:ring-[#0A2240]"
                                        onchange="toggleCompanyField()">
                                    <span class="ml-2 font-semibold">Candidature d'entreprise / association</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Grid Container Removed for better vertical control -->
                    <div class="space-y-6">
                        <!-- Conteneur Dynamique Nom / Prénom / Raison Sociale -->
                        <div id="nameFieldsContainer">
                            <!-- Individuel: Prénom et Nom -->
                            <div id="individualNameFields" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Prénom <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="firstname" id="firstnameInput" required
                                        class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Nom <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="lastname" id="lastnameInput" required
                                        class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                                </div>
                            </div>

                            <!-- Corporate: Raison Sociale et Contact -->
                            <div id="corporateNameFields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Raison sociale <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="companyInput" placeholder="Nom de l'entreprise ou organisme"
                                        class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2">Nom du contact / représentant <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" id="lastnameCorpInput" placeholder="Nom et Prénom"
                                        class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                                </div>
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Adresse Postale (Requise pour les droits
                                d'auteur) <span class="text-red-500">*</span></label>
                            <input type="text" name="address" required placeholder="Votre adresse complète"
                                class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                        </div>

                        <!-- Email / Réseaux -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Email Professionnel <span
                                        class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="email" name="email" required
                                        class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <i class="fas fa-asterisk text-purple-500 animate-pulse"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">LinkedIn / Instagram <span
                                        class="text-xs font-normal text-gray-400">(Optionnel)</span></label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" name="linkedin" placeholder="Lien LinkedIn"
                                            class="w-full border border-gray-300 pl-9 pr-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition text-sm">
                                        <i class="fab fa-linkedin absolute left-3 top-4 text-blue-600"></i>
                                    </div>
                                    <div class="relative flex-1">
                                        <input type="text" name="instagram" placeholder="@pseudo Insta"
                                            class="w-full border border-gray-300 pl-9 pr-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition text-sm">
                                        <i class="fab fa-instagram absolute left-3 top-4 text-pink-500"></i>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1 italic">Pour vous citer lors de la promotion
                                    sur nos réseaux.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photos -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#FF9900]">2. Vos Photos (Max 5)</h2>
                    <div class="bg-blue-50 p-4 rounded text-sm text-[#0A2240]">
                        <ul class="list-disc list-inside">
                            <li>Format : JPEG ou PNG (Max 25 Mo/photo)</li>
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
                    <h2 class="text-xl font-bold text-[#FF9900]">3. Règlements du concours</h2>

                    <!-- Box Scrollable Règlement (Texte complet) -->
                    <div id="rulesBox"
                        class="border p-4 rounded bg-gray-50 text-sm max-h-[500px] overflow-y-auto mb-4 border-l-4 border-[#0A2240] text-justify space-y-2">

                        <h3 class="font-bold text-[#0A2240] text-lg mt-2 text-center">Règlement du Concours Photo ouvert au public 2026-2027 – CFBR</h3>
                        <div class="whitespace-pre-line text-sm text-gray-700 leading-relaxed">
                            <strong>Préambule</strong>
                            À l’occasion du centenaire de sa création, le Comité Français des Barrages et Réservoirs (CFBR) organise un grand concours photographique ouvert au public. Après une édition réservée à ses membres, le CFBR invite désormais le grand public, les professionnels et les organismes partenaires à poser leur regard sur les ouvrages hydrauliques (barrages, digues, canaux). L'objectif est de révéler la majesté de ces géants, leur intégration dans le paysage et l'excellence des savoir-faire humains qui les entourent.

                            <strong>Article 1 - Organisateur et Objet du Concours</strong>
                            Le Comité Français des Barrages et Réservoirs organise un concours photo gratuit 2026-2027 intitulé « Barrages : Entre nature et architecture ». Ce concours vise à valoriser le patrimoine hydraulique français à travers deux prismes : l'esthétique environnementale et l'expertise humaine.

                            <strong>Article 2 - Catégories du Concours</strong>
                            Le concours est structuré autour de deux catégories distinctes :
                            Catégorie « Intégration Environnementale » : Cette catégorie récompense les clichés mettant en scène l'ouvrage dans son écrin naturel, son architecture, ses jeux de lumière et son harmonie avec le paysage.
                            Catégorie « Femmes & Hommes de l’Art » : Cette catégorie est dédiée à la valorisation des métiers, des gestes professionnels, de la maintenance, de la construction et de la vie des femmes et des hommes sur les sites hydrauliques.

                            <strong>Article 3 - Typologie des Prix</strong>
                            Dans chacune des deux catégories, un classement individuel distinguera trois lauréats. Un Prix Spécial Organisme unique, toutes catégories confondues, sera par ailleurs attribué au meilleur portfolio de cinq photographies.

                            <strong>Article 4 - Calendrier du Concours</strong>
                            Lancement du concours : 1er août 2026.
                            Clôture des soumissions : 31 décembre 2026 à 23h59.
                            Délibération du jury : courant janvier 2027.
                            Remise des prix : elle se tiendra le 28 janvier 2027 lors du symposium du CFBR.

                            <strong>Article 5 - Conditions de Participation</strong>
                            Le concours est gratuit et ouvert à toute personne physique majeure ainsi qu'à tout organisme (entreprise, institution, association).
                            Participants individuels : Peuvent soumettre jusqu'à cinq (5) photographies au total (réparties ou non dans les deux catégories).
                            Organismes : Doivent soumettre un portfolio complet de cinq (5) photographies pour concourir au Prix Spécial Organisme.
                            Les organismes participent par l’intermédiaire d’un représentant dûment habilité. Ils garantissent avoir obtenu l’accord de chaque auteur des photographies composant le portfolio ainsi que la signature, par chacun d’eux, de l’autorisation prévue à l’annexe A.

                            <strong>Article 6 - Caractéristiques Techniques et Éthique</strong>
                            Format : Les photographies doivent être transmises au format JPEG en haute qualité ou TIFF. Elles doivent présenter une définition minimale de 3 000 pixels sur leur plus grand côté et être adaptées à une impression en haute résolution.
                            Taille : Chaque fichier doit avoir une taille comprise entre 3 Mo et 25 Mo.
                            Retouches autorisées : Sont admis les ajustements usuels qui ne modifient pas la réalité de la scène photographiée, notamment le recadrage, le redressement, ainsi que les corrections de luminosité, de contraste, de netteté, de saturation, de colorimétrie et de balance des blancs.
                            Modifications interdites : Il est interdit d’ajouter, de supprimer, de déplacer, de dupliquer ou de remplacer un élément de l’image. Les photomontages, les images entièrement ou partiellement générées par une intelligence artificielle ainsi que l’utilisation de fonctions génératives destinées à créer ou à modifier des éléments de la photographie sont également interdits.
                            Les photographies doivent avoir été réalisées dans le respect de la réglementation applicable et des règles d’accès aux sites concernés. Le participant garantit notamment avoir obtenu les éventuelles autorisations nécessaires auprès des propriétaires ou exploitants des ouvrages et avoir respecté la réglementation applicable à l’utilisation de drones.

                            <strong>Article 7 - Modalités de Soumission et Promotion Instagram</strong>
                            Les candidatures s'effectuent via le formulaire dédié sur le site internet du CFBR. Les photographies pourront être publiées sur les comptes Instagram du CFBR et du concours, dans les conditions prévues à l’article 10 et à l’annexe A, afin de promouvoir le concours et le centenaire. Le CFBR s’engage à faire figurer systématiquement le nom de l’auteur sous la forme d’un crédit photographique.

                            <strong>Article 8 - Jury et Critères de Sélection</strong>
                            Le jury est composé d'experts du CFBR et de professionnels de l'image. Les critères sont : pertinence vis-à-vis du thème et de la catégorie, qualité esthétique, composition et maîtrise de la lumière, originalité de l'angle de vue. Pour les organismes : cohérence narrative et visuelle du portfolio de cinq photographies.
                            Le jury délibère souverainement au regard des critères énoncés dans le présent article. En cas d’égalité, il procède à un nouveau vote.

                            <strong>Article 9 - Prix et Récompenses</strong>
                            Dans chacune des deux catégories, les trois premiers lauréats individuels recevront les récompenses suivantes.
                            Pour le premier lauréat :
                            · une visite privée d’un aménagement hydroélectrique français remarquable, avec accès à des zones d’intérêt et autorisation de prises de vues. Les frais de déplacement liés à cette visite seront pris en charge dans la limite de 200 € TTC par lauréat, sur présentation de justificatifs. Cette prise en charge porte exclusivement sur les frais de transport, à l’exclusion des frais d’hébergement, de restauration
                            · une impression sur support métallique Alu-Dibond de la photographie lauréate ;
                            · une invitation au symposium du CFBR organisé le 28 janvier 2027 ;
                            · un exemplaire du livre « Barrage, le savoir-faire français ».
                            Pour les deuxième et troisième lauréats :
                            · un tirage d’art haute qualité de leur photographie ;
                            · une invitation au symposium du CFBR organisé le 28 janvier 2027 ;
                            · un exemplaire du livre « Barrage, le savoir-faire français ».
                            Les modalités de remise du Prix spécial Organisme seront communiquées directement au lauréat.

                            <strong>Article 10 - Droits d’auteur et droit à l’image</strong>
                            L’auteur garantit être titulaire des droits nécessaires sur les photographies soumises et avoir obtenu les autorisations d’utilisation de l’image des personnes identifiables, conformément à l’annexe B. Il accorde au CFBR une autorisation d’exploitation non exclusive et gratuite portant sur la reproduction, la représentation et les adaptations techniques nécessaires à la diffusion de ses photographies, dans les conditions définies à l’annexe A.

                            <strong>Article 11 – Données personnelles</strong>
                            Les données personnelles recueillies dans le cadre du concours font l’objet d’un traitement mis en œuvre par le Comité Français des Barrages et Réservoirs, responsable du traitement, afin d’assurer la réception et l’instruction des candidatures, l’organisation des délibérations du jury, la désignation et l’information des lauréats, la remise des récompenses ainsi que la diffusion des photographies dans les conditions prévues par le présent règlement.
                            Les traitements nécessaires à la gestion des candidatures et à l’organisation du concours reposent sur l’exécution du présent règlement. La conservation des autorisations et des éléments permettant de justifier les droits accordés au CFBR repose sur son intérêt légitime à assurer la défense de ses droits. La diffusion de l’image des personnes identifiables est réalisée dans les conditions prévues par l’autorisation figurant en annexe B.
                            Les données sont accessibles aux personnes habilitées du CFBR, aux membres du jury ainsi qu’aux prestataires intervenant, le cas échéant, dans l’organisation du concours. Les noms des auteurs et les photographies sélectionnées pourront être rendus publics sur les supports de communication mentionnés dans le présent règlement.
                            Les données relatives aux candidatures non retenues sont conservées pendant une durée maximale de six mois à compter de la remise des prix. Les données relatives aux photographies exploitées, aux auteurs et aux autorisations correspondantes sont conservées pendant la durée de l’autorisation d’exploitation et, le cas échéant, pendant la durée nécessaire à la constatation, à l’exercice ou à la défense des droits du CFBR.
                            Les informations signalées comme obligatoires dans le formulaire sont nécessaires à la prise en compte de la candidature. À défaut, celle-ci ne pourra pas être examinée.
                            Chaque personne concernée peut exercer, selon les conditions prévues par la réglementation, ses droits d’accès, de rectification, d’effacement, de limitation, d’opposition et, lorsque les conditions sont réunies, de portabilité, en écrivant à : dpo@barrages-cfbr.eu. Elle peut également introduire une réclamation auprès de la Commission nationale de l’informatique et des libertés.

                            <strong>Article 12 - Acceptation</strong>
                            La participation implique l'acceptation sans réserve du présent règlement.
                        </div>
                    </div>
                <!-- Instagram Option Removed as per request -->

                <!-- Annexes Obligatoires -->
                <div class="space-y-6 pt-4 mt-4">
                    <!-- SEPARATION EN 3 FENETRES : REGLEMENT (HAUT), ANNEXE A, ANNEXE B -->

                    <!-- 2. Fenêtre Annexe A (Cession de Droits) -->
                    <div class="pt-4">
                        <h3 class="font-bold text-[#FF9900] text-lg mb-2">4. Annexe A : Cession de Droits d'Auteur
                            (Obligatoire)</h3>
                        <div id="annexABox"
                            class="border p-4 rounded bg-gray-50 text-sm h-48 overflow-y-auto border-l-4 border-green-600 text-justify mb-2">
                            <strong>Titre : Cession de Droits d'Auteur à Titre Gratuit et Non Exclusif</strong><br><br>
                            Entre les soussignés : Le Cédant (Vous) et le Cessionnaire (Le CFBR).<br><br>

                            <strong>Article 1 : Objet de la cession</strong><br>
                            Le présent contrat a pour objet la cession des droits d'exploitation des photographies
                            listées ci-dessous, dans le cadre du Concours Photo ouvert au public 2026-2027 organisé par le
                            CFBR.<br><br>

                            <div class="bg-white border rounded p-3 mb-3">
                                <strong>Photos concernées par la cession :</strong>
                                <ul id="annexPhotoList" class="list-disc list-inside text-xs text-gray-600 mt-1 italic">
                                    <li>Aucune photo sélectionnée pour le moment.</li>
                                </ul>
                            </div>

                            <strong>Article 2 : Droits cédés</strong><br>
                            L'Auteur autorise le CFBR, pour les photographies soumises, à exercer les droits
                            patrimoniaux suivants :<br>
                            - Le droit de reproduction (fixer, dupliquer, copier sur tous supports).<br>
                            - Le droit de représentation (exposition, diffusion web, réseaux sociaux).<br>
                            - Le droit d'effectuer les adaptations strictement techniques nécessaires à la diffusion de l’œuvre.<br><br>

                            <strong>Article 3 : Étendue de la cession</strong><br>
                            Cette cession est consentie à titre non exclusif, à titre gratuit, pour le monde entier et
                            pour une durée de dix ans à compter de la signature de la présente autorisation, pour une exploitation sur tous les
                            supports de communication du CFBR dans un but non commercial.
                        </div>

                        <div class="flex items-start space-x-3 mt-2">
                            <input type="checkbox" name="agree_annex_a" id="agree_annex_a" required
                                class="mt-1 w-5 h-5 text-[#FF9900] focus:ring-[#0A2240] cursor-pointer shrink-0">
                            <label for="agree_annex_a" class="text-sm cursor-pointer select-none text-gray-700 pt-0.5">
                                <strong>Lu et approuvé :</strong> J'accepte les termes de l'Annexe A pour les photos
                                listées.
                            </label>
                        </div>
                    </div>

                    <!-- 3. Fenêtre Annexe B (Droit à l'image) -->
                    <div class="pt-4">
                        <h3 class="font-bold text-[#FF9900] text-lg mb-2">5. Annexe B : Droit à l'image (Facultatif / Si
                            applicable)</h3>
                        <div id="annexBBox"
                            class="border p-4 rounded bg-gray-50 text-sm h-40 overflow-y-auto border-l-4 border-blue-500 text-justify mb-2">
                            <strong>Titre : Autorisation d'Utilisation de l'Image d'une Personne</strong><br>
                            <em>(Applicable uniquement si des personnes sont identifiables sur vos photos)</em><br><br>

                            En soumettant des photos comportant des personnes identifiables, vous garantissez avoir
                            recueilli leur consentement écrit (ou celui de leur représentant légal pour les mineurs)
                            pour autoriser le CFBR à utiliser, reproduire et diffuser leur image dans le cadre du
                            concours pour une durée de dix ans à compter de la signature de la présente autorisation, à des fins non commerciales.
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Personnes identifiables
                                (Nom/Prénom) :</label>
                            <input type="text" name="identifiable_persons"
                                placeholder="Ex: Jean Dupont (Photo 1), Marie Curie (Photo 2)... ou laisser vide"
                                class="w-full border p-2 rounded text-sm focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div class="flex items-start space-x-3 mt-2">
                            <input type="checkbox" name="agree_annex_b" id="agree_annex_b" required
                                class="mt-1 w-5 h-5 text-[#FF9900] focus:ring-[#0A2240] cursor-pointer shrink-0">
                            <label for="agree_annex_b" class="text-sm cursor-pointer select-none text-gray-700 pt-0.5">
                                <strong>Certification :</strong> Je certifie avoir obtenu les autorisations nécessaires
                                pour les personnes identifiables (ou qu'aucune personne n'est identifiable).
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Signature Check -->
                <div class="flex items-start space-x-3 mt-4">
                    <input type="checkbox" name="signature" id="signature" required
                        class="mt-1 w-5 h-5 text-[#FF9900] focus:ring-[#0A2240] cursor-pointer shrink-0">
                    <label for="signature" id="lblSignature" class="text-sm text-gray-500 select-none pt-0.5">
                        Je reconnais avoir lu le règlement et les annexes, et je signe numériquement cette cession
                        de droits.
                        <br><span class="text-xs">Veuillez prendre le temps de lire le règlement ci-dessus pour
                            activer cette case.</span>
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
        fileInput.addEventListener('change', function () {
            let filesAdded = false;
            let limitReached = false;
            for (let i = 0; i < this.files.length; i++) {
                if (this.files[i].size > 25 * 1024 * 1024) {
                    showCustomAlert("Fichier trop lourd", "Le fichier " + this.files[i].name + " dépasse la limite de 25 Mo.");
                    continue;
                }
                if (dt.items.length < 5) {
                    dt.items.add(this.files[i]);
                    filesAdded = true;
                } else {
                    limitReached = true;
                }
            }
            if (limitReached) {
                showCustomAlert("Limite de photos atteinte", "Vous ne pouvez pas ajouter plus de 5 photos au total. Les fichiers excédentaires ont été ignorés.");
            }
            if (filesAdded) {
                renderPhotos();
            }
            // Clear input value so selecting the same file again triggers change event
            fileInput.value = '';
        });

        // 2. Handle Drag & Drop
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('bg-blue-100'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('bg-blue-100'); });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('bg-blue-100');
            let filesAdded = false;
            let limitReached = false;
            for (let i = 0; i < e.dataTransfer.files.length; i++) {
                if (e.dataTransfer.files[i].size > 25 * 1024 * 1024) {
                    showCustomAlert("Fichier trop lourd", "Le fichier " + e.dataTransfer.files[i].name + " dépasse la limite de 25 Mo.");
                    continue;
                }
                if (dt.items.length < 5) {
                    dt.items.add(e.dataTransfer.files[i]);
                    filesAdded = true;
                } else {
                    limitReached = true;
                }
            }
            if (limitReached) {
                showCustomAlert("Limite de photos atteinte", "Vous ne pouvez pas ajouter plus de 5 photos au total. Les fichiers excédentaires ont été ignorés.");
            }
            if (filesAdded) {
                renderPhotos();
            }
        });

        // 3. Sync files before submit
        document.getElementById('uploadForm').addEventListener('submit', function () {
            fileInput.files = dt.files;
        });

        function updateAnnexAPhotoList(files) {
            const list = document.getElementById('annexPhotoList');
            if (!list) return;

            list.innerHTML = '';
            if (files.length === 0) {
                list.innerHTML = '<li>Aucune photo sélectionnée pour le moment.</li>';
                return;
            }

            for (let i = 0; i < files.length; i++) {
                const li = document.createElement('li');
                li.textContent = `Photo ${i + 1}: ${files[i].name}`;
                list.appendChild(li);
            }
        }

        window.removeFile = function (index) {
            dt.items.remove(index);
            renderPhotos();
        }

        // 4. Render Photos Function
        function renderPhotos() {
            // First, collect current values from inputs to avoid losing them
            const savedValues = {};
            photosContainer.querySelectorAll('[data-filename]').forEach(item => {
                const filename = item.getAttribute('data-filename');
                const titleInput = item.querySelector('input[name="titles[]"]');
                const locationInput = item.querySelector('input[name="locations[]"]');
                const descInput = item.querySelector('textarea[name="descriptions[]"]');
                savedValues[filename] = {
                    title: titleInput ? titleInput.value : '',
                    location: locationInput ? locationInput.value : '',
                    desc: descInput ? descInput.value : ''
                };
            });

            photosContainer.innerHTML = '';
            const files = dt.files;

            if (files.length === 0) {
                photosContainer.innerHTML = '<p class="text-gray-400 text-sm italic text-center">Aucune photo sélectionnée.</p>';
            } else {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const saved = savedValues[file.name] || { title: '', location: '', desc: '' };
                    const div = document.createElement('div');
                    div.className = "bg-white p-4 rounded shadow border border-gray-200 animate-fade-in-down";
                    div.setAttribute('data-filename', file.name);

                    div.innerHTML = `
                        <div class="flex flex-col md:flex-row md:items-start gap-4">
                            <div class="w-full md:w-1/4 flex-shrink-0 text-center bg-gray-100 p-2 rounded relative">
                                <div id="dim-warning-${i}" class="hidden absolute -top-2 -right-2 bg-amber-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg animate-bounce" title="Basse résolution (< 3000px)">
                                    <i class="fas fa-exclamation-triangle text-[10px]"></i>
                                </div>
                                <i class="fas fa-image text-4xl text-gray-300 mb-2 block"></i>
                                <span class="text-xs text-gray-600 break-all line-clamp-2" title="${file.name}">${file.name}</span>
                                <span class="text-xs text-gray-400 block">${(file.size / 1024 / 1024).toFixed(2)} Mo</span>
                                <div id="dim-info-${i}" class="text-[9px] text-gray-500 mt-1">Analyse...</div>
                                <button type="button" class="mt-2 text-red-500 text-xs underline hover:text-red-700 font-bold" onclick="removeFile(${i})">Supprimer</button>
                            </div>
                            <div class="flex-grow space-y-3 w-full">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Titre de l'œuvre <span class="text-red-500">*</span></label>
                                    <input type="text" name="titles[]" required placeholder="Ex: Barrage au crépuscule" value="${saved.title.replace(/"/g, '&quot;')}" 
                                        class="w-full border-b-2 border-gray-200 focus:border-[#0A2240] outline-none py-1 transition bg-transparent text-[#0A2240] font-semibold">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Lieu de prise de vue <span class="text-red-500">*</span></label>
                                    <input type="text" name="locations[]" required placeholder="Ex: Barrage de Roselend (73)" value="${saved.location.replace(/"/g, '&quot;')}" 
                                        class="w-full border-b-2 border-gray-200 focus:border-[#0A2240] outline-none py-1 transition bg-transparent text-[#0A2240]">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Note d'intention (Facultatif)</label>
                                    <textarea name="descriptions[]" rows="2" placeholder="Quelques mots sur votre démarche, le lieu, l'instant..."
                                        class="w-full border border-gray-200 rounded p-2 text-sm focus:ring-1 focus:ring-[#0A2240]">${saved.desc}</textarea>
                                </div>
                            </div>
                        </div>
                    `;
                    photosContainer.appendChild(div);

                    // Async Dimension Check
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const img = new Image();
                        img.onload = function () {
                            const w = img.width;
                            const h = img.height;
                            const longestSide = Math.max(w, h);
                            const infoSpan = document.getElementById(`dim-info-${i}`);
                            const warningDiv = document.getElementById(`dim-warning-${i}`);

                            if (infoSpan) infoSpan.textContent = `${w} x ${h} px`;

                            if (longestSide < 3000 && warningDiv) {
                                warningDiv.classList.remove('hidden');
                                infoSpan.classList.add('text-amber-600', 'font-bold');
                                // Simple notification once
                                if (!window.hasShownDimWarning) {
                                    showCustomAlert("⚠️ Qualité Photo", "Certaines de vos photos font moins de 3000px. Pour maximiser vos chances de gagner et permettre un tirage grand format, privilégiez la plus haute résolution possible.");
                                    window.hasShownDimWarning = true;
                                }
                            }
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }

            // Update Annex A List
            updateAnnexAPhotoList(files);

            // Update Submit Button State
            updateSubmitState();
        }

        // --- Logic Lecture Règlement ---
        let startTime = Date.now();
        let minReadTime = 60000; // 60 seconds
        let hasScrolled = false;

        // Track scroll
        rulesBox.addEventListener('scroll', function () {
            if (this.scrollTop + this.clientHeight >= this.scrollHeight - 50) {
                hasScrolled = true;
            }
        });

        // Intercept click on signature
        signatureCheckbox.addEventListener('click', function (e) {
            let timeElapsed = Date.now() - startTime;

            if (!hasScrolled) {
                e.preventDefault();
                showCustomAlert("⚠️ Lecture Incomplète", "Veuillez faire défiler le règlement jusqu'en bas pour confirmer que vous l'avez parcouru.");
                return;
            }

            if (timeElapsed < minReadTime) {
                e.preventDefault();
                let remaining = Math.ceil((minReadTime - timeElapsed) / 1000);
                showCustomAlert("🌊 Wow, quelle vitesse !", `Vous lisez plus vite que l'écoulement de l'eau d'un évacuateur de crue ! <br><br>Prenez encore <strong>${remaining} secondes</strong> pour bien lire les détails importants.`);
                return;
            }

            // If OK, let it check and update UI
            updateSubmitState();
        });

        function updateSubmitState() {
            // Check Photos
            const hasPhotos = dt.files.length > 0;
            const hasSignature = signatureCheckbox.checked;

            // Logic: Must have signature AND at least one photo
            if (hasSignature && hasPhotos) {
                // Enabled
                submitBtn.disabled = false;
                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.add('bg-[#FF9900]', 'text-[#0A2240]', 'hover:bg-[#0A2240]', 'hover:text-white', 'cursor-pointer');
                submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i> Valider ma participation';

                // Visual feedback on label
                lblSignature.classList.remove('text-gray-500');
                lblSignature.classList.add('text-black');
                lblSignature.innerHTML = `Je reconnais avoir lu le règlement et les annexes, et je signe numériquement cette cession de droits.<br><span class="text-xs text-green-600 font-bold"><i class="fas fa-check"></i> Lecture confirmée</span>`;
            } else {
                // Disabled
                submitBtn.disabled = true;
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.remove('bg-[#FF9900]', 'text-[#0A2240]', 'hover:bg-[#0A2240]', 'hover:text-white', 'cursor-pointer');

                // Explain why
                if (!hasPhotos) {
                    submitBtn.innerHTML = 'Ajoutez au moins une photo...';
                } else if (!hasSignature) {
                    submitBtn.innerHTML = 'Signez le règlement...';
                }

                // Reset label if needed (only if signature not checked)
                if (!hasSignature) {
                    lblSignature.classList.add('text-gray-500');
                    lblSignature.classList.remove('text-black');
                    lblSignature.innerHTML = `Je reconnais avoir lu le règlement et les annexes, et je signe numériquement cette cession de droits.<br><span class="text-xs">Veuillez prendre le temps de lire le règlement ci-dessus pour activer cette case.</span>`;
                }
            }
        }

        // Handle manual change calls (if any) or re-checks
        signatureCheckbox.addEventListener('change', updateSubmitState);

        // Custom Modal
        function showCustomAlert(title, message) {
            // Remove existing if any
            const existing = document.getElementById('customAlert');
            if (existing) existing.remove();

            const modal = document.createElement('div');
            modal.id = 'customAlert';
            modal.className = "fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm animate-fade-in";
            modal.innerHTML = `
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center transform scale-100 transition-transform">
                    <div class="text-5xl mb-4">🙊</div>
                    <h3 class="text-xl font-bold text-[#0A2240] mb-2">${title}</h3>
                    <p class="text-gray-600 mb-6">${message}</p>
                    <button onclick="document.getElementById('customAlert').remove()" 
                        class="bg-[#FF9900] text-[#0A2240] font-bold px-6 py-2 rounded-full hover:bg-[#0A2240] hover:text-white transition">
                        D'accord, je patiente
                    </button>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function toggleCompanyField() {
            // Check radio value
            const candidacyType = document.querySelector('input[name="candidacy_type"]:checked').value;
            
            const categorySelect = document.getElementById('category');
            const categoryFieldContainer = document.getElementById('categoryFieldContainer');
            const categoryCorporateContainer = document.getElementById('categoryCorporateContainer');
            
            const individualNameFields = document.getElementById('individualNameFields');
            const corporateNameFields = document.getElementById('corporateNameFields');
            
            const firstnameInput = document.getElementById('firstnameInput');
            const lastnameInput = document.getElementById('lastnameInput');
            const companyInput = document.getElementById('companyInput');
            const lastnameCorpInput = document.getElementById('lastnameCorpInput');

            if (candidacyType === 'corporate') {
                // Show corporate read-only label and select corporate value
                categoryFieldContainer.classList.add('hidden');
                categoryCorporateContainer.classList.remove('hidden');
                
                // Add the corporate option if it doesn't exist
                let corpOption = categorySelect.querySelector('option[value="corporate"]');
                if (!corpOption) {
                    corpOption = document.createElement('option');
                    corpOption.value = 'corporate';
                    corpOption.text = 'Prix Spécial Organisme';
                    categorySelect.appendChild(corpOption);
                }
                categorySelect.value = 'corporate';
                
                // Toggle Name Fields
                individualNameFields.classList.add('hidden');
                corporateNameFields.classList.remove('hidden');
                
                // Configure attributes
                firstnameInput.required = false;
                firstnameInput.name = '';
                firstnameInput.value = 'Corporate'; // Fill default value to avoid any backend issues
                
                lastnameInput.required = false;
                lastnameInput.name = '';
                
                companyInput.required = true;
                companyInput.name = 'company';
                
                lastnameCorpInput.required = true;
                lastnameCorpInput.name = 'lastname';
            } else {
                // Show normal category select
                categoryFieldContainer.classList.remove('hidden');
                categoryCorporateContainer.classList.add('hidden');
                
                // Reset select value if it was corporate
                if (categorySelect.value === 'corporate') {
                    categorySelect.value = '';
                }
                
                // Remove corporate option if it exists
                const corpOption = categorySelect.querySelector('option[value="corporate"]');
                if (corpOption) {
                    corpOption.remove();
                }
                
                // Toggle Name Fields
                individualNameFields.classList.remove('hidden');
                corporateNameFields.classList.add('hidden');
                
                // Configure attributes
                firstnameInput.required = true;
                firstnameInput.name = 'firstname';
                firstnameInput.value = '';
                
                lastnameInput.required = true;
                lastnameInput.name = 'lastname';
                
                companyInput.required = false;
                companyInput.name = '';
                companyInput.value = '';
                
                lastnameCorpInput.required = false;
                lastnameCorpInput.name = '';
                lastnameCorpInput.value = '';
            }
        }

        // Semi-auto Visual Feedback for Annexes
        function addAnnexFeedback(checkboxId, labelId) {
            const cb = document.getElementById(checkboxId);
            const lbl = document.querySelector(`label[for="${checkboxId}"]`);

            cb.addEventListener('change', function () {
                const existingFeedback = lbl.querySelector('.annex-feedback');
                if (this.checked) {
                    if (!existingFeedback) {
                        const span = document.createElement('div');
                        span.className = 'annex-feedback text-xs text-green-600 font-bold mt-1 ml-1 animate-fade-in';
                        const date = new Date().toLocaleDateString('fr-FR');
                        span.innerHTML = `<i class="fas fa-file-signature"></i> Lu et approuvé le ${date}`;
                        lbl.appendChild(span);
                    }
                } else {
                    if (existingFeedback) existingFeedback.remove();
                }
            });
        }

        addAnnexFeedback('agree_annex_a');
        addAnnexFeedback('agree_annex_b');

    </script>
</body>

</html>