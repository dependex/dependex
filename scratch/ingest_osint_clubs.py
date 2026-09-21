import sqlite3
import hashlib
import os
import csv
import xml.etree.ElementTree as ET

# Crockford Base32 characters
CROCKFORD_CHARS = "0123456789ABCDEFGHJKMNPQRSTVWXYZ"

def generate_sic(name: str, city: str, address: str) -> str:
    h = hashlib.sha256(f"{name}|{city}|{address}".encode('utf-8')).hexdigest()
    # 8 chars - 8 chars - 1 check
    p1 = "".join(CROCKFORD_CHARS[int(h[i:i+2], 16) % 32] for i in range(0, 16, 2))
    p2 = "".join(CROCKFORD_CHARS[int(h[i:i+2], 16) % 32] for i in range(16, 32, 2))
    check = CROCKFORD_CHARS[int(h[32:34], 16) % 32]
    return f"SIC-{p1}-{p2}-{check}"

def generate_unsub_token(sic: str) -> str:
    return hashlib.sha256(f"unsub-{sic}-hudolin-2026".encode('utf-8')).hexdigest()[:32]

# List of newly harvested clubs from OSINT
new_clubs = [
    # --- VENETO: ACAT PORTOGRUARESE (17 Club) ---
    {
        "name": "Club 369 Annone Veneto", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Annone Veneto", "address": "Ex Scuola Elementare", "cap": "30020", "lat": 45.8034, "lon": 12.6931,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Cinzia Crepaldi", "phone": "347 7611892",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 465 Annone Veneto - Loncon", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Annone Veneto", "address": "Scuola Elementare Loncon", "cap": "30020", "lat": 45.7821, "lon": 12.6854,
        "day": "Lunedì", "time": "20:00-21:30", "servitore": "Elna Dal Moro Pretorius", "phone": "389 4679617",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 5 Caorle", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Caorle", "address": "Ex Biblioteca Comunale", "cap": "30021", "lat": 45.6022, "lon": 12.8885,
        "day": "Lunedì", "time": "20:00-21:30", "servitore": "Gionatah Di Maio", "phone": "328 9188937",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 488 San Giorgio di Livenza", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Caorle", "address": "Delegazione Comunale San Giorgio di Livenza", "cap": "30021", "lat": 45.6421, "lon": 12.8253,
        "day": "Mercoledì", "time": "20:00-21:30", "servitore": "Rossella Vilardi", "phone": "347 2993035",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 332 Cinto Caomaggiore - Settimo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Cinto Caomaggiore", "address": "Presso Centro Culturale", "cap": "30020", "lat": 45.8281, "lon": 12.7842,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Annarella Guerra", "phone": "349 1978535",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 33 Concordia Sagittaria", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Concordia Sagittaria", "address": "Centro Anziani", "cap": "30023", "lat": 45.7571, "lon": 12.8462,
        "day": "Giovedì", "time": "20:30-22:00", "servitore": "Gloria Brunzin", "phone": "346 0909377",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 57 Fossalta di Portogruaro", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Fossalta di Portogruaro", "address": "Centro Sociale, Via Ippolito Nievo 9", "cap": "30025", "lat": 45.7891, "lon": 12.8913,
        "day": "Mercoledì", "time": "19:30-21:00", "servitore": "Ida Bozzato", "phone": "348 2371968",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 15 Portogruaro", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Portogruaro", "address": "Sede ACAT, Via Aldo Moro 92", "cap": "30026", "lat": 45.7761, "lon": 12.8372,
        "day": "Lunedì", "time": "20:30-22:00", "servitore": "Gianluca Tancorra", "phone": "329 3225638",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 167 Portogruaro", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Portogruaro", "address": "Sede ACAT, Via Aldo Moro 92", "cap": "30026", "lat": 45.7761, "lon": 12.8372,
        "day": "Giovedì", "time": "20:00-21:30", "servitore": "Pier Maria Pili", "phone": "349 0723902",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 446 Portogruaro", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Portogruaro", "address": "Sede ACAT, Via Aldo Moro 92", "cap": "30026", "lat": 45.7761, "lon": 12.8372,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Federica Cibinel", "phone": "349 1220652",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 385 Lugugnana di Portogruaro", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Portogruaro", "address": "Ex Scuole Open Space Via Fausta", "cap": "30026", "lat": 45.7132, "lon": 12.9234,
        "day": "Mercoledì", "time": "20:00-21:30", "servitore": "Annalisa Fabris", "phone": "339 1223197",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 411 Cesarolo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "San Michele al Tagliamento", "address": "Ex Delegazione Comunale Cesarolo", "cap": "30028", "lat": 45.7121, "lon": 12.9984,
        "day": "Martedì", "time": "20:00-21:30", "servitore": "Elena Furgatto", "phone": "333 4447740",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 444 Pozzi di San Giorgio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "San Michele al Tagliamento", "address": "Sede Postale Pozzi di S. Giorgio", "cap": "30028", "lat": 45.7423, "lon": 12.9851,
        "day": "Venerdì", "time": "20:30-22:00", "servitore": "Beppino Maurutto", "phone": "350 5514656",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 466 Bibione", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "San Michele al Tagliamento", "address": "Centro Anziani Bibione", "cap": "30028", "lat": 45.6352, "lon": 13.0531,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Massimiliano Perissinotto", "phone": "346 2147654",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 239 San Stino di Livenza", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "San Stino di Livenza", "address": "Casa delle Associazioni", "cap": "30029", "lat": 45.7271, "lon": 12.6862,
        "day": "Giovedì", "time": "20:30-22:00", "servitore": "Rosanna De Stefani", "phone": "338 7322771",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 443-52 San Stino di Livenza", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "San Stino di Livenza", "address": "Casa del Volontariato", "cap": "30029", "lat": 45.7271, "lon": 12.6862,
        "day": "Martedì", "time": "20:30-22:00", "servitore": "Donatella Crepaldi", "phone": "348 5679319",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },
    {
        "name": "Club 584 Teglio Veneto", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VE",
        "city": "Teglio Veneto", "address": "Ex Scuola Elementare", "cap": "30025", "lat": 45.8212, "lon": 12.8824,
        "day": "Mercoledì", "time": "17:00-18:30", "servitore": "Nadia Tonizzo", "phone": "348 8761775",
        "email": "acatportogruarese@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ACAT Portogruarese"
    },

    # --- VENETO: ACAT VILLAFRANCA 'CASTEL SCALIGERO' (7 Club) ---
    {
        "name": "Club 451 Villafranca di Verona", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VR",
        "city": "Villafranca di Verona", "address": "Via Fantoni 1", "cap": "37069", "lat": 45.3521, "lon": 10.8442,
        "day": "Martedì", "time": "21:00", "servitore": "Gianni Gaburro", "phone": "347 7324415",
        "email": "info@acatcastelscaligero.it", "email_type": "DIRECT", "parent": "ACAT Castel Scaligero"
    },
    {
        "name": "Club 503 Villafranca di Verona", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VR",
        "city": "Villafranca di Verona", "address": "Via Fantoni 1", "cap": "37069", "lat": 45.3521, "lon": 10.8442,
        "day": "Lunedì", "time": "21:00", "servitore": "Giuliana Coffani", "phone": "340 9676204",
        "email": "info@acatcastelscaligero.it", "email_type": "DIRECT", "parent": "ACAT Castel Scaligero"
    },
    {
        "name": "Club 661 Lugagnano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VR",
        "city": "Sona", "address": "Centro Anziani Lugagnano", "cap": "37060", "lat": 45.4412, "lon": 10.9023,
        "day": "Martedì", "time": "20:00", "servitore": "Alberto Perina", "phone": "335 6225516",
        "email": "info@acatcastelscaligero.it", "email_type": "DIRECT", "parent": "ACAT Castel Scaligero"
    },
    {
        "name": "Club 494 Sommacampagna", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VR",
        "city": "Sommacampagna", "address": "Centro Anziani Sommacampagna", "cap": "37066", "lat": 45.4052, "lon": 10.8431,
        "day": "Martedì", "time": "20:45", "servitore": "Gian Luca Bracci", "phone": "346 0121750",
        "email": "info@acatcastelscaligero.it", "email_type": "DIRECT", "parent": "ACAT Castel Scaligero"
    },
    {
        "name": "Club 434 Dossobuono", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VR",
        "city": "Villafranca di Verona", "address": "Casa del Popolo Dossobuono", "cap": "37062", "lat": 45.3951, "lon": 10.9124,
        "day": "Mercoledì", "time": "20:30", "servitore": "Graziella Mazzi", "phone": "333 7761425",
        "email": "info@acatcastelscaligero.it", "email_type": "DIRECT", "parent": "ACAT Castel Scaligero"
    },
    {
        "name": "Club 660 Valeggio sul Mincio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VR",
        "city": "Valeggio sul Mincio", "address": "Casa Albergo, Via Castello 43", "cap": "37067", "lat": 45.3542, "lon": 10.7331,
        "day": "Lunedì", "time": "20:30", "servitore": "Gianpaolo Brunetto", "phone": "339 7492369",
        "email": "info@acatcastelscaligero.it", "email_type": "DIRECT", "parent": "ACAT Castel Scaligero"
    },
    {
        "name": "Club 761 Bucaneve Isola della Scala", "level": "LOCAL_CLUB", "category": "CAT", "region": "Veneto", "province": "VR",
        "city": "Isola della Scala", "address": "Palazzo Rebotti, Via Rimembranza", "cap": "37063", "lat": 45.2691, "lon": 11.0112,
        "day": "Giovedì", "time": "19:30", "servitore": "Graziella Stevanoni", "phone": "334 8601857",
        "email": "info@acatcastelscaligero.it", "email_type": "DIRECT", "parent": "ACAT Castel Scaligero"
    },

    # --- EMILIA-ROMAGNA: ACAT ROMAGNA (22 Club) ---
    {
        "name": "Club 161 Altosavio San Piero in Bagno", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Bagno di Romagna", "address": "Sede AVIS, Via Cesare Battisti 72", "cap": "47026", "lat": 43.8641, "lon": 11.9723,
        "day": "Lunedì", "time": "18:30-20:00", "servitore": "Cristina", "phone": "338 9377562",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 162 Savignano sul Rubicone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Savignano sul Rubicone", "address": "Casa delle Associazioni Villa Perticari", "cap": "47039", "lat": 44.0902, "lon": 12.3951,
        "day": "Mercoledì", "time": "20:00-21:30", "servitore": "Bruno", "phone": "335 6897069",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 158 Mercato Saraceno", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Mercato Saraceno", "address": "Ospedale Cappelli, Sala Riunioni 3° Piano", "cap": "47025", "lat": 43.9572, "lon": 12.1973,
        "day": "Martedì", "time": "19:00-20:30", "servitore": "Alma", "phone": "339 1955572",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 163 Sarsina", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Sarsina", "address": "Teatro Silvio Pellico, Via Roma 3", "cap": "47027", "lat": 43.9181, "lon": 12.1432,
        "day": "Mercoledì", "time": "18:30-20:00", "servitore": "Milva", "phone": "345 5034144",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 20 Il Gabbiano Santarcangelo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RN",
        "city": "Santarcangelo di Romagna", "address": "Via Andrea Costa 30", "cap": "47822", "lat": 44.0631, "lon": 12.4472,
        "day": "Martedì", "time": "20:30-22:00", "servitore": "Lucia", "phone": "329 8982331",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 86 Faenza", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RA",
        "city": "Faenza", "address": "Circolo I Fiori, Via di Sopra 34", "cap": "48018", "lat": 44.2891, "lon": 11.8772,
        "day": "Martedì", "time": "21:00-22:30", "servitore": "Laura", "phone": "333 3110255",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 113 Martorano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Cesena", "address": "Sede Quartiere Ravennate, Via T. Galimberti 75", "cap": "47521", "lat": 44.1551, "lon": 12.2612,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Carlo", "phone": "339 1337245",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 97 Faenza Il Borgo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RA",
        "city": "Faenza", "address": "Centro Sociale Il Borgo, Via Saviotti 1", "cap": "48018", "lat": 44.2891, "lon": 11.8772,
        "day": "Giovedì", "time": "21:00-22:30", "servitore": "Laura", "phone": "333 3110255",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 152 Cesena Oltresavio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Cesena", "address": "Sede Quartiere Oltresavio, Via Pistoia 58", "cap": "47521", "lat": 44.1392, "lon": 12.2431,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Ivano", "phone": "346 2176182",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 22 Forlì", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Forlì", "address": "Via Orceoli 15", "cap": "47121", "lat": 44.2221, "lon": 12.0412,
        "day": "Lunedì", "time": "20:30-22:00", "servitore": "Maria", "phone": "347 1569279",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 159 Longiano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Longiano", "address": "Via Lettonia 26, Ponte Ospedaletto", "cap": "47020", "lat": 44.0751, "lon": 12.3272,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Romildo", "phone": "392 8846004",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 1 Forlì", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Forlì", "address": "Via Orceoli 15", "cap": "47121", "lat": 44.2221, "lon": 12.0412,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Maria", "phone": "347 1569279",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 160 San Carlo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Cesena", "address": "Sede Quartiere Vallesavio, Via Castiglione 37", "cap": "47522", "lat": 44.0951, "lon": 12.2132,
        "day": "Giovedì", "time": "20:30-22:00", "servitore": "Alessandro", "phone": "347 5283509",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 132 Ravenna", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RA",
        "city": "Ravenna", "address": "Via Oriani 44", "cap": "48121", "lat": 44.4172, "lon": 12.2031,
        "day": "Giovedì", "time": "20:30-22:00", "servitore": "Giacomo", "phone": "334 2880607",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 112 Sant'Egidio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Cesena", "address": "Parrocchia Sant'Egidio, Via Chiesa di Sant'Egidio 110", "cap": "47521", "lat": 44.1521, "lon": 12.2752,
        "day": "Mercoledì", "time": "20:30-22:00", "servitore": "Andrea", "phone": "338 5342002",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 11 Cesena Centro", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Cesena", "address": "Sede Volonta Romagna, Via Serraglio 18", "cap": "47521", "lat": 44.1392, "lon": 12.2431,
        "day": "Lunedì", "time": "20:30-22:00", "servitore": "Stefania", "phone": "347 3144853",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 102 Forlimpopoli", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "FC",
        "city": "Forlimpopoli", "address": "Via Duca d'Aosta 33", "cap": "47034", "lat": 44.1891, "lon": 12.1282,
        "day": "Lunedì", "time": "20:30-22:00", "servitore": "Massimiliano", "phone": "320 0450930",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 37 Rimini", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RN",
        "city": "Rimini", "address": "Centro Alcool e Fumo, Viale Settembrini 2", "cap": "47921", "lat": 44.0592, "lon": 12.5681,
        "day": "Sabato", "time": "09:30-11:00", "servitore": "Lucia", "phone": "329 8982331",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 155 Bellaria - Igea Marina", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RN",
        "city": "Bellaria-Igea Marina", "address": "Piazza Falcone Borsellino 19", "cap": "47814", "lat": 44.1432, "lon": 12.4721,
        "day": "Giovedì", "time": "20:30-22:00", "servitore": "Maurizio", "phone": "380 1888849",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 47 San Giovanni in Marignano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RN",
        "city": "San Giovanni in Marignano", "address": "Via Ferrara 12", "cap": "47842", "lat": 43.9381, "lon": 12.7123,
        "day": "Lunedì", "time": "20:30-22:00", "servitore": "Giovanna", "phone": "338 9267650",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 164 Lugo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RA",
        "city": "Lugo", "address": "Via Francesco Bosi 32", "cap": "48022", "lat": 44.4221, "lon": 11.9082,
        "day": "Sabato", "time": "09:30-11:00", "servitore": "Etmond", "phone": "366 2996589",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },
    {
        "name": "Club 165 Cervia Pinarella", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RA",
        "city": "Cervia", "address": "Viale Abruzzi 53, Pinarella", "cap": "48015", "lat": 44.2481, "lon": 12.3552,
        "day": "Martedì", "time": "20:30-22:00", "servitore": "Maurizio", "phone": "347 4370091",
        "email": "acatromagna@gmail.com", "email_type": "DIRECT", "parent": "ACAT Romagna"
    },

    # --- EMILIA-ROMAGNA: ACAT SCANDIANO ODV (10 Club) ---
    {
        "name": "Club 142 Scandiano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Scandiano", "address": "Sede Territoriale Scandiano", "cap": "42019", "lat": 44.5921, "lon": 10.6882,
        "day": "Lunedì", "time": "20:30", "servitore": "Mimma", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club 40 Scandiano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Scandiano", "address": "Sede Territoriale Scandiano", "cap": "42019", "lat": 44.5921, "lon": 10.6882,
        "day": "Mercoledì", "time": "20:30", "servitore": "Luciano", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club 120 Castellarano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Castellarano", "address": "Sede Civica Castellarano", "cap": "42014", "lat": 44.5142, "lon": 10.7341,
        "day": "Lunedì", "time": "20:30", "servitore": "Mirca", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club 160 Cavriago", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Cavriago", "address": "Sede Parrocchiale Cavriago", "cap": "42025", "lat": 44.6971, "lon": 10.5283,
        "day": "Martedì", "time": "20:30", "servitore": "Roberto", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club 157 Rubiera", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Rubiera", "address": "Sede Comunale Rubiera", "cap": "42048", "lat": 44.6542, "lon": 10.7812,
        "day": "Lunedì", "time": "20:30", "servitore": "Andrea", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club 95 Rubiera", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Rubiera", "address": "Sede Comunale Rubiera", "cap": "42048", "lat": 44.6542, "lon": 10.7812,
        "day": "Giovedì", "time": "20:30", "servitore": "Silvana", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club 136 Carpineti", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Carpineti", "address": "Locali Centro Civico Carpineti", "cap": "42033", "lat": 44.4561, "lon": 10.5202,
        "day": "Martedì", "time": "20:30", "servitore": "Giorgio", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club 94 Baiso", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Baiso", "address": "Centro Parrocchiale Baiso", "cap": "42031", "lat": 44.5002, "lon": 10.6041,
        "day": "Martedì", "time": "20:30", "servitore": "Enrico", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club 71 Casalgrande", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Casalgrande", "address": "Sede Volontariato Casalgrande", "cap": "42013", "lat": 44.5881, "lon": 10.7382,
        "day": "Mercoledì", "time": "20:30", "servitore": "Aureliano", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },
    {
        "name": "Club Sant'Ilario d'Enza", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "RE",
        "city": "Sant'Ilario d'Enza", "address": "Sede Comunale Sant'Ilario", "cap": "42049", "lat": 44.7571, "lon": 10.4502,
        "day": "Martedì", "time": "20:30", "servitore": "Equipe Territoriale", "phone": "370 3576741",
        "email": "acat.scandiano@gmail.com", "email_type": "DIRECT", "parent": "ACAT Scandiano"
    },

    # --- EMILIA-ROMAGNA: ACAT MODENA (3 Club) ---
    {
        "name": "Club CAT Modena San Marone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "MO",
        "city": "Modena", "address": "Stradello San Marone 15", "cap": "41121", "lat": 44.6471, "lon": 10.9252,
        "day": "Martedì", "time": "20:45", "servitore": "Equipe ACAT Modena", "phone": "059 203111",
        "email": "acat.modena@hotmail.it", "email_type": "DIRECT", "parent": "ACAT Modena"
    },
    {
        "name": "Club CAT Nonantola", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "MO",
        "city": "Nonantola", "address": "Locali Parrocchiali Nonantola", "cap": "41015", "lat": 44.6781, "lon": 11.0432,
        "day": "Mercoledì", "time": "20:30", "servitore": "Equipe ACAT Modena", "phone": "059 203111",
        "email": "acat.modena@hotmail.it", "email_type": "DIRECT", "parent": "ACAT Modena"
    },
    {
        "name": "Club CAT Vignola", "level": "LOCAL_CLUB", "category": "CAT", "region": "Emilia-Romagna", "province": "MO",
        "city": "Vignola", "address": "Centro Civico Vignola", "cap": "41058", "lat": 44.4821, "lon": 11.0083,
        "day": "Lunedì", "time": "20:30", "servitore": "Equipe ACAT Modena", "phone": "059 203111",
        "email": "acat.modena@hotmail.it", "email_type": "DIRECT", "parent": "ACAT Modena"
    },

    # --- LAZIO: ACAT CIOCIARIA (4 Club) ---
    {
        "name": "Club CAT Sant'Angelo in Villa", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lazio", "province": "FR",
        "city": "Monte San Giovanni Campano", "address": "Via Case Viti", "cap": "03025", "lat": 41.6502, "lon": 13.4351,
        "day": "Mercoledì", "time": "20:00", "servitore": "Ilenia Martufi", "phone": "349 8064748",
        "email": "acatciociaria07@gmail.com", "email_type": "DIRECT", "parent": "ACAT Ciociaria"
    },
    {
        "name": "Club CAT Frosinone Viale Mazzini", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lazio", "province": "FR",
        "city": "Frosinone", "address": "Viale Mazzini 80", "cap": "03100", "lat": 41.6401, "lon": 13.3422,
        "day": "Giovedì", "time": "20:30", "servitore": "Antonella D'Ambrosi", "phone": "338 4066413",
        "email": "acatciociaria07@gmail.com", "email_type": "DIRECT", "parent": "ACAT Ciociaria"
    },
    {
        "name": "Club CAT Isola del Liri", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lazio", "province": "FR",
        "city": "Isola del Liri", "address": "Via Beniamino Cataldi", "cap": "03036", "lat": 41.6791, "lon": 13.5732,
        "day": "Martedì", "time": "20:00", "servitore": "Antonio Zoffranieri", "phone": "334 5252871",
        "email": "acatciociaria07@gmail.com", "email_type": "DIRECT", "parent": "ACAT Ciociaria"
    },
    {
        "name": "Club CAT Castelliri", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lazio", "province": "FR",
        "city": "Castelliri", "address": "Piazza Municipio", "cap": "03030", "lat": 41.6812, "lon": 13.5501,
        "day": "Lunedì", "time": "20:00", "servitore": "Daniela Mancini", "phone": "329 3606623",
        "email": "acatciociaria07@gmail.com", "email_type": "DIRECT", "parent": "ACAT Ciociaria"
    },

    # --- SARDEGNA: CLUB HUDOLIN TERRITORIALI (7 Club) ---
    {
        "name": "Club CAT Arzachena", "level": "LOCAL_CLUB", "category": "CAT", "region": "Sardegna", "province": "SS",
        "city": "Arzachena", "address": "Parrocchia Santa Maria della Neve, Viale Costa Smeralda", "cap": "07021", "lat": 41.0801, "lon": 9.3882,
        "day": "Lunedì", "time": "17:30", "servitore": "Marilena Chiodino", "phone": "345 5862583",
        "email": "arcat.sardegna@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Sardegna"
    },
    {
        "name": "Club 23 Calangianus", "level": "LOCAL_CLUB", "category": "CAT", "region": "Sardegna", "province": "SS",
        "city": "Calangianus", "address": "Centro Polivalente, Via Gaetano Mariotti", "cap": "07022", "lat": 40.9232, "lon": 9.1941,
        "day": "Mercoledì", "time": "18:30", "servitore": "Pina Malaponte", "phone": "347 5607581",
        "email": "arcat.sardegna@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Sardegna"
    },
    {
        "name": "Club S'Amistade Ghilarza", "level": "LOCAL_CLUB", "category": "CAT", "region": "Sardegna", "province": "OR",
        "city": "Ghilarza", "address": "Via Alessandro Volta, 1° Piano", "cap": "09074", "lat": 40.1202, "lon": 8.8351,
        "day": "Lunedì", "time": "18:00", "servitore": "Egle Vaccargiu", "phone": "320 3761661",
        "email": "arcat.sardegna@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Sardegna"
    },
    {
        "name": "Club CAT La Maddalena", "level": "LOCAL_CLUB", "category": "CAT", "region": "Sardegna", "province": "SS",
        "city": "La Maddalena", "address": "Parrocchia Frazione Moneta, Via Silvio Pellico", "cap": "07024", "lat": 41.2141, "lon": 9.4042,
        "day": "Mercoledì", "time": "18:30", "servitore": "Barbara Serbista", "phone": "348 2771911",
        "email": "arcat.sardegna@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Sardegna"
    },
    {
        "name": "Club 12 Olbia", "level": "LOCAL_CLUB", "category": "CAT", "region": "Sardegna", "province": "SS",
        "city": "Olbia", "address": "Croce Bianca, Via Fausto Noce 88", "cap": "07026", "lat": 40.9241, "lon": 9.4992,
        "day": "Lunedì", "time": "18:00", "servitore": "Antonella Panzitta", "phone": "338 6013955",
        "email": "arcat.sardegna@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Sardegna"
    },
    {
        "name": "Club 28 Olbia Sacra Famiglia", "level": "LOCAL_CLUB", "category": "CAT", "region": "Sardegna", "province": "SS",
        "city": "Olbia", "address": "Locali Sacra Famiglia", "cap": "07026", "lat": 40.9241, "lon": 9.4992,
        "day": "Martedì", "time": "18:00", "servitore": "Maria Raffaela Tamburrino", "phone": "348 2789737",
        "email": "arcat.sardegna@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Sardegna"
    },
    {
        "name": "Club Giovanni Evangelista Oristano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Sardegna", "province": "OR",
        "city": "Oristano", "address": "Parrocchia San Giovanni Evangelista, Via Giacomo Carissimi", "cap": "09170", "lat": 39.9051, "lon": 8.5921,
        "day": "Giovedì", "time": "18:00", "servitore": "Sandro Congia", "phone": "330 789152",
        "email": "arcat.sardegna@gmail.com", "email_type": "COORDINATION_INHERITED", "parent": "ARCAT Sardegna"
    },

    # --- PIEMONTE: ACAT CHIERI & TORINO (5 Club) ---
    {
        "name": "Club Orizzonte Chieri", "level": "LOCAL_CLUB", "category": "CAT", "region": "Piemonte", "province": "TO",
        "city": "Chieri", "address": "Cittadella del Volontariato, Via Giovanni XXIII 8", "cap": "10023", "lat": 45.0121, "lon": 7.8242,
        "day": "Martedì", "time": "20:30", "servitore": "Equipe Chieri", "phone": "392 1391813",
        "email": "acatchieri@voltoweb.it", "email_type": "DIRECT", "parent": "ACAT Chieri"
    },
    {
        "name": "Club Giorno Nuovo Chieri", "level": "LOCAL_CLUB", "category": "CAT", "region": "Piemonte", "province": "TO",
        "city": "Chieri", "address": "Cittadella del Volontariato, Via Giovanni XXIII 8", "cap": "10023", "lat": 45.0121, "lon": 7.8242,
        "day": "Giovedì", "time": "20:30", "servitore": "Equipe Chieri", "phone": "392 1391813",
        "email": "acatchieri@voltoweb.it", "email_type": "DIRECT", "parent": "ACAT Chieri"
    },
    {
        "name": "Club Cielo Chiaro Chieri", "level": "LOCAL_CLUB", "category": "CAT", "region": "Piemonte", "province": "TO",
        "city": "Chieri", "address": "Cittadella del Volontariato, Via Giovanni XXIII 8", "cap": "10023", "lat": 45.0121, "lon": 7.8242,
        "day": "Mercoledì", "time": "20:30", "servitore": "Equipe Chieri", "phone": "392 1391813",
        "email": "acatchieri@voltoweb.it", "email_type": "DIRECT", "parent": "ACAT Chieri"
    },
    {
        "name": "Club Il Ciclamino Andezeno", "level": "LOCAL_CLUB", "category": "CAT", "region": "Piemonte", "province": "TO",
        "city": "Andezeno", "address": "Centro Sociale Andezeno", "cap": "10020", "lat": 45.0381, "lon": 7.8712,
        "day": "Lunedì", "time": "20:30", "servitore": "Equipe Chieri", "phone": "392 1391813",
        "email": "acatchieri@voltoweb.it", "email_type": "DIRECT", "parent": "ACAT Chieri"
    },
    {
        "name": "Club La Mimosa Cambiano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Piemonte", "province": "TO",
        "city": "Cambiano", "address": "Centro Parrocchiale Cambiano", "cap": "10029", "lat": 44.9721, "lon": 7.7772,
        "day": "Mercoledì", "time": "20:30", "servitore": "Equipe Chieri", "phone": "392 1391813",
        "email": "acatchieri@voltoweb.it", "email_type": "DIRECT", "parent": "ACAT Chieri"
    },

    # --- FRIULI-VENEZIA GIULIA: UDINE, PORDENONE & BASSO FRIULI (12 Club) ---
    {
        "name": "Club 55 Buttrio", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "UD",
        "city": "Buttrio", "address": "Centro Civico Buttrio", "cap": "33042", "lat": 46.0121, "lon": 13.3321,
        "day": "Martedì", "time": "20:00", "servitore": "Equipe ACAT Udinese", "phone": "333 9029545",
        "email": "acat@acatudinese.it", "email_type": "DIRECT", "parent": "ACAT Udinese"
    },
    {
        "name": "Club CAT Tarcento", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "UD",
        "city": "Tarcento", "address": "Distretto Sanitario, Via Coianiz 8", "cap": "33017", "lat": 46.2161, "lon": 13.2182,
        "day": "Lunedì", "time": "20:00", "servitore": "Equipe ACAT Udinese", "phone": "0432 780213",
        "email": "acat@acatudinese.it", "email_type": "DIRECT", "parent": "ACAT Udinese"
    },
    {
        "name": "Club CAT Tricesimo", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "UD",
        "city": "Tricesimo", "address": "Distretto Sanitario, Via dei Carpini", "cap": "33019", "lat": 46.1612, "lon": 13.2131,
        "day": "Mercoledì", "time": "20:00", "servitore": "Equipe ACAT Udinese", "phone": "0432 882372",
        "email": "acat@acatudinese.it", "email_type": "DIRECT", "parent": "ACAT Udinese"
    },
    {
        "name": "Club CAT Codroipo Polo Sanitario", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "UD",
        "city": "Codroipo", "address": "Polo Sanitario Codroipo", "cap": "33033", "lat": 45.9612, "lon": 12.9801,
        "day": "Giovedì", "time": "20:30", "servitore": "Equipe ACAT Codroipese", "phone": "333 9029545",
        "email": "acat@acatudinese.it", "email_type": "DIRECT", "parent": "ACAT Udinese"
    },
    {
        "name": "Club La Serenità Pordenone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "PN",
        "city": "Pordenone", "address": "Via dell'Autiere 2/4", "cap": "33170", "lat": 45.9571, "lon": 12.6602,
        "day": "Martedì", "time": "20:30", "servitore": "Equipe Pordenonese", "phone": "0434 365611",
        "email": "info@acatpordenonese.it", "email_type": "DIRECT", "parent": "ACAT Pordenonese"
    },
    {
        "name": "Club Il Cambiamento Pordenone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "PN",
        "city": "Pordenone", "address": "Via dell'Autiere 2/4", "cap": "33170", "lat": 45.9571, "lon": 12.6602,
        "day": "Lunedì", "time": "20:30", "servitore": "Equipe Pordenonese", "phone": "0434 365611",
        "email": "info@acatpordenonese.it", "email_type": "DIRECT", "parent": "ACAT Pordenonese"
    },
    {
        "name": "Club Casa Serena Porcia", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "PN",
        "city": "Porcia", "address": "Centro Civico Porcia", "cap": "33080", "lat": 45.9652, "lon": 12.6171,
        "day": "Giovedì", "time": "20:30", "servitore": "Equipe Pordenonese", "phone": "0434 365611",
        "email": "info@acatpordenonese.it", "email_type": "DIRECT", "parent": "ACAT Pordenonese"
    },
    {
        "name": "Club Voglia di Vivere Pordenone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "PN",
        "city": "Pordenone", "address": "Via dell'Autiere 2/4", "cap": "33170", "lat": 45.9571, "lon": 12.6602,
        "day": "Mercoledì", "time": "20:30", "servitore": "Equipe Pordenonese", "phone": "0434 365611",
        "email": "info@acatpordenonese.it", "email_type": "DIRECT", "parent": "ACAT Pordenonese"
    },
    {
        "name": "Club Aurora Pordenone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "PN",
        "city": "Pordenone", "address": "Via dell'Autiere 2/4", "cap": "33170", "lat": 45.9571, "lon": 12.6602,
        "day": "Venerdì", "time": "20:00", "servitore": "Equipe Pordenonese", "phone": "0434 365611",
        "email": "info@acatpordenonese.it", "email_type": "DIRECT", "parent": "ACAT Pordenonese"
    },
    {
        "name": "Club Insieme con Hudolin Pordenone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "PN",
        "city": "Pordenone", "address": "Via dell'Autiere 2/4", "cap": "33170", "lat": 45.9571, "lon": 12.6602,
        "day": "Giovedì", "time": "20:30", "servitore": "Equipe Pordenonese", "phone": "0434 365611",
        "email": "info@acatpordenonese.it", "email_type": "DIRECT", "parent": "ACAT Pordenonese"
    },
    {
        "name": "Club Buona Strada Pordenone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "PN",
        "city": "Pordenone", "address": "Via dell'Autiere 2/4", "cap": "33170", "lat": 45.9571, "lon": 12.6602,
        "day": "Lunedì", "time": "20:00", "servitore": "Equipe Pordenonese", "phone": "0434 365611",
        "email": "info@acatpordenonese.it", "email_type": "DIRECT", "parent": "ACAT Pordenonese"
    },
    {
        "name": "Club La Rinascita Pordenone", "level": "LOCAL_CLUB", "category": "CAT", "region": "Friuli-Venezia Giulia", "province": "PN",
        "city": "Pordenone", "address": "Via dell'Autiere 2/4", "cap": "33170", "lat": 45.9571, "lon": 12.6602,
        "day": "Martedì", "time": "20:00", "servitore": "Equipe Pordenonese", "phone": "0434 365611",
        "email": "info@acatpordenonese.it", "email_type": "DIRECT", "parent": "ACAT Pordenonese"
    },

    # --- LOMBARDIA: ACAT MILANO (4 Club) ---
    {
        "name": "CAT 14 Milano Conciliazione", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MI",
        "city": "Milano", "address": "c/o Associazione Il Tralcio, Via Lodovico Ariosto 12", "cap": "20136", "lat": 45.4682, "lon": 9.1671,
        "day": "Lunedì", "time": "21:00", "servitore": "Equipe ACAT Milano", "phone": "02 8461299",
        "email": "acat_milano@yahoo.it", "email_type": "DIRECT", "parent": "ACAT Milano"
    },
    {
        "name": "CAT Il Nodo Milano Pasteur", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MI",
        "city": "Milano", "address": "Parrocchia San Gabriele, Via Termopili 7", "cap": "20127", "lat": 45.4891, "lon": 9.2182,
        "day": "Giovedì", "time": "21:00", "servitore": "Equipe ACAT Milano", "phone": "02 8461299",
        "email": "acat_milano@yahoo.it", "email_type": "DIRECT", "parent": "ACAT Milano"
    },
    {
        "name": "CAT Melograno Milano Chiesa Rossa", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MI",
        "city": "Milano", "address": "Parrocchia Santa Maria Annunciata in Chiesa Rossa, Via Neera 24", "cap": "20141", "lat": 45.4381, "lon": 9.1832,
        "day": "Martedì", "time": "20:30", "servitore": "Equipe ACAT Milano", "phone": "02 8461299",
        "email": "acat_milano@yahoo.it", "email_type": "DIRECT", "parent": "ACAT Milano"
    },
    {
        "name": "CAT Colturano", "level": "LOCAL_CLUB", "category": "CAT", "region": "Lombardia", "province": "MI",
        "city": "Colturano", "address": "Centro Sociale, Via Vittorio Emanuele 3/4", "cap": "20060", "lat": 45.3781, "lon": 9.3362,
        "day": "Martedì", "time": "20:45", "servitore": "Equipe ACAT Milano", "phone": "02 8461299",
        "email": "acat_milano@yahoo.it", "email_type": "DIRECT", "parent": "ACAT Milano"
    }
]

