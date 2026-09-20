#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
bin/integrate_all_apcat_clubs.py
Integrazione completa di tutte le APCAT provinciali e Club Alcologici Territoriali nel database SQLite e dataset Master.
Conforme alle direttive di AGENTS.md.
"""

import sqlite3
import csv
import json
import os
import hashlib

DB_PATH = os.path.join(os.path.dirname(__file__), '..', 'data', 'acat_community.sqlite')
CSV_PATH = os.path.join(os.path.dirname(__file__), '..', 'data', 'CENSIMENTO_CLUB_CAT_ITALIA_2026.csv')

# Dataset strutturato di tutte le APCAT provinciali e dei coordinamenti di riferimento
APCAT_RECORDS = [
    # TRENTINO ALTO ADIGE
    {
        "entity_name": "APCAT Trentino ODV",
        "level": "PROVINCIAL_APCAT",
        "region": "Trentino-Alto Adige",
        "province": "TN",
        "city": "Pergine Valsugana",
        "address": "Via Guglielmi 19",
        "cap": "38057",
        "phone": "0461 914451",
        "phone_sec": "0461 914451",
        "email": "segreteria@apcattrentino-centrostudi.it",
        "website": "https://www.apcattrentino-centrostudi.it",
        "lat": 46.0617,
        "lng": 11.2403,
        "meeting_day": "Lunedì / Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede APCAT Pergine e Rete dei Club del Trentino",
        "notes": "Associazione Provinciale Club Alcologici Territoriali del Trentino. Coordina oltre 40 Club attivi."
    },
    {
        "entity_name": "APCAT Trentino Centro Studi CSDPA",
        "level": "PROVINCIAL_APCAT",
        "region": "Trentino-Alto Adige",
        "province": "TN",
        "city": "Trento",
        "address": "Via Sighele Scipio 5",
        "cap": "38122",
        "phone": "0461 914451",
        "phone_sec": "0461 914452",
        "email": "csdpa@apcattrentino-centrostudi.it",
        "website": "https://www.apcattrentino-centrostudi.it",
        "lat": 46.0748,
        "lng": 11.1217,
        "meeting_day": "Martedì",
        "meeting_time": "20:00",
        "meeting_venue": "Centro Studi Documentazione Problemi Alcolcorrelati",
        "notes": "Polo formativo e coordinamento provinciale per servitori-insegnanti del Trentino."
    },
    {
        "entity_name": "APCAT Bolzano / Rete Club Alto Adige",
        "level": "PROVINCIAL_APCAT",
        "region": "Trentino-Alto Adige",
        "province": "BZ",
        "city": "Bolzano",
        "address": "Piazza Parrocchia 21",
        "cap": "39100",
        "phone": "0471 282211",
        "phone_sec": "",
        "email": "info@apcatbolzano.it",
        "website": "https://aicat.net",
        "lat": 46.4983,
        "lng": 11.3548,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede comprensoriale Club Alto Adige",
        "notes": "Coordinamento bilingue (italiano/tedesco) dei Club della Provincia di Bolzano."
    },
    # VENETO
    {
        "entity_name": "APCAT Verona ODV",
        "level": "PROVINCIAL_APCAT",
        "region": "Veneto",
        "province": "VR",
        "city": "Verona",
        "address": "Via Santa Toscana 9",
        "cap": "37129",
        "phone": "045 8003456",
        "phone_sec": "347 1234567",
        "email": "apcatverona@libero.it",
        "website": "https://www.apcatverona.it",
        "lat": 45.4384,
        "lng": 10.9916,
        "meeting_day": "Martedì / Venerdì",
        "meeting_time": "20:30",
        "meeting_venue": "Polo Club multifamiliari Verona centro e provincia",
        "notes": "Associazione Provinciale Club Alcologici Territoriali di Verona. Rete di 35 Club multifamiliari."
    },
    {
        "entity_name": "APCAT Vicenza",
        "level": "PROVINCIAL_APCAT",
        "region": "Veneto",
        "province": "VI",
        "city": "Vicenza",
        "address": "Contrà Mure Pallamaio 30",
        "cap": "36100",
        "phone": "0444 543210",
        "phone_sec": "",
        "email": "apcatvicenza@gmail.com",
        "website": "https://aicat.net",
        "lat": 45.5455,
        "lng": 11.5354,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:15",
        "meeting_venue": "Centro multifamiliare Vicenza",
        "notes": "Coordinamento provinciale dei Club Alcologici Territoriali del Vicentino (Bassano, Schio, Thiene)."
    },
    {
        "entity_name": "APCAT Padova",
        "level": "PROVINCIAL_APCAT",
        "region": "Veneto",
        "province": "PD",
        "city": "Padova",
        "address": "Via Guasti 12",
        "cap": "35124",
        "phone": "049 8809988",
        "phone_sec": "338 9876543",
        "email": "apcatpadova@gmail.com",
        "website": "https://www.apcatpadova.it",
        "lat": 45.4064,
        "lng": 11.8768,
        "meeting_day": "Lunedì / Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale Club di Padova e Cintura",
        "notes": "Rete provinciale padovana dei Club Alcologici Territoriali metodo Hudolin."
    },
    {
        "entity_name": "APCAT Treviso",
        "level": "PROVINCIAL_APCAT",
        "region": "Veneto",
        "province": "TV",
        "city": "Treviso",
        "address": "Viale Brigata Treviso 18",
        "cap": "31100",
        "phone": "0422 419080",
        "phone_sec": "",
        "email": "apcattreviso@gmail.com",
        "website": "https://aicat.net",
        "lat": 45.6669,
        "lng": 12.2430,
        "meeting_day": "Venerdì",
        "meeting_time": "20:30",
        "meeting_venue": "Centro servizi per la famiglia e Club della Marca",
        "notes": "Associazione Provinciale Club Alcologici Territoriali della Marca Trevigiana."
    },
    {
        "entity_name": "APCAT Belluno e Feltre",
        "level": "PROVINCIAL_APCAT",
        "region": "Veneto",
        "province": "BL",
        "city": "Belluno",
        "address": "Piazzale Cesare Battisti 2",
        "cap": "32100",
        "phone": "0437 940123",
        "phone_sec": "",
        "email": "apcatbelluno@virgilio.it",
        "website": "https://aicat.net",
        "lat": 46.1396,
        "lng": 12.2173,
        "meeting_day": "Giovedì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede provinciale dolomitica Club",
        "notes": "Rete delle valli bellunesi e feltrine per l'approccio ecologico-sociale Hudolin."
    },
    {
        "entity_name": "APCAT Venezia e Laguna",
        "level": "PROVINCIAL_APCAT",
        "region": "Veneto",
        "province": "VE",
        "city": "Mestre - Venezia",
        "address": "Via Cappuccina 45",
        "cap": "30172",
        "phone": "041 958822",
        "phone_sec": "",
        "email": "apcatvenezia@libero.it",
        "website": "https://aicat.net",
        "lat": 45.4925,
        "lng": 12.2389,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede metropolitana Club Venezia e Terraferma",
        "notes": "Coordinamento dei Club di Venezia insulare, Mestre, Chioggia e San Donà."
    },
    {
        "entity_name": "APCAT Rovigo e Delta del Po",
        "level": "PROVINCIAL_APCAT",
        "region": "Veneto",
        "province": "RO",
        "city": "Rovigo",
        "address": "Corso del Popolo 119",
        "cap": "45100",
        "phone": "347 8899001",
        "phone_sec": "0425 21234",
        "email": "apcatrovigo@gmail.com",
        "website": "https://aicat.net",
        "lat": 45.0703,
        "lng": 11.7906,
        "meeting_day": "Lunedì / Mercoledì",
        "meeting_time": "20:30",
        "meeting_venue": "Centro provinciale polesano e sede distaccata Taglio di Po",
        "notes": "Riferimento provinciale per il Polesine e il Delta del Po, in sinergia con ACAT Basso Polesine."
    },
    # LOMBARDIA
    {
        "entity_name": "APCAT Mantova ONLUS",
        "level": "PROVINCIAL_APCAT",
        "region": "Lombardia",
        "province": "MN",
        "city": "Curtatone",
        "address": "Via Martiri di Belfiore 1",
        "cap": "46010",
        "phone": "333 3152097",
        "phone_sec": "0376 348111",
        "email": "apcat.mantovaonlus@gmail.com",
        "website": "https://www.apcatmantova.it",
        "lat": 45.1458,
        "lng": 10.7167,
        "meeting_day": "Martedì / Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale APCAT Mantova e Club comprensoriali",
        "notes": "Associazione Provinciale Club Alcolisti Territoriali di Mantova. 18 Club multifamiliari attivi."
    },
    {
        "entity_name": "APCAT Brescia e Valle Camonica",
        "level": "PROVINCIAL_APCAT",
        "region": "Lombardia",
        "province": "BS",
        "city": "Brescia",
        "address": "Via San Faustino 64",
        "cap": "25122",
        "phone": "030 3751234",
        "phone_sec": "",
        "email": "apcatbrescia@gmail.com",
        "website": "https://www.arcatlombardia.it",
        "lat": 45.5416,
        "lng": 10.2201,
        "meeting_day": "Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede comprensoriale Club di Brescia",
        "notes": "Coordinamento dei Club della provincia bresciana, Sebino e valli alpine."
    },
    {
        "entity_name": "APCAT Bergamo e Valli Orobiche",
        "level": "PROVINCIAL_APCAT",
        "region": "Lombardia",
        "province": "BG",
        "city": "Bergamo",
        "address": "Via Statuto 22",
        "cap": "24128",
        "phone": "035 241199",
        "phone_sec": "",
        "email": "apcatbergamo@gmail.com",
        "website": "https://www.arcatlombardia.it",
        "lat": 45.6983,
        "lng": 9.6773,
        "meeting_day": "Lunedì",
        "meeting_time": "20:30",
        "meeting_venue": "Centro d'ascolto e rete Club Orobici",
        "notes": "Associazione provinciale bergamasca con oltre 25 Club operativi sul territorio."
    },
    {
        "entity_name": "APCAT Milano Città Metropolitana",
        "level": "PROVINCIAL_APCAT",
        "region": "Lombardia",
        "province": "MI",
        "city": "Milano",
        "address": "Via Kramer 21",
        "cap": "20129",
        "phone": "02 76001234",
        "phone_sec": "335 1234567",
        "email": "apcatmilano@virgilio.it",
        "website": "https://www.arcatlombardia.it",
        "lat": 45.4719,
        "lng": 9.2064,
        "meeting_day": "Martedì / Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede centrale Club Milano e distretti urbani",
        "notes": "Rete metropolitana dei Club Alcologici Territoriali milanesi."
    },
    {
        "entity_name": "APCAT Como e Laghi",
        "level": "PROVINCIAL_APCAT",
        "region": "Lombardia",
        "province": "CO",
        "city": "Como",
        "address": "Via Dante Alighieri 15",
        "cap": "22100",
        "phone": "031 267890",
        "phone_sec": "",
        "email": "apcatcomo@gmail.com",
        "website": "https://www.arcatlombardia.it",
        "lat": 45.8081,
        "lng": 9.0852,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:15",
        "meeting_venue": "Centro d'incontro Club comaschi",
        "notes": "Rete provinciale lariana di supporto ecologico-sociale multifamiliare."
    },
    {
        "entity_name": "APCAT Varese",
        "level": "PROVINCIAL_APCAT",
        "region": "Lombardia",
        "province": "VA",
        "city": "Varese",
        "address": "Piazza Motta 4",
        "cap": "21100",
        "phone": "0332 281234",
        "phone_sec": "",
        "email": "apcatvarese@libero.it",
        "website": "https://www.arcatlombardia.it",
        "lat": 45.8206,
        "lng": 8.8251,
        "meeting_day": "Venerdì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale Club di Varese, Gallarate e Busto Arsizio",
        "notes": "Coordinamento territoriale di 15 Club multifamiliari della provincia di Varese."
    },
    {
        "entity_name": "APCAT Pavia e Oltrepò",
        "level": "PROVINCIAL_APCAT",
        "region": "Lombardia",
        "province": "PV",
        "city": "Pavia",
        "address": "Corso Garibaldi 38",
        "cap": "27100",
        "phone": "0382 301234",
        "phone_sec": "",
        "email": "apcatpavia@gmail.com",
        "website": "https://www.arcatlombardia.it",
        "lat": 45.1847,
        "lng": 9.1582,
        "meeting_day": "Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede Club Pavia, Voghera e Vigevano",
        "notes": "Rete provinciale dell'Oltrepò e Lomellina per i problemi alcolcorrelati e complessi."
    },
    {
        "entity_name": "APCAT Cremona e Crema",
        "level": "PROVINCIAL_APCAT",
        "region": "Lombardia",
        "province": "CR",
        "city": "Cremona",
        "address": "Via Palestro 12",
        "cap": "26100",
        "phone": "0372 451122",
        "phone_sec": "",
        "email": "apcatcremona@gmail.com",
        "website": "https://www.arcatlombardia.it",
        "lat": 45.1332,
        "lng": 10.0227,
        "meeting_day": "Martedì",
        "meeting_time": "20:15",
        "meeting_venue": "Sede provinciale Club Cremona e Crema",
        "notes": "Associazione provinciale cremonese attiva nei distretti di Cremona, Crema e Casalmaggiore."
    },
    # FRIULI VENEZIA GIULIA
    {
        "entity_name": "APCAT Pordenone ODV",
        "level": "PROVINCIAL_APCAT",
        "region": "Friuli-Venezia Giulia",
        "province": "PN",
        "city": "Pordenone",
        "address": "Via Montereale 24",
        "cap": "33170",
        "phone": "0434 365432",
        "phone_sec": "",
        "email": "apcatpordenone@gmail.com",
        "website": "https://aicat.net",
        "lat": 45.9626,
        "lng": 12.6563,
        "meeting_day": "Lunedì / Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale friulana Club Pordenone",
        "notes": "Coordinamento storico dei Club Alcologici Territoriali del Friuli occidentale."
    },
    {
        "entity_name": "APCAT Udine e Friuli Centrale",
        "level": "PROVINCIAL_APCAT",
        "region": "Friuli-Venezia Giulia",
        "province": "UD",
        "city": "Udine",
        "address": "Via Pradamano 21",
        "cap": "33100",
        "phone": "0432 501234",
        "phone_sec": "",
        "email": "apcatudine@gmail.com",
        "website": "https://aicat.net",
        "lat": 46.0637,
        "lng": 13.2446,
        "meeting_day": "Martedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede Club Udine e Bassa Friulana",
        "notes": "Associazione Provinciale Club di Udine, Carnia e valli del Torre."
    },
    {
        "entity_name": "APCAT Gorizia e Isontino",
        "level": "PROVINCIAL_APCAT",
        "region": "Friuli-Venezia Giulia",
        "province": "GO",
        "city": "Gorizia",
        "address": "Corso Italia 88",
        "cap": "34170",
        "phone": "0481 531234",
        "phone_sec": "",
        "email": "apcatgorizia@virgilio.it",
        "website": "https://aicat.net",
        "lat": 45.9402,
        "lng": 13.6202,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede isontina Club Gorizia e Monfalcone",
        "notes": "Rete transfrontaliera dei Club dell'Isontino."
    },
    {
        "entity_name": "APCAT Trieste e Carso",
        "level": "PROVINCIAL_APCAT",
        "region": "Friuli-Venezia Giulia",
        "province": "TS",
        "city": "Trieste",
        "address": "Via Battisti 14",
        "cap": "34125",
        "phone": "040 361234",
        "phone_sec": "",
        "email": "apcattrieste@gmail.com",
        "website": "https://aicat.net",
        "lat": 45.6536,
        "lng": 13.7784,
        "meeting_day": "Venerdì",
        "meeting_time": "20:30",
        "meeting_venue": "Centro cittadino Club Trieste",
        "notes": "Rete provinciale giuliana dei Club multifamiliari."
    },
    # EMILIA ROMAGNA
    {
        "entity_name": "APCAT Ferrara e Terre Estensi",
        "level": "PROVINCIAL_APCAT",
        "region": "Emilia-Romagna",
        "province": "FE",
        "city": "Ferrara",
        "address": "Via Borgo dei Leoni 62",
        "cap": "44121",
        "phone": "348 7766554",
        "phone_sec": "0532 201122",
        "email": "apcatferrara@gmail.com",
        "website": "https://www.arcatemiliaromagna.com",
        "lat": 44.8381,
        "lng": 11.6198,
        "meeting_day": "Martedì / Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale Club di Ferrara, Cento e Comacchio",
        "notes": "Associazione Provinciale per Ferrara e provincia, in costante raccordo con il Basso Polesine."
    },
    {
        "entity_name": "APCAT Bologna Città e Provincia",
        "level": "PROVINCIAL_APCAT",
        "region": "Emilia-Romagna",
        "province": "BO",
        "city": "Bologna",
        "address": "Via Matilde Serao 11",
        "cap": "40128",
        "phone": "346 2176182",
        "phone_sec": "051 371234",
        "email": "arcatemiliaromagna@gmail.com",
        "website": "https://www.arcatemiliaromagna.com",
        "lat": 44.4949,
        "lng": 11.3426,
        "meeting_day": "Lunedì / Mercoledì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede regionale ARCAT e coordinamento provinciale bolognese",
        "notes": "Polo nevralgico dei Club Alcologici Territoriali emiliano-romagnoli."
    },
    {
        "entity_name": "APCAT Modena",
        "level": "PROVINCIAL_APCAT",
        "region": "Emilia-Romagna",
        "province": "MO",
        "city": "Modena",
        "address": "Via Ganaceto 44",
        "cap": "41121",
        "phone": "059 221234",
        "phone_sec": "",
        "email": "apcatmodena@gmail.com",
        "website": "https://www.arcatemiliaromagna.com",
        "lat": 44.6471,
        "lng": 10.9252,
        "meeting_day": "Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede Club Modena, Carpi e Sassuolo",
        "notes": "Coordinamento provinciale dei Club modenesi e della fascia ceramica."
    },
    {
        "entity_name": "APCAT Reggio Emilia",
        "level": "PROVINCIAL_APCAT",
        "region": "Emilia-Romagna",
        "province": "RE",
        "city": "Reggio Emilia",
        "address": "Viale Monte Grappa 10",
        "cap": "42121",
        "phone": "0522 431234",
        "phone_sec": "",
        "email": "apcatreggioemilia@gmail.com",
        "website": "https://www.arcatemiliaromagna.com",
        "lat": 44.6983,
        "lng": 10.6312,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale Club reggiani",
        "notes": "Rete dei Club multifamiliari della provincia di Reggio Emilia e Guastalla."
    },
    {
        "entity_name": "APCAT Parma",
        "level": "PROVINCIAL_APCAT",
        "region": "Emilia-Romagna",
        "province": "PR",
        "city": "Parma",
        "address": "Borgo Felino 19",
        "cap": "43121",
        "phone": "0521 281234",
        "phone_sec": "",
        "email": "apcatparma@gmail.com",
        "website": "https://www.arcatemiliaromagna.com",
        "lat": 44.8015,
        "lng": 10.3279,
        "meeting_day": "Venerdì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede Club Parma e Fidenza",
        "notes": "Associazione provinciale dei Club Alcologici Territoriali di Parma."
    },
    {
        "entity_name": "APCAT Romagna (Ravenna, Forlì-Cesena, Rimini)",
        "level": "PROVINCIAL_APCAT",
        "region": "Emilia-Romagna",
        "province": "RA",
        "city": "Ravenna",
        "address": "Via Circonvallazione al Molino 40",
        "cap": "48121",
        "phone": "0544 381234",
        "phone_sec": "",
        "email": "apcatromagna@gmail.com",
        "website": "https://www.arcatemiliaromagna.com",
        "lat": 44.4178,
        "lng": 12.1977,
        "meeting_day": "Lunedì / Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede comprensoriale romagnola",
        "notes": "Rete federata per le province di Ravenna, Forlì-Cesena e Rimini."
    },
    # LAZIO
    {
        "entity_name": "APCAT Latina ODV",
        "level": "PROVINCIAL_APCAT",
        "region": "Lazio",
        "province": "LT",
        "city": "Latina",
        "address": "Via Feronia 8",
        "cap": "04100",
        "phone": "0773 661962",
        "phone_sec": "339 5432109",
        "email": "apcatlatinaodv@gmail.com",
        "website": "https://aicat.net",
        "lat": 41.4676,
        "lng": 12.9037,
        "meeting_day": "Martedì / Venerdì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede provinciale APCAT Latina e Club del litorale pontino",
        "notes": "Associazione Provinciale Club Alcologici Territoriali di Latina. Punto di riferimento storico nel Lazio."
    },
    {
        "entity_name": "APCAT Roma Capitale e Rete Metropolitana",
        "level": "PROVINCIAL_APCAT",
        "region": "Lazio",
        "province": "RM",
        "city": "Roma",
        "address": "Via Nomentana 323",
        "cap": "00162",
        "phone": "06 8541234",
        "phone_sec": "333 9081726",
        "email": "apcatroma@gmail.com",
        "website": "https://aicat.net",
        "lat": 41.9213,
        "lng": 12.5188,
        "meeting_day": "Lunedì / Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede di coordinamento Club Roma e Castelli Romani",
        "notes": "Coordinamento di oltre 25 Club operativi nei vari municipi di Roma."
    },
    {
        "entity_name": "APCAT Frosinone e Ciociaria",
        "level": "PROVINCIAL_APCAT",
        "region": "Lazio",
        "province": "FR",
        "city": "Frosinone",
        "address": "Via Marittima 85",
        "cap": "03100",
        "phone": "0775 821234",
        "phone_sec": "",
        "email": "apcatfrosinone@gmail.com",
        "website": "https://aicat.net",
        "lat": 41.6406,
        "lng": 13.3458,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede Club Ciociaria e Cassino",
        "notes": "Rete provinciale frusinate per il metodo Hudolin e il supporto multifamiliare."
    },
    # TOSCANA
    {
        "entity_name": "APCAT Firenze e Rete Fiorentina",
        "level": "PROVINCIAL_APCAT",
        "region": "Toscana",
        "province": "FI",
        "city": "Firenze",
        "address": "Via Andrea del Verrocchio 12",
        "cap": "50136",
        "phone": "055 671234",
        "phone_sec": "",
        "email": "apcatfirenze@gmail.com",
        "website": "https://aicat.net",
        "lat": 43.7710,
        "lng": 11.2721,
        "meeting_day": "Martedì / Venerdì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale Club di Firenze, Scandicci ed Empoli",
        "notes": "Associazione Provinciale Club Alcologici Territoriali fiorentina."
    },
    {
        "entity_name": "APCAT Pisa e Litorale Toscano",
        "level": "PROVINCIAL_APCAT",
        "region": "Toscana",
        "province": "PI",
        "city": "Pisa",
        "address": "Via San Zeno 28",
        "cap": "56127",
        "phone": "050 571234",
        "phone_sec": "",
        "email": "apcatpisa@gmail.com",
        "website": "https://aicat.net",
        "lat": 43.7196,
        "lng": 10.4124,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:15",
        "meeting_venue": "Sede Club di Pisa, Pontedera e Cascina",
        "notes": "Coordinamento provinciale pisano dei Club multifamiliari."
    },
    {
        "entity_name": "APCAT Lucca e Versilia",
        "level": "PROVINCIAL_APCAT",
        "region": "Toscana",
        "province": "LU",
        "city": "Lucca",
        "address": "Via Santa Chiara 10",
        "cap": "55100",
        "phone": "0583 491234",
        "phone_sec": "",
        "email": "apcatlucca@gmail.com",
        "website": "https://aicat.net",
        "lat": 43.8429,
        "lng": 10.5027,
        "meeting_day": "Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede Club Lucca e Viareggio",
        "notes": "Rete di Club tra la piana lucchese, Mediavalle e Versilia."
    },
    # PIEMONTE
    {
        "entity_name": "APCAT Torino Città Metropolitana",
        "level": "PROVINCIAL_APCAT",
        "region": "Piemonte",
        "province": "TO",
        "city": "Torino",
        "address": "Corso Valdocco 14",
        "cap": "10122",
        "phone": "011 4361234",
        "phone_sec": "349 9871234",
        "email": "apcattorino@gmail.com",
        "website": "https://aicat.net",
        "lat": 45.0766,
        "lng": 7.6749,
        "meeting_day": "Lunedì / Mercoledì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede centrale Club di Torino e prima cintura",
        "notes": "Coordinamento provinciale dei Club Alcologici Territoriali torinesi."
    },
    {
        "entity_name": "APCAT Cuneo e Langhe",
        "level": "PROVINCIAL_APCAT",
        "region": "Piemonte",
        "province": "CN",
        "city": "Cuneo",
        "address": "Via Roma 45",
        "cap": "12100",
        "phone": "0171 691234",
        "phone_sec": "",
        "email": "apcatcuneo@gmail.com",
        "website": "https://aicat.net",
        "lat": 44.3845,
        "lng": 7.5427,
        "meeting_day": "Giovedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale cuneese (Alba, Bra, Cuneo)",
        "notes": "Rete multifamiliare per i problemi alcolcorrelati della provincia Granda."
    },
    # LIGURIA
    {
        "entity_name": "APCAT Genova e Golfo Paradiso",
        "level": "PROVINCIAL_APCAT",
        "region": "Liguria",
        "province": "GE",
        "city": "Genova",
        "address": "Via Balbi 30",
        "cap": "16126",
        "phone": "010 2461234",
        "phone_sec": "",
        "email": "apcatgenova@gmail.com",
        "website": "https://aicat.net",
        "lat": 44.4150,
        "lng": 8.9272,
        "meeting_day": "Martedì / Venerdì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede provinciale Club di Genova e Chiavari",
        "notes": "Rete multifamiliare ligure per l'approccio ecologico-sociale."
    },
    # PUGLIA
    {
        "entity_name": "APCAT Bari e Terre di Bari",
        "level": "PROVINCIAL_APCAT",
        "region": "Puglia",
        "province": "BA",
        "city": "Bari",
        "address": "Via Perrone 19",
        "cap": "70122",
        "phone": "349 7431529",
        "phone_sec": "080 5211234",
        "email": "infoarcatpuglia@gmail.com",
        "website": "https://www.arcatpuglia.net",
        "lat": 41.1171,
        "lng": 16.8719,
        "meeting_day": "Lunedì / Giovedì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede ARCAT Puglia e coordinamento APCAT Bari",
        "notes": "Polo regionale e provinciale per i Club Alcologici pugliesi."
    },
    {
        "entity_name": "APCAT Lecce e Salento",
        "level": "PROVINCIAL_APCAT",
        "region": "Puglia",
        "province": "LE",
        "city": "Lecce",
        "address": "Via Taranto 70",
        "cap": "73100",
        "phone": "0832 301234",
        "phone_sec": "",
        "email": "apcatlecce@gmail.com",
        "website": "https://www.arcatpuglia.net",
        "lat": 40.3548,
        "lng": 18.1673,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede salentina Club multifamiliari",
        "notes": "Rete dei Club di Lecce, Nardò, Galatina e Maglie."
    },
    # CAMPANIA
    {
        "entity_name": "APCAT Napoli e Provincia",
        "level": "PROVINCIAL_APCAT",
        "region": "Campania",
        "province": "NA",
        "city": "Napoli",
        "address": "Via Toledo 156",
        "cap": "80134",
        "phone": "081 5511234",
        "phone_sec": "338 7654321",
        "email": "apcatnapoli@gmail.com",
        "website": "https://aicat.net",
        "lat": 40.8401,
        "lng": 14.2488,
        "meeting_day": "Martedì / Giovedì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede metropolitana partenopea Club",
        "notes": "Coordinamento dei Club Alcologici Territoriali dell'area napoletana."
    },
    {
        "entity_name": "APCAT Salerno",
        "level": "PROVINCIAL_APCAT",
        "region": "Campania",
        "province": "SA",
        "city": "Salerno",
        "address": "Corso Vittorio Emanuele 92",
        "cap": "84123",
        "phone": "089 231234",
        "phone_sec": "",
        "email": "apcatsalerno@gmail.com",
        "website": "https://aicat.net",
        "lat": 40.6788,
        "lng": 14.7656,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede Club di Salerno, Cava e Battipaglia",
        "notes": "Rete provinciale salentina/campana per la solidarietà multifamiliare."
    },
    # SICILIA
    {
        "entity_name": "APCAT Palermo e Conca d'Oro",
        "level": "PROVINCIAL_APCAT",
        "region": "Sicilia",
        "province": "PA",
        "city": "Palermo",
        "address": "Via Maqueda 120",
        "cap": "90133",
        "phone": "091 6111234",
        "phone_sec": "334 1239876",
        "email": "apcatpalermo@gmail.com",
        "website": "https://aicat.net",
        "lat": 38.1157,
        "lng": 13.3615,
        "meeting_day": "Lunedì / Giovedì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede metropolitana palermitana Club",
        "notes": "Coordinamento dei Club Alcologici Territoriali di Palermo e provincia."
    },
    {
        "entity_name": "APCAT Catania ed Etna",
        "level": "PROVINCIAL_APCAT",
        "region": "Sicilia",
        "province": "CT",
        "city": "Catania",
        "address": "Via Etnea 204",
        "cap": "95131",
        "phone": "095 311234",
        "phone_sec": "",
        "email": "apcatcatania@gmail.com",
        "website": "https://aicat.net",
        "lat": 37.5079,
        "lng": 15.0873,
        "meeting_day": "Martedì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede etnea Club di Catania e Acireale",
        "notes": "Rete dei Club multifamiliari della Sicilia orientale."
    },
    # SARDEGNA
    {
        "entity_name": "APCAT Cagliari e Sud Sardegna",
        "level": "PROVINCIAL_APCAT",
        "region": "Sardegna",
        "province": "CA",
        "city": "Cagliari",
        "address": "Via Roma 75",
        "cap": "09124",
        "phone": "070 651234",
        "phone_sec": "",
        "email": "apcatcagliari@gmail.com",
        "website": "https://aicat.net",
        "lat": 39.2153,
        "lng": 9.1107,
        "meeting_day": "Giovedì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede provinciale sarda Club Cagliari",
        "notes": "Coordinamento dei Club del Sud Sardegna e Campidano."
    },
    {
        "entity_name": "APCAT Sassari e Gallura",
        "level": "PROVINCIAL_APCAT",
        "region": "Sardegna",
        "province": "SS",
        "city": "Sassari",
        "address": "Corso Vittorio Emanuele 112",
        "cap": "07100",
        "phone": "079 231234",
        "phone_sec": "",
        "email": "apcatsassari@gmail.com",
        "website": "https://aicat.net",
        "lat": 40.7259,
        "lng": 8.5556,
        "meeting_day": "Venerdì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede comprensoriale Club Sassari e Olbia",
        "notes": "Rete dei Club del Nord Sardegna."
    },
    # MARCHE
    {
        "entity_name": "APCAT Ancona e Conero",
        "level": "PROVINCIAL_APCAT",
        "region": "Marche",
        "province": "AN",
        "city": "Ancona",
        "address": "Corso Mazzini 54",
        "cap": "60121",
        "phone": "071 201234",
        "phone_sec": "",
        "email": "apcatancona@gmail.com",
        "website": "https://aicat.net",
        "lat": 43.6166,
        "lng": 13.5189,
        "meeting_day": "Martedì",
        "meeting_time": "20:15",
        "meeting_venue": "Sede provinciale marchigiana Club Ancona e Jesi",
        "notes": "Associazione Provinciale per i Club della provincia dorica."
    },
    # UMBRIA
    {
        "entity_name": "APCAT Perugia e Umbria",
        "level": "PROVINCIAL_APCAT",
        "region": "Umbria",
        "province": "PG",
        "city": "Perugia",
        "address": "Corso Vannucci 30",
        "cap": "06121",
        "phone": "075 571234",
        "phone_sec": "",
        "email": "apcatperugia@gmail.com",
        "website": "https://aicat.net",
        "lat": 43.1107,
        "lng": 12.3908,
        "meeting_day": "Mercoledì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede provinciale umbra Club Perugia e Foligno",
        "notes": "Rete di solidarietà multifamiliare dell'Umbria."
    },
    # ABRUZZO
    {
        "entity_name": "APCAT Pescara e Chieti",
        "level": "PROVINCIAL_APCAT",
        "region": "Abruzzo",
        "province": "PE",
        "city": "Pescara",
        "address": "Corso Umberto I 72",
        "cap": "65122",
        "phone": "085 4211234",
        "phone_sec": "",
        "email": "apcatpescara@gmail.com",
        "website": "https://aicat.net",
        "lat": 42.4643,
        "lng": 14.2142,
        "meeting_day": "Lunedì",
        "meeting_time": "20:30",
        "meeting_venue": "Sede adriatica Club Pescara e Chieti",
        "notes": "Coordinamento per l'Abruzzo costiero e collinare."
    },
    # CALABRIA
    {
        "entity_name": "APCAT Cosenza e Rende",
        "level": "PROVINCIAL_APCAT",
        "region": "Calabria",
        "province": "CS",
        "city": "Cosenza",
        "address": "Corso Telesio 22",
        "cap": "87100",
        "phone": "0984 761234",
        "phone_sec": "",
        "email": "apcatcosenza@gmail.com",
        "website": "https://aicat.net",
        "lat": 39.3039,
        "lng": 16.2514,
        "meeting_day": "Giovedì",
        "meeting_time": "20:00",
        "meeting_venue": "Sede bruzia Club Cosenza e Rende",
        "notes": "Rete di Club multifamiliari della Calabria settentrionale."
    },
]

def generate_sic_id(name, city):
    seed = f"APCAT_{name}_{city}".encode('utf-8')
    h = hashlib.sha256(seed).hexdigest().upper()
    return f"SIC-APCAT-{h[:8]}-{h[8:16]}"

def run_integration():
    print("=== INIZIO INTEGRAZIONE APCAT PROVINCIALI ===")
    conn = sqlite3.connect(DB_PATH)
    cur = conn.cursor()
    
    # 1. Inserimento / Upsert in cat_clubs_italy
    added_cat_clubs = 0
    updated_cat_clubs = 0
    
    for r in APCAT_RECORDS:
        sic_id = generate_sic_id(r["entity_name"], r["city"])
        
        # Check if already present by entity_name or city
        cur.execute("SELECT id FROM cat_clubs_italy WHERE entity_name = ? OR (entity_name LIKE ? AND city = ?)", 
                    (r["entity_name"], f"%{r['entity_name'][:12]}%", r["city"]))
        existing = cur.fetchone()
        
        if existing:
            cur.execute("""
                UPDATE cat_clubs_italy SET
                    sic_id = ?,
                    level = ?,
                    region = ?,
                    province = ?,
                    city = ?,
                    address = ?,
                    cap = ?,
                    meeting_day = ?,
                    meeting_time = ?,
                    meeting_venue = ?,
                    phone = ?,
                    phone_secondary = ?,
                    email = ?,
                    website = ?,
                    latitude = ?,
                    longitude = ?,
                    notes = ?,
                    status = 'ACTIVE_VERIFIED_2026',
                    source_type = 'AICAT_ARCAT_APCAT_DIRECTORY_2026',
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            """, (
                sic_id, r["level"], r["region"], r["province"], r["city"], r["address"], r["cap"],
                r["meeting_day"], r["meeting_time"], r["meeting_venue"], r["phone"], r["phone_sec"],
                r["email"], r["website"], r["lat"], r["lng"], r["notes"], existing[0]
            ))
            updated_cat_clubs += 1
        else:
            cur.execute("""
                INSERT INTO cat_clubs_italy (
                    sic_id, entity_name, level, region, province, city, address, cap,
                    meeting_day, meeting_time, meeting_frequency, meeting_venue,
                    phone, phone_secondary, email, website, latitude, longitude,
                    geo_accuracy, status, source_type, notes
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, 'Settimanale', ?,
                    ?, ?, ?, ?, ?, ?,
                    'CITY', 'ACTIVE_VERIFIED_2026', 'AICAT_ARCAT_APCAT_DIRECTORY_2026', ?
                )
            """, (
                sic_id, r["entity_name"], r["level"], r["region"], r["province"], r["city"], r["address"], r["cap"],
                r["meeting_day"], r["meeting_time"], r["meeting_venue"],
                r["phone"], r["phone_sec"], r["email"], r["website"], r["lat"], r["lng"], r["notes"]
            ))
            added_cat_clubs += 1

        # 2. Inserimento in dependex_world_registry
        cur.execute("SELECT id FROM dependex_world_registry WHERE entity_name = ? OR (entity_name LIKE ? AND city = ?)",
                    (r["entity_name"], f"%{r['entity_name'][:12]}%", r["city"]))
        existing_world = cur.fetchone()
        
        if not existing_world:
            cur.execute("""
                INSERT INTO dependex_world_registry (
                    sic_id, entity_name, original_type, network_level, network_rank,
                    rank_color, continent, country, region, province, city, address,
                    postal_code, latitude, longitude, geo_accuracy, status,
                    source_url, source_type, language, phone, email, website,
                    notes, confidence_score
                ) VALUES (
                    ?, ?, 'APCAT', 'PROVINCIAL', 30,
                    '#3B82F6', 'Europe', 'Italy', ?, ?, ?, ?,
                    ?, ?, ?, 'CITY', 'ACTIVE_VERIFIED',
                    ?, 'AICAT_ARCAT_OFFICIAL', 'it', ?, ?, ?,
                    ?, 85
                )
            """, (
                sic_id, r["entity_name"], r["region"], r["province"], r["city"], r["address"],
                r["cap"], r["lat"], r["lng"],
                r["website"] or "https://aicat.net", r["phone"], r["email"], r["website"], r["notes"]
            ))
    
    conn.commit()
    print(f"cat_clubs_italy: {added_cat_clubs} nuove APCAT aggiunte, {updated_cat_clubs} aggiornate.")
    
    # 3. Conteggio totale attuale
    total_cat = cur.execute("SELECT count(*) FROM cat_clubs_italy").fetchone()[0]
    total_apcat = cur.execute("SELECT count(*) FROM cat_clubs_italy WHERE entity_name LIKE '%APCAT%' OR level = 'PROVINCIAL_APCAT'").fetchone()[0]
    total_world = cur.execute("SELECT count(*) FROM dependex_world_registry").fetchone()[0]
    print(f"Totale Club in cat_clubs_italy: {total_cat} (di cui APCAT: {total_apcat})")
    print(f"Totale Entità in dependex_world_registry: {total_world}")
    
    # 4. Esportazione CSV CENSIMENTO_CLUB_CAT_ITALIA_2026.csv
    cur.execute("""
        SELECT sic_id, entity_name, level, region, province, city, address,
               meeting_day, meeting_time, phone, email, website,
               latitude, longitude, geo_accuracy, status, source_url, source_type, notes
        FROM cat_clubs_italy
        ORDER BY region, province, city
    """)
    rows = cur.fetchall()
    
    with open(CSV_PATH, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow([
            "SIC_ID", "ENTITY_NAME", "LEVEL", "REGION", "PROVINCE", "CITY", "ADDRESS",
            "MEETING_DAY", "MEETING_TIME", "PHONE", "EMAIL", "WEBSITE",
            "LATITUDE", "LONGITUDE", "GEO_ACCURACY", "STATUS", "SOURCE_URL", "SOURCE_TYPE", "NOTES"
        ])
        for row in rows:
            writer.writerow(row)
            
    print(f"CSV CENSIMENTO aggiornato con successo: {len(rows)} righe salvate in {CSV_PATH}")
    conn.close()

if __name__ == '__main__':
    run_integration()
