#!/usr/bin/env python3
"""
Script de compression d'images pour le projet Concours Photo
Scanne le répertoire data/slide/ et compresse les images dans photos/slides_optimized/
"""

import os
import sys
from pathlib import Path
from PIL import Image, ExifTags
import re

# Configuration
SOURCE_DIR = Path(__file__).parent.parent / "original_photos" / "slide"
TARGET_DIR = Path(__file__).parent.parent.parent / "photos" / "slides_optimized"
MAX_DIM = 1920  # Full HD
QUALITY = 85

def process_image(source_path, dest_path, max_dim, quality):
    """
    Redimensionne et sauvegarde une image en format JPEG
    
    Args:
        source_path: Chemin de l'image source
        dest_path: Chemin de destination
        max_dim: Dimension maximale (largeur ou hauteur)
        quality: Qualité JPEG (0-100)
    
    Returns:
        bool: True si succès, False sinon
    """
    try:
        # Ouvrir l'image et appliquer l'orientation EXIF automatiquement
        img = Image.open(source_path)
        
        # Appliquer la rotation EXIF si présente
        # 1: Normal, 3: 180, 6: 270 (Portrait Clockwise), 8: 90 (Portrait Counter-Clockwise)
        # Note: image_transpose handles this automatically in modern PIL
        try:
            exif = img._getexif()
            if exif:
                for tag, value in exif.items():
                    if ExifTags.TAGS.get(tag) == 'Orientation':
                        if value == 3: img = img.rotate(180, expand=True)
                        elif value == 6: img = img.rotate(270, expand=True)
                        elif value == 8: img = img.rotate(90, expand=True)
                        break
        except Exception:
            pass  # Ignore si pas d'EXIF or error
        
        # Convertir en RGB si nécessaire (pour les PNG avec transparence)
        if img.mode in ('RGBA', 'LA', 'P'):
            background = Image.new('RGB', img.size, (255, 255, 255))
            background.paste(img, mask=img.split()[-1] if img.mode in ('RGBA', 'LA') else None)
            img = background
        elif img.mode != 'RGB':
            img = img.convert('RGB')
        
        # Calculer les nouvelles dimensions
        width, height = img.size
        ratio = width / height
        
        if width > max_dim or height > max_dim:
            if ratio > 1:  # Paysage
                new_width = max_dim
                new_height = int(max_dim / ratio)
            else:  # Portrait
                new_height = max_dim
                new_width = int(max_dim * ratio)
        else:
            new_width = width
            new_height = height
        
        # Redimensionner si nécessaire
        if (new_width, new_height) != (width, height):
            img = img.resize((new_width, new_height), Image.Resampling.LANCZOS)
        
        # Sauvegarder en JPEG
        img.save(dest_path, 'JPEG', quality=quality, optimize=True)
        
        return True
    
    except Exception as e:
        print(f"    ERREUR: {e}")
        return False

def sanitize_filename(name):
    """Nettoie un nom de fichier en gardant les accents et la casse"""
    # Garde lettres (unicode), chiffres, espaces, tirets et underscores
    # On évite seulement les caractères problématiques pour les systèmes de fichiers (/ \ : * ? " < > |)
    cleaned = re.sub(r'[\\/*?:"<>|]', '', name)
    return cleaned.strip()

