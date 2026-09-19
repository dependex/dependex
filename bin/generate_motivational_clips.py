# -*- coding: utf-8 -*-
"""
Generatore Robusto di Video Clip Motivazionali 9:16 (1080x1920) per DEPENDEX.SOCIAL.
Crea sottotitoli ASS formattati con stili cinematografici (fade, ombreggiatura, font pulito)
e assembla con ffmpeg video MP4 H.264 / AAC ad altissima compatibilità.
"""

import os
import shutil
import subprocess

BRAIN_DIR = r"C:\Users\piano\.gemini\antigravity-ide\brain\53ccf53f-12e8-4ec0-bbb2-0b3e2e67f50a"
REPO_DIR = r"c:\81PLUS_GLOBAL_MASTER\dependex.social"
CLIPS_DIR = os.path.join(REPO_DIR, "assets", "clips")

os.makedirs(CLIPS_DIR, exist_ok=True)

# 5 Clip Specifiche con timing in secondi
CLIPS = [
    {
        "id": "clip1_non_devi_sapere_tutto",
        "title": "Non devi avere già le risposte",
        "duration": 10,
        "bg_image": "clip1_bg.jpg",
        "freq": 432,
        "subtitles": [
            {"start": "00:00:00.50", "end": "00:00:03.50", "text": "Non devi sapere gia' tutto."},
            {"start": "00:00:03.80", "end": "00:00:06.50", "text": "Non devi avere gia' le risposte."},
            {"start": "00:00:06.80", "end": "00:00:09.70", "text": "Fai solo il primo passo.\\NIl cerchio e' qui per te.\\N\\N{\\fs36\\c&H00F0FF&}dependex.social"}
        ]
    },
    {
        "id": "clip2_nessuno_e_solo",
        "title": "Nessuno si salva da solo",
        "duration": 8,
        "bg_image": "clip2_bg.jpg",
        "freq": 396,
        "subtitles": [
            {"start": "00:00:00.50", "end": "00:00:02.70", "text": "La solitudine e' un'illusione."},
            {"start": "00:00:03.00", "end": "00:00:05.20", "text": "Nel cerchio non ci sono etichette."},
            {"start": "00:00:05.40", "end": "00:00:07.70", "text": "Solo persone che camminano insieme.\\N\\N{\\fs36\\c&H00F0FF&}Metodo Hudolin · Rete Club CAT"}
        ]
    },
    {
        "id": "clip3_fermati_e_respira",
        "title": "Fermati e Respira",
        "duration": 10,
        "bg_image": "clip3_bg.jpg",
        "freq": 528,
        "subtitles": [
            {"start": "00:00:00.50", "end": "00:00:03.20", "text": "Ferma tutto per un minuto."},
            {"start": "00:00:03.50", "end": "00:00:06.50", "text": "Espira l'ansia...\\NInspira la calma."},
            {"start": "00:00:06.80", "end": "00:00:09.70", "text": "Sei qui. Sei al sicuro.\\NRiparti dal tuo respiro.\\N\\N{\\fs36\\c&H00F0FF&}Consapevolezza Somatica · Metodo H+"}
        ]
    },
    {
        "id": "clip4_navigare_le_onde",
        "title": "Navigare le Onde della Vita",
        "duration": 9,
        "bg_image": "clip4_bg.jpg",
        "freq": 432,
        "subtitles": [
            {"start": "00:00:00.50", "end": "00:00:02.80", "text": "Non puoi fermare le onde."},
            {"start": "00:00:03.10", "end": "00:00:05.80", "text": "Ma puoi imparare a navigarle."},
            {"start": "00:00:06.10", "end": "00:00:08.70", "text": "Osserva. Scegli. Rinasci.\\N\\N{\\fs36\\c&H00F0FF&}Resilienza & Vita · BetterWay"}
        ]
    },
    {
        "id": "clip5_rinascita_quotidiana",
        "title": "Un giorno alla volta",
        "duration": 8,
        "bg_image": "clip5_bg.jpg",
        "freq": 432,
        "subtitles": [
            {"start": "00:00:00.50", "end": "00:00:02.60", "text": "Un giorno alla volta."},
            {"start": "00:00:02.90", "end": "00:00:05.10", "text": "La tua storia ha un valore immenso."},
            {"start": "00:00:05.30", "end": "00:00:07.70", "text": "Oltre ogni caduta c'e' la tua liberta'.\\N\\N{\\fs36\\c&H00F0FF&}dependex.social"}
        ]
    }
]

