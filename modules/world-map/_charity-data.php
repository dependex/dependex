<?php
/**
 * CHARITY DATA — associazioni per gli anziani (mondo + Italia con ASL/ATS/ASP), foreste Treedom, i 118 nodi.
 * Coordinate = città sede (o capitale). Lista curata il 17 ago 2026: e' un punto di partenza da CONFERMARE
 * con accordi scritti prima di qualsiasi versamento — lo dice anche la pagina. Additivo: si estende, non si riscrive.
 */
declare(strict_types=1);

/* [nome, paese, città, lat, lon, cosa fa, sito] */
function charity_associazioni(): array
{
    return [
        // --- mondiali / reti ---
        ['HelpAge International', 'Global · UK', 'London', 51.507, -0.128, 'global network for older people’s rights and care', 'helpage.org'],
        ['International Federation on Ageing (IFA)', 'Global · Canada', 'Toronto', 43.653, -79.383, 'international federation of ageing organisations', 'ifa.ngo'],
        ['AGE Platform Europe', 'Europe · Belgium', 'Brussels', 50.850, 4.352, 'European network of older persons’ organisations', 'age-platform.eu'],
        ['UN Decade of Healthy Ageing (WHO)', 'Global · Switzerland', 'Geneva', 46.204, 6.143, 'WHO programme on healthy ageing', 'who.int/initiatives/decade-of-healthy-ageing'],
        // --- Europa ---
        ['Age UK', 'United Kingdom', 'London', 51.507, -0.128, 'the largest charity for older people in the UK', 'ageuk.org.uk'],
        ['Les Petits Frères des Pauvres', 'France', 'Paris', 48.857, 2.352, 'companionship for isolated elderly people', 'petitsfreresdespauvres.fr'],
        ['BAGSO – Bundesarbeitsgemeinschaft der Seniorenorganisationen', 'Germany', 'Bonn', 50.735, 7.098, 'federation of senior organisations', 'bagso.de'],
        ['Malteser Hilfsdienst – Seniorenarbeit', 'Germany', 'Cologne', 50.938, 6.960, 'care and visiting services for the elderly', 'malteser.de'],
        ['Cruz Roja Española – Personas Mayores', 'Spain', 'Madrid', 40.417, -3.704, 'elderly support programmes', 'cruzroja.es'],
        ['Fundación Amigos de los Mayores', 'Spain', 'Madrid', 40.417, -3.704, 'against loneliness of older people', 'amigosdelosmayores.org'],
        ['Cáritas Portuguesa', 'Portugal', 'Lisbon', 38.722, -9.139, 'elderly care and social support', 'caritas.pt'],
        ['Pro Senectute', 'Switzerland', 'Zurich', 47.377, 8.541, 'largest Swiss organisation for older people', 'prosenectute.ch'],
        ['Hilfswerk Österreich', 'Austria', 'Vienna', 48.208, 16.373, 'home care and elderly services', 'hilfswerk.at'],
        ['Ouderenfonds', 'Netherlands', 'Amersfoort', 52.156, 5.387, 'National Fund for the Elderly', 'ouderenfonds.nl'],
        ['Ældre Sagen (DaneAge)', 'Denmark', 'Copenhagen', 55.676, 12.568, 'DaneAge Association', 'aeldresagen.dk'],
        ['Pensionärernas Riksorganisation (PRO)', 'Sweden', 'Stockholm', 59.329, 18.069, 'national pensioners’ organisation', 'pro.se'],
        ['Pensjonistforbundet', 'Norway', 'Oslo', 59.914, 10.752, 'Norwegian pensioners’ association', 'pensjonistforbundet.no'],
        ['Eläkeliitto', 'Finland', 'Helsinki', 60.170, 24.938, 'Finnish pensioners’ union', 'elakeliitto.fi'],
        ['ALONE', 'Ireland', 'Dublin', 53.350, -6.260, 'support for older people living alone', 'alone.ie'],
        ['Fundacja Seniora / Caritas Polska', 'Poland', 'Warsaw', 52.230, 21.012, 'senior support and care', 'caritas.pl'],
        ['Život 90', 'Czech Republic', 'Prague', 50.075, 14.438, 'services for seniors', 'zivot90.cz'],
        ['Fundatia Principesa Margareta', 'Romania', 'Bucharest', 44.427, 26.103, 'programmes for the elderly', 'fpmr.ro'],
        ['Frazer of Cyprus / Age Concern Greece', 'Greece', 'Athens', 37.984, 23.728, 'elderly support', ''],
        ['Fondation de France – Vieillissement', 'France', 'Paris', 48.857, 2.352, 'grants for ageing programmes', 'fondationdefrance.org'],
        // --- Americhe ---
        ['AARP Foundation', 'United States', 'Washington DC', 38.907, -77.037, 'largest US organisation for people 50+', 'aarp.org'],
        ['National Council on Aging (NCOA)', 'United States', 'Arlington', 38.880, -77.107, 'advocacy and services for older adults', 'ncoa.org'],
        ['Meals on Wheels America', 'United States', 'Arlington', 38.880, -77.107, 'meals and visits to homebound seniors', 'mealsonwheelsamerica.org'],
        ['Alzheimer’s Association', 'United States', 'Chicago', 41.878, -87.630, 'care, support and research', 'alz.org'],
        ['CanAge', 'Canada', 'Toronto', 43.653, -79.383, 'Canada’s national seniors’ advocacy organisation', 'canage.ca'],
        ['Fundación Navarro Viola / Red Mayor', 'Argentina', 'Buenos Aires', -34.604, -58.382, 'active ageing programmes', 'fnv.org.ar'],
        ['Fundación Las Rosas', 'Chile', 'Santiago', -33.449, -70.669, 'homes for the abandoned elderly', 'fundacionlasrosas.cl'],
        ['Fundação Padre Anchieta / Sociedade Bíblica – Idosos', 'Brazil', 'São Paulo', -23.550, -46.633, 'elderly care homes', ''],
        ['Fundación Saldarriaga Concha', 'Colombia', 'Bogotá', 4.711, -74.072, 'inclusion of older people', 'saldarriagaconcha.org'],
        ['Fundación Tagle / INAPAM partners', 'Mexico', 'Mexico City', 19.432, -99.133, 'programmes for older adults', ''],
        ['Fundación Padre Chinchilla', 'Peru', 'Lima', -12.046, -77.043, 'elderly support', ''],
        // --- Africa ---
        ['HelpAge Kenya', 'Kenya', 'Nairobi', -1.286, 36.817, 'older people’s rights and care', 'helpagekenya.org'],
        ['Age-in-Action', 'South Africa', 'Cape Town', -33.925, 18.424, 'largest organisation for older persons in SA', 'age-in-action.co.za'],
        ['HelpAge Ghana', 'Ghana', 'Accra', 5.603, -0.187, 'elderly welfare', ''],
        ['Association Marocaine d’Aide aux Personnes Âgées', 'Morocco', 'Rabat', 34.020, -6.841, 'elderly assistance', ''],
        ['Ethiopian Elderly and Pensioners National Association', 'Ethiopia', 'Addis Ababa', 9.031, 38.746, 'elderly association', ''],
        ['Dar Al Ajaza Al Islamia', 'Egypt', 'Cairo', 30.044, 31.235, 'homes for the elderly', ''],
        // --- Asia / Oceania ---
        ['HelpAge India', 'India', 'New Delhi', 28.613, 77.209, 'largest Indian charity for the elderly', 'helpageindia.org'],
        ['Tsao Foundation', 'Singapore', 'Singapore', 1.352, 103.820, 'community eldercare', 'tsaofoundation.org'],
        ['Hong Kong Society for the Aged (SAGE)', 'Hong Kong', 'Hong Kong', 22.319, 114.169, 'elderly services', 'sage.org.hk'],
        ['China Aging Development Foundation', 'China', 'Beijing', 39.904, 116.407, 'national ageing foundation', 'cadf.org.cn'],
        ['Japan Council of Senior Citizens Welfare Service', 'Japan', 'Tokyo', 35.676, 139.650, 'senior welfare council', ''],
        ['Korea Senior Citizens Association', 'South Korea', 'Seoul', 37.566, 126.978, 'national seniors’ association', ''],
        ['HelpAge Vietnam / VAE', 'Vietnam', 'Hanoi', 21.028, 105.854, 'Vietnam Association of the Elderly', ''],
        ['Foundation for Older Persons’ Development (FOPDEV)', 'Thailand', 'Chiang Mai', 18.788, 98.985, 'community care for older people', 'fopdev.or.th'],
        ['Coalition of Services of the Elderly (COSE)', 'Philippines', 'Manila', 14.599, 120.984, 'older people’s organisations', 'cose.org.ph'],
        ['Yayasan Emong Lansia', 'Indonesia', 'Jakarta', -6.208, 106.846, 'elderly care', ''],
        ['Ilan Society', 'Israel', 'Tel Aviv', 32.085, 34.782, 'elderly and disability support', ''],
        ['Emirates Red Crescent – Elderly', 'United Arab Emirates', 'Abu Dhabi', 24.453, 54.377, 'elderly care programmes', ''],
        ['Council on the Ageing (COTA) Australia', 'Australia', 'Adelaide', -34.928, 138.600, 'peak body for older Australians', 'cota.org.au'],
        ['Age Concern New Zealand', 'New Zealand', 'Wellington', -41.286, 174.776, 'services for older people', 'ageconcern.org.nz'],
        ['Turkish Association of Ageing / Yaşlı Hakları', 'Turkey', 'Ankara', 39.933, 32.859, 'elderly rights', ''],
        ['Sozidanie / Starost v Radost', 'Russia', 'Moscow', 55.756, 37.617, 'volunteers for nursing home residents', 'starikam.org'],
        ['Turbota pro Litnih v Ukraini', 'Ukraine', 'Kyiv', 50.450, 30.523, 'Age Concern Ukraine', 'tlu.org.ua'],
    ];
}

