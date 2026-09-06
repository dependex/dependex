"""
content_factory.py — Motore Creativo ed Esecutivo per DEPENDEX.SOCIAL / OLTRE.SOCIAL
Generatore di Banner Grafici Brandizzati (Pillow) in Oro e Nero OLED,
Copywriting Neuro-Linguistico & Script di Dispatcher per Campagne Email.

Zero parole vietate. 100% Identità Sovrana: Al Club. Col Club.
"""

import os
import sys
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont
from config import BANNERS_DIR, BRAND_NAME, BRAND_PAYOFF

# Palette Ufficiale
COLOR_BG = (7, 7, 9)             # Nero OLED (#070709)
COLOR_CARD = (16, 17, 22)        # Antracite card (#101116)
COLOR_GOLD = (212, 175, 55)      # Oro Puro (#D4AF37)
COLOR_GOLD_LIGHT = (255, 242, 178) # Oro Chiaro (#FFF2B2)
COLOR_WHITE = (255, 255, 255)
COLOR_MUTED = (161, 161, 170)

def create_banner(title_text, subtitle_text, output_filename, size=(1200, 630)):
    """Genera un banner grafico ad alta definizione per email o social"""
    img = Image.new("RGB", size, color=COLOR_BG)
    draw = ImageDraw.Draw(img)

    # Hairline gold border
    draw.rectangle([(10, 10), (size[0]-10, size[1]-10)], outline=COLOR_GOLD, width=2)
    # Inner border
    draw.rectangle([(14, 14), (size[0]-14, size[1]-14)], outline=(50, 45, 20), width=1)

    # Decorazioni angolari in oro
    corner_len = 40
    for x, y in [(10, 10), (size[0]-10, 10), (10, size[1]-10), (size[0]-10, size[1]-10)]:
        x_sign = 1 if x == 10 else -1
        y_sign = 1 if y == 10 else -1
        draw.line([(x, y), (x + (corner_len * x_sign), y)], fill=COLOR_GOLD_LIGHT, width=3)
        draw.line([(x, y), (x, y + (corner_len * y_sign))], fill=COLOR_GOLD_LIGHT, width=3)

    # Testo Brand
    try:
        font_brand = ImageFont.truetype("arialbd.ttf", 36)
        font_title = ImageFont.truetype("arialbd.ttf", 52)
        font_sub = ImageFont.truetype("arial.ttf", 28)
    except Exception:
        font_brand = ImageFont.load_default()
        font_title = font_brand
        font_sub = font_brand

    # Header brand
    brand_str = f"{BRAND_NAME} · {BRAND_PAYOFF}"
    draw.text((size[0]//2, 80), brand_str, fill=COLOR_GOLD, font=font_brand, anchor="mm")

    # Titolo principale
    draw.text((size[0]//2, size[1]//2 - 30), title_text, fill=COLOR_WHITE, font=font_title, anchor="mm")

    # Sottotitolo
    draw.text((size[0]//2, size[1]//2 + 60), subtitle_text, fill=COLOR_MUTED, font=font_sub, anchor="mm")

    # Footer badge
    draw.text((size[0]//2, size[1] - 80), "542 CLUB NEL MONDO · 100% VOLONTARIATO · SOVRANITÀ PERSONALE", fill=COLOR_GOLD_LIGHT, font=font_sub, anchor="mm")

    out_path = BANNERS_DIR / output_filename
    img.save(out_path, "PNG", quality=95)
    print(f"[OK] Banner generato: {out_path}")
    return out_path

if __name__ == "__main__":
    create_banner(
        "Riconquista la Tua Lucidità Mentale",
        "Nessun giudizio clinico. C'è una sedia pronta per te nei 542 Club.",
        "banner_welcome_email.png"
    )
    create_banner(
        "Il Metodo Ecologico-Sociale Hudolin",
        "La favola del 'smetto quando voglio' finisce qui. Un giorno alla volta.",
        "banner_metodo_email.png"
    )
    create_banner(
        "Architettura di Valore Sovrano",
        "Starter Kit & Diagnosi: gli strumenti concreti per la trasformazione.",
        "banner_valore_email.png"
    )
