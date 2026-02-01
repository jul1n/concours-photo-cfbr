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

            <form action="process_upload.php" method="POST" enctype="multipart/form-data" id="uploadForm"
                class="space-y-6">

                <!-- Identité & Catégorie -->
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#FF9900]">1. Catégorie & Coordonnées</h2>

                    <!-- Choix Catégorie -->
                    <div class="bg-gray-50 p-4 rounded border border-gray-200 mb-4 space-y-4">
                        <div>
                            <label class="block font-bold mb-2 text-[#0A2240]">Dans quelle catégorie participez-vous ?
                                <span class="text-red-500">*</span></label>
                            <select name="category" id="category"
                                class="w-full border p-2 rounded focus:ring-2 focus:ring-[#0A2240] bg-white cursor-pointer"
                                required>
                                <option value="" disabled selected>-- Sélectionnez une catégorie --</option>
                                <option value="cat1">Catégorie 1 : Intégration Environnementale</option>
                                <option value="cat2">Catégorie 2 : Hommes & Femmes de l'Art</option>
                            </select>
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
                                    <span class="ml-2 font-semibold">Candidature Corporate / Entreprise</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Grid Container Removed for better vertical control -->
                    <div class="space-y-6">
                        <!-- Champ Entreprise Dynamique -->
                        <div id="companyFieldContainer"
                            class="hidden animate-fade-in-down p-4 bg-orange-50 border border-orange-100 rounded-lg">
                            <label class="block font-bold mb-2 text-[#0A2240]">Nom de l'Entreprise <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="company" id="companyInput" placeholder="Nom de l'entité candidate"
                                class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-[#0A2240] focus:border-[#0A2240] transition">
                        </div>

                        <!-- Ligne Prénom / Nom -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Prénom <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="firstname" required
                                    class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Nom <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="lastname" required
                                    class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Adresse Postale (Requise pour les droits
                                d'auteur) <span class="text-red-500">*</span></label>
                            <input type="text" name="address" required placeholder="Votre adresse complète"
                                class="w-full border border-gray-300 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0A2240] transition">
                        </div>

                        <!-- Email -->
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
                    <!-- Box Scrollable Règlement (Texte complet) -->
                    <div id="rulesBox"
                        class="border p-4 rounded bg-gray-50 text-sm h-60 overflow-y-auto mb-4 border-l-4 border-[#0A2240] text-justify space-y-2">

                        <h3 class="font-bold text-[#0A2240] text-lg mt-2 text-center">Règlement du Concours Photo Grand
                            Public 2026 – CFBR</h3>

                        <div class="whitespace-pre-line text-sm text-gray-700 leading-relaxed">
                            <strong>« Barrages : Entre nature et architecture »</strong>

                            <strong>Préambule</strong>
                            À l’occasion du centenaire de sa création, le Comité Français des Barrages et Réservoirs
                            (CFBR) organise un grand concours photographique ouvert à tous. Après une édition réservée à
                            ses membres, le CFBR invite désormais le grand public, les professionnels et les organismes
                            partenaires à poser leur regard sur les ouvrages hydrauliques (barrages, digues, canaux).
                            L'objectif est de révéler la majesté de ces géants, leur intégration dans le paysage et
                            l'excellence des savoir-faire humains qui les entourent.

                            <strong>Article 1 - Organisateur et Objet du Concours</strong>
                            Le Comité Français des Barrages et Réservoirs (CFBR) organise en 2026 un concours photo
                            gratuit intitulé « Barrages : Entre nature et architecture ». Ce concours vise à valoriser
                            le patrimoine hydraulique français à travers deux prismes : l'esthétique environnementale et
                            l'expertise humaine.

                            <strong>Article 2 - Catégories du Concours</strong>
                            Le concours est structuré autour de deux catégories distinctes :
                            Catégorie « Intégration Environnementale » : Cette catégorie récompense les clichés mettant
                            en scène l'ouvrage dans son écrin naturel, son architecture, ses jeux de lumière et son
                            harmonie avec le paysage.
                            Catégorie « Hommes & Femmes de l’Art » : Cette catégorie est dédiée à la valorisation des
                            métiers, des gestes professionnels, de la maintenance, de la construction et de la vie des
                            agents sur les sites hydrauliques.

                            <strong>Article 3 - Typologie des Prix</strong>
                            Pour chacune des deux catégories susmentionnées, deux types de prix seront décernés :
                            Le Prix Individuel : Récompense la meilleure photographie unique soumise par un participant
                            (amateur ou professionnel).
                            Le Prix Spécial Organisme (Prix du Portfolio) : Récompense la meilleure contribution globale
                            d'une entreprise, association ou organisme public, sur la base d'un portfolio cohérent de
                            cinq (5) photographies.

                            <strong>Article 4 - Calendrier du Concours</strong>
                            Lancement du concours : Printemps 2026.
                            Clôture des soumissions : 1er octobre 2026 à 23h59.
                            Délibération du jury : Courant octobre 2026.
                            Remise des prix : Elle se tiendra les 18 ou 19 novembre 2026 lors du Colloque Prospective
                            Eau organisé par le CFBR au Palais des Congrès d'Aix-les-Bains.

                            <strong>Article 5 - Conditions de Participation</strong>
                            Le concours est gratuit et ouvert à toute personne physique majeure ainsi qu'à tout
                            organisme (entreprise, institution, association).
                            Participants individuels : Peuvent soumettre jusqu'à cinq (5) photographies au total
                            (réparties ou non dans les deux catégories).
                            Organismes : Doivent soumettre un portfolio complet de cinq (5) photographies pour concourir
                            au Prix Spécial Organisme.

                            <strong>Article 6 - Caractéristiques Techniques et Éthique</strong>
                            Format : Fichiers JPEG haute qualité ou TIFF. Résolution de 300 dpi minimum (3 000 pixels
                            minimum sur le plus grand côté).
                            Taille : Entre 3 Mo et 25 Mo par fichier.
                            Nommage : Nom_Prenom_Categorie_Numero.jpg (ou NomOrganisme_Portfolio_Numero.jpg).
                            Authenticité : Les retouches légères sont autorisées. Les photomontages complexes et l'usage
                            d'intelligences artificielles génératives sont strictement interdits.

                            <strong>Article 7 - Modalités de Soumission et Promotion Instagram</strong>
                            Les candidatures s'effectuent via le formulaire dédié sur le site cfbr.eu.
                            Clause Instagram : En participant, le candidat autorise le CFBR à publier ses clichés sur le
                            compte Instagram officiel de l'association pour promouvoir le concours et le centenaire. Le
                            CFBR s'engage à citer systématiquement le nom du photographe (crédit photo) sur chaque
                            publication.

                            <strong>Article 8 - Jury et Critères de Sélection</strong>
                            Le jury est composé d'experts du CFBR et de professionnels de l'image. Les critères sont :
                            Pertinence vis-à-vis du thème et de la catégorie.
                            Qualité esthétique, composition et maîtrise de la lumière.
                            Originalité de l'angle de vue.
                            Pour les organismes : cohérence narrative et visuelle du portfolio de 5 photos.

                            <strong>Article 9 - Prix et Récompenses</strong>
                            Pour les 1ers Prix de chaque catégorie (Individuels et Organismes) :
                            Le Trophée du Centenaire : Une œuvre unique réalisée par un maître ébéniste.
                            L'Expérience "Immersion" : Une visite privée d'un aménagement hydroélectrique français
                            remarquable, avec accès à des zones d'intérêt et autorisation exceptionnelle de prises de
                            vues. Note : Ce prix comprend exclusivement l'accès au site ; les frais de transport,
                            d'hébergement et de restauration sont à la charge exclusive du lauréat.
                            Impression de Prestige : Une impression sur support métallique (Alu-Dibond) du cliché
                            lauréat.
                            Pour les 2èmes et 3èmes Prix :
                            Tirages d'art haute qualité.
                            Invitation au Colloque Prospective Eau : Accès complet au colloque (délivrance du badge) les
                            18 et 19 novembre 2026 au Palais des Congrès d'Aix-les-Bains. Note : Cette invitation ne
                            couvre pas les frais de transport, d'hôtellerie, ni les autres frais afférents qui incombent
                            au participant.

                            <strong>Article 10 - Droits de Propriété et Droit à l'Image</strong>
                            L'auteur garantit l'exclusivité de ses droits et l'obtention des autorisations de droit à
                            l'image des personnes identifiables (Annexe B). Le participant cède au CFBR une licence non
                            exclusive, gratuite, pour la reproduction et la diffusion des œuvres à des fins de promotion
                            des activités du CFBR et du patrimoine hydraulique, sur tous supports, pour la durée légale
                            des droits d'auteur.

                            <strong>Article 11 - Données Personnelles</strong>
                            Conformément au RGPD, les données collectées sont uniquement destinées à la gestion du
                            concours. Chaque participant dispose d'un droit d'accès, de rectification et de suppression
                            via : dpo@barrages-cfbr.eu.

                            <strong>Article 12 - Acceptation</strong>
                            La participation implique l'acceptation sans réserve du présent règlement.
                        </div>
                    </div>

                    <!-- DUPLICATE REMOVED
                            <strong>Article 1 – Organisateur et Objet du Concours</strong>
                            Le Comité Français des Barrages et Réservoirs (CFBR) organise en 2026 un concours photo
                            gratuit réservé aux membres du CFBR.
                            L'objectif est de valoriser les barrages et autres ouvrages hydrauliques (digues, canaux,
                            …), en mettant en lumière leur intégration dans l'environnement naturel et paysager, leur
                            dimension architecturale et les multiples usages de l’eau qu'ils permettent (production
                            hydroélectrique, soutien d'étiage, eau potable, irrigation, protection contre les crues,
                            loisirs, biodiversité, etc.).

                            <strong>Article 2 – Calendrier Prévisionnel</strong>
                            Le concours se déroulera selon le calendrier prévisionnel suivant :
                            • Lancement du concours : Le concours sera ouvert à partir du 1er décembre 2025, avec une
                            annonce officielle sur le site cfbr.eu, ainsi que par mail auprès de ses membres.
                            • Clôture des soumissions : La date limite pour l'envoi des photographies est fixée au 6
                            janvier 2026 à 23h59 (CET, heure de Paris). L'heure de réception du courriel de
                            participation fera foi.
                            • Délibération : Au cours du mois de janvier 2026, le jury procèdera à la sélection des
                            finalistes et à leur classement.
                            • Remise des prix : La cérémonie de remise des prix et le vernissage de l'exposition se
                            tiendront le 29 janvier 2026 lors du symposium CFBR.

                            <strong>Article 3 – Conditions de Participation</strong>
                            • Être membre du CFBR.
                            • La participation est gratuite.
                            • Chaque participant(e) peut soumettre un maximum de cinq (5) photographies.

                            <strong>Article 4 – Caractéristiques Techniques des Photographies</strong>
                            • Format : Fichiers JPEG haute qualité ou TIFF non compressé.
                            • Résolution : 300 dpi minimum, avec au moins 3 000 pixels sur le plus grand côté.
                            • Taille de fichier : Entre 3 Mo et 25 Mo.
                            • Nommage du fichier : Pour assurer une bonne gestion des soumissions, chaque fichier doit
                            être impérativement nommé comme suit : NomPrénom_NuméroDeLaPhoto.jpg.
                            Le numéro (de 1 à 5) doit correspondre à l'ordre de présentation de la photographie dans
                            votre document texte d'accompagnement.
                            Exemple : Pour une soumission de trois photos, les fichiers seront nommés :
                            DupontChloe_01.jpg, DupontChloe_02.jpg, et DupontChloe_03.jpg.
                            Authenticité : Les retouches légères (luminosité, contraste, colorimétrie, recadrage) sont
                            autorisées. Les photomontages, les manipulations profondes de l'image et l'utilisation
                            d'intelligences artificielles génératives sont interdits et entraîneront la
                            disqualification.

                            <strong>Article 5 – Modalités de Soumission</strong>
                            Pour être valide, la participation doit être envoyée par courriel à l’adresse
                            concoursphoto2026@barrages-cfbr.eu avant la date de clôture. Le courriel devra contenir :
                            • Les photographies en pièces jointes ou via un lien de téléchargement.
                            • Un document texte (DOCX, TXT PDF) listant pour chaque photo :
                            o Le numéro du fichier (ex: 01) et le titre de l’œuvre (70 caractères max.).
                            o Le nom de l’ouvrage, le lieu précis et la date de la prise de vue.
                            o Une note d’intention (optionnel, 500 caractères max.) expliquant la démarche ou le
                            contexte, les aspects mis en avant, le message....
                            • Le présent règlement daté et signé, précédé de la mention « Lu et approuvé ».
                            • Le formulaire de cession de droits (Annexe A) dûment complété et signé.
                            • Le cas échéant, le formulaire de renonciation au droit à l’image (Annexe B) pour toute
                            personne identifiable sur une photo.
                            Tout dossier incomplet ou non conforme sera rejeté.

                            <strong>Article 6 – Jury et Critères de Sélection</strong>
                            Le jury sera désigné par la Commission Exécutive du CFBR. Il sera en partie composé de
                            professionnels des barrages. Pour garantir une impartialité totale, les photographies seront
                            présentées aux membres du jury de manière anonyme. Le nom des auteurs ne sera révélé
                            qu’après la délibération finale. Ses décisions sont souveraines et sans appel. Les critères
                            de sélection sont :
                            • Pertinence au thème : Force du lien entre l’ouvrage et son environnement.
                            • Originalité et créativité : Vision artistique unique et approche personnelle.
                            • Qualité esthétique et technique : Composition, lumière, maitrise de la mise au point.
                            • Portée documentaire ou pédagogique : Capacité de la photo à informer, à faire comprendre
                            un enjeu.

                            <strong>Article 7 – Prix et Récompenses</strong>
                            Les lauréats du concours recevront les dotations financières et les récompenses suivantes :
                            • Le premier prix est doté d'une somme de 300 €.
                            • Le deuxième prix est d'une valeur de 200 €.
                            • Le troisième prix s'élève à 100 €.
                            Récompenses additionnelles pour tous les lauréats :
                            En plus de la dotation financière, les lauréats recevront un tirage d'art de leur œuvre.
                            Pour les lauréats du 1er prix, ce tirage d'art prendra la forme d'une impression sur support
                            métallique.

                            <strong>Article 8 – Droits de Propriété Intellectuelle et Droit à l'Image</strong>
                            Le participant déclare et garantit être l’auteur exclusif des photographies soumises,
                            qu'elles sont issues de prises de vues réelles et ne sont pas le produit d'une intelligence
                            artificielle générative, et qu'elles ne portent atteinte à aucun droit de tiers.
                            Le participant est seul responsable de l'obtention des autorisations nécessaires, notamment
                            en ce qui concerne le droit à l'image des personnes éventuellement représentées et les
                            autorisations d'accès à des propriétés privées ou réglementées.
                            En participant, chaque photographe accepte de céder au CFBR, à titre gratuit, une licence
                            d'utilisation non exclusive des droits patrimoniaux sur les œuvres primées et
                            présélectionnées. Cette licence est valable pour le monde entier et pour la durée légale de
                            protection du droit d'auteur.
                            Cette licence autorise le CFBR à reproduire, représenter, adapter et diffuser les photos sur
                            tous ses supports de communication (site web, réseaux sociaux, rapports, newsletters,
                            expositions, publications papier, etc.), à des fins non commerciales de promotion de ses
                            activités et du patrimoine hydraulique.
                            Le crédit photo (Nom de l'auteur) sera systématiquement et lisiblement mentionné à chaque
                            utilisation. L'auteur conserve la pleine propriété de son œuvre et reste libre de l'utiliser
                            par ailleurs.
                            Les photographies non présélectionnées resteront la pleine propriété de leurs auteurs, et le
                            CFBR ne disposera d'aucun droit d'utilisation sur celles-ci.

                            <strong>Article 9 – Expositions et Communication</strong>
                            Les photographies finalistes et lauréates feront l'objet d'une large diffusion, incluant :
                            • Une exposition inaugurale au colloque des 100 ans du Cfbr.
                            • Une exposition itinérante potentielle en 2027 dans des musées, espaces publics ou lors
                            d'événements partenaires.
                            • Des publications sur les supports de communication du CFBR.

                            <strong>Article 10 – Données Personnelles</strong>
                            Conformément au RGPD, les données personnelles collectées sont uniquement destinées à la
                            gestion du concours. Elles ne seront pas transmises à des tiers. Chaque participant dispose
                            d'un droit d’accès, de rectification et de suppression en contactant : dpo@barrages-cfbr.eu

                            <strong>Article 11 – Responsabilités et Annulation</strong>
                            Le CFBR ne pourra être tenu responsable en cas de problème de transmission, de perte ou de
                            piratage des fichiers. Le CFBR se réserve le droit de modifier, reporter ou annuler le
                            concours en cas de force majeure, sans qu'aucune indemnité ne puisse être réclamée.

                            <strong>Article 12 – Acceptation du Règlement</strong>
                            La participation à ce concours photo implique l'acceptation pleine, entière et sans réserve
                            du présent règlement. Tout litige non résolu à l'amiable sera tranché souverainement par le
                        </div>
                        -->
                </div>

                <!-- Instagram Option -->
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
                            listées ci-dessous, dans le cadre du Concours Photo Grand Public 2026 organisé par le
                            CFBR.<br><br>

                            <div class="bg-white border rounded p-3 mb-3">
                                <strong>Photos concernées par la cession :</strong>
                                <ul id="annexPhotoList" class="list-disc list-inside text-xs text-gray-600 mt-1 italic">
                                    <li>Aucune photo sélectionnée pour le moment.</li>
                                </ul>
                            </div>

                            <strong>Article 2 : Droits cédés</strong><br>
                            L'Auteur cède au CFBR, pour les photographies présélectionnées et/ou primées, les droits
                            patrimoniaux suivants :<br>
                            - Le droit de reproduction (fixer, dupliquer, copier sur tous supports).<br>
                            - Le droit de représentation (exposition, diffusion web, réseaux sociaux).<br>
                            - Le droit d'adaptation (recadrage, colorimétrie pour l'impression).<br><br>

                            <strong>Article 3 : Étendue de la cession</strong><br>
                            Cette cession est consentie à titre non exclusif, à titre gratuit, pour le monde entier et
                            pour la durée légale de protection des droits d'auteur, pour une exploitation sur tous les
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
                            concours à des fins non commerciales.
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
            for (let i = 0; i < this.files.length; i++) {
                if (dt.items.length < 5) dt.items.add(this.files[i]);
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
            for (let i = 0; i < e.dataTransfer.files.length; i++) {
                if (dt.items.length < 5) dt.items.add(e.dataTransfer.files[i]);
            }
            renderPhotos();
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
                // Use custom title if available, otherwise filename? 
                // Using filename is safer as titles are inputs.
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
            photosContainer.innerHTML = '';
            const files = dt.files;

            if (files.length === 0) {
                photosContainer.innerHTML = '<p class="text-gray-400 text-sm italic text-center">Aucune photo sélectionnée.</p>';
            } else {
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
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Lieu de prise de vue <span class="text-red-500">*</span></label>
                                    <input type="text" name="locations[]" required placeholder="Ex: Barrage de Roselend (73)" 
                                        class="w-full border-b-2 border-gray-200 focus:border-[#0A2240] outline-none py-1 transition bg-transparent text-[#0A2240]">
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
            const companyContainer = document.getElementById('companyFieldContainer');
            const companyInput = document.getElementById('companyInput');

            if (candidacyType === 'corporate') {
                companyContainer.classList.remove('hidden');
                companyInput.required = true;
            } else {
                companyContainer.classList.add('hidden');
                companyInput.required = false;
                companyInput.value = ''; // Clear value
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