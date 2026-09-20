<?php
/**
 * DEPENDEX.SOCIAL — API CLUB LOCATOR GEODESICO AD ALTE PRESTAZIONI
 * Calcola istantaneamente il Club CAT o l'APCAT più vicina alle coordinate GPS dell'utente.
 * Formula geodesica di Haversine su coordinate sferiche terrestri (R = 6371 km).
 * Conforme alle direttive di AGENTS.md e MOBILE_FIRST_VIEWPORT_SPEC.md.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $pdo = db();

    $lat = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : null;
    $lon = isset($_GET['lon']) && is_numeric($_GET['lon']) ? (float)$_GET['lon'] : null;
    $cap = trim($_GET['cap'] ?? '');
    $query = trim($_GET['q'] ?? '');
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? min((int)$_GET['limit'], 20) : 3;
    $preferredLevel = trim($_GET['level'] ?? '');

    // Se non abbiamo coordinate dirette, ma abbiamo un CAP o un nome di città, cerchiamo le coordinate nel database
    if (($lat === null || $lon === null) && ($cap !== '' || $query !== '')) {
        $searchTerm = $cap !== '' ? $cap : $query;
        $geoStmt = $pdo->prepare("
            SELECT latitude, longitude, city, province 
            FROM cat_clubs_italy 
            WHERE cap = ? OR LOWER(city) = LOWER(?) OR entity_name LIKE ? 
            LIMIT 1
        ");
        $geoStmt->execute([$searchTerm, $searchTerm, "%{$searchTerm}%"]);
        $geoMatch = $geoStmt->fetch(PDO::FETCH_ASSOC);

        if ($geoMatch && !empty($geoMatch['latitude']) && !empty($geoMatch['longitude'])) {
            $lat = (float)$geoMatch['latitude'];
            $lon = (float)$geoMatch['longitude'];
        }
    }

    if ($lat === null || $lon === null) {
        // Nessuna coordinata: restituisce i primi Club consigliati per area territoriale polesana/nazionale
        $stmt = $pdo->prepare("
            SELECT id, sic_id, entity_name, level, region, province, city, address, cap,
                   meeting_day, meeting_time, meeting_venue, servitore_insegnante,
                   phone, phone_secondary, email, website, latitude, longitude, notes
            FROM cat_clubs_italy
            WHERE level IN ('PROVINCIAL_APCAT', 'TERRITORIAL', 'LOCAL_CLUB')
            ORDER BY id ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'ok' => true,
            'source' => 'default_featured',
            'user_coords' => null,
            'count' => count($clubs),
            'items' => array_map('format_club_locator_item', $clubs),
            'message' => 'Attiva il GPS o inserisci il tuo comune per trovare il Club più vicino.'
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Calcolo geodesico Haversine in memoria su tutti i club con coordinate
    $clubsStmt = $pdo->query("
        SELECT id, sic_id, entity_name, level, region, province, city, address, cap,
               meeting_day, meeting_time, meeting_venue, servitore_insegnante,
               phone, phone_secondary, email, website, latitude, longitude, notes
        FROM cat_clubs_italy
        WHERE latitude != 0 AND longitude != 0
    ");
    $allClubs = $clubsStmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];
    $latFrom = deg2rad($lat);
    $lonFrom = deg2rad($lon);

    foreach ($allClubs as $club) {
        if ($preferredLevel !== '' && $preferredLevel !== 'ALL') {
            if ($preferredLevel === 'PROVINCIAL_APCAT' && $club['level'] !== 'PROVINCIAL_APCAT' && strpos($club['entity_name'], 'APCAT') === false) {
                continue;
            }
        }

        $cLat = (float)$club['latitude'];
        $cLon = (float)$club['longitude'];

        $latTo = deg2rad($cLat);
        $lonTo = deg2rad($cLon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        $distKm = round($angle * 6371, 1);

        $club['distance_km'] = $distKm;
        $results[] = $club;
    }

    usort($results, fn($a, $b) => $a['distance_km'] <=> $b['distance_km']);
    $topResults = array_slice($results, 0, $limit);

    echo json_encode([
        'ok' => true,
        'source' => 'geodesic_haversine',
        'user_coords' => ['lat' => $lat, 'lon' => $lon],
        'count' => count($topResults),
        'items' => array_map('format_club_locator_item', $topResults),
        'welfare_note' => 'La sedia al Club è sempre aperta. Non serve alcuna prescrizione o spesa: puoi semplicemente presentarti all\'incontro.'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Errore nel localizzatore Club: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

function format_club_locator_item(array $c): array {
    $cleanPhone = preg_replace('/[^0-9+]/', '', $c['phone'] ?? '');
    $lat = (float)($c['latitude'] ?? 0);
    $lon = (float)($c['longitude'] ?? 0);

    return [
        'id' => (int)$c['id'],
        'sic_id' => $c['sic_id'] ?? '',
        'name' => $c['entity_name'] ?? '',
        'level' => $c['level'] ?? 'LOCAL_CLUB',
        'is_apcat' => ($c['level'] === 'PROVINCIAL_APCAT' || strpos($c['entity_name'], 'APCAT') !== false),
        'location' => [
            'region' => $c['region'] ?? '',
            'province' => $c['province'] ?? '',
            'city' => $c['city'] ?? '',
            'address' => $c['address'] ?? '',
            'cap' => $c['cap'] ?? '',
            'lat' => $lat,
            'lon' => $lon,
            'distance_km' => $c['distance_km'] ?? null
        ],
        'meeting' => [
            'day' => $c['meeting_day'] ?? 'Da concordare',
            'time' => $c['meeting_time'] ?? '',
            'venue' => $c['meeting_venue'] ?? ''
        ],
        'contact' => [
            'phone' => $c['phone'] ?? '',
            'phone_clean' => $cleanPhone,
            'email' => $c['email'] ?? '',
            'website' => $c['website'] ?? '',
            'servitore' => $c['servitore_insegnante'] ?? ''
        ],
        'actions' => [
            'call_url' => $cleanPhone ? 'tel:' . $cleanPhone : null,
            'whatsapp_url' => $cleanPhone ? 'https://wa.me/' . str_replace('+', '', $cleanPhone) . '?text=' . urlencode("Salve, vi contatto tramite Dependex. Vorrei informazioni sul prossimo incontro del Club.") : null,
            'map_directions_url' => ($lat && $lon) ? "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lon}" : null
        ]
    ];
}