def main():
    """Fonction principale"""
    # Créer le répertoire cible
    TARGET_DIR.mkdir(parents=True, exist_ok=True)
    
    # Extensions autorisées
    allowed_extensions = {'.jpg', '.jpeg', '.png', '.webp', '.tif', '.tiff'}
    
    count = 0
    skipped = 0
    errors = 0
    
    print(f"Démarrage de l'optimisation...")
    print(f"Source: {SOURCE_DIR}")
    print(f"Cible: {TARGET_DIR}")
    print(f"Dimension max: {MAX_DIM}px")
    print(f"Qualité: {QUALITY}%")
    print()
    
    # Parcourir tous les fichiers
    for file_path in SOURCE_DIR.rglob('*'):
        if not file_path.is_file():
            continue
        
        ext = file_path.suffix.lower()
        if ext not in allowed_extensions:
            continue
        
        # Extraire le nom du participant depuis le chemin
        # Format: data/slide/Prenom Nom/photo.jpg
        relative_path = file_path.relative_to(SOURCE_DIR)
        participant_name = str(relative_path.parts[0]) if relative_path.parts else "Inconnu"
        
        # Nettoyer les préfixes types '---' utilisés pour le tri local
        participant_name = participant_name.lstrip('-').strip()
        
        # Nettoyer les noms
        safe_participant = sanitize_filename(participant_name)
        safe_filename = sanitize_filename(file_path.stem)
        
        # Format: "Participant Name___OriginalName.jpg"
        target_filename = f"{safe_participant}___{safe_filename}.jpg"
        target_path = TARGET_DIR / target_filename
        
        if target_path.exists():
            skipped += 1
            continue
        
        print(f"Traitement: {file_path.name} (Participant: {participant_name})... ", end='')
        
        if process_image(file_path, target_path, MAX_DIM, QUALITY):
            # Afficher la taille avant/après
            original_size = file_path.stat().st_size / 1024  # KB
            compressed_size = target_path.stat().st_size / 1024  # KB
            reduction = ((original_size - compressed_size) / original_size) * 100
            
            print(f"OK ({original_size:.0f}KB -> {compressed_size:.0f}KB, -{reduction:.0f}%)")
            count += 1
        else:
            errors += 1
    
    print()
    print("=" * 60)
    # --- SECTION OPTIMISATION DES PRIX (Nouveau) ---
    PRIZE_SOURCE = Path(__file__).parent.parent / "original_photos" / "prizes"
    PRIZE_TARGET = Path(__file__).parent.parent.parent / "data" / "interne"
    
    prize_map = {
        '01.PHOTO_MEMBRE_001.jpg': 'prix-01-photo.jpg',
        '1er prix bon format.jpg': 'prix-01-overlay.jpg',
        '02.PHOTO_MEMBRE_101.jpg': 'prix-02-photo.jpg',
        '2ème prix bon format.jpg': 'prix-02-overlay.jpg',
        '03.PHOTO_MEMBRE_087.jpg':  'prix-03-photo.jpg',
        '3ème prix bon format.jpg': 'prix-03-overlay.jpg',
    }

    prize_count = 0
    prize_errors = 0

    if PRIZE_SOURCE.exists():
        print("\nOptimisation des photos de PRIX...")
        PRIZE_TARGET.mkdir(parents=True, exist_ok=True)
        for old_name, new_name in prize_map.items():
            source_file = PRIZE_SOURCE / old_name
            if source_file.exists():
                target_file = PRIZE_TARGET / new_name
                try:
                    with Image.open(source_file) as img:
                        # Auto-orient
                        try:
                            for orientation in ExifTags.TAGS.keys():
                                if ExifTags.TAGS[orientation] == 'Orientation':
                                    break
                            exif = dict(img._getexif().items())
                            if exif[orientation] == 3: img = img.rotate(180, expand=True)
                            elif exif[orientation] == 6: img = img.rotate(270, expand=True) # Corrected elseif to elif
                            elif exif[orientation] == 8: img = img.rotate(90, expand=True)  # Corrected elseif to elif
                        except (AttributeError, KeyError, IndexError): pass

                        img = img.convert('RGB')
                        img.thumbnail((MAX_DIM, MAX_DIM), Image.Resampling.LANCZOS)
                        img.save(target_file, 'JPEG', quality=QUALITY, optimize=True)
                        print(f"  Prix: {old_name} -> {new_name} OK")
                        prize_count += 1
                except Exception as e:
                    print(f"  Erreur sur {old_name}: {e}")
                    prize_errors += 1
    print("\n" + "=" * 60)
    print(f"Terminé!")
    print("Vous pouvez maintenant uploader ce dossier sur votre hébergeur.")

if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\nInterrompu par l'utilisateur.")
        sys.exit(1)
    except Exception as e:
        print(f"\nERREUR FATALE: {e}", file=sys.stderr)
        sys.exit(1)
