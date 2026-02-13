# Script PowerShell pour compresser les photos
# Plus fiable que le batch sur Windows moderne

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Compression des Photos - Concours Photo" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si Python est installé
$pythonInstalled = $false
try {
    $pythonVersion = & python --version 2>&1
    if ($LASTEXITCODE -eq 0 -and $pythonVersion -match "Python \d+\.\d+") {
        # Vérifier que ce n'est pas le stub Windows Store
        $pipVersion = & pip --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            $pythonInstalled = $true
            Write-Host "[OK] Python detecte" -ForegroundColor Green
            Write-Host "     $pythonVersion" -ForegroundColor Gray
            Write-Host ""
        }
    }
} catch {
    $pythonInstalled = $false
}

if (-not $pythonInstalled) {
    Write-Host "[ERREUR] Python n'est pas installe correctement" -ForegroundColor Red
    Write-Host ""
    Write-Host "SOLUTIONS RAPIDES:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  Option 1: Microsoft Store (Le plus simple)" -ForegroundColor Cyan
    Write-Host "    1. Ouvrez le Microsoft Store"
    Write-Host "    2. Recherchez 'Python 3.12'"
    Write-Host "    3. Cliquez sur Installer"
    Write-Host "    4. Relancez ce script"
    Write-Host ""
    Write-Host "  Option 2: Winget (Rapide)" -ForegroundColor Cyan
    Write-Host "    winget install Python.Python.3.12"
    Write-Host ""
    Write-Host "  Option 3: Site officiel" -ForegroundColor Cyan
    Write-Host "    https://www.python.org/downloads/"
    Write-Host "    IMPORTANT: Cochez 'Add Python to PATH'"
    Write-Host ""
    
    $response = Read-Host "Voulez-vous installer Python via winget maintenant? (O/N)"
    if ($response -eq "O" -or $response -eq "o") {
        Write-Host ""
        Write-Host "Installation de Python via winget..." -ForegroundColor Yellow
        winget install Python.Python.3.12
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "[OK] Python installe! Veuillez REDEMARRER POWERSHELL et relancer ce script." -ForegroundColor Green
        } else {
            Write-Host ""
            Write-Host "[ERREUR] Installation echouee. Utilisez le Microsoft Store ou le site officiel." -ForegroundColor Red
        }
    }
    
    Write-Host ""
    Read-Host "Appuyez sur Entree pour quitter"
    exit 1
}

# Vérifier si Pillow est installé
Write-Host "Verification de Pillow..." -ForegroundColor Gray
$pillowInstalled = $false
try {
    & python -c "import PIL" 2>$null
    if ($LASTEXITCODE -eq 0) {
        $pillowInstalled = $true
        Write-Host "[OK] Pillow est deja installe" -ForegroundColor Green
        Write-Host ""
    }
} catch {
    $pillowInstalled = $false
}

if (-not $pillowInstalled) {
    Write-Host "[INFO] Installation de Pillow (bibliotheque d'images)..." -ForegroundColor Yellow
    Write-Host "        Cela peut prendre 1-2 minutes..." -ForegroundColor Gray
    Write-Host ""
    
    & pip install Pillow
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host ""
        Write-Host "[ERREUR] Installation de Pillow echouee" -ForegroundColor Red
        Write-Host ""
        Write-Host "Essayez manuellement:" -ForegroundColor Yellow
        Write-Host "  pip install --user Pillow"
        Write-Host ""
        Read-Host "Appuyez sur Entree pour quitter"
        exit 1
    }
    
    Write-Host ""
    Write-Host "[OK] Pillow installe avec succes!" -ForegroundColor Green
    Write-Host ""
}

# Lancer le script de compression
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Lancement de la compression..." -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

& python compress_photos.py

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "[ERREUR] La compression a rencontre un probleme" -ForegroundColor Red
    Write-Host "Verifiez que vos photos sont dans: data\slide\" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Appuyez sur Entree pour quitter"
    exit 1
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "TERMINE AVEC SUCCES!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Les photos compressees sont dans:" -ForegroundColor Cyan
Write-Host "  photos\slides_optimized\" -ForegroundColor White
Write-Host ""
Write-Host "Vous pouvez maintenant uploader ce dossier sur votre hebergeur." -ForegroundColor Gray
Write-Host ""
Read-Host "Appuyez sur Entree pour quitter"
