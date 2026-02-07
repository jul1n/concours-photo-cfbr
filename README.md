# 📘 Documentation - Concours Photo CFBR

Ce document regroupe les informations techniques pour l'installation, la maintenance et l'administration du site du Concours Photo CFBR.

---

## 🚀 1. Stack Technique & Pré-requis

L'application est conçue pour être légère et simple à déployer (PHP "Vanilla" sans framework lourd).

### 🖥️ Serveur
*   **OS** : Linux (recommandé) ou Windows Server.
*   **Serveur Web** : Apache ou Nginx.
*   **Langage** : **PHP 7.4 ou supérieur** (testé compatible PHP 8.x).
*   **Base de Données** : **SQLite 3** (Fichier local, pas de serveur SQL requis).

### 📦 Extensions PHP Requises
Assurez-vous que les extensions suivantes sont activées dans `php.ini` :
*   `pdo_sqlite` : Pour la connexion à la base de données.
*   `gd` : Pour le redimensionnement et l'optimisation des images (thumbnails, 4K).
*   `exif` : Pour lire l'orientation des photos (rotation automatique).
*   `mbstring` : Pour la gestion des caractères UTF-8 (noms des participants).
*   `fileinfo` : Pour valider les types MIME des uploads.

### ⚙️ Configuration PHP (`php.ini`)
Les participants envoient des photos haute définition. Il est **impératif** d'ajuster ces variables pour éviter les erreurs d'upload :
```ini
upload_max_filesize = 32M ; (Suffisant pour une photo de 25Mo + marge)
post_max_size = 160M ; (Pour accepter 5 photos simultanées)
memory_limit = 512M
max_execution_time = 300 ; (Optionnel, si traitement images lent)
```

### 📧 Configuration SMTP (Délivrabilité des Emails)
Pour éviter que les emails de confirmation (contenant les tokens de validation) ne soient classés comme SPAM ou rejetés ("This may not be the person who sent this"), **il est crucial de configurer un serveur SMTP authentifié** au lieu d'utiliser la fonction `mail()` de base de PHP qui passe par le serveur local.

**Option A : Configuration Globale (Windows/Linux)**
Dans le fichier `php.ini`, configurez la section `[mail function]` (principalement pour Windows) ou configurez votre agent de transfert de mail (Postfix/Sendmail) sur Linux pour relayer vers un vrai SMTP (ex: SendGrid, Mailjet, ou le SMTP de votre hébergeur).

**Option B : Utilisation d'une Librairie (Recommandé)**
L'application utilise actuellement la fonction native `mail()`. Pour une fiabilité maximale, il est recommandé d'installer **PHPMailer** et de modifier `process_upload.php` pour utiliser une connexion SMTP authentifiée.

Exemple de paramétrage SMTP type :
*   **Host**: `smtp.votre-domaine.com` (ou `smtp.office365.com`, `smtp.gmail.com`...)
*   **Port**: `587` (TLS) ou `465` (SSL)
*   **SMTPAuth**: `true`
*   **Username**: `ne-pas-repondre@barrages-cfbr.eu`
*   **Password**: `votre_mot_de_passe`

> 💡 **Conseil** : Assurez-vous que l'adresse "From" (`no-reply@barrages-cfbr.eu`) correspond bien au domaine authentifié et que les enregistrements DNS **SPF** et **DKIM** sont correctement configurés sur votre domaine.

---

## 2. Installation & Déploiement

### 📂 Structure des Dossiers
Voici l'arborescence clé et les permissions nécessaires :

| Chemin | Rôle | Permission (chmod) |
| :--- | :--- | :--- |
| `/` | Racine du site (scripts PHP) | 755 (Lecture/Exéc) |
| `/data/` | Contient la **Base de Données** (`concours.db`) | **775 ou 777** (Selon user web) |
| `/photos/` | Stockage des images uploadées | **775 ou 777** (Écriture requise) |
| `/photos/originals/` | Images brutes (telles qu'envoyées) | Écriture |
| `/photos/display_4k/` | Images optimisées pour affichage TV/Web | Écriture |
| `/photos/thumbs/` | Miniatures pour la galerie | Écriture |
| `/photos/slides_optimized/`| Généré par le script Slide Show | Écriture |

> ⚠️ **Important Securité** : Le dossier `/data/` contient `concours.db`. **Il ne doit PAS être accessible via le navigateur Web**.
> *   Sous Apache : Un fichier `.htaccess` avec `Deny from all` est recommandé dans `/data/`.
> *   Sous Nginx : Ajoutez une règle `location ~ /data/ { deny all; }`.

### 🚀 Initialisation
1.  **Copiez les sources** sur le serveur.
2.  Assurez-vous que le dossier `/data/` est inscriptible.
3.  L'application créera automatiquement la base de données SQLite au premier lancement si elle n'existe pas, ou vous pouvez forcer l'initialisation/reset en visitant (avec précaution) : `http://votre-site/init_db.php`.
    *   *Note : Supprimez `init_db.php` de la production une fois initialisé pour éviter tout reset accidentel.*

---

## 3. Gestion du Concours (Back-Office)

Il n'y a pas de "compte administrateur" avec login/mot de passe classique. L'administration se fait via des scripts ou des pages spécifiques sécurisées par l'obscurité (URL non publiques) ou par Token.

### 👥 Gestion du Jury
Les membres du jury accèdent à l'interface de vote via un **lien unique (Token)** envoyé par email.

1.  **Ajouter des membres** :
    *   Éditez le fichier `add_jury_members.php` pour lister les emails/noms dans le tableau `$jury_members`.
    *   Exécutez-le une fois : `http://votre-site/add_jury_members.php`.
2.  **Envoyer les accès** :
    *   Utilisez `jury_login.php` pour générer et envoyer les liens magiques aux jurés.
3.  **Auditer les accès** :
    *   Le fichier `admin_audit_jury.php` permet de voir qui s'est connecté et l'état des tokens.

### 📸 Maintenance des Photos
Si des photos s'affichent mal (ex: mauvaise rotation) ou pour préparer le Diaporama final :

*   **Script de correction** : `optimize_slides.php`
    *   Ce script scanne les dossiers, corrige l'orientation (EXIF), redimensionne en 1080p/4K et stocke le résultat dans `photos/slides_optimized/`.
    *   **À lancer** après une vague d'uploads et surtout **avant une projection** pour générer les slides. Attention : Il faut vider le dossier `photos/slides_optimized/` avant de relancer le script si vous voulez forcer la régénération.

### 📊 Export des Résultats
*   **`admin_results.php`** : Affiche le classement provisoire ou final (Tour 1 et Tour 2).
*   **`admin_export_zip.php`** : Permet de télécharger toutes les photos (fonctionnalité à vérifier selon volume).
*   **`admin_export_pdf.php`** : Génère un rapport PDF des participations (fiche technique + photo).

---

## 4. Base de Données (Schéma simplifiée)

Le fichier SQLite est `data/concours.db`. Vous pouvez l'ouvrir avec n'importe quel client SQLite (ex: *DB Browser for SQLite*).

**Tables principales :**
*   `participants` : Infos persos, statut de validation, token email, accord annexes.
*   `photos` : Lien vers fichiers, titre, description, catégorie, flags (is_upscale_suspect).
*   `votes_tour1` : Votes Oui/Non pour la pré-sélection.
*   `votes_tour2` : Classement/Notes pour la finale.
*   `jury_members` & `jury_tokens` : Gestion des accès jurés.
