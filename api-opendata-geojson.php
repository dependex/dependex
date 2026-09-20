<?php
/**
 * DEPENDEX.SOCIAL — API OPENDATA GEOJSON DEI CLUB ALCOLOGICI TERRITORIALI (CAT)
 * Specifiche standard RFC 7946 (GeoJSON FeatureCollection).
 * Consente l'interoperabilità aperta per ASL, Ser.D, Comuni, Medici di Medicina Generale,
 * ricercatori e sistemi GIS territoriali.
 * 
 * Filtri supportati (GET):
 * - region: Nome regione (es. Veneto, Lombardia)
 * - province: Sigla provincia (es. RO, VR, MI)
 * - level: Livello entità (LOCAL_CLUB, PROVINCIAL_APCAT, TERRITORIAL, REGIONAL)
 * - lat, lon, radius: Filtro di prossimità geografica in km (Haversine)
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/geo+json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Cache-Control: public, max-age=3600');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $pdo = db();

    $region = trim($_GET['region'] ?? '');
    $province = trim($_GET['province'] ?? '');
    $level = trim($_GET['level'] ?? '');
    $userLat = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : null;
    $userLon = isset($_GET['lon']) && is_numeric($_GET['lon']) ? (float)$_GET['lon'] : null;
    $radiusKm = isset($_GET['radius']) && is_numeric($_GET['radius']) ? (float)$_GET['radius'] : null;

    $where = ["latitude != 0 AND longitude != 0"];
    $params = [];

    if ($region !== '' && strtolower($region) !== 'all') {
        $where[] = "LOWER(region) = LOWER(?)";
        $params[] = $region;
    }

    if ($province !== '' && strtolower($province) !== 'all') {
        $where[] = "LOWER(province) = LOWER(?)";
        $params[] = $province;
    }

    if ($level !== '' && strtolower($level) !== 'all') {
        $where[] = "LOWER(level) = LOWER(?)";
        $params[] = $level;
    }

    $sql = "
        SELECT id, sic_id, entity_name, level, region, province, city, address, cap,
               meeting_day, meeting_time, meeting_frequency, meeting_venue,
               servitore_insegnante, phone, phone_secondary, email, website,
               parent_entity, asl_serd_reference, latitude, longitude,
               status, source_url, notes, families_count, updated_at
        FROM cat_clubs_italy
        WHERE " . implode(" AND ", $where) . "
        ORDER BY region ASC, city ASC, entity_name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $features = [];
    $totalFamilies = 0;

    foreach ($rows as $r) {
        $lat = (float)$r['latitude'];
        $lon = (float)$r['longitude'];

        // Filtro di prossimità geodesica se richiesto
        if ($userLat !== null && $userLon !== null && $radiusKm !== null) {
            $latFrom = deg2rad($userLat);
            $lonFrom = deg2rad($userLon);
            $latTo = deg2rad($lat);
            $lonTo = deg2rad($lon);
            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;
            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $dist = $angle * 6371;
            if ($dist > $radiusKm) {
                continue;
            }
        }

        $famCount = (int)($r['families_count'] ?? 11);
        $totalFamilies += $famCount;

        $isApcat = ($r['level'] === 'PROVINCIAL_APCAT' || strpos($r['entity_name'], 'APCAT') !== false);
        $cleanPhone = preg_replace('/[^0-9+]/', '', $r['phone'] ?? '');

        $features[] = [
            'type' => 'Feature',
            'id' => $r['sic_id'],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$lon, $lat] // GeoJSON RFC 7946: [lon, lat]
            ],
            'properties' => [
                'sic_id' => $r['sic_id'],
                'name' => $r['entity_name'],
                'level' => $r['level'],
                'is_apcat' => $isApcat,
                'families_count' => $famCount,
                'families_label' => $isApcat ? "{$famCount} Famiglie nella Rete" : "{$famCount} Famiglie nel Cerchio",
                'address' => $r['address'] ?? '',
                'city' => $r['city'] ?? '',
                'province' => $r['province'] ?? '',
                'region' => $r['region'] ?? '',
                'cap' => $r['cap'] ?? '',
                'country' => 'Italy',
                'meeting' => [
                    'day' => $r['meeting_day'] ?? '',
                    'time' => $r['meeting_time'] ?? '',
                    'frequency' => $r['meeting_frequency'] ?? 'Settimanale',
                    'venue' => $r['meeting_venue'] ?? ''
                ],
                'contacts' => [
                    'servitore' => $r['servitore_insegnante'] ?? '',
                    'phone' => $r['phone'] ?? '',
                    'phone_clean' => $cleanPhone,
                    'email' => $r['email'] ?? '',
                    'website' => $r['website'] ?? '',
                    'asl_serd' => $r['asl_serd_reference'] ?? ''
                ],
                'service' => [
                    'free_access' => true,
                    'method' => 'Vladimir Hudolin Multifamiliare',
                    'gemmazione_threshold' => 14,
                    'status' => $r['status'] ?? 'ACTIVE_VERIFIED_2026'
                ],
                'notes' => $r['notes'] ?? ''
            ]
        ];
    }

    $geoJson = [
        'type' => 'FeatureCollection',
        'metadata' => [
            'title' => 'Rete Nazionale Georeferenziata Club Alcologici Territoriali d\'Italia (CAT & APCAT)',
            'method' => 'Metodo Vladimir Hudolin (1979-1996) · Ecologia Familiare e Sociale',
            'license' => 'Open Data Solidale · ACAT / ARCAT / AICAT / DEPENDEX.SOCIAL',
            'version' => '2026.1',
            'total_features' => count($features),
            'total_families_estimated' => $totalFamilies,
            'generated_at' => gmdate('Y-m-d\TH:i:s\Z')
        ],
        'features' => $features
    ];

    echo json_encode($geoJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'type' => 'FeatureCollection',
        'error' => 'Errore generazione GeoJSON: ' . $e->getMessage(),
        'features' => []
    ], JSON_UNESCAPED_UNICODE);
}
