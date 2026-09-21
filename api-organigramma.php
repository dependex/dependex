<?php
/**
 * DEPENDEX.SOCIAL — API ORGANIGRAMMA & STRUTTURA AD ALBERO DELLA RETE
 * Restituisce la struttura gerarchica della Rete dei Club Hudolin:
 * Livello Nazionale (AICAT) -> Livello Regionale (ARCAT) -> Livello Territoriale (ACAT) -> Club Locali & Famiglie.
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

    // Recupero tutti i nodi censiti
    $stmt = $pdo->query("
        SELECT id, sic_id, entity_name, level, region, province, city, address, cap,
               phone, phone_secondary, email, website, servitore_insegnante,
               meeting_day, meeting_time, meeting_frequency, meeting_venue,
               families_count, notes, latitude, longitude
        FROM cat_clubs_italy
        ORDER BY region ASC, province ASC, city ASC, entity_name ASC
    ");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Identificazione nodo radice nazionale AICAT (ID 54)
    $aicat = null;
    foreach ($items as $item) {
        if ($item['level'] === 'NATIONAL') {
            $aicat = $item;
            break;
        }
    }

    if (!$aicat) {
        $aicat = [
            'id' => 54,
            'sic_id' => 'SIC-A5AE0RB4-VN6Z09JB-X',
            'entity_name' => 'AICAT - Associazione Italiana dei Club Alcologici Territoriali',
            'level' => 'NATIONAL',
            'region' => 'Friuli-Venezia Giulia',
            'city' => 'Udine',
            'phone' => '800 974250',
            'email' => 'segreteria@aicat.net',
            'website' => 'https://www.aicat.net'
        ];
    }

    $regionsMap = [];
    $totalClubsCount = 0;
    $totalFamiliesCount = 0;

    foreach ($items as $i) {
        if ($i['level'] === 'NATIONAL') {
            continue;
        }

        $reg = !empty($i['region']) ? $i['region'] : 'Altra Regione';
        if (!isset($regionsMap[$reg])) {
            $regionsMap[$reg] = [
                'regional' => [],
                'territorial' => [],
                'clubs' => []
            ];
        }

        $lvl = $i['level'] ?? '';
        $prov = !empty($i['province']) ? $i['province'] : 'Provincia';

        if (str_contains($lvl, 'REGIONAL') || str_contains($lvl, 'FEDERATION_REGIONAL')) {
            $regionsMap[$reg]['regional'][] = $i;
        } elseif (str_contains($lvl, 'ACAT') || str_contains($lvl, 'APCAT') || str_contains($lvl, 'TERRITORIAL') || str_contains($lvl, 'ASSOCIATION')) {
            if (!isset($regionsMap[$reg]['territorial'][$prov])) {
                $regionsMap[$reg]['territorial'][$prov] = [
                    'info' => [],
                    'clubs' => []
                ];
            }
            $regionsMap[$reg]['territorial'][$prov]['info'][] = $i;
        } else {
            // LOCAL_CLUB
            $totalClubsCount++;
            $fam = (int)($i['families_count'] ?? 11);
            $totalFamiliesCount += $fam;

            if (!isset($regionsMap[$reg]['territorial'][$prov])) {
                $regionsMap[$reg]['territorial'][$prov] = [
                    'info' => [],
                    'clubs' => []
                ];
            }
            $regionsMap[$reg]['territorial'][$prov]['clubs'][] = $i;
        }
    }

    // Costruzione albero D3
    $tree = [
        'id' => (int)$aicat['id'],
        'name' => $aicat['entity_name'],
        'type' => 'NATIONAL',
        'level_label' => 'Base di Supporto Nazionale',
        'sic_id' => $aicat['sic_id'],
        'phone' => $aicat['phone'],
        'email' => $aicat['email'],
        'website' => $aicat['website'] ?? 'https://www.aicat.net',
        'city' => $aicat['city'] ?? 'Udine',
        'address' => $aicat['address'] ?? 'Via Chisimaio 40, Udine',
        'total_regions' => count($regionsMap),
        'total_clubs' => $totalClubsCount,
        'total_families' => $totalFamiliesCount,
        'children' => []
    ];

    ksort($regionsMap);

    foreach ($regionsMap as $regName => $regData) {
        $regRep = !empty($regData['regional']) ? $regData['regional'][0] : null;
        
        $regClubs = 0;
        $regFamilies = 0;
        foreach ($regData['territorial'] as $pData) {
            $regClubs += count($pData['clubs']);
            foreach ($pData['clubs'] as $clb) {
                $regFamilies += (int)($clb['families_count'] ?? 11);
            }
        }

        $regNode = [
            'id' => $regRep ? (int)$regRep['id'] : null,
            'name' => $regRep ? $regRep['entity_name'] : "ARCAT {$regName}",
            'type' => 'REGIONAL',
            'level_label' => 'Associazione Regionale',
            'region' => $regName,
            'sic_id' => $regRep ? $regRep['sic_id'] : '',
            'phone' => $regRep && $regRep['phone'] ? $regRep['phone'] : '800 974250',
            'email' => $regRep ? $regRep['email'] : '',
            'website' => $regRep ? $regRep['website'] : '',
            'city' => $regRep ? $regRep['city'] : '',
            'total_clubs' => $regClubs,
            'total_families' => $regFamilies,
            'children' => []
        ];

        ksort($regData['territorial']);

        foreach ($regData['territorial'] as $provCode => $provData) {
            $acatRep = !empty($provData['info']) ? $provData['info'][0] : null;
            $pClubsCount = count($provData['clubs']);
            $pFamiliesCount = 0;
            foreach ($provData['clubs'] as $c) {
                $pFamiliesCount += (int)($c['families_count'] ?? 11);
            }

            $provNode = [
                'id' => $acatRep ? (int)$acatRep['id'] : null,
                'name' => $acatRep ? $acatRep['entity_name'] : "ACAT {$provCode}",
                'type' => 'TERRITORIAL_ACAT',
                'level_label' => 'Presidio Territoriale',
                'region' => $regName,
                'province' => $provCode,
                'city' => $acatRep && $acatRep['city'] ? $acatRep['city'] : '',
                'sic_id' => $acatRep ? $acatRep['sic_id'] : '',
                'phone' => $acatRep && $acatRep['phone'] ? $acatRep['phone'] : ($regRep['phone'] ?? '800 974250'),
                'email' => $acatRep ? $acatRep['email'] : ($regRep['email'] ?? ''),
                'website' => $acatRep ? $acatRep['website'] : '',
                'total_clubs' => $pClubsCount,
                'total_families' => $pFamiliesCount,
                'children' => []
            ];

            foreach ($provData['clubs'] as $club) {
                $clubFam = (int)($club['families_count'] ?? 11);
                $clubNode = [
                    'id' => (int)$club['id'],
                    'name' => $club['entity_name'],
                    'type' => 'LOCAL_CLUB',
                    'level_label' => 'Club Alcologico Territoriale',
                    'region' => $regName,
                    'province' => $provCode,
                    'city' => $club['city'] ?? '',
                    'address' => $club['address'] ?? '',
                    'sic_id' => $club['sic_id'],
                    'phone' => $club['phone'] ?: ($club['phone_secondary'] ?: ($acatRep['phone'] ?? '800 974250')),
                    'email' => $club['email'] ?: ($acatRep['email'] ?? ''),
                    'website' => $club['website'] ?: ($acatRep['website'] ?? ''),
                    'meeting_day' => $club['meeting_day'] ?? 'Settimanale',
                    'meeting_time' => $club['meeting_time'] ?? '',
                    'meeting_venue' => $club['meeting_venue'] ?? '',
                    'servitore_insegnante' => $club['servitore_insegnante'] ?? '',
                    'families_count' => $clubFam,
                    'notes' => $club['notes'] ?? '',
                    'latitude' => $club['latitude'] ?? null,
                    'longitude' => $club['longitude'] ?? null,
                    'children' => []
                ];

                // Aggiunta nodi simbolici Famiglie nel cerchio
                for ($f = 1; $f <= min($clubFam, 12); $f++) {
                    $clubNode['children'][] = [
                        'name' => "Famiglia Accolta #{$f}",
                        'type' => 'FAMILY',
                        'level_label' => 'Famiglia nel Cerchio',
                        'status' => 'Membro Attivo',
                        'value' => 1
                    ];
                }

                $provNode['children'][] = $clubNode;
            }

            $regNode['children'][] = $provNode;
        }

        $tree['children'][] = $regNode;
    }

    echo json_encode([
        'ok' => true,
        'tree' => $tree,
        'stats' => [
            'total_nodes' => count($items),
            'total_clubs' => $totalClubsCount,
            'total_families' => $totalFamiliesCount,
            'regions_count' => count($regionsMap)
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
