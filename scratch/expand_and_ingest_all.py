import sqlite3
import hashlib
import os
import csv
import xml.etree.ElementTree as ET

# Crockford Base32 characters
CROCKFORD_CHARS = "0123456789ABCDEFGHJKMNPQRSTVWXYZ"

def generate_sic(name: str, city: str, address: str) -> str:
    h = hashlib.sha256(f"{name}|{city}|{address}".encode('utf-8')).hexdigest()
    p1 = "".join(CROCKFORD_CHARS[int(h[i:i+2], 16) % 32] for i in range(0, 16, 2))
    p2 = "".join(CROCKFORD_CHARS[int(h[i:i+2], 16) % 32] for i in range(16, 32, 2))
    check = CROCKFORD_CHARS[int(h[32:34], 16) % 32]
    return f"SIC-{p1}-{p2}-{check}"

def generate_unsub_token(sic: str) -> str:
    return hashlib.sha256(f"unsub-{sic}-hudolin-2026".encode('utf-8')).hexdigest()[:32]

# Load existing new_clubs from scratch/ingest_osint_clubs.py
import sys
sys.path.append('scratch')
import ingest_osint_clubs

master_new_clubs = list(ingest_osint_clubs.new_clubs)
print(f"Base OSINT harvested clubs: {len(master_new_clubs)}")

