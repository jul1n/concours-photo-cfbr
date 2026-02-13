# 📘 Documentation - Concours Photo CFBR

Ce document regroupe les informations techniques pour l'installation, la maintenance et l'administration du site du Concours Photo CFBR.

---

## 🚀 1. Stack Technique & Pré-requis

L'application est conçue pour être légère et simple à déployer (PHP "Vanilla" sans framework lourd).

### 🖥️ Serveur
- **OS** : Linux (recommandé) ou Windows Server.
- **Serveur Web** : Apache ou Nginx.
- **Langage** : **PHP 7.4 ou supérieur** (testé compatible PHP 8.x).
- **Base de Données** : **SQLite 3** (Fichier local, pas de serveur SQL requis).

### 📦 Extensions PHP Requises
Assurez-vous que les extensions suivantes sont activées dans `php.ini` :
- `pdo_sqlite` : Pour la connexion à la base de données.
- `gd` : Pour le redimensionnement et l'optimisation des images (thumbnails, 4K).
- `exif` : Pour lire l'orientation des photos (rotation automatique).
- `mbstring` : Pour la gestion des caractères UTF-8 (noms des participants).
- `fileinfo` : Pour valider les types MIME des uploads.

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

| `/` | Racine du site (scripts PHP) | 755 (Lecture/Exéc) |
| `/_LOCAL_ONLY/` | **NE PAS UPLOADER** (Scripts locaux de compression) | Local uniquement |
| `/data/` | Contient la **Base de Données** (`concours.db`) | **775 ou 777** |
| `/photos/` | Stockage des images uploadées | **775 ou 777** (Écriture requise) |
| `/photos/originals/` | Images brutes (telles qu'envoyées) | Écriture |
| `/photos/display_4k/` | Images optimisées pour affichage TV/Web | Écriture |
| `/photos/thumbs/` | Miniatures pour la galerie | Écriture |
| `/photos/slides_optimized/`| Généré par le script Slide Show | Écriture |

> ⚠️ **Important Securité** : Le dossier `/data/` contient `concours.db`. **Il ne doit PAS être accessible via le navigateur Web**.
> *   Sous Apache : Un fichier `.htaccess` avec `Deny from all` est recommandé dans `/data/`.
> *   Sous Nginx : Ajoutez une règle `location ~ /data/ { deny all; }`.

### 🚀 Initialisation (Première installation)
1.  **Copiez les sources** sur le serveur (en ignorant le dossier `_LOCAL_ONLY`).
2.  Assurez-vous que le dossier `/data/` est inscriptible.
3.  **Utilisez le Hub de Maintenance** pour tout configurer d'un coup :
    `https://votre-site.fr/maintenance/index.php?token=cfbr_repair_2026`
    *   **Diagnostic** : Vérifiez que tous les voyants sont au vert (PHP, extensions, permissions).
    *   **Initialisation Database** : Cliquez sur "Initialiser / Réparer" pour créer les tables.
    *   **Gestion du Jury** : Cliquez sur "Injecter le Jury Officiel" pour ajouter les 12 membres.
    *   *Note Securité : Une fois le site opérationnel, vous pouvez supprimer le dossier `maintenance/` ou limiter son accès.*

---

### 🛠️ Maintenance & Diagnostic
Le Hub de Maintenance centralise tous les outils techniques.
- Accès : `/maintenance/index.php?token=cfbr_repair_2026`
- Fonctions : Initialisation DB, Réparation, Seeding Jury, Test Email/PDF, Santé PWA.

### 👥 Gestion du Jury
1.  **Ajouts** : Via le **Maintenance Hub** (Injection liste ou manuel via form).
2.  **Accès** : Les jurés utilisent `/jury/login.php` pour recevoir leur lien magique.
3.  **Audit** : Consultez `/admin/audit.php` pour suivre les connexions et l'état des tokens.
4.  **Vote** : Se déroule via le lien reçu ou dans `/jury/auth.php`.

### 🏆 Résultats & Administration
Les résultats sont calculés automatiquement après le Tour 2.
- Dashboard des gagnants : `/admin/results.php`.
- Export Rapport complet (PDF) : `/admin/export_pdf.php`.
- Export Photos (ZIP) : `/admin/export_zip.php`.

---
*Note technique : Pour assurer la délivrabilité des emails, configurez les accès SMTP dans le Maintenance Hub.*

---

## 4. Base de Données (Schéma simplifiée)

Le fichier SQLite est `data/concours.db`. Vous pouvez l'ouvrir avec n'importe quel client SQLite (ex: *DB Browser for SQLite*).

**Tables principales :**
*   `participants` : Infos persos, statut de validation, token email, accord annexes.
*   `photos` : Lien vers fichiers, titre, description, catégorie, flags (is_upscale_suspect).
*   `votes_tour1` : Votes Oui/Non pour la pré-sélection.
*   `votes_tour2` : Classement/Notes pour la finale.
*   `jury_members` & `jury_tokens` : Gestion des accès jurés.
