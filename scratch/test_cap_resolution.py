import json
import re

CAP2_MAP = {
    '00': ('Roma', 'RM', 'Lazio', 41.9028, 12.4964),
    '01': ('Viterbo', 'VT', 'Lazio', 42.4207, 12.1077),
    '02': ('Rieti', 'RI', 'Lazio', 42.4041, 12.8628),
    '03': ('Frosinone', 'FR', 'Lazio', 41.6433, 13.3444),
    '04': ('Latina', 'LT', 'Lazio', 41.4676, 12.9037),
    '05': ('Terni', 'TR', 'Umbria', 42.5641, 12.6427),
    '06': ('Perugia', 'PG', 'Umbria', 43.1107, 12.3908),
    '07': ('Sassari', 'SS', 'Sardegna', 40.7259, 8.5556),
    '08': ('Nuoro', 'NU', 'Sardegna', 40.3204, 9.3308),
    '09': ('Cagliari', 'CA', 'Sardegna', 39.2238, 9.1217),
    '10': ('Torino', 'TO', 'Piemonte', 45.0703, 7.6869),
    '11': ('Aosta', 'AO', "Valle d'Aosta", 45.7370, 7.3190),
    '12': ('Cuneo', 'CN', 'Piemonte', 44.3845, 7.5427),
    '13': ('Vercelli', 'VC', 'Piemonte', 45.3217, 8.4190),
    '14': ('Asti', 'AT', 'Piemonte', 44.8991, 8.2045),
    '15': ('Alessandria', 'AL', 'Piemonte', 44.9129, 8.6152),
    '16': ('Genova', 'GE', 'Liguria', 44.4056, 8.9463),
    '17': ('Savona', 'SV', 'Liguria', 44.3079, 8.4811),
    '18': ('Imperia', 'IM', 'Liguria', 43.8872, 8.0289),
    '19': ('La Spezia', 'SP', 'Liguria', 44.1025, 9.8241),
    '20': ('Milano', 'MI', 'Lombardia', 45.4642, 9.1900),
    '21': ('Varese', 'VA', 'Lombardia', 45.8206, 8.8251),
    '22': ('Como', 'CO', 'Lombardia', 45.8081, 9.0852),
    '23': ('Sondrio', 'SO', 'Lombardia', 46.1689, 9.8693),
    '24': ('Bergamo', 'BG', 'Lombardia', 45.6983, 9.6773),
    '25': ('Brescia', 'BS', 'Lombardia', 45.5416, 10.2118),
    '26': ('Cremona', 'CR', 'Lombardia', 45.1332, 10.0248),
    '27': ('Pavia', 'PV', 'Lombardia', 45.1847, 9.1582),
    '28': ('Novara', 'NO', 'Piemonte', 45.4469, 8.6212),
    '29': ('Piacenza', 'PC', 'Emilia-Romagna', 45.0526, 9.6934),
    '30': ('Venezia', 'VE', 'Veneto', 45.4408, 12.3155),
    '31': ('Treviso', 'TV', 'Veneto', 45.6669, 12.2430),
    '32': ('Belluno', 'BL', 'Veneto', 46.1425, 12.2167),
    '33': ('Udine', 'UD', 'Friuli-Venezia Giulia', 46.0711, 13.2346),
    '34': ('Trieste', 'TS', 'Friuli-Venezia Giulia', 45.6495, 13.7768),
    '35': ('Padova', 'PD', 'Veneto', 45.4064, 11.8768),
    '36': ('Vicenza', 'VI', 'Veneto', 45.5455, 11.5355),
    '37': ('Verona', 'VR', 'Veneto', 45.4384, 10.9916),
    '38': ('Trento', 'TN', 'Trentino-Alto Adige', 46.0679, 11.1211),
    '39': ('Bolzano', 'BZ', 'Trentino-Alto Adige', 46.4983, 11.3548),
    '40': ('Bologna', 'BO', 'Emilia-Romagna', 44.4949, 11.3426),
    '41': ('Modena', 'MO', 'Emilia-Romagna', 44.6471, 10.9252),
    '42': ('Reggio Emilia', 'RE', 'Emilia-Romagna', 44.6982, 10.6312),
    '43': ('Parma', 'PR', 'Emilia-Romagna', 44.8015, 10.3279),
    '44': ('Ferrara', 'FE', 'Emilia-Romagna', 44.8381, 11.6198),
    '45': ('Rovigo', 'RO', 'Veneto', 45.0703, 11.7900),
    '46': ('Mantova', 'MN', 'Lombardia', 45.1564, 10.7914),
    '47': ('Forlì-Cesena', 'FC', 'Emilia-Romagna', 44.2227, 12.0407),
    '48': ('Ravenna', 'RA', 'Emilia-Romagna', 44.4184, 12.2035),
    '50': ('Firenze', 'FI', 'Toscana', 43.7696, 11.2558),
    '51': ('Pistoia', 'PT', 'Toscana', 43.9333, 10.9167),
    '52': ('Arezzo', 'AR', 'Toscana', 43.4632, 11.8796),
    '53': ('Siena', 'SI', 'Toscana', 43.3188, 11.3308),
    '54': ('Massa', 'MS', 'Toscana', 44.0367, 10.1417),
    '55': ('Lucca', 'LU', 'Toscana', 43.8430, 10.5079),
    '56': ('Pisa', 'PI', 'Toscana', 43.7228, 10.4017),
    '57': ('Livorno', 'LI', 'Toscana', 43.5485, 10.3106),
    '58': ('Grosseto', 'GR', 'Toscana', 42.7636, 11.1119),
    '59': ('Prato', 'PO', 'Toscana', 43.8777, 11.1022),
    '60': ('Ancona', 'AN', 'Marche', 43.6158, 13.5189),
    '61': ('Pesaro', 'PU', 'Marche', 43.9102, 12.9133),
    '62': ('Macerata', 'MC', 'Marche', 43.3002, 13.4534),
    '63': ('Ascoli Piceno', 'AP', 'Marche', 42.8550, 13.5764),
    '64': ('Teramo', 'TE', 'Abruzzo', 42.6589, 13.7044),
    '65': ('Pescara', 'PE', 'Abruzzo', 42.4618, 14.2161),
    '66': ('Chieti', 'CH', 'Abruzzo', 42.3510, 14.1675),
    '67': ("L'Aquila", 'AQ', 'Abruzzo', 42.3498, 13.3995),
    '70': ('Bari', 'BA', 'Puglia', 41.1171, 16.8719),
    '71': ('Foggia', 'FG', 'Puglia', 41.4622, 15.5447),
    '72': ('Brindisi', 'BR', 'Puglia', 40.6327, 17.9418),
    '73': ('Lecce', 'LE', 'Puglia', 40.3515, 18.1750),
    '74': ('Taranto', 'TA', 'Puglia', 40.4644, 17.2470),
    '75': ('Matera', 'MT', 'Basilicata', 40.6664, 16.6043),
    '76': ('Barletta', 'BT', 'Puglia', 41.3197, 16.2827),
    '80': ('Napoli', 'NA', 'Campania', 40.8518, 14.2681),
    '81': ('Caserta', 'CE', 'Campania', 41.0726, 14.3323),
    '82': ('Benevento', 'BN', 'Campania', 41.1297, 14.7824),
    '83': ('Avellino', 'AV', 'Campania', 40.9148, 14.7906),
    '84': ('Salerno', 'SA', 'Campania', 40.6824, 14.7681),
    '85': ('Potenza', 'PZ', 'Basilicata', 40.6404, 15.8056),
    '86': ('Campobasso', 'CB', 'Molise', 41.5603, 14.6627),
    '87': ('Cosenza', 'CS', 'Calabria', 39.3039, 16.2518),
    '88': ('Catanzaro', 'CZ', 'Calabria', 38.9098, 16.5877),
    '89': ('Reggio Calabria', 'RC', 'Calabria', 38.1113, 15.6473),
    '90': ('Palermo', 'PA', 'Sicilia', 38.1157, 13.3615),
    '91': ('Trapani', 'TP', 'Sicilia', 38.0176, 12.5365),
    '92': ('Agrigento', 'AG', 'Sicilia', 37.3111, 13.5765),
    '93': ('Caltanissetta', 'CL', 'Sicilia', 37.4901, 14.0622),
    '94': ('Enna', 'EN', 'Sicilia', 37.5674, 14.2792),
    '95': ('Catania', 'CT', 'Sicilia', 37.5079, 15.0873),
    '96': ('Siracusa', 'SR', 'Sicilia', 37.0755, 15.2866),
    '97': ('Ragusa', 'RG', 'Sicilia', 36.9269, 14.7307),
    '98': ('Messina', 'ME', 'Sicilia', 38.1938, 15.5540)
}

with open('scratch/amalo_national_harvest.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

resolved_regions = {}
for d in data:
    street = (d.get('street') or '').strip()
    city = (d.get('city') or '').strip()
    cap = (d.get('cap') or '').strip()
    prov = (d.get('province') or '').strip()
    reg = (d.get('region') or '').strip()

    # Look for 5 digit CAP
    if not cap:
        for fld in [prov, city, street]:
            m = re.search(r'\b(\d{5})\b', fld)
            if m:
                cap = m.group(1)
                break

    if not reg and cap and cap[:2] in CAP2_MAP:
        reg = CAP2_MAP[cap[:2]][2]

    reg = reg or 'Unknown'
    resolved_regions[reg] = resolved_regions.get(reg, 0) + 1

print("Resolved regional breakdown across all 1996 groups:")
for r, c in sorted(resolved_regions.items(), key=lambda x: x[1], reverse=True):
    print(f"  {r}: {c}")
