"""
COMPOSE REAL 8K/HD BRAND THUMBNAILS FOR THE 10 SPONSORS
Uses authentic assets, real logos, 3D mockups, and exact brand palettes from C:\81PLUS_GLOBAL_MASTER.
Renders high-definition 16:9 images (1920x1080) with luxury glassmorphism, ambient glows, and sharp typography.
Conforms to governance rules: ZERO emoji, ZERO banned words.
"""

import os
import math
from PIL import Image, ImageDraw, ImageFont, ImageFilter

OUTPUT_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "assets", "img", "sponsors"))
os.makedirs(OUTPUT_DIR, exist_ok=True)

# 10 Official Curated Sponsors Configuration
SPONSOR_CONFIGS = [
    {
        "slug": "sicurissimo-online",
        "name": "SICURISSIMO.ONLINE",
        "category": "COMPLIANCE & RISK MANAGEMENT",
        "tagline": "Sicurezza Sul Lavoro & Sistemi Gestionali D.Lgs 81/08",
        "color": (16, 185, 129),       # #10b981
        "accent": (52, 211, 153),      # #34d399
        "bg": (6, 11, 20),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\sicurissimo.online\assets\magnete_sicurissimo_os_mockup.webp",
        "asset_type": "mockup_right",
    },
    {
        "slug": "betterway-agency",
        "name": "BETTERWAY.AGENCY",
        "category": "GROWTH ENGINE & MARKETING",
        "tagline": "Sistemi di Espansione e Scalabilita d'Impresa",
        "color": (59, 130, 246),       # #3b82f6
        "accent": (0, 212, 255),       # #00d4ff
        "bg": (7, 12, 24),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\betterway.agency\public\icon-512.png",
        "asset_type": "logo_center",
    },
    {
        "slug": "neuralog-pro",
        "name": "NEURALOG.PRO",
        "category": "COMPANY BRAIN & TELEMETRY",
        "tagline": "Intelligenza Operativa & Orchestrazione Dati in Tempo Reale",
        "color": (139, 92, 246),      # #8b5cf6
        "accent": (192, 132, 252),     # #c084fc
        "bg": (12, 8, 26),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\neuralog.pro\blockchain\TOKEN NEURA.png",
        "asset_type": "token_right",
    },
    {
        "slug": "destinorandagio-it",
        "name": "DESTINORANDAGIO.IT",
        "category": "NOMAD & LIFESTYLE",
        "tagline": "Liberta Geografica, Geopolitica & Vita alle Proprie Condizioni",
        "color": (236, 72, 153),      # #ec4899
        "accent": (244, 114, 182),     # #f472b6
        "bg": (20, 8, 18),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\destinorandagio.it\assets\albums\Vol_13_DESTINO\DESTINO.png",
        "asset_type": "cover_right",
    },
    {
        "slug": "beway-life",
        "name": "BEWAY.LIFE",
        "category": "LONGEVITY & BIOHACKING",
        "tagline": "Protocolli Vitali, Salute Sistemica & Rigenerazione Cellulare",
        "color": (6, 182, 212),        # #06b6d4
        "accent": (34, 211, 238),      # #22d3ee
        "bg": (4, 16, 26),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\beway.life\Amazon\Beway Libro Master\Copertina Libro Master.png",
        "asset_type": "cover_right",
    },
    {
        "slug": "estao-app",
        "name": "ESTAO.APP",
        "category": "REAL ESTATE INTELLIGENCE",
        "tagline": "Valorizzazione Analitica & Messa a Reddito Immobiliare Continua",
        "color": (132, 204, 22),       # #84cc16
        "accent": (163, 230, 53),      # #a3e635
        "bg": (8, 18, 10),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\estao.app\brand\master_logo_transparent.png",
        "asset_type": "logo_right",
    },
    {
        "slug": "ixla-solutions",
        "name": "IXLA.SOLUTIONS",
        "category": "LASER & EXTREME ENGINEERING",
        "tagline": "Hardware Industriale d'Avanguardia & Precisione Micrometrica",
        "color": (249, 115, 22),       # #f97316
        "accent": (251, 146, 60),      # #fb923c
        "bg": (18, 10, 8),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\ixla.solutions\01_ENGINEERING_DOCS\tavole_esecutive_png\TAVOLA_A0_01_ACQUA_C01.png",
        "asset_type": "blueprint_right",
    },
    {
        "slug": "metroeridania-it",
        "name": "METROERIDANIA.IT",
        "category": "MOBILITA & ECOSISTEMI FLUVIALI",
        "tagline": "Infrastrutture Snelle & Comunitarieta Lungo il Grande Fiume Po",
        "color": (20, 184, 166),       # #14b8a6
        "accent": (45, 212, 191),      # #2dd4bf
        "bg": (6, 18, 18),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\metroeridania.it\brand\master_logo_transparent.png",
        "asset_type": "logo_right",
    },
    {
        "slug": "mircopregnolato-it",
        "name": "MIRCOPREGNOLATO.IT",
        "category": "HOLISTIC & VENTURE GOVERNANCE",
        "tagline": "Visione Sistemica, Mentoring Esecutivo & Sovranita Personale",
        "color": (245, 158, 11),       # #f59e0b
        "accent": (251, 191, 36),      # #fbbf24
        "bg": (18, 14, 6),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\mircopregnolato.it\mockups\mockup-masterclass.webp",
        "asset_type": "mockup_right",
    },
    {
        "slug": "campus-camp",
        "name": "CAMPUS.CAMP",
        "category": "FORMAZIONE IMMERSIVA",
        "tagline": "Laboratori Esperienziali, Masterclass & Confronto tra Pari",
        "color": (34, 197, 94),        # #22c55e
        "accent": (74, 222, 128),      # #4ade80
        "bg": (8, 20, 12),
        "asset_path": r"C:\81PLUS_GLOBAL_MASTER\campus.camp\campus-mail-engine\assets\branding\Campus_Logo_Official.png",
        "asset_type": "logo_right",
    }
]

