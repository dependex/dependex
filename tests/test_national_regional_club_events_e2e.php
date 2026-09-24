<?php
/**
 * TEST E2E: RETE EVENTI NAZIONALE, REGIONALE, PROVINCIALE & CLUB
 * DEPENDEX.SOCIAL · OLTRE.SOCIAL
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/events/EventSyncService.php';
require_once __DIR__ . '/../modules/news/AcatNewsService.php';

$testCount = 0;
$passCount = 0;
$failCount = 0;

function it(string $desc, bool $cond, string $details = ''): void {
    global $testCount, $passCount, $failCount;
    $testCount++;
    if ($cond) {
        $passCount++;
        echo "  [PASS] {$desc}\n";
    } else {
        $failCount++;
        echo "  [FAIL] {$desc}" . ($details ? " - Details: {$details}" : "") . "\n";
    }
}

echo "=== TEST E2E: RETE EVENTI NAZIONALE, REGIONALE, PROVINCIALE & CLUB ===\n\n";

// =========================================================================
// 1. VERIFICA DATABASE EVENTI & LIVELLI (EventSyncService)
// =========================================================================
echo "1. Verifica Sincronizzazione EventSyncService:\n";
EventSyncService::syncWebEvents();
$events = EventSyncService::syncAndGetActiveEvents();

it("Numero totale eventi attivi >= 40", count($events) >= 40, "Trovati: " . count($events));
it("Evento #1 e' Taglio di Po (Flagship #1)", ($events[0]['sic_id'] ?? '') === 'SIC-EVT-ACAT-BP-2026-COMM');
it("Evento #2 e' Porto Tolle (Flagship #2)", ($events[1]['sic_id'] ?? '') === 'SIC-EVT-ACAT-BP-2026-SAT2');

$levels = array_count_values(array_column($events, 'level'));
it("Presenza eventi a livello NAZIONALE (>= 10)", ($levels['NATIONAL'] ?? 0) >= 10, "Nazionali: " . ($levels['NATIONAL'] ?? 0));
it("Presenza eventi a livello REGIONALE ARCAT (>= 15)", ($levels['REGIONAL'] ?? 0) >= 15, "Regionali: " . ($levels['REGIONAL'] ?? 0));
it("Presenza eventi a livello PROVINCIALE APCAT/ACAT (>= 5)", ($levels['PROVINCIAL'] ?? 0) >= 5, "Provinciali: " . ($levels['PROVINCIAL'] ?? 0));
it("Presenza incontri a livello CLUB LOCALE (>= 5)", ($levels['CLUB'] ?? 0) >= 5, "Club: " . ($levels['CLUB'] ?? 0));

$sics = array_column($events, 'sic_id');
it("Presenza evento AICAT Nazionale", in_array('SIC-EVT-AICAT-NAT-2026', $sics, true));
it("Presenza evento FeDerSerD Sanità", in_array('SIC-EVT-FEDERSERD-NAT-2026', $sics, true));
it("Presenza evento San Patrignano", in_array('SIC-EVT-SANPATRIGNANO-2026', $sics, true));
it("Presenza evento Giocatori Anonimi", in_array('SIC-EVT-GA-CONV-2026', $sics, true));
it("Presenza evento Narcotici Anonimi", in_array('SIC-EVT-NA-CONV-2026', $sics, true));
it("Presenza evento Alcolisti Anonimi", in_array('SIC-EVT-AA-CONV-2026', $sics, true));
it("Presenza evento ARCAT Veneto", in_array('SIC-EVT-ARCAT-VEN-2026', $sics, true));
it("Presenza evento ARCAT Lombardia", in_array('SIC-EVT-ARCAT-LOM-2026', $sics, true));
it("Presenza evento ARCAT Emilia-Romagna", in_array('SIC-EVT-ARCAT-EMR-2026', $sics, true));
it("Presenza evento ARCAT Toscana", in_array('SIC-EVT-ARCAT-TOS-2026', $sics, true));
it("Presenza evento ARCAT Lazio", in_array('SIC-EVT-ARCAT-LAZ-2026', $sics, true));
it("Presenza evento ARCAT Piemonte", in_array('SIC-EVT-ARCAT-PIE-2026', $sics, true));
it("Presenza evento APCAT Treviso", in_array('SIC-EVT-APCAT-TV-2026', $sics, true));
it("Presenza evento APCAT Padova", in_array('SIC-EVT-APCAT-PD-2026', $sics, true));
it("Presenza evento ACAT Milano", in_array('SIC-EVT-ACAT-MI-2026', $sics, true));
it("Presenza incontro Club Taglio di Po", in_array('SIC-EVT-CLUB-TAGLIOPO', $sics, true));
it("Presenza incontro Club Porto Tolle", in_array('SIC-EVT-CLUB-PORTOTOLLE', $sics, true));
it("Presenza incontro Club Mestre", in_array('SIC-EVT-CLUB-MESTRE', $sics, true));

// =========================================================================
// 2. VERIFICA REGIONI & CENSIMENTO 1.761 CLUB
// =========================================================================
echo "\n2. Verifica Regioni e Censimento Club:\n";
$regions = EventSyncService::getActiveRegions();
it("Regioni attive censite pari a 20", count($regions) >= 20, "Regioni trovate: " . count($regions));
it("Contiene Veneto", in_array('Veneto', $regions, true));
it("Contiene Lombardia", in_array('Lombardia', $regions, true));
it("Contiene Lazio", in_array('Lazio', $regions, true));
it("Contiene Sicilia", in_array('Sicilia', $regions, true));

$clubsVeneto = EventSyncService::searchClubsDirectory('Veneto', null, null, 10);
it("Ricerca club per regione restituisce risultati (Veneto >= 5)", count($clubsVeneto) >= 5, "Clubs: " . count($clubsVeneto));

$clubsSearch = EventSyncService::searchClubsDirectory(null, null, 'Mestre', 5);
it("Ricerca club per comune funziona ('Mestre' >= 1)", count($clubsSearch) >= 1, "Clubs trovati: " . count($clubsSearch));

// =========================================================================
// 3. VERIFICA TICKER NEWS & EVENTI (AcatNewsService)
// =========================================================================
echo "\n3. Verifica Ticker News & Eventi:\n";
$tickerCards = AcatNewsService::getLatestCards(50);
it("Ticker contiene almeno 35 card", count($tickerCards) >= 35, "Card ticker: " . count($tickerCards));
it("Card #1 del Ticker e' Taglio di Po", ($tickerCards[0]['guid'] ?? '') === 'taglio-di-po-ottobre-2026-official');
it("Card #2 del Ticker e' Porto Tolle", ($tickerCards[1]['guid'] ?? '') === 'porto-tolle-ottobre-2026-sat2-official');

$tickerLevels = array_count_values(array_column($tickerCards, 'level'));
it("Ticker include card NAZIONALI (>= 8)", ($tickerLevels['NAZIONALE'] ?? 0) >= 8, "Trovate: " . ($tickerLevels['NAZIONALE'] ?? 0));
it("Ticker include card REGIONALI (>= 8)", ($tickerLevels['REGIONALE'] ?? 0) >= 8, "Trovate: " . ($tickerLevels['REGIONALE'] ?? 0));
it("Ticker include card PROVINCIALI (>= 5)", ($tickerLevels['PROVINCIALE'] ?? 0) >= 5, "Trovate: " . ($tickerLevels['PROVINCIALE'] ?? 0));
it("Ticker include card CLUB (>= 8)", ($tickerLevels['CLUB'] ?? 0) >= 8, "Trovate: " . ($tickerLevels['CLUB'] ?? 0));

// =========================================================================
// 4. VERIFICA RENDERING PAGINA EVENTI events-public.php
// =========================================================================
echo "\n4. Verifica Rendering events-public.php:\n";
ob_start();
require __DIR__ . '/../events-public.php';
$html = ob_get_clean();

it("Pagina events-public.php restituisce HTML valido (> 50KB)", strlen($html) > 50000, "Bytes: " . strlen($html));
it("Include evento faro Taglio di Po", strpos($html, 'id="evento-taglio-di-po"') !== false || strpos($html, 'Taglio di Po') !== false);
it("Include evento faro Porto Tolle", strpos($html, 'id="evento-porto-tolle"') !== false || strpos($html, 'Porto Tolle') !== false);
it("Include ticker orizzontale eventi", strpos($html, 'dx-ticker-track') !== false);
it("Include barra filtri per livello (Pill Tabs)", strpos($html, 'id="levelFilterTabs"') !== false);
it("Include selettore a tendina per Regione", strpos($html, 'id="eventRegionSelect"') !== false);
it("Include selettore a tendina per Provincia", strpos($html, 'id="eventProvinceSelect"') !== false);
it("Include input ricerca libera eventi", strpos($html, 'id="eventSearchInput"') !== false);
it("Include sezione Esploratore dei 1.761 Club", strpos($html, 'id="trova-club"') !== false && strpos($html, '1.761 Club') !== false);
it("Include script client-side di filtraggio istantaneo", strpos($html, 'applyEventFilters') !== false);

// =========================================================================
// 5. VERIFICA HOME PAGE index.php
// =========================================================================
echo "\n5. Verifica Ticker in Home Page index.php:\n";
$indexContent = file_get_contents(__DIR__ . '/../index.php');
it("index.php richiede almeno 40 card per il ticker", strpos($indexContent, 'AcatNewsService::getLatestCards(44)') !== false || strpos($indexContent, 'AcatNewsService::getLatestCards(40)') !== false);

// =========================================================================
// 6. BONIFICA TERMINOLOGICA & HARD CONSTRAINTS
// =========================================================================
echo "\n6. Verifica Governance & Bonifica Terminologica:\n";
$bannedWords = ['magico', 'magic', 'm.a.g.i.c', 'giorgian putanu', '81plus'];
$filesToCheck = [
    __DIR__ . '/../modules/events/EventSyncService.php',
    __DIR__ . '/../modules/news/AcatNewsService.php',
    __DIR__ . '/../events-public.php',
    __DIR__ . '/../index.php',
    __DIR__ . '/../evento-ottobre-porto-tolle.php',
    __DIR__ . '/../evento-ottobre-taglio-di-po.php'
];

$bannedFound = 0;
foreach ($filesToCheck as $file) {
    if (!file_exists($file)) continue;
    $content = strtolower(file_get_contents($file));
    foreach ($bannedWords as $word) {
        if (strpos($content, $word) !== false) {
            $bannedFound++;
            echo "  [VIOLATION] Trovata parola vietata '{$word}' in " . basename($file) . "\n";
        }
    }
}
it("Zero violazioni terminologiche riscontrate (Banned words count: 0)", $bannedFound === 0, "Trovate: {$bannedFound}");

echo "\n======================================================\n";
echo "ESITO FINALE: {$passCount} PASSATI, {$failCount} FALLITI.\n";
echo "======================================================\n";

if ($failCount > 0) {
    exit(1);
}
