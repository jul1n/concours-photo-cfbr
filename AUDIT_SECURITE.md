# Audit de sécurité, performance et bonnes pratiques — Concours Photo CFBR

**Dépôt audité :** `jul1n/concours-photo-cfbr`
**Date :** 7 juillet 2026
**Périmètre :** code applicatif actif (dossiers `OLD/` et bibliothèque `fpdf/` exclus car archivés/vendored).
**Stack :** PHP « vanilla » (7.4+/8.x), SQLite 3, GD, front Tailwind (CDN).

Ce document liste les vulnérabilités et faiblesses identifiées, classées par priorité, puis récapitule les correctifs appliqués et les actions manuelles restant à ta charge.

---

## Synthèse

Le socle est sain : requêtes PDO préparées partout (pas d'injection SQL détectée), tokens en `random_bytes`, séparation claire `core` / `admin` / `jury`. En revanche, plusieurs **failles critiques** exposaient l'application : endpoints de vote sans authentification, scripts de maintenance/diagnostic publics et destructifs, secrets en clair dans le code, et données personnelles (e-mails, IP du jury) committées dans Git.

L'essentiel a été corrigé. Restent quelques actions manuelles (purge de l'historique Git, dépôt des `.htaccess` sur le serveur, génération des hash de mots de passe, migration Tailwind).

---

## 🔴 Vulnérabilités critiques

### 1. Données personnelles committées dans Git
Le fichier `jury/data/login_requests.csv` était **suivi par Git** et contenait de vraies adresses e-mail et IP (dont la tienne). La liste complète des e-mails du jury était aussi en dur dans `maintenance/index.php`. Le `.htaccess` protège le fichier côté serveur web, mais **pas** dans le dépôt GitHub. Enjeu RGPD autant que technique.

### 2. Endpoints de vote jury sans authentification
`jury/vote_q.php` et `jury/vote_r.php` acceptaient des `INSERT` de votes **sans vérifier la session jury**. Les pages HTML étaient protégées, mais pas les endpoints POST qu'elles appellent : n'importe qui connaissant l'URL pouvait bourrer les urnes. L'identité du votant était en plus dérivée de `REMOTE_ADDR` (trivialement usurpable).

### 3. Scripts de diagnostic/réparation publics et destructifs
`db_diagnostic.php`, `diag_db.php`, `diag_db_extra.php`, `repair_database.php`, `delete_duplicate_slides.php`, `fix_slideshow_files.php`, `admin/audit.php` et `admin/optimize.php` n'avaient **aucun contrôle d'accès**. `admin/audit.php` exposait IP + e-mails avec un `// TODO: Add authentication` explicite ; certains exécutaient du DDL/DELETE.

### 4. Mot de passe admin en clair + hash invalide
Dans `admin/results.php`, la condition était `password_verify(...) || $_POST['unlock_password'] === 'concours2026'`. Le repli en clair rendait le hash inutile, et ce hash était de toute façon tronqué/invalide.

### 5. Mot de passe SMTP en clair réinjecté dans le HTML
`maintenance/index.php` faisait `value="<?= htmlspecialchars($settings['smtp_pass']) ?>"` : le mot de passe SMTP, stocké en clair dans la table `settings`, était renvoyé dans un attribut de formulaire.

### 6. Authentification « maintenance » par token GET partagé en clair
Le dashboard de maintenance (DELETE, DDL, gestion jury) se déverrouillait avec `?token=cfbr_repair_2026` — un secret partagé, en clair dans le code, qui apparaît dans les logs serveur et l'historique de navigation, et qui donnait aussi les droits admin.

---

## 🟠 Vulnérabilités importantes

### 7. Extension de fichier contrôlée par l'attaquant à l'upload
`getimagesize()` validait bien qu'il s'agissait d'une image, mais l'original était sauvegardé avec **l'extension d'origine** (`pathinfo($originalName)`). Un fichier polyglotte (image valide + payload PHP) sauvé en `.php` = risque d'exécution de code. Aucun `.htaccess` ne protégeait `photos/originals/`, `photos/display_4k/`, `photos/thumbs/`, `uploads/pdfs/`.

### 8. Absence de protection CSRF
Seul `maintenance/index.php` implémentait un token CSRF. L'upload participant, les votes jury et le déverrouillage admin n'en avaient pas.

### 9. Fuite de détails techniques via `die($e->getMessage())`
`core/db.php` et plusieurs endpoints affichaient le message d'exception à l'utilisateur (chemins, structure SQL), utile à un attaquant.

### 10. Liens e-mail en `http://` + `HTTP_HOST` non validé
Les liens de connexion/validation étaient construits en clair à partir de `$_SERVER['HTTP_HOST']` (falsifiable → empoisonnement de lien / host header injection).

### 11. Tokens jury sans expiration + porte dérobée de debug
`verify.php` vérifiait l'usage unique mais pas l'ancienneté, et contenait un token en dur `local_debug_token_julien` contournant les contrôles.

---

## 🟡 Performance

### 12. Aucun réglage SQLite
Pas de mode WAL ni de `busy_timeout` : sous charge (jury qui vote pendant que le public consulte), les écritures pouvaient bloquer les lectures.

### 13. Requête d'agrégat coûteuse dans `jury/header.php`
Le header (inclus sur toutes les pages jury) exécutait un `GROUP BY ... HAVING` sur toute la table `photos`⋈`analytics` juste pour savoir si une shortlist existait.