def create_ass_subtitles(clip, ass_path):
    """Genera file ASS con stili cinematografici centrate"""
    header = """[Script Info]
ScriptType: v4.00+
PlayResX: 1080
PlayResY: 1920
ScaledBorderAndShadow: yes

[V4+ Styles]
Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
Style: Default,Arial,58,&H00FFFFFF,&H000000FF,&H00000000,&HA0000000,-1,0,0,0,100,100,1,0,1,4,5,5,80,80,0,1

[Events]
Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text
"""
    events = []
    for sub in clip["subtitles"]:
        st = sub["start"]
        en = sub["end"]
        # Fade in 300ms, fade out 300ms
        txt = "{\\fad(350,350)}" + sub["text"]
        events.append(f"Dialogue: 0,{st},{en},Default,,0,0,0,,{txt}")
    
    with open(ass_path, "w", encoding="utf-8") as f:
        f.write(header + "\n".join(events) + "\n")

def render_clip(clip):
    clip_id = clip["id"]
    bg_file = os.path.join(CLIPS_DIR, clip["bg_image"])
    ass_file = os.path.join(CLIPS_DIR, f"{clip_id}.ass")
    output_mp4 = os.path.join(CLIPS_DIR, f"{clip_id}.mp4")
    dur = clip["duration"]
    freq = clip["freq"]
    fps = 30
    total_frames = dur * fps

    create_ass_subtitles(clip, ass_file)

    # Path escaping for ffmpeg filter
    ass_escaped = ass_file.replace("\\", "/").replace(":", "\\:")

    # Video filter: Scale, Ken burns zoompan, Box scuro al centro, Subtitles ASS
    vf = (
        f"scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,"
        f"zoompan=z='min(zoom+0.0003,1.05)':d={total_frames}:x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':s=1080x1920:fps={fps},"
        f"drawbox=y=ih*0.35:color=black@0.45:width=iw:height=ih*0.32:t=fill,"
        f"ass='{ass_escaped}'"
    )

    # Genera audio armonico
    cmd = [
        "ffmpeg", "-y",
        "-loop", "1",
        "-i", bg_file,
        "-f", "lavfi",
        "-i", f"sine=frequency={freq}:duration={dur}",
        "-vf", vf,
        "-af", f"volume=0.15,afade=t=in:ss=0:d=1.5,afade=t=out:st={dur-1.5}:d=1.5",
        "-c:v", "libx264",
        "-preset", "medium",
        "-crf", "20",
        "-pix_fmt", "yuv420p",
        "-c:a", "aac",
        "-b:a", "192k",
        "-t", str(dur),
        output_mp4
    ]

    print(f"\n[RENDER] Generazione {clip_id}.mp4 ({dur}s)...")
    res = subprocess.run(cmd, capture_output=True, text=True)
    if res.returncode == 0:
        size_mb = os.path.getsize(output_mp4) / (1024 * 1024)
        print(f"  --> [OK] Creato con successo! Dimensione: {size_mb:.2f} MB")
    else:
        print(f"  --> [FAIL] Errore: {res.stderr[-300:]}")

def main():
    print("=== GENERATORE CLIPS VIDEO MOTIVAZIONALI DEPENDEX.SOCIAL ===")
    for c in CLIPS:
        render_clip(c)
    print("\nProcesso completato.")

if __name__ == "__main__":
    main()
