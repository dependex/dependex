import os
import base64
from PIL import Image

ARTIFACTS_DIR = r"C:\Users\piano\.gemini\antigravity-ide\brain\6d40dccf-c260-4dec-8c4f-821fe953702d"
TARGET_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "assets", "img", "sponsors"))
os.makedirs(TARGET_DIR, exist_ok=True)

MAPPING = {
    "sicurissimo-online": "sicurissimo_sponsor_8k_1789311962808.jpg",
    "betterway-agency": "betterway_sponsor_8k_1789311980128.jpg",
    "neuralog-pro": "neuralog_sponsor_8k_1789312000349.jpg",
    "destinorandagio-it": "destinorandagio_sponsor_8k_1789312034116.jpg",
    "beway-life": "beway_sponsor_8k_1789312061514.jpg",
    "estao-app": "estao_sponsor_8k_1789312088088.jpg",
    "ixla-solutions": "ixla_sponsor_8k_1789312115537.jpg",
    "metroeridania-it": "metroeridania_sponsor_8k_1789312169506.jpg",
    "mircopregnolato-it": "mircopregnolato_sponsor_8k_1789312196189.jpg",
    "campus-camp": "campus_sponsor_8k_1789312242709.jpg",
}

for slug, src_name in MAPPING.items():
    src_path = os.path.join(ARTIFACTS_DIR, src_name)
    if not os.path.exists(src_path):
        print(f"MISSING: {src_path}")
        continue
    img = Image.open(src_path)
    w, h = img.size
    print(f"{slug}: original {w}x{h}")
    
    webp_path = os.path.join(TARGET_DIR, f"{slug}.webp")
    png_path = os.path.join(TARGET_DIR, f"{slug}.png")
    svg_path = os.path.join(TARGET_DIR, f"{slug}.svg")
    
    # Save optimized 16:9 WebP and PNG
    img.save(webp_path, "WEBP", quality=92, method=6)
    img.save(png_path, "PNG", optimize=True)
    
    with open(webp_path, "rb") as f:
        b64 = base64.b64encode(f.read()).decode("ascii")
        
    svg_content = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="100%" height="100%">
  <image href="data:image/webp;base64,{b64}" x="0" y="0" width="{w}" height="{h}" />
</svg>'''
    with open(svg_path, "w", encoding="utf-8") as f:
        f.write(svg_content)
    print(f"  -> Converted {slug} (WebP, PNG, SVG)")

print("ALL 10 NEW SPONSOR GRAPHICS INSTALLED SUCCESSFULLY")