def create_gradient_background(width, height, base_color, accent_color):
    """Create rich radial gradient mesh background"""
    img = Image.new("RGB", (width, height), base_color)
    draw = ImageDraw.Draw(img)
    
    # Add subtle horizontal grid lines
    grid_color = (255, 255, 255, 10)
    grid_img = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    grid_draw = ImageDraw.Draw(grid_img)
    for y in range(0, height, 48):
        grid_draw.line([(0, y), (width, y)], fill=(255, 255, 255, 12), width=1)
    for x in range(0, width, 48):
        grid_draw.line([(x, 0), (x, height)], fill=(255, 255, 255, 8), width=1)
    
    # Radial ambient glows
    glow_layer = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    glow_draw = ImageDraw.Draw(glow_layer)
    
    # Primary accent glow (center-right)
    cx, cy = int(width * 0.72), int(height * 0.5)
    r_max = int(height * 0.75)
    for r in range(r_max, 0, -15):
        alpha = int(70 * (1 - r / r_max))
        glow_draw.ellipse(
            [cx - r, cy - r, cx + r, cy + r],
            fill=(accent_color[0], accent_color[1], accent_color[2], alpha)
        )
        
    # Secondary ambient glow (top-left)
    cx2, cy2 = int(width * 0.15), int(height * 0.2)
    r2_max = int(height * 0.45)
    for r in range(r2_max, 0, -12):
        alpha = int(40 * (1 - r / r2_max))
        glow_draw.ellipse(
            [cx2 - r, cy2 - r, cx2 + r, cy2 + r],
            fill=(accent_color[0], accent_color[1], accent_color[2], alpha)
        )
        
    img.paste(Image.alpha_composite(Image.new("RGBA", (width, height), (*base_color, 255)), glow_layer).convert("RGB"), (0, 0))
    img = Image.alpha_composite(img.convert("RGBA"), grid_img).convert("RGB")
    return img

