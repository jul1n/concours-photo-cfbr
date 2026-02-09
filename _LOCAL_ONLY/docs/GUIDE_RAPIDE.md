# 📸 Guide Rapide - Compression des Photos

## ⚡ Méthode la Plus Simple

### Étape 1 : Installer Python (une seule fois)

**Via Microsoft Store** (recommandé) :
1. Appuyez sur `Win + S` (ou cliquez sur la loupe Windows)
2. Tapez "Microsoft Store"
3. Ouvrez Microsoft Store
4. Dans la barre de recherche du Store, tapez "Python 3.12"
5. Cliquez sur le premier résultat "Python 3.12"
6. Cliquez sur "Installer" (ou "Obtenir")
7. Attendez la fin de l'installation (1-2 minutes)

**OU via Winget** (plus rapide si vous êtes à l'aise avec PowerShell) :
```powershell
winget install Python.Python.3.12
```

### Étape 2 : Lancer la Compression

Vous avez **3 options** :

#### Option A : Depuis PowerShell (le plus direct)
1. Ouvrez PowerShell dans le dossier du projet
2. Tapez : `.\compress_photos.ps1`
3. Appuyez sur Entrée
4. Suivez les instructions à l'écran

#### Option B : Clic-droit sur le fichier
1. Ouvrez l'Explorateur de fichiers
2. Allez dans le dossier : `C:\Users\Julien\OneDrive\42.Codes\ConcoursPhotoExt`
3. Trouvez le fichier `compress_photos.ps1`
4. **Clic-droit** sur le fichier
5. Choisissez "**Exécuter avec PowerShell**"
   - Si vous ne voyez pas cette option, choisissez "Ouvrir avec" → "PowerShell"

#### Option C : Double-clic sur le .bat
1. Dans le même dossier
2. Double-cliquez sur `compress_photos.bat`

---

## 🚀 Ce qui va se passer

1. Le script vérifie si Python est installé
2. Si Python n'est pas installé, il vous guide pour l'installer
3. Il installe automatiquement Pillow (bibliothèque d'images)
4. Il compresse toutes vos photos dans `data/slide/`
5. Il sauvegarde les photos compressées dans `photos/slides_optimized/`

**Temps estimé** : 2-5 minutes (selon le nombre de photos)

---

## 📊 Résultat Attendu

- **Avant** : 889 MB (139 photos)
- **Après** : ~180-350 MB (60-80% de réduction)
- **Format** : Toutes les photos en JPEG optimisé
- **Dimension max** : 1920px (Full HD)
- **Qualité** : 85%

---

## ❓ Si ça ne marche pas

1. **"Impossible de charger le script"**
   → Dans PowerShell, tapez : `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass`
   → Puis relancez le script

2. **"Python est introuvable"**
   → Installez Python via Microsoft Store (voir Étape 1)

3. **Le script se ferme immédiatement**
   → Utilisez l'Option A (depuis PowerShell) pour voir les messages d'erreur

4. **Autre problème**
   → Consultez `README_COMPRESSION.md` pour plus de détails
   → Ou `INSTALLER_PYTHON.md` pour l'installation de Python

---

## 💡 Astuce

Si vous avez déjà PowerShell ouvert dans le bon dossier, tapez simplement :
```powershell
.\compress_photos.ps1
```

C'est la méthode la plus rapide ! ⚡
