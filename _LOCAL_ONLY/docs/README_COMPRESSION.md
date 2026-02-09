# Guide: Compression des Photos avant Upload

## 🎯 Objectif
Compresser les photos localement pour réduire la taille du projet avant l'upload sur votre hébergeur.

---

## 📋 Étapes Rapides

### 🥇 Option 1: Script PowerShell (RECOMMANDÉ - Plus fiable)

**Clic-droit** sur `compress_photos.ps1` → **"Exécuter avec PowerShell"**

Si vous voyez un message de sécurité, tapez : `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass`

### 🥈 Option 2: Script Batch (Simple)

**Double-cliquez** sur `compress_photos.bat`

### 🥉 Option 3: Manuelle (Si vous avez déjà Python)

```powershell
pip install Pillow
python compress_photos.py
```


---

## 📁 Résultat

Les photos compressées seront dans : **`photos/slides_optimized/`**

Format des noms : `NomParticipant___NomPhoto.jpg`

### Réduction Attendue
- **Dimension max** : 1920px (Full HD)
- **Qualité** : 85%
- **Réduction typique** : 60-80% de la taille originale

---

## 🛠️ Installation de Python (si nécessaire)

### Windows 10/11

**Option A - Téléchargement Direct**
1. Allez sur https://www.python.org/downloads/
2. Téléchargez Python 3.11 ou supérieur
3. ⚠️ **IMPORTANT** : Cochez "Add Python to PATH" pendant l'installation
4. Relancez `compress_photos.bat`

**Option B - Winget (Windows 11)**
```powershell
winget install Python.Python.3.11
```

---

## ⚙️ Configuration Avancée

Pour ajuster la compression, modifiez `compress_photos.py` :

```python
MAX_DIM = 1920    # Changez pour 1280 (720p) ou 1600
QUALITY = 85      # Changez pour 75 (plus petit) ou 90 (meilleure qualité)
```

**Suggestions selon vos besoins :**

| Usage | MAX_DIM | QUALITY | Réduction |
|-------|---------|---------|-----------|
| Web standard | 1280 | 75 | ~80% |
| Haute qualité | 1920 | 85 | ~65% |
| Maximum qualité | 2560 | 90 | ~50% |

---

## 📊 Vérifier les Économies

Après compression, vérifiez la taille :

```powershell
# Taille AVANT (dossier original)
Get-ChildItem -Path "data\slide" -Recurse | Measure-Object -Property Length -Sum

# Taille APRÈS (dossier compressé)
Get-ChildItem -Path "photos\slides_optimized" -Recurse | Measure-Object -Property Length -Sum
```

---

## 🚀 Upload sur l'Hébergeur

1. **Compresser** en archive le dossier `photos/slides_optimized/`
2. **Uploader** uniquement ce fichier ZIP sur votre hébergeur
3. **Extraire** sur le serveur
4. **Mettre à jour** vos scripts PHP pour pointer vers `photos/slides_optimized/` au lieu de `data/slide/`

---

## 🔄 Alternative : Script PHP

Si vous préférez utiliser PHP (doit être installé) :

```powershell
# 1. Démarrer un serveur local
php -S localhost:8000

# 2. Ouvrir dans le navigateur
# http://localhost:8000/admin/optimize.php
```

---

## ❓ Problèmes Courants

### "Python n'est pas reconnu"
→ Réinstallez Python en cochant "Add to PATH"

### "pip n'est pas reconnu"
→ Python n'est pas dans le PATH, réinstallez-le correctement

### "PIL module not found"
→ Exécutez : `pip install Pillow`

### Les photos ne sont pas trouvées
→ Vérifiez que vos photos sont dans `data/slide/NomParticipant/`

---

## 📝 Support

Pour plus d'informations, consultez :
- `compress_photos.py` - Le script de compression
- `admin/optimize.php` - Version PHP équivalente
