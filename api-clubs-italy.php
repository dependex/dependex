<?php
/**
 * DEPENDEX.SOCIAL — API CENSIMENTO CLUB CAT / ACAT / ARCAT / AICAT ITALIA
 * Fornisce accesso ad alte prestazioni per la ricerca georeferenziata, filtri per regione,
 * provincia, giorno di riunione e calcolo di prossimità GPS (Haversine).
 */

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
    $action = trim($_GET['action'] ?? 'list');

    // 1. STATISTICHE GENERALI
    if ($action === 'stats') {
        $total = (int)$pdo->query("SELECT COUNT(*) FROM cat_clubs_italy")->fetchColumn();
        
        $byRegion = $pdo->query("
            SELECT region, COUNT(*) as count 
            FROM cat_clubs_italy 
            WHERE region != '' 
            GROUP BY region 
            ORDER BY count DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $byLevel = $pdo->query("
            SELECT level, COUNT(*) as count 
            FROM cat_clubs_italy 
            GROUP BY level 
            ORDER BY count DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $byDay = $pdo->query("
            SELECT meeting_day, COUNT(*) as count 
            FROM cat_clubs_italy 
            WHERE meeting_day != '' 
            GROUP BY meeting_day 
            ORDER BY count DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'ok' => true,
            'total' => $total,
            'regions' => $byRegion,
            'levels' => $byLevel,
            'days' => $byDay
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // 2. DETTAGLIO SINGOLO CLUB PER SIC-ID
    if ($action === 'get') {
        $sicId = trim($_GET['sic_id'] ?? '');
        if (!$sicId) {
            echo json_encode(['ok' => false, 'error' => 'Parametro sic_id obbligatorio.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $stmt = $pdo->prepare("SELECT * FROM cat_clubs_italy WHERE sic_id = ? LIMIT 1");
        $stmt->execute([$sicId]);
        $club = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$club) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Club non trovato.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(['ok' => true, 'item' => $club], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // 3. RICERCA E LISTA GENERALE CON FILTRI E GEOLOCALIZZAZIONE
    $q = trim($_GET['q'] ?? '');
    $region = trim($_GET['region'] ?? '');
    $province = trim($_GET['province'] ?? '');
    $level = trim($_GET['level'] ?? '');
    $day = trim($_GET['day'] ?? '');
    $userLat = isset($_GET['lat']) && is_numeric($_GET['lat']) ? (float)$_GET['lat'] : null;
    $userLon = isset($_GET['lon']) && is_numeric($_GET['lon']) ? (float)$_GET['lon'] : null;
    $radiusKm = isset($_GET['radius']) && is_numeric($_GET['radius']) ? (float)$_GET['radius'] : 100.0;
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? min((int)$_GET['limit'], 1000) : 500;

    $where = [];
    $params = [];

    if ($q !== '') {
        $where[] = "(entity_name LIKE ? OR city LIKE ? OR address LIKE ? OR province LIKE ? OR servitore_insegnante LIKE ? OR notes LIKE ?)";
        $term = "%{$q}%";
        for ($i = 0; $i < 6; $i++) {
            $params[] = $term;
        }
    }

    if ($region !== '' && strtolower($region) !== 'all' && strtolower($region) !== 'tutte') {
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

    if ($day !== '' && strtolower($day) !== 'all') {
        $where[] = "LOWER(meeting_day) LIKE LOWER(?)";
        $params[] = "%{$day}%";
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "
        SELECT id, sic_id, entity_name, level, region, province, city, address, cap,
               meeting_day, meeting_time, meeting_frequency, meeting_venue,
               servitore_insegnante, phone, phone_secondary, email, website,
               parent_entity, parent_sic_id, asl_serd_reference,
               latitude, longitude, geo_accuracy, status, source_url, source_type, notes
        FROM cat_clubs_italy
        {$whereSql}
        ORDER BY region ASC, city ASC, entity_name ASC
        LIMIT {$limit}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se sono fornite coordinate utente, calcola la distanza Haversine e filtra per raggio
    if ($userLat !== null && $userLon !== null) {
        $withDistance = [];
        foreach ($rows as $row) {
            $cLat = (float)$row['latitude'];
            $cLon = (float)$row['longitude'];
            
            // Formula di Haversine per distanza in km
            $latFrom = deg2rad($userLat);
            $lonFrom = deg2rad($userLon);
            $latTo = deg2rad($cLat);
            $lonTo = deg2rad($cLon);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $distanceKm = round($angle * 6371, 1);

            if ($radiusKm <= 0 || $distanceKm <= $radiusKm) {
                $row['distance_km'] = $distanceKm;
                $withDistance[] = $row;
            }
        }

        // Ordina dal più vicino al più lontano
        usort($withDistance, function ($a, $b) {
            return ($a['distance_km'] <=> $b['distance_km']);
        });

        $rows = $withDistance;
    }

    $totalDb = (int)$pdo->query("SELECT COUNT(*) FROM cat_clubs_italy")->fetchColumn();

    echo json_encode([
        'ok' => true,
        'total_database' => $totalDb,
        'count' => count($rows),
        'items' => $rows,
        'filters' => [
            'q' => $q,
            'region' => $region,
            'province' => $province,
            'level' => $level,
            'day' => $day,
            'user_lat' => $userLat,
            'user_lon' => $userLon,
            'radius_km' => $radiusKm
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Errore durante l\'interrogazione del censimento: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
