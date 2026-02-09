# 🚀 Installation Rapide de Python pour Windows

## Le script batch ne fonctionne pas ?

C'est probablement parce que **Python n'est pas installé** sur votre système.

---

## ✅ SOLUTION RECOMMANDÉE : Microsoft Store (Le plus simple)

1. **Ouvrez le Microsoft Store** (déjà installé sur Windows 10/11)
2. **Recherchez** "Python 3.12" ou "Python 3.11"
3. **Cliquez sur "Installer"**
4. Attendez la fin de l'installation
5. **Relancez** `compress_photos.bat`

✨ **Avantage** : Installation automatique, PATH configuré automatiquement

---

## Alternative : Site Officiel Python

1. Allez sur **https://www.python.org/downloads/**
2. Téléchargez **Python 3.12** ou version supérieure
3. **⚠️ IMPORTANT** : Pendant l'installation, **cochez "Add Python to PATH"**
4. Terminez l'installation
5. **Redémarrez** votre terminal/invite de commandes
6. Relancez `compress_photos.bat`

---

## Vérifier que Python est installé

Ouvrez PowerShell et tapez :

```powershell
python --version
pip --version
```

Si vous voyez les versions, c'est bon ! ✅

Si vous voyez "Python est introuvable", réinstallez en cochant "Add to PATH" ❌

---

## Exécution Manuelle (si le .bat ne marche toujours pas)

Si Python est installé mais le script batch pose problème :

```powershell
# 1. Installer Pillow
pip install Pillow

# 2. Lancer la compression
python compress_photos.py
```

---

## Problèmes Courants

### "Python est introuvable" malgré l'installation
→ Python n'est pas dans le PATH
→ **Solution** : Réinstallez en cochant "Add Python to PATH"

### "pip n'est pas reconnu"
→ pip n'est pas installé ou pas dans le PATH
→ **Solution** : Réinstallez Python depuis python.org en cochant "Add to PATH"

### Le script batch se ferme immédiatement
→ Il y a une erreur mais la fenêtre se ferme trop vite
→ **Solution** : Ouvrez PowerShell dans le dossier et lancez `.\compress_photos.bat` pour voir l'erreur

---

## ⚡ Après l'installation

Une fois Python installé :
1. Double-cliquez sur `compress_photos.bat`
2. Le script va automatiquement :
   - Installer Pillow (si besoin)
   - Compresser toutes vos photos
   - Les sauvegarder dans `photos/slides_optimized/`

**Temps estimé** : 2-5 minutes selon le nombre de photos