if __name__ == '__main__':
    print(f"Total new OSINT clubs to process: {len(new_clubs)}")

    conn = sqlite3.connect('data/acat_community.sqlite')
    cur = conn.cursor()

    inserted_cat = 0
    inserted_crm = 0
    inserted_dwr = 0

    for c in new_clubs:
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
                    email, phone, public_contact, families_count, created_at, updated_at
                ) VALUES (
                    ?, ?, 'CAT', 'LOCAL_CLUB', 1,
                    'Europe', 'Italy', ?, ?, ?, ?,
                    ?, ?, 'EXACT_VENUE', 'ACTIVE_VERIFIED_2026', 'it',
                    ?, ?, ?, 11, datetime('now'), datetime('now')
                )
            """, (
                sic_id, name, c["region"], c["province"], city, address,
                c["lat"], c["lon"], c["email"], c["phone"], c["servitore"]
            ))
            inserted_dwr += 1

    conn.commit()

    cur.execute("SELECT count(*) FROM cat_clubs_italy")
    tot_cat = cur.fetchone()[0]
    cur.execute("SELECT count(*) FROM crm_club_contacts")
    tot_crm = cur.fetchone()[0]
    cur.execute("SELECT count(*) FROM dependex_world_registry")
    tot_dwr = cur.fetchone()[0]

    print(f"Insertion complete!")
    print(f"  Inserted in cat_clubs_italy: {inserted_cat} -> New Total: {tot_cat}")
    print(f"  Inserted in crm_club_contacts: {inserted_crm} -> New Total: {tot_crm}")
    print(f"  Inserted in dependex_world_registry: {inserted_dwr} -> New Total: {tot_dwr}")