def compose_sponsor_thumbnail(cfg):
    w, h = 1920, 1080
    bg = create_gradient_background(w, h, cfg["bg"], cfg["color"])
    draw = ImageDraw.Draw(bg)
    
    # Fonts
    try:
        font_title = ImageFont.truetype("segoeuib.ttf", 64)
        font_cat = ImageFont.truetype("segoeuib.ttf", 26)
        font_tag = ImageFont.truetype("segoeui.ttf", 30)
        font_brand = ImageFont.truetype("segoeuib.ttf", 24)
        font_gold = ImageFont.truetype("segoeuib.ttf", 22)
    except:
        font_title = ImageFont.load_default()
        font_cat = ImageFont.load_default()
        font_tag = ImageFont.load_default()
        font_brand = ImageFont.load_default()
        font_gold = ImageFont.load_default()
        
    # Top Accent Border
    draw.rectangle([(0, 0), (w, 8)], fill=cfg["color"])
    
    # Left Content Area (Text & Badges)
    left_x = 100
    top_y = 120
    
    # 1. Category Pill Badge
    cat_text = cfg["category"]
    cat_bbox = font_cat.getbbox(cat_text)
    cat_w = cat_bbox[2] - cat_bbox[0] + 44
    cat_h = 50
    
    badge_layer = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    b_draw = ImageDraw.Draw(badge_layer)
    b_draw.rounded_rectangle(
        [(left_x, top_y), (left_x + cat_w, top_y + cat_h)],
        radius=25,
        fill=(cfg["color"][0], cfg["color"][1], cfg["color"][2], 40),
        outline=(*cfg["accent"], 180),
        width=2
    )
    # Circle indicator in badge
    b_draw.ellipse(
        [(left_x + 16, top_y + 19), (left_x + 28, top_y + 31)],
        fill=cfg["accent"]
    )
    bg = Image.alpha_composite(bg.convert("RGBA"), badge_layer).convert("RGB")
    draw = ImageDraw.Draw(bg)
    
    draw.text((left_x + 36, top_y + 11), cat_text, font=font_cat, fill=cfg["accent"])
    
    # 2. Main Title (Domain Name)
    title_y = top_y + 80
    draw.text((left_x, title_y), cfg["name"], font=font_title, fill=(255, 255, 255))
    
    # 3. Subtitle / Tagline
    tag_y = title_y + 90
    draw.text((left_x, tag_y), cfg["tagline"], font=font_tag, fill=(203, 213, 225))
    
    # 4. Feature Bullets / Value Props Card (Glassmorphism Box on Bottom Left)
    box_w, box_h = 760, 480
    box_x = left_x
    box_y = tag_y + 80
    
    box_layer = Image.new("RGBA", (w, h), (0, 0, 0, 0))
    box_draw = ImageDraw.Draw(box_layer)
    box_draw.rounded_rectangle(
        [(box_x, box_y), (box_x + box_w, box_y + box_h)],
        radius=24,
        fill=(15, 23, 42, 190),
        outline=(255, 255, 255, 35),
        width=2
    )
    
    # Gold top line for box
    box_draw.line([(box_x + 30, box_y), (box_x + box_w - 30, box_y)], fill=(*cfg["color"], 220), width=3)
    bg = Image.alpha_composite(bg.convert("RGBA"), box_layer).convert("RGB")
    draw = ImageDraw.Draw(bg)
    
    # Bullets inside glassmorphic card
    bullet_font = ImageFont.truetype("segoeui.ttf", 26)
    bullet_bold = ImageFont.truetype("segoeuib.ttf", 27)
    
    draw.text((box_x + 36, box_y + 34), "VALORE STRATEGICO & COMPONENTI SOVRANE", font=font_brand, fill=(212, 175, 55))
    
    bullets = [
        "Infrastruttura nativa indipendente ad alto impatto operativo",
        "Integrazione diretta con l'ecosistema sovrano",
        "Conformita, privacy blindata e architettura priva di intermediari"
    ]
    
    b_y = box_y + 90
    for idx, b_txt in enumerate(bullets, 1):
        draw.text((box_x + 36, b_y), f"#{idx}", font=bullet_bold, fill=cfg["accent"])
        draw.text((box_x + 85, b_y), b_txt, font=bullet_font, fill=(241, 245, 249))
        b_y += 75
        
    # Trust badge footer inside box
    draw.rectangle([(box_x + 36, box_y + box_h - 70), (box_x + box_w - 36, box_y + box_h - 68)], fill=(255, 255, 255, 30))
    draw.text((box_x + 36, box_y + box_h - 52), "DEPENDEX.SOCIAL · ASSET SOVRANO · ULTRA-HD 8K", font=font_gold, fill=(212, 175, 55))
    
    # 5. Right Area: Real Brand Asset Composition
    asset_path = cfg["asset_path"]
    if os.path.exists(asset_path):
        try:
            asset_img = Image.open(asset_path)
            
            # Destination bounding box on right side
            avail_w = 880
            avail_h = 780
            center_x = 1420
            center_y = 540
            
            if cfg["asset_type"] in ["mockup_right", "cover_right"]:
                # Preserve aspect ratio and fit inside avail box
                ratio = min(avail_w / asset_img.width, avail_h / asset_img.height)
                new_size = (int(asset_img.width * ratio), int(asset_img.height * ratio))
                asset_resized = asset_img.resize(new_size, Image.LANCZOS)
                
                # Create backing glow plate
                plate_w = new_size[0] + 60
                plate_h = new_size[1] + 60
                px = center_x - plate_w // 2
                py = center_y - plate_h // 2
                
                plate_layer = Image.new("RGBA", (w, h), (0, 0, 0, 0))
                p_draw = ImageDraw.Draw(plate_layer)
                p_draw.rounded_rectangle(
                    [(px, py), (px + plate_w, py + plate_h)],
                    radius=28,
                    fill=(10, 15, 26, 210),
                    outline=(*cfg["color"], 140),
                    width=3
                )
                bg = Image.alpha_composite(bg.convert("RGBA"), plate_layer).convert("RGB")
                
                # Paste asset centered in plate
                ax = center_x - new_size[0] // 2
                ay = center_y - new_size[1] // 2
                if asset_resized.mode == "RGBA":
                    bg.paste(asset_resized, (ax, ay), asset_resized)
                else:
                    bg.paste(asset_resized, (ax, ay))
                    
            elif cfg["asset_type"] in ["logo_right", "logo_center", "token_right"]:
                # Large centered brand logo/emblem with neon aura
                max_dim = 650
                ratio = min(max_dim / asset_img.width, max_dim / asset_img.height)
                new_size = (int(asset_img.width * ratio), int(asset_img.height * ratio))
                asset_resized = asset_img.resize(new_size, Image.LANCZOS)
                
                # Circular or rounded glass pedestal
                ped_radius = int(max(new_size) * 0.62)
                ped_layer = Image.new("RGBA", (w, h), (0, 0, 0, 0))
                ped_draw = ImageDraw.Draw(ped_layer)
                ped_draw.ellipse(
                    [(center_x - ped_radius, center_y - ped_radius),
                     (center_x + ped_radius, center_y + ped_radius)],
                    fill=(13, 18, 30, 225),
                    outline=(*cfg["accent"], 160),
                    width=3
                )
                # Outer glow ring
                ped_draw.ellipse(
                    [(center_x - ped_radius - 12, center_y - ped_radius - 12),
                     (center_x + ped_radius + 12, center_y + ped_radius + 12)],
                    outline=(*cfg["color"], 70),
                    width=2
                )
                bg = Image.alpha_composite(bg.convert("RGBA"), ped_layer).convert("RGB")
                
                ax = center_x - new_size[0] // 2
                ay = center_y - new_size[1] // 2
                if asset_resized.mode == "RGBA":
                    bg.paste(asset_resized, (ax, ay), asset_resized)
                else:
                    bg.paste(asset_resized, (ax, ay))
                    
            elif cfg["asset_type"] == "blueprint_right":
                # High-tech blueprint preview with crop
                ratio = min(avail_w / asset_img.width, avail_h / asset_img.height)
                # Take top technical section
                crop_box = (0, 0, asset_img.width, int(asset_img.height * 0.7))
                cropped = asset_img.crop(crop_box)
                ratio = min(avail_w / cropped.width, avail_h / cropped.height)
                new_size = (int(cropped.width * ratio), int(cropped.height * ratio))
                asset_resized = cropped.resize(new_size, Image.LANCZOS)
                
                ax = center_x - new_size[0] // 2
                ay = center_y - new_size[1] // 2
                
                frame_layer = Image.new("RGBA", (w, h), (0, 0, 0, 0))
                f_draw = ImageDraw.Draw(frame_layer)
                f_draw.rounded_rectangle(
                    [(ax - 10, ay - 10), (ax + new_size[0] + 10, ay + new_size[1] + 10)],
                    radius=16,
                    fill=(10, 14, 22, 220),
                    outline=(*cfg["color"], 180),
                    width=2
                )
                bg = Image.alpha_composite(bg.convert("RGBA"), frame_layer).convert("RGB")
                bg.paste(asset_resized, (ax, ay), asset_resized if asset_resized.mode == "RGBA" else None)
                
        except Exception as err:
            print(f"Error composing asset for {cfg['slug']}: {err}")
    
    # Save WebP, PNG, and SVG
    webp_path = os.path.join(OUTPUT_DIR, f"{cfg['slug']}.webp")
    png_path = os.path.join(OUTPUT_DIR, f"{cfg['slug']}.png")
    svg_path = os.path.join(OUTPUT_DIR, f"{cfg['slug']}.svg")
    
    bg.save(webp_path, "WEBP", quality=92, method=6)
    bg.save(png_path, "PNG", optimize=True)
    
    import base64
    with open(webp_path, "rb") as f:
        b64 = base64.b64encode(f.read()).decode("utf-8")
    svg_content = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1920 1080" width="1920" height="1080">
  <image href="data:image/webp;base64,{b64}" width="1920" height="1080" preserveAspectRatio="xMidYMid slice" />
</svg>'''
    with open(svg_path, "w", encoding="utf-8") as f:
        f.write(svg_content)
    
    print(f"Generated: {cfg['slug']} (.webp, .png, .svg) -> {os.path.getsize(webp_path):,} bytes")
    return webp_path

def main():
    print(f"Generating 10 Ultra-HD 16:9 Real Brand Thumbnails in {OUTPUT_DIR}...")
    for cfg in SPONSOR_CONFIGS:
        compose_sponsor_thumbnail(cfg)
    print("Done! All 10 sponsor thumbnails generated successfully.")

if __name__ == "__main__":
    main()