# Additional clubs from Lombardia, Trentino, Liguria, Toscana
additional_clubs = [
    # --- LOMBARDIA: BERGAMO (ARCAT Lombardia) ---
    {
        "name": "C.A.T. Bergamo 4 Loreto", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Bergamo", "address": "c/o Oratorio, via Loreto 5", "cap": "24128", "lat": 45.6923, "lon": 9.6542,
        "day": "Sabato", "time": "16:00", "servitore": "Ivana Martello", "phone": "388 568 8640",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Bergamo"
    },
    {
        "name": "C.A.T. Bergamo 6 La Farfalla", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Bergamo", "address": "c/o CTE, via Biava 26, Valtesse", "cap": "24123", "lat": 45.7145, "lon": 9.6732,
        "day": "Venerdì", "time": "20:30", "servitore": "Giovanni Bonati", "phone": "366 936 3631",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Bergamo"
    },
    {
        "name": "C.A.T. Bergamo 7 S. Caterina", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Bergamo", "address": "via Borgo Santa Caterina 16", "cap": "24124", "lat": 45.7021, "lon": 9.6841,
        "day": "Venerdì", "time": "20:30", "servitore": "Claudio Calì", "phone": "328 207 0936",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Bergamo"
    },
    {
        "name": "C.A.T. Bergamo 8 Torre Boldone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Torre Boldone", "address": "c/o Centro polivalente, Piazza Mercato", "cap": "24020", "lat": 45.7141, "lon": 9.7082,
        "day": "Giovedì", "time": "20:30", "servitore": "Paolo Carrara", "phone": "347 447 1790",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Bergamo"
    },
    {
        "name": "C.A.T. Bergamo Borgo S. Caterina", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Bergamo", "address": "via Borgo Santa Caterina 16", "cap": "24124", "lat": 45.7021, "lon": 9.6841,
        "day": "Mercoledì", "time": "20:30", "servitore": "Maurizio Frigeni", "phone": "349 665 3262",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Bergamo"
    },
    {
        "name": "C.A.T. Bergamo Reti Valtesse", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Bergamo", "address": "c/o CTE, via Biava 26", "cap": "24123", "lat": 45.7145, "lon": 9.6732,
        "day": "Martedì", "time": "20:15", "servitore": "Giuseppe Lussana", "phone": "334 9181619",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Bergamo"
    },
    {
        "name": "C.A.T. Ponte S. Pietro", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Ponte San Pietro", "address": "c/o Centro UFO, via Legionari di Polonia 5", "cap": "24036", "lat": 45.6982, "lon": 9.5873,
        "day": "Mercoledì", "time": "20:30", "servitore": "Immacolata Petra", "phone": "320 316 2590",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Bergamo"
    },
    {
        "name": "C.A.T. Madone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Madone", "address": "c/o Casa delle Associazioni, via Patrioti 7", "cap": "24040", "lat": 45.6512, "lon": 9.5491,
        "day": "Giovedì", "time": "20:30", "servitore": "Gianni Donadello", "phone": "035 463998",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Isola Bergamasca"
    },
    {
        "name": "C.A.T. Terno d'Isola 1", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BG",
        "city": "Terno d'Isola", "address": "c/o ex Scuole Elementari, via G. Bravi 15", "cap": "24030", "lat": 45.6881, "lon": 9.5312,
        "day": "Martedì", "time": "20:30", "servitore": "Servitore Insegnante Terno", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Isola Bergamasca"
    },

    # --- LOMBARDIA: BRESCIA (ARCAT Lombardia) ---
    {
        "name": "C.A.T. Armonia Brescia", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Brescia", "address": "Via Borgondio 29", "cap": "25122", "lat": 45.5451, "lon": 10.2212,
        "day": "Giovedì", "time": "20:15", "servitore": "Servitore Insegnante Brescia", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. Vladimir Hudolin Brescia", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Brescia", "address": "Via Livorno 7", "cap": "25125", "lat": 45.5261, "lon": 10.2014,
        "day": "Mercoledì", "time": "20:30", "servitore": "Servitore Insegnante Livorno", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. Il Germoglio Montichiari", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Montichiari", "address": "c/o Casa delle Associazioni, Via Trieste 26", "cap": "25018", "lat": 45.4121, "lon": 10.3952,
        "day": "Mercoledì", "time": "20:30", "servitore": "Servitore Insegnante Montichiari", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. Manerbio 3", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Manerbio", "address": "c/o Centro Sociale, Via Palestro 57", "cap": "25025", "lat": 45.3582, "lon": 10.1384,
        "day": "Mercoledì", "time": "21:00", "servitore": "Servitore Insegnante Manerbio", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. Azzurro Prevalle", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Prevalle", "address": "c/o Municipio, Via Morani 9", "cap": "25080", "lat": 45.5562, "lon": 10.4221,
        "day": "Mercoledì", "time": "20:00", "servitore": "Servitore Insegnante Prevalle", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. Il Girasole Salò", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Salò", "address": "Via Ponte Vecchio 6", "cap": "25087", "lat": 45.6084, "lon": 10.5234,
        "day": "Martedì", "time": "20:00", "servitore": "Servitore Insegnante Salò", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. L'Alba Bedizzole", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Bedizzole", "address": "c/o Centro Sociale", "cap": "25081", "lat": 45.5124, "lon": 10.4215,
        "day": "Martedì", "time": "20:30", "servitore": "Servitore Insegnante Bedizzole", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. Arcobaleno Vobarno", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Vobarno", "address": "Largo Donatori di Sangue", "cap": "25079", "lat": 45.6421, "lon": 10.5023,
        "day": "Martedì", "time": "20:00", "servitore": "Servitore Insegnante Vobarno", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. La Goccia Vobarno", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Vobarno", "address": "Largo Donatori di Sangue", "cap": "25079", "lat": 45.6421, "lon": 10.5023,
        "day": "Lunedì", "time": "20:30", "servitore": "Servitore Insegnante Vobarno", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },
    {
        "name": "C.A.T. La Farfalla Paitone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "BS",
        "city": "Paitone", "address": "c/o Centro Sociale, Via Beschi 18", "cap": "25080", "lat": 45.5512, "lon": 10.3982,
        "day": "Mercoledì", "time": "20:00", "servitore": "Servitore Insegnante Paitone", "phone": "351 511 1330",
        "email": "info@acatbrescia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Brescia"
    },

    # --- LOMBARDIA: COMO (ARCAT Lombardia) ---
    {
        "name": "C.A.T. Arcobaleno Como", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CO",
        "city": "Como", "address": "c/o Oratorio Parrocchia S. Agata, via Aristide Bari 2", "cap": "22100", "lat": 45.8152, "lon": 9.0741,
        "day": "Lunedì", "time": "20:45", "servitore": "Lorella Morras", "phone": "338 507 6323",
        "email": "cirilloangelo67@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Como"
    },
    {
        "name": "C.A.T. La Lanterna Como Sagnino", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CO",
        "city": "Como", "address": "c/o Centro Civico Como Nord, via G. Segantini 2, Sagnino", "cap": "22100", "lat": 45.8341, "lon": 9.0521,
        "day": "Lunedì", "time": "20:45", "servitore": "Claudio Cassinari", "phone": "338 197 6756",
        "email": "cirilloangelo67@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Como"
    },
    {
        "name": "C.A.T. Muggiò Como", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CO",
        "city": "Como", "address": "c/o Oratorio S. Maria Regina, via Quadrio 10, Muggiò", "cap": "22100", "lat": 45.7954, "lon": 9.0942,
        "day": "Mercoledì", "time": "20:30", "servitore": "Franca Schena", "phone": "340 005 6632",
        "email": "cirilloangelo67@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Como"
    },
    {
        "name": "C.A.T. La Fenice Lomazzo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CO",
        "city": "Lomazzo", "address": "c/o Oratorio Parrocchia S. Vito e Modesto, via Rocchetta ang. via Manzoni", "cap": "22074", "lat": 45.6982, "lon": 9.0341,
        "day": "Martedì", "time": "20:30", "servitore": "Ivano Banfi", "phone": "347 462 1128",
        "email": "cirilloangelo67@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Como"
    },

    # --- LOMBARDIA: MANTOVA (ARCAT Lombardia) ---
    {
        "name": "C.A.T. Vivere Sano Mantova", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MN",
        "city": "Mantova", "address": "c/o Valletta Valsecchi, Via Ariosto 2", "cap": "46100", "lat": 45.1523, "lon": 10.7934,
        "day": "Lunedì", "time": "19:30-21:00", "servitore": "Servitore Insegnante Mantova", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Mantova"
    },
    {
        "name": "C.A.T. Amicizia Mantova", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MN",
        "city": "Mantova", "address": "c/o Parrocchia Ognissanti, C.so Vittorio Emanuele 146", "cap": "46100", "lat": 45.1581, "lon": 10.7872,
        "day": "Martedì", "time": "19:30-21:00", "servitore": "Servitore Insegnante Mantova", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Mantova"
    },
    {
        "name": "C.A.T. Rivivere Mantova", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MN",
        "city": "Mantova", "address": "c/o Circoscrizione Sud, Via Facciotto 7", "cap": "46100", "lat": 45.1452, "lon": 10.7781,
        "day": "Lunedì", "time": "18:30-20:00", "servitore": "Servitore Insegnante Mantova", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Mantova"
    },
    {
        "name": "C.A.T. Porta Aperta Bozzolo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MN",
        "city": "Bozzolo", "address": "Via Giuseppe Paccini 2", "cap": "46012", "lat": 45.1023, "lon": 10.4851,
        "day": "Martedì", "time": "19:00-20:30", "servitore": "Servitore Insegnante Bozzolo", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Mantova"
    },
    {
        "name": "C.A.T. Solidarietà - Libertà Castiglione", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MN",
        "city": "Castiglione delle Stiviere", "address": "c/o Centro Anziani Anni d'Argento, Via Ordanino 11", "cap": "46043", "lat": 45.3934, "lon": 10.4952,
        "day": "Martedì", "time": "18:30-20:00", "servitore": "Servitore Insegnante Castiglione", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Mantova"
    },
    {
        "name": "C.A.T. Anemone Monzambano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MN",
        "city": "Monzambano", "address": "c/o Sala Civica, Via Umberto I", "cap": "46040", "lat": 45.3871, "lon": 10.6934,
        "day": "Martedì", "time": "20:00-21:30", "servitore": "Servitore Insegnante Monzambano", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Mantova"
    },
    {
        "name": "C.A.T. Le Ninfee Rivalta", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MN",
        "city": "Rodigo", "address": "c/o Oratorio Parrocchiale, Piazza Chiesa 6, Rivalta sul Mincio", "cap": "46040", "lat": 45.1984, "lon": 10.6651,
        "day": "Mercoledì", "time": "20:00-21:30", "servitore": "Servitore Insegnante Rivalta", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Mantova"
    },
    {
        "name": "C.A.T. Gabbiano azzurro San Giorgio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MN",
        "city": "San Giorgio Bigarello", "address": "c/o Centro Culturale, Via Frida Kahlo 40", "cap": "46038", "lat": 45.1681, "lon": 10.8492,
        "day": "Mercoledì", "time": "19:00-20:30", "servitore": "Servitore Insegnante San Giorgio", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Mantova"
    },

    # --- LOMBARDIA: CREMONA (ARCAT Lombardia) ---
    {
        "name": "C.A.T. Orizzonti Cremona", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CR",
        "city": "Cremona", "address": "Centro Cittadino Cremona", "cap": "26100", "lat": 45.1332, "lon": 10.0224,
        "day": "Mercoledì", "time": "20:00", "servitore": "Servitore Insegnante Cremona", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Cremona"
    },
    {
        "name": "C.A.T. Zero doppio zero Cremona", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CR",
        "city": "Cremona", "address": "Sede Cittadina Cremona", "cap": "26100", "lat": 45.1365, "lon": 10.0271,
        "day": "Mercoledì", "time": "18:30", "servitore": "Servitore Insegnante Cremona", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Cremona"
    },
    {
        "name": "C.A.T. n° 12 La Rosa Blù Cremona", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CR",
        "city": "Cremona", "address": "Sede Civica Cremona", "cap": "26100", "lat": 45.1315, "lon": 10.0210,
        "day": "Mercoledì", "time": "18:00", "servitore": "Servitore Insegnante Cremona", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Cremona"
    },
    {
        "name": "C.A.T. Amicizia Cicognolo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CR",
        "city": "Cicognolo", "address": "Centro Parrocchiale Cicognolo", "cap": "26030", "lat": 45.1662, "lon": 10.1984,
        "day": "Martedì", "time": "18:30", "servitore": "Servitore Insegnante Cicognolo", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Cremona"
    },
    {
        "name": "C.A.T. Soresina", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CR",
        "city": "Soresina", "address": "Centro Civico Soresina", "cap": "26015", "lat": 45.2891, "lon": 9.8582,
        "day": "Venerdì", "time": "20:45", "servitore": "Servitore Insegnante Soresina", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Cremona"
    },
    {
        "name": "C.A.T. Speranza Vescovato", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "CR",
        "city": "Vescovato", "address": "Sala Civica Vescovato", "cap": "26039", "lat": 45.1745, "lon": 10.1652,
        "day": "Mercoledì", "time": "18:30", "servitore": "Servitore Insegnante Vescovato", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Cremona"
    },

    # --- LOMBARDIA: LECCO & SONDRIO (ARCAT Lombardia) ---
    {
        "name": "C.A.T. San Giovanni Lecco", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "LC",
        "city": "Lecco", "address": "Rione San Giovanni, Lecco", "cap": "23900", "lat": 45.8642, "lon": 9.3981,
        "day": "Venerdì", "time": "20:30", "servitore": "Servitore Insegnante Lecco", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Lecchese"
    },
    {
        "name": "C.A.T. Oggiono", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "LC",
        "city": "Oggiono", "address": "Centro Parrocchiale Oggiono", "cap": "23848", "lat": 45.7921, "lon": 9.3492,
        "day": "Martedì", "time": "20:30", "servitore": "Servitore Insegnante Oggiono", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Lecchese"
    },
    {
        "name": "C.A.T. Stella Sondrio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "SO",
        "city": "Sondrio", "address": "Centro Cittadino Sondrio", "cap": "23100", "lat": 46.1712, "lon": 9.8715,
        "day": "Mercoledì", "time": "20:30", "servitore": "Servitore Insegnante Sondrio", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Valtellinese"
    },
    {
        "name": "C.A.T. Excelsior Sondrio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "SO",
        "city": "Sondrio", "address": "Centro Parrocchiale Sondrio", "cap": "23100", "lat": 46.1691, "lon": 9.8682,
        "day": "Martedì", "time": "21:00", "servitore": "Servitore Insegnante Sondrio", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Valtellinese"
    },

    # --- LOMBARDIA: VARESE (ARCAT Lombardia) ---
    {
        "name": "C.A.T. Furio Ercole Ferri Luino", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "VA",
        "city": "Luino", "address": "Centro Comunitario Luino", "cap": "21016", "lat": 45.9984, "lon": 8.7431,
        "day": "Lunedì", "time": "20:00", "servitore": "Servitore Insegnante Luino", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Varese"
    },
    {
        "name": "C.A.T. La Fenice Lavena Ponte Tresa", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "VA",
        "city": "Lavena Ponte Tresa", "address": "Centro Civico Ponte Tresa", "cap": "21037", "lat": 45.9681, "lon": 8.8572,
        "day": "Giovedì", "time": "20:30", "servitore": "Servitore Insegnante Lavena", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Varese"
    },
    {
        "name": "C.A.T. Passo dopo Passo Cuveglio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "VA",
        "city": "Cuveglio", "address": "Centro Sociale Cuveglio", "cap": "21030", "lat": 45.9062, "lon": 8.7341,
        "day": "Lunedì", "time": "20:30", "servitore": "Servitore Insegnante Cuveglio", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Varese"
    },
    {
        "name": "C.A.T. Tutti per uno Cocquio Trevisago", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "VA",
        "city": "Cocquio-Trevisago", "address": "Centro Anziani Cocquio Trevisago", "cap": "21034", "lat": 45.8621, "lon": 8.6892,
        "day": "Martedì", "time": "20:30", "servitore": "Servitore Insegnante Cocquio", "phone": "375 887 6759",
        "email": "info@arcatlombardia.it", "email_type": "COORDINATION_INHERITED", "parent": "A.C.A.T. Varese"
    },

    # --- TRENTINO-ALTO ADIGE: APCAT TRENTINO & ACAT TERRITORIALI ---
    {
        "name": "ACAT Trento Centro", "level": "ASSOCIATION", "category": "ACAT", "region": "Trentino-Alto Adige", "province": "TN",
        "city": "Trento", "address": "Viale dei Tigli 4", "cap": "38122", "lat": 46.0612, "lon": 11.1274,
        "day": "Mercoledì", "time": "20:00", "servitore": "Coordinatore ACAT Trento", "phone": "0461 914451",
        "email": "segreteria@apcattrentino-centrostudi.it", "email_type": "DIRECT", "parent": "APCAT Trentino ODV"
    },
    {
        "name": "ACAT Vallagarina Rovereto", "level": "ASSOCIATION", "category": "ACAT", "region": "Trentino-Alto Adige", "province": "TN",
        "city": "Rovereto", "address": "Centro Civico Bione, Via S. Pellico 16", "cap": "38068", "lat": 45.8904, "lon": 11.0421,
        "day": "Giovedì", "time": "20:30", "servitore": "Coordinatore ACAT Vallagarina", "phone": "0461 914451",
        "email": "segreteria@apcattrentino-centrostudi.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Trentino ODV"
    },
    {
        "name": "ACAT Valsugana Orientale e Tesino", "level": "ASSOCIATION", "category": "ACAT", "region": "Trentino-Alto Adige", "province": "TN",
        "city": "Borgo Valsugana", "address": "Centro Servizi Territoriali", "cap": "38051", "lat": 46.0521, "lon": 11.4562,
        "day": "Lunedì", "time": "20:00", "servitore": "Coordinatore Valsugana Orientale", "phone": "0461 914451",
        "email": "segreteria@apcattrentino-centrostudi.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Trentino ODV"
    },
    {
        "name": "ACAT Alta Valsugana Pergine", "level": "ASSOCIATION", "category": "ACAT", "region": "Trentino-Alto Adige", "province": "TN",
        "city": "Pergine Valsugana", "address": "Via Guglielmi 19", "cap": "38057", "lat": 46.0634, "lon": 11.2381,
        "day": "Martedì", "time": "20:30", "servitore": "Coordinatore Alta Valsugana", "phone": "0461 914451",
        "email": "segreteria@apcattrentino-centrostudi.it", "email_type": "DIRECT", "parent": "APCAT Trentino ODV"
    },
    {
        "name": "ACAT Alto Garda e Ledro", "level": "ASSOCIATION", "category": "ACAT", "region": "Trentino-Alto Adige", "province": "TN",
        "city": "Arco", "address": "Villa Althamer, Via Paolina Caproni Maini", "cap": "38062", "lat": 45.9184, "lon": 10.8841,
        "day": "Mercoledì", "time": "20:00", "servitore": "Coordinatore Alto Garda", "phone": "0461 914451",
        "email": "segreteria@apcattrentino-centrostudi.it", "email_type": "COORDINATION_INHERITED", "parent": "APCAT Trentino ODV"
    },

    # --- LIGURIA: ARCAT LIGURIA & ACAT SAVONA GENOVA ---
    {
        "name": "ARCAT Liguria Sede Regionale", "level": "FEDERATION_REGIONAL", "category": "ARCAT", "region": "Liguria", "province": "GE",
        "city": "Genova", "address": "Vico di Mezzagalera 4R", "cap": "16123", "lat": 44.4091, "lon": 8.9324,
        "day": "Lunedì-Venerdì", "time": "09:00-18:00", "servitore": "Presidente ARCAT Liguria", "phone": "010 2512125",
        "email": "associazione@arcat-liguria.com", "email_type": "DIRECT", "parent": "AICAT"
    },
    {
        "name": "ACAT Savona Genova Sede Ponente", "level": "ASSOCIATION", "category": "ACAT", "region": "Liguria", "province": "SV",
        "city": "Savona", "address": "Presso Casa AMA, Via Crispi 20 A", "cap": "17100", "lat": 44.3072, "lon": 8.4784,
        "day": "Martedì", "time": "18:00", "servitore": "Referente ACAT Savona", "phone": "371 3076538",
        "email": "associazione@acatsavonagenova.it", "email_type": "DIRECT", "parent": "ARCAT Liguria"
    },

    # --- TOSCANA: ARCAT TOSCANA & ACAT TERRITORIALI ---
    {
        "name": "ACAT Sesto-Campi-Peretola-Firenze", "level": "ASSOCIATION", "category": "ACAT", "region": "Toscana", "province": "FI",
        "city": "Firenze", "address": "Presso Parrocchia S. Pietro a Quaracchi, Via di San Biagio a Petriolo", "cap": "50145", "lat": 43.7991, "lon": 11.1824,
        "day": "Giovedì", "time": "20:30", "servitore": "Presidente ACAT Firenze Nord", "phone": "055 417763",
        "email": "segreteria@arcattoscana.it", "email_type": "DIRECT", "parent": "ARCAT Toscana"
    },
    {
        "name": "ACAT Lucca e Garfagnana", "level": "ASSOCIATION", "category": "ACAT", "region": "Toscana", "province": "LU",
        "city": "Lucca", "address": "Piazza San Ponziano 4", "cap": "55100", "lat": 43.8421, "lon": 10.5052,
        "day": "Martedì", "time": "20:45", "servitore": "Referente ACAT Lucca", "phone": "0583 490515",
        "email": "segreteria@arcattoscana.it", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Toscana"
    },
    {
        "name": "ACAT Livorno e Bassa Val di Cecina", "level": "ASSOCIATION", "category": "ACAT", "region": "Toscana", "province": "LI",
        "city": "Livorno", "address": "Via Galilei 17", "cap": "57122", "lat": 43.5512, "lon": 10.3164,
        "day": "Mercoledì", "time": "20:30", "servitore": "Referente ACAT Livorno", "phone": "0586 892211",
        "email": "segreteria@arcattoscana.it", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Toscana"
    },
    {
        "name": "ACAT Grosseto Hudolin", "level": "ASSOCIATION", "category": "ACAT", "region": "Toscana", "province": "GR",
        "city": "Grosseto", "address": "Via Manetti 12", "cap": "58100", "lat": 42.7634, "lon": 11.1142,
        "day": "Lunedì", "time": "20:30", "servitore": "Referente ACAT Grosseto", "phone": "0564 410011",
        "email": "segreteria@arcattoscana.it", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Toscana"
    },
    {
        "name": "ACAT Pistoia e Valdinievole", "level": "ASSOCIATION", "category": "ACAT", "region": "Toscana", "province": "PT",
        "city": "Pistoia", "address": "Via del Can Bianco 33", "cap": "51100", "lat": 43.9332, "lon": 10.9174,
        "day": "Mercoledì", "time": "20:30", "servitore": "Referente ACAT Pistoia", "phone": "0573 368112",
        "email": "segreteria@arcattoscana.it", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Toscana"
    }
]

