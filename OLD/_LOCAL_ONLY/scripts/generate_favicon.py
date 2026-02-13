#!/usr/bin/env python3
import os
from pathlib import Path
from PIL import Image

def generate_favicon():
    # Paths
    root_dir = Path(__file__).parent.parent.parent
    logo_path = root_dir / "assets" / "logo_cfbr_100_ans.png"
    target_png = root_dir / "assets" / "favicon.png"
    target_ico = root_dir / "favicon.ico"

    if not logo_path.exists():
        print(f"Erreur: Logo introuvable à {logo_path}")
        return

    # Open logo
    img = Image.open(logo_path).convert("RGBA")
    
    # Calculate square size based on the larger dimension (or just use 512)
    w, h = img.size
    size = max(w, h)
    
    # Create a new transparent square image
    square = Image.new("RGBA", (size, size), (255, 255, 255, 0))
    
    # Paste logo in center
    offset = ((size - w) // 2, (size - h) // 2)
    square.paste(img, offset, img)
    
    # Resize for common favicon sizes
    # PNG 512x512
    square.resize((512, 512), Image.Resampling.LANCZOS).save(target_png, "PNG")
    print(f"PNG généré: {target_png}")
    
    # ICO (includes multiple sizes)
    square.save(target_ico, format='ICO', sizes=[(16, 16), (32, 32), (48, 48), (64, 64)])
    print(f"ICO généré: {target_ico}")

if __name__ == "__main__":
    generate_favicon()