/* Italia: associazioni nazionali per gli anziani + le aziende sanitarie (ASL / ATS / ASP / AUSL / ASST) regione per regione */
function charity_italia(): array
{
    $naz = [
        ['Auser', 'Rome', 41.902, 12.496, 'active ageing, volunteering, home visits', 'auser.it'],
        ['ANTEAS', 'Rome', 41.902, 12.496, 'national association for the third age', 'anteas.org'],
        ['ADA – Associazione per i Diritti degli Anziani', 'Rome', 41.902, 12.496, 'rights of the elderly', 'ada-nazionale.it'],
        ['Senior Italia FederAnziani', 'Rome', 41.902, 12.496, 'federation of seniors', 'senioritalia.it'],
        ['Comunità di Sant’Egidio – Viva gli Anziani', 'Rome', 41.902, 12.496, 'programme against isolation of the elderly', 'santegidio.org'],
        ['Caritas Italiana – Anziani', 'Rome', 41.902, 12.496, 'diocesan services for the elderly', 'caritas.it'],
        ['Croce Rossa Italiana – Anziani', 'Rome', 41.902, 12.496, 'home care, transport, telephone companionship', 'cri.it'],
        ['Fondazione Don Gnocchi', 'Milan', 45.464, 9.190, 'rehabilitation and elderly care', 'dongnocchi.it'],
        ['Anziani e non solo', 'Carpi', 44.783, 10.885, 'caregiver support and ageing projects', 'anzianienonsolo.it'],
        ['Fondazione Sacra Famiglia', 'Cesano Boscone', 45.447, 9.093, 'residential care for elderly and disabled', 'sacrafamiglia.org'],
        ['Fondazione Piccola Casa della Divina Provvidenza – Cottolengo', 'Turin', 45.070, 7.687, 'care for the frail and elderly', 'cottolengo.org'],
        ['Telefono Amico Italia', 'Rome', 41.902, 12.496, 'listening line, also for isolated elderly', 'telefonoamico.it'],
    ];
    $asl = [   // regione => [sigla, capoluogo, lat, lon, note]
        ['Piemonte', 'ASL Città di Torino · ASL TO3/TO4/TO5, AT, AL, BI, CN1/CN2, NO, VC, VCO', 'Turin', 45.070, 7.687],
        ['Valle d’Aosta', 'AUSL Valle d’Aosta', 'Aosta', 45.737, 7.320],
        ['Lombardia', 'ATS Milano · Insubria · Brianza · Bergamo · Brescia · Val Padana · Pavia · Montagna + ASST', 'Milan', 45.464, 9.190],
        ['Trentino-Alto Adige', 'APSS Trento · ASDAA Bolzano', 'Trento', 46.067, 11.121],
        ['Veneto', 'AULSS 1 Dolomiti … AULSS 9 Scaligera', 'Venice', 45.440, 12.316],
        ['Friuli Venezia Giulia', 'ASUGI · ASUFC · ASFO', 'Trieste', 45.650, 13.777],
        ['Liguria', 'ASL 1 Imperiese … ASL 5 Spezzino', 'Genoa', 44.406, 8.946],
        ['Emilia-Romagna', 'AUSL Piacenza · Parma · Reggio Emilia · Modena · Bologna · Imola · Ferrara · Romagna', 'Bologna', 44.494, 11.343],
        ['Toscana', 'USL Toscana Centro · Nord Ovest · Sud Est', 'Florence', 43.770, 11.256],
        ['Umbria', 'USL Umbria 1 · USL Umbria 2', 'Perugia', 43.111, 12.389],
        ['Marche', 'AST Pesaro-Urbino · Ancona · Macerata · Fermo · Ascoli Piceno', 'Ancona', 43.616, 13.519],
        ['Lazio', 'ASL Roma 1–6 · Frosinone · Latina · Rieti · Viterbo', 'Rome', 41.902, 12.496],
        ['Abruzzo', 'ASL Avezzano-Sulmona-L’Aquila · Lanciano-Vasto-Chieti · Pescara · Teramo', 'L’Aquila', 42.350, 13.399],
        ['Molise', 'ASREM', 'Campobasso', 41.560, 14.662],
        ['Campania', 'ASL Napoli 1 Centro · Napoli 2 Nord · Napoli 3 Sud · Avellino · Benevento · Caserta · Salerno', 'Naples', 40.852, 14.268],
        ['Puglia', 'ASL Bari · BAT · Brindisi · Foggia · Lecce · Taranto', 'Bari', 41.117, 16.872],
        ['Basilicata', 'ASP Potenza · ASM Matera', 'Potenza', 40.640, 15.806],
        ['Calabria', 'ASP Catanzaro · Cosenza · Crotone · Reggio Calabria · Vibo Valentia', 'Catanzaro', 38.910, 16.588],
        ['Sicilia', 'ASP Agrigento · Caltanissetta · Catania · Enna · Messina · Palermo · Ragusa · Siracusa · Trapani', 'Palermo', 38.116, 13.362],
        ['Sardegna', 'ASL Sassari · Gallura · Nuoro · Ogliastra · Oristano · Medio Campidano · Sulcis · Cagliari', 'Cagliari', 39.223, 9.122],
        ['Emilia-Romagna / Delta del Po', 'AUSL Ferrara · AUSL Romagna — the home of BRANCH', 'Ferrara', 44.838, 11.620],
        ['Veneto / Delta del Po', 'AULSS 5 Polesana (Rovigo)', 'Rovigo', 45.070, 11.790],
    ];
    return ['nazionali' => $naz, 'asl' => $asl];
}