### 14. Tailwind via CDN sur 25 fichiers
`cdn.tailwindcss.com` est un compilateur JIT navigateur, explicitement déconseillé en production (FOUC, dépendance externe, plus lent). Idem Font Awesome et Google Fonts (self-host = moins de requêtes tierces, meilleure conformité RGPD).

---

## 🟢 Bonnes pratiques & maintenabilité

- **IDOR maîtrisée mais token unique multi-usage** : `download_pdf.php` s'appuie sur le `validation_token` en GET, qui sert aussi à la validation e-mail et à l'accès dossier. Un seul secret pour plusieurs usages ; idéalement séparer les tokens.
- **`.gitignore` en UTF-16** : fragile, réenregistré en UTF-8.
- **Dossier `OLD/`** (~1000 fichiers d'archives) déployé inutilement.
- **Duplication de la logique d'auth** copiée-collée dans chaque page.
- **`htmlspecialchars()` à l'entrée** (dans `process_upload.php`) : encoder au stockage risque le double-encodage. Convention plus sûre : stocker brut (PDO protège l'injection) et échapper uniquement à l'affichage.
- **Déploiement FTP en clair** (`deploy.yml`) : préférer FTPS/SFTP.

---

## ✅ Correctifs appliqués

| # | Correctif | Fichiers |
|---|-----------|----------|
| 1 | CSV de logs retiré du suivi Git + ajouté au `.gitignore` | `.gitignore`, `jury/data/login_requests.csv` |
| 2 | Garde d'authentification jury + identifiant fiable (e-mail de session) | `jury/vote_q.php`, `jury/vote_r.php`, `jury/api_notifications.php`, `jury/toggle_promo.php` |
| 3 | Garde `require_maintenance()` / `require_admin()` sur les scripts sensibles | `db_diagnostic.php`, `diag_db.php`, `diag_db_extra.php`, `repair_database.php`, `delete_duplicate_slides.php`, `fix_slideshow_files.php`, `admin/audit.php`, `admin/optimize.php` |
| 4 | Suppression du mot de passe en clair, hash lu depuis la config | `admin/results.php` |
| 5 | Mot de passe SMTP non réinjecté dans le HTML, conservé si champ vide | `maintenance/index.php` |
| 6 | Auth maintenance par mot de passe hashé + formulaire de connexion | `maintenance/index.php` |
| 7 | Whitelist stricte d'extensions + `.htaccess` créés automatiquement | `core/process_upload.php` |
| 8 | Protection CSRF (helpers centralisés) sur tous les formulaires à effet de bord | `core/auth.php`, `upload.php`, `jury/home.php`, `jury/ranking.php`, `admin/results.php`, `maintenance/index.php` |
| 9 | Erreurs génériques côté utilisateur, détail dans `error_log` | `core/db.php`, `jury/home.php`, `jury/toggle_promo.php` |
| 10 | Liens e-mail en HTTPS construits depuis `base_url` (config) | `jury/auth.php`, `core/process_upload.php` |
| 11 | Expiration des tokens jury + suppression de la porte dérobée | `jury/verify.php` |
| 12 | Réglages SQLite : WAL, `synchronous=NORMAL`, `foreign_keys`, `busy_timeout` | `core/db.php` |
| 13 | Test `EXISTS` à la place du `GROUP BY/HAVING` | `jury/header.php` |
| — | Module d'auth central (gardes + CSRF + `app_config`) | `core/auth.php` (nouveau) |
| — | Configuration hors dépôt pour les secrets | `core/config.php` (ignoré), `core/config.example.php` (versionné) |

Tous les fichiers PHP modifiés passent `php -l` (aucune erreur de syntaxe) et un test de fumée (connexion DB + WAL + CSRF + gardes).

---

## 🔧 Actions manuelles restantes

1. **Purger l'historique Git** du CSV et des e-mails jury (le `git rm --cached` ne suffit pas, ils restent dans l'historique) :
   ```bash
   pip install git-filter-repo
   git filter-repo --path jury/data/login_requests.csv --invert-paths
   # puis forcer le push (attention, réécrit l'historique)
   ```

2. **Créer `core/config.php`** à partir de `core/config.example.php` et renseigner :
   - `base_url` (domaine réel du site),
   - les hash de mots de passe, générés avec :
     ```bash
     php -r "echo password_hash('MON_MOT_DE_PASSE', PASSWORD_DEFAULT), PHP_EOL;"
     ```
   `core/config.php` ne doit **jamais** être committé (déjà dans `.gitignore`).

3. **Déposer les `.htaccess` sur le serveur** dans les dossiers d'images/PDF. `process_upload.php` les crée désormais automatiquement au prochain upload, mais tu peux les poser tout de suite. Modèles fournis :
   - `photos/originals.htaccess.txt` → `photos/originals/.htaccess`
   - `photos/nophp.htaccess.txt` → `photos/display_4k/.htaccess` **et** `photos/thumbs/.htaccess`
   - `uploads_pdfs.htaccess.txt` → `uploads/pdfs/.htaccess`

4. **Migrer Tailwind / Font Awesome / Google Fonts** vers des fichiers statiques self-hostés (Tailwind CLI) pour la production.

5. **Sortir `OLD/` du dépôt de production** (conserver dans une branche ou un tag).

6. **Passer le déploiement en FTPS/SFTP** si l'hébergeur le permet.

7. **Changer les mots de passe** qui ont pu transiter en clair (SMTP, admin `concours2026`, token maintenance `cfbr_repair_2026`).