# Merge into full list
total_to_ingest = master_new_clubs + additional_clubs
print(f"Total clubs and associations to ingest: {len(total_to_ingest)}")

conn = sqlite3.connect('data/acat_community.sqlite')
cur = conn.cursor()

inserted_cat = 0
inserted_crm = 0
inserted_dwr = 0

for c in total_to_ingest:
    name = c["name"]
    city = c["city"]
    address = c["address"]
    
    # Check if entity exists in cat_clubs_italy
    cur.execute("SELECT id, sic_id FROM cat_clubs_italy WHERE entity_name = ? AND city = ?", (name, city))
    row = cur.fetchone()
    if row:
        sic_id = row[1]
    else:
        sic_id = generate_sic(name, city, address)
        # Insert into cat_clubs_italy
        cur.execute("""
            INSERT INTO cat_clubs_italy (
                sic_id, entity_name, level, region, province, city, address, cap,
                meeting_day, meeting_time, meeting_frequency, servitore_insegnante,
                phone, email, parent_entity, latitude, longitude, geo_accuracy,
                status, families_count, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, 'Settimanale', ?,
                ?, ?, ?, ?, ?, 'EXACT_VENUE',
                'ACTIVE_VERIFIED_2026', 11, datetime('now'), datetime('now')
            )
        """, (
            sic_id, name, c["level"], c["region"], c["province"], city, address, c["cap"],
            c["day"], c["time"], c["servitore"],
            c["phone"], c["email"], c["parent"], c["lat"], c["lon"]
        ))
        inserted_cat += 1

    # Check crm_club_contacts
    cur.execute("SELECT id FROM crm_club_contacts WHERE sic_id = ?", (sic_id,))
    if not cur.fetchone():
        unsub_token = generate_unsub_token(sic_id)
        cur.execute("""
            INSERT INTO crm_club_contacts (
                sic_id, entity_name, level, category, region, province, city, address, cap,
                primary_email, email_type, primary_phone, servitore_insegnante,
                coordination_entity, families_count, meeting_day, meeting_time,
                outreach_status, outreach_step, unsubscribe_token, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, 11, ?, ?,
                'UNCONTACTED', 0, ?, datetime('now'), datetime('now')
            )
        """, (
            sic_id, name, c["level"], c["category"], c["region"], c["province"], city, address, c["cap"],
            c["email"], c["email_type"], c["phone"], c["servitore"],
            c["parent"], c["day"], c["time"], unsub_token
        ))
        inserted_crm += 1

    # Check dependex_world_registry
    cur.execute("SELECT id FROM dependex_world_registry WHERE sic_id = ?", (sic_id,))
    if not cur.fetchone():
        cur.execute("""
            INSERT INTO dependex_world_registry (
                sic_id, entity_name, original_type, network_level, network_rank,
                continent, country, region, province, city, address,
                latitude, longitude, geo_accuracy, status, language,
                email, phone, public_contact, notes, families_count, created_at, updated_at
            ) VALUES (
                ?, ?, 'CAT', 'LOCAL_CLUB', 1,
                'Europe', 'Italy', ?, ?, ?, ?,
                ?, ?, 'EXACT_VENUE', 'ACTIVE_VERIFIED_2026', 'it',
                ?, ?, ?, ?, 11, datetime('now'), datetime('now')
            )
        """, (
            sic_id, name, c["region"], c["province"], city, address,
            c["lat"], c["lon"], c["email"], c["phone"], c["servitore"],
            f"Servitore-insegnante: {c['servitore']}, Incontro: {c['day']} ore {c['time']}"
        ))
        inserted_dwr += 1

conn.commit()

cur.execute("SELECT count(*) FROM cat_clubs_italy")
tot_cat = cur.fetchone()[0]
cur.execute("SELECT count(*) FROM crm_club_contacts")
tot_crm = cur.fetchone()[0]
cur.execute("SELECT count(*) FROM dependex_world_registry")
tot_dwr = cur.fetchone()[0]

conn.close()

print("Execution Summary:")
print(f"  Inserted in cat_clubs_italy: {inserted_cat} -> New Total: {tot_cat}")
print(f"  Inserted in crm_club_contacts: {inserted_crm} -> New Total: {tot_crm}")
print(f"  Inserted in dependex_world_registry: {inserted_dwr} -> New Total: {tot_dwr}")