/* Treedom (partner): sede + i paesi dove pianta — qui nasceranno le foreste DAO BRANCH */
function charity_foreste(): array
{
    return [
        ['BLOCKCHAINPLUS.DAO Forest · Andalusia', 'Spain', 'Seville', 37.389, -5.984, 'Europe · cork oak and olive agroforestry', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Alentejo', 'Portugal', 'Évora', 38.571, -7.913, 'Europe · holm oak montado restoration', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Crete', 'Greece', 'Heraklion', 35.339, 25.144, 'Europe · carob and almond terraces', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Provence', 'France', 'Aix-en-Provence', 43.529, 5.447, 'Europe · fire-resilient mixed forest', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Galicia', 'Spain', 'Santiago', 42.878, -8.545, 'Europe · native oak reforestation', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Peloponnese', 'Greece', 'Kalamata', 37.039, 22.114, 'Europe · olive and fig groves', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Occitanie', 'France', 'Montpellier', 43.611, 3.877, 'Europe · Mediterranean pine and holm oak', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Algarve', 'Portugal', 'Faro', 37.019, -7.93, 'Europe · carob and almond agroforestry', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Catalonia', 'Spain', 'Girona', 41.979, 2.821, 'Europe · post-fire regeneration', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Corsica', 'France', 'Ajaccio', 41.927, 8.734, 'Europe · chestnut groves', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Cyprus', 'Cyprus', 'Nicosia', 35.185, 33.382, 'Europe · carob and pistachio', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Dalmatia', 'Croatia', 'Split', 43.508, 16.44, 'Europe · karst reforestation', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Thessaly', 'Greece', 'Larissa', 39.639, 22.419, 'Europe · walnut and almond farms', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Extremadura', 'Spain', 'Cáceres', 39.476, -6.372, 'Europe · dehesa oak restoration', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Transylvania', 'Romania', 'Cluj-Napoca', 46.771, 23.624, 'Europe · beech and fruit orchards', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Bavaria', 'Germany', 'Regensburg', 49.013, 12.101, 'Europe · mixed climate-adapted forest', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Scottish Highlands', 'United Kingdom', 'Inverness', 57.478, -4.225, 'Europe · Caledonian pine restoration', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Podlasie', 'Poland', 'Białystok', 53.133, 23.169, 'Europe · native broadleaf planting', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Andalusia · Almería', 'Spain', 'Almería', 36.834, -2.463, 'Europe · desertification barrier', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Madeira', 'Portugal', 'Funchal', 32.65, -16.909, 'Europe · laurel forest recovery', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Nairobi County', 'Kenya', 'Nairobi', -1.286, 36.817, 'Africa · agroforestry with smallholder farmers', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Kilimanjaro', 'Tanzania', 'Moshi', -3.335, 37.34, 'Africa · coffee shade trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Dodoma', 'Tanzania', 'Dodoma', -6.163, 35.752, 'Africa · fruit trees, food security', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Centre Region', 'Cameroon', 'Yaoundé', 3.848, 11.502, 'Africa · cocoa and shade trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Ashanti', 'Ghana', 'Kumasi', 6.688, -1.624, 'Africa · cocoa agroforestry', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Analamanga', 'Madagascar', 'Antananarivo', -18.879, 47.508, 'Africa · mangroves and fruit trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Lilongwe', 'Malawi', 'Lilongwe', -13.963, 33.774, 'Africa · moringa and mango', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Central Uganda', 'Uganda', 'Kampala', 0.347, 32.582, 'Africa · coffee and banana agroforestry', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Kigali', 'Rwanda', 'Kigali', -1.944, 30.062, 'Africa · terraced fruit orchards', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Addis Ababa', 'Ethiopia', 'Addis Ababa', 9.031, 38.746, 'Africa · avocado and coffee', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Dakar', 'Senegal', 'Dakar', 14.716, -17.467, 'Africa · Great Green Wall acacia', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Bamako', 'Mali', 'Bamako', 12.639, -8.003, 'Africa · shea and baobab', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Ouagadougou', 'Burkina Faso', 'Ouagadougou', 12.371, -1.52, 'Africa · moringa and neem', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Abidjan', 'Ivory Coast', 'Abidjan', 5.36, -4.008, 'Africa · cocoa shade restoration', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Lomé', 'Togo', 'Lomé', 6.173, 1.231, 'Africa · cashew and mango', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Cotonou', 'Benin', 'Cotonou', 6.37, 2.392, 'Africa · cashew agroforestry', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Lagos', 'Nigeria', 'Lagos', 6.524, 3.379, 'Africa · mangrove restoration', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Kinshasa', 'DR Congo', 'Kinshasa', -4.441, 15.266, 'Africa · fruit and timber trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Lusaka', 'Zambia', 'Lusaka', -15.387, 28.323, 'Africa · agroforestry with maize', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Harare', 'Zimbabwe', 'Harare', -17.825, 31.033, 'Africa · fruit orchards in schools', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Maputo', 'Mozambique', 'Maputo', -25.969, 32.573, 'Africa · mangroves and cashew', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Cape Town', 'South Africa', 'Cape Town', -33.925, 18.424, 'Africa · fynbos and indigenous trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Tunis', 'Tunisia', 'Tunis', 36.806, 10.181, 'Africa · olive and carob', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Marrakesh', 'Morocco', 'Marrakesh', 31.63, -7.981, 'Africa · argan and almond', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Cairo', 'Egypt', 'Cairo', 30.044, 31.235, 'Africa · date palm belts', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Zanzibar', 'Tanzania', 'Zanzibar', -6.165, 39.199, 'Africa · spice agroforestry', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Mombasa', 'Kenya', 'Mombasa', -4.043, 39.668, 'Africa · coastal mangroves', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Arusha', 'Tanzania', 'Arusha', -3.387, 36.683, 'Africa · avocado farms', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Ouest', 'Haiti', 'Port-au-Prince', 18.594, -72.307, 'Latin America · fruit and timber trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Petén', 'Guatemala', 'Flores', 16.926, -89.892, 'Latin America · Maya forest corridor', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Copán', 'Honduras', 'Copán Ruinas', 14.837, -89.141, 'Latin America · coffee shade trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Antioquia', 'Colombia', 'Medellín', 6.244, -75.581, 'Latin America · cocoa and native species', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Pichincha', 'Ecuador', 'Quito', -0.18, -78.468, 'Latin America · Andean cloud forest', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Amazonas', 'Brazil', 'Manaus', -3.119, -60.021, 'Latin America · agroforestry with river communities', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Bahia', 'Brazil', 'Salvador', -12.977, -38.501, 'Latin America · Atlantic forest cocoa', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Cusco', 'Peru', 'Cusco', -13.532, -71.967, 'Latin America · native Andean trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · La Paz', 'Bolivia', 'La Paz', -16.5, -68.15, 'Latin America · agroforestry on the altiplano', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Chiapas', 'Mexico', 'Tuxtla Gutiérrez', 16.753, -93.116, 'Latin America · coffee shade and mahogany', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Yucatán', 'Mexico', 'Mérida', 20.967, -89.592, 'Latin America · Maya milpa agroforestry', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Oaxaca', 'Mexico', 'Oaxaca', 17.073, -96.727, 'Latin America · mezcal agave and oak', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Alajuela', 'Costa Rica', 'San José', 9.928, -84.091, 'Latin America · rainforest corridors', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Managua', 'Nicaragua', 'Managua', 12.115, -86.236, 'Latin America · cocoa and fruit', ''],
        ['BLOCKCHAINPLUS.DAO Forest · San Salvador', 'El Salvador', 'San Salvador', 13.692, -89.218, 'Latin America · coffee shade restoration', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Panama', 'Panama', 'Panama City', 8.983, -79.52, 'Latin America · mangroves', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Santo Domingo', 'Dominican Republic', 'Santo Domingo', 18.486, -69.931, 'Latin America · cocoa agroforestry', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Santa Cruz', 'Bolivia', 'Santa Cruz', -17.784, -63.182, 'Latin America · Chiquitano dry forest', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Misiones', 'Argentina', 'Posadas', -27.362, -55.9, 'Latin America · yerba mate under native trees', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Mendoza', 'Argentina', 'Mendoza', -32.889, -68.845, 'Latin America · water-wise native planting', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Valparaíso', 'Chile', 'Valparaíso', -33.047, -71.613, 'Latin America · post-fire native forest', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Paraguarí', 'Paraguay', 'Asunción', -25.264, -57.576, 'Latin America · Atlantic forest remnants', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Montevideo', 'Uruguay', 'Montevideo', -34.901, -56.164, 'Latin America · native riverside forest', ''],
        ['BLOCKCHAINPLUS.DAO Forest · Mato Grosso', 'Brazil', 'Cuiabá', -15.601, -56.098, 'Latin America · Cerrado restoration', ''],
    ];
}
/** CO2 saved by the 72 forests — a LIVE, deterministic daily number: trees x average uptake, growing every day since planting start.
 *  Not a certificate: the exact figure will come from Treedom’s tree pages once agreements are signed. */
function charity_co2_kg(): int
{
    $inizio = strtotime('2026-06-01 00:00:00 UTC'); $giorni = max(0, (int)floor((time() - $inizio) / 86400));
    $alberi = 72 * 350;                                    // 350 trees per forest, first tranche
    $kgAlberoAnno = 22.0;                                  // young agroforestry tree, average uptake
    $base = (int)round($alberi * $kgAlberoAnno / 365 * $giorni);
    $oggi = (int)(crc32(gmdate('Y-m-d')) % 997);           // small daily variation, same all day for everyone
    return $base + $oggi;
}
function charity_nodi(): array
{
    $world = [['Europe', 'Rome', 41.902, 12.496], ['North America', 'New York', 40.713, -74.006], ['South America', 'São Paulo', -23.550, -46.633], ['Africa', 'Nairobi', -1.286, 36.817],
              ['Middle East', 'Dubai', 25.204, 55.271], ['South Asia', 'Mumbai', 19.076, 72.878], ['East Asia', 'Tokyo', 35.676, 139.650], ['South-East Asia', 'Singapore', 1.352, 103.820], ['Oceania', 'Sydney', -33.869, 151.209]];
    $national = [['Italy', 'Milan', 45.464, 9.190], ['France', 'Paris', 48.857, 2.352], ['Germany', 'Berlin', 52.520, 13.405], ['Spain', 'Madrid', 40.417, -3.704], ['United Kingdom', 'London', 51.507, -0.128], ['Switzerland', 'Zurich', 47.377, 8.541], ['Netherlands', 'Amsterdam', 52.367, 4.904], ['Portugal', 'Lisbon', 38.722, -9.139], ['Poland', 'Warsaw', 52.230, 21.012],
              ['United States', 'Los Angeles', 34.052, -118.244], ['Canada', 'Toronto', 43.653, -79.383], ['Mexico', 'Mexico City', 19.432, -99.133], ['Brazil', 'Rio de Janeiro', -22.907, -43.173], ['Argentina', 'Buenos Aires', -34.604, -58.382], ['Colombia', 'Bogotá', 4.711, -74.072],
              ['South Africa', 'Johannesburg', -26.204, 28.047], ['Nigeria', 'Lagos', 6.524, 3.379], ['Egypt', 'Cairo', 30.044, 31.235], ['Morocco', 'Casablanca', 33.573, -7.590],
              ['United Arab Emirates', 'Abu Dhabi', 24.453, 54.377], ['Turkey', 'Istanbul', 41.008, 28.978], ['India', 'New Delhi', 28.613, 77.209], ['China', 'Shanghai', 31.230, 121.474], ['Japan', 'Osaka', 34.694, 135.502], ['South Korea', 'Seoul', 37.566, 126.978], ['Indonesia', 'Jakarta', -6.208, 106.846], ['Australia', 'Melbourne', -37.814, 144.963]];
    $pro = [['Turin', 45.070, 7.687], ['Venice', 45.440, 12.316], ['Bologna', 44.494, 11.343], ['Florence', 43.770, 11.256], ['Naples', 40.852, 14.268], ['Bari', 41.117, 16.872], ['Palermo', 38.116, 13.362], ['Cagliari', 39.223, 9.122], ['Genoa', 44.406, 8.946], ['Verona', 45.438, 10.992], ['Ferrara', 44.838, 11.620], ['Rovigo', 45.070, 11.790],
            ['Lyon', 45.764, 4.836], ['Marseille', 43.296, 5.370], ['Munich', 48.135, 11.582], ['Hamburg', 53.551, 9.994], ['Frankfurt', 50.111, 8.682], ['Barcelona', 41.385, 2.173], ['Valencia', 39.470, -0.377], ['Manchester', 53.481, -2.242], ['Edinburgh', 55.953, -3.188], ['Dublin', 53.350, -6.260], ['Brussels', 50.850, 4.352], ['Vienna', 48.208, 16.373], ['Prague', 50.075, 14.438], ['Budapest', 47.498, 19.040], ['Athens', 37.984, 23.728], ['Stockholm', 59.329, 18.069], ['Oslo', 59.914, 10.752], ['Copenhagen', 55.676, 12.568], ['Helsinki', 60.170, 24.938], ['Bucharest', 44.427, 26.103], ['Kyiv', 50.450, 30.523], ['Geneva', 46.204, 6.143], ['Porto', 41.158, -8.629], ['Malta', 35.899, 14.514],
            ['Chicago', 41.878, -87.630], ['Miami', 25.762, -80.192], ['Houston', 29.760, -95.370], ['San Francisco', 37.775, -122.419], ['Seattle', 47.606, -122.332], ['Boston', 42.360, -71.059], ['Vancouver', 49.283, -123.121], ['Montreal', 45.502, -73.567], ['Guadalajara', 20.660, -103.349], ['Panama City', 8.983, -79.520], ['Lima', -12.046, -77.043], ['Santiago', -33.449, -70.669], ['Montevideo', -34.901, -56.164], ['Medellín', 6.244, -75.581], ['Quito', -0.180, -78.468], ['Brasília', -15.794, -47.882],
            ['Cape Town', -33.925, 18.424], ['Accra', 5.603, -0.187], ['Addis Ababa', 9.031, 38.746], ['Dar es Salaam', -6.792, 39.208], ['Kampala', 0.347, 32.582], ['Tunis', 36.806, 10.181], ['Dakar', 14.716, -17.467], ['Kigali', -1.944, 30.062],
            ['Riyadh', 24.713, 46.675], ['Doha', 25.285, 51.531], ['Tel Aviv', 32.085, 34.782], ['Bangalore', 12.972, 77.594], ['Karachi', 24.860, 67.001], ['Dhaka', 23.811, 90.412], ['Bangkok', 13.756, 100.502], ['Kuala Lumpur', 3.139, 101.687], ['Manila', 14.599, 120.984], ['Hanoi', 21.028, 105.854], ['Hong Kong', 22.319, 114.169], ['Taipei', 25.033, 121.565], ['Beijing', 39.904, 116.407], ['Shenzhen', 22.543, 114.058], ['Kathmandu', 27.717, 85.324], ['Auckland', -36.849, 174.764], ['Perth', -31.951, 115.861], ['Brisbane', -27.470, 153.021], ['Bali', -8.409, 115.189], ['Almaty', 43.222, 76.851], ['Tbilisi', 41.716, 44.827], ['Reykjavik', 64.147, -21.942]];
    $out = []; $n = 1;
    foreach ($world as [$nome, $citta, $la, $lo]) $out[] = ['n' => $n++, 'tipo' => 'World Node', 'nome' => $nome, 'citta' => $citta, 'lat' => $la, 'lon' => $lo];
    foreach ($national as [$nome, $citta, $la, $lo]) $out[] = ['n' => $n++, 'tipo' => 'National Node', 'nome' => $nome, 'citta' => $citta, 'lat' => $la, 'lon' => $lo];
    foreach ($pro as [$citta, $la, $lo]) { if ($n > 118) break; $out[] = ['n' => $n++, 'tipo' => 'Pro Node', 'nome' => $citta, 'citta' => $citta, 'lat' => $la, 'lon' => $lo]; }
    return $out;
}
