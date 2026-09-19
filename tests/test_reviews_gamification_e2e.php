<?php
declare(strict_types=1);

/**
 * TEST E2E: RECENSIONI GLOBALI, TICKER HOME, DASHBOARD GAMIFICATION,
 * RUOTA DELLA VITA 2D/3D E PIRAMIDE DI MASLOW
 */
require_once __DIR__ . '/../bootstrap.php';

echo "=== TEST SUITE: RECENSIONI, GAMIFICATION HUDOLIN & STRUMENTI 2D/3D ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(string $desc, bool $condition, string $detail = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$desc}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$desc}" . ($detail ? " ({$detail})" : "") . "\n";
        $failCount++;
    }
}

// 1. Verifica Database Recensioni & ReviewsService
echo "1. Verifica Database Recensioni & ReviewsService:\n";
require_once __DIR__ . '/../modules/reviews/ReviewsService.php';

$allReviews = ReviewsService::getAll();
assertCheck("Database recensioni contiene almeno 12 testimonianze (trovate: " . count($allReviews) . ")", count($allReviews) >= 12);

$firstRev = $allReviews[0] ?? [];
assertCheck("Prima recensione ha autore definito", !empty($firstRev['author']));
assertCheck("Prima recensione ha ruolo definito", !empty($firstRev['role']));
assertCheck("Prima recensione ha citazione dirompente", !empty($firstRev['highlight']));
assertCheck("Prima recensione ha storia completa", !empty($firstRev['story']));
assertCheck("Prima recensione ha rating valido (5)", ($firstRev['rating'] ?? 0) === 5);
assertCheck("Prima recensione appartiene a un Club", !empty($firstRev['club']));

$stats = ReviewsService::getStats();
assertCheck("Statistiche recensioni calcolate correttamente", ($stats['avg_rating'] ?? 0) >= 4.5 && ($stats['total_count'] ?? 0) >= 12);

$tickerCards = ReviewsService::getTickerCards(6);
assertCheck("Ticker Cards restituite correttamente (conteggio: " . count($tickerCards) . ")", count($tickerCards) === 6);

// 2. Verifica Rendering Pagina Dedicata recensioni.php
echo "\n2. Verifica Rendering Pagina Dedicata recensioni.php:\n";
ob_start();
require __DIR__ . '/../recensioni.php';
$reviewsHtml = ob_get_clean();

assertCheck("recensioni.php renderizza correttamente (> 15KB)", strlen($reviewsHtml) > 15000);
assertCheck("recensioni.php include Schema.org AggregateRating", str_contains($reviewsHtml, 'AggregateRating'));
assertCheck("recensioni.php include pulsanti filtri per ruolo", str_contains($reviewsHtml, 'roleFilters'));
assertCheck("recensioni.php include form di condivisione testimonianza", str_contains($reviewsHtml, 'reviewForm'));
assertCheck("recensioni.php contiene link alla Mappa 2D", str_contains($reviewsHtml, 'mappa-club.php'));

// 3. Verifica Ticker Recensioni in templates/_reviews_ticker.php e Home Page index.php
echo "\n3. Verifica Ticker Recensioni in Home Page index.php:\n";
ob_start();
require __DIR__ . '/../templates/_reviews_ticker.php';
$tickerHtml = ob_get_clean();

assertCheck("_reviews_ticker.php renderizza track scorrevole", str_contains($tickerHtml, 'dx-reviews-ticker-track'));
assertCheck("_reviews_ticker.php contiene card recensioni", str_contains($tickerHtml, 'dx-review-card'));

$indexHtml = file_get_contents(__DIR__ . '/../index.php');
assertCheck("Home page index.php include _reviews_ticker.php", str_contains($indexHtml, '_reviews_ticker.php'));

// 4. Verifica Ruota della Vita 2D e 3D ruota-della-vita.php
echo "\n4. Verifica Ruota della Vita 2D & 3D ruota-della-vita.php:\n";
ob_start();
require __DIR__ . '/../ruota-della-vita.php';
$wheelHtml = ob_get_clean();

assertCheck("ruota-della-vita.php renderizza correttamente (> 20KB)", strlen($wheelHtml) > 20000);
assertCheck("Contiene canvas 2D per il radar", str_contains($wheelHtml, 'wheelCanvas2D'));
assertCheck("Contiene container WebGL per il 3D Three.js", str_contains($wheelHtml, 'webglCanvas'));
assertCheck("Include la libreria Three.js", str_contains($wheelHtml, 'three.min.js'));
assertCheck("Contiene i 12 raggi della vita Hudolin", str_contains($wheelHtml, 'Salute & Corpo') && str_contains($wheelHtml, 'Presenza al Club CAT'));
assertCheck("Contiene calcolo indice di equilibrio", str_contains($wheelHtml, 'balanceScore'));

// 5. Verifica Piramide di Maslow Hudolin 2D e 3D piramide-maslow.php
echo "\n5. Verifica Piramide di Maslow Hudolin 2D & 3D piramide-maslow.php:\n";
ob_start();
require __DIR__ . '/../piramide-maslow.php';
$maslowHtml = ob_get_clean();

assertCheck("piramide-maslow.php renderizza correttamente (> 20KB)", strlen($maslowHtml) > 20000);
assertCheck("Contiene i 5 livelli della piramide", str_contains($maslowHtml, 'Livello 5') && str_contains($maslowHtml, 'Livello 1 (La Base)'));
assertCheck("Contiene Trascendenza & Servizio", str_contains($maslowHtml, 'Trascendenza & Servizio'));
assertCheck("Contiene container 3D per Three.js", str_contains($maslowHtml, 'maslowWebglCanvas'));
assertCheck("Include la libreria Three.js", str_contains($maslowHtml, 'three.min.js'));

// 6. Verifica Dashboard Utente & Gamification dashboard.php
echo "\n6. Verifica Dashboard Utente & Gamification dashboard.php:\n";
ob_start();
require __DIR__ . '/../dashboard.php';
$dashHtml = ob_get_clean();

assertCheck("dashboard.php renderizza correttamente (> 20KB)", strlen($dashHtml) > 20000);
assertCheck("Contiene contatore giorni sobrietà", str_contains($dashHtml, 'streakDays'));
assertCheck("Contiene calcolatore denaro risparmiato", str_contains($dashHtml, 'moneySaved'));
assertCheck("Contiene calcolatore bicchieri evitati", str_contains($dashHtml, 'drinksAvoided'));
assertCheck("Contiene Hudolin Quest System (Missioni del Giorno)", str_contains($dashHtml, 'dailyQuestsList'));
assertCheck("Contiene Punti Vitalità (PV)", str_contains($dashHtml, 'userPvTotal'));
assertCheck("Contiene certificato di sobrietà stampabile", str_contains($dashHtml, 'generateCertificate'));

// 7. Verifica Drawer Navigation Header e Footer
echo "\n7. Verifica Collegamenti in Header e Footer:\n";
$headerHtml = file_get_contents(__DIR__ . '/../_header.php');
assertCheck("Header contiene link a recensioni.php", str_contains($headerHtml, 'recensioni.php'));
assertCheck("Header contiene link a dashboard.php", str_contains($headerHtml, 'dashboard.php'));
assertCheck("Header contiene link a ruota-della-vita.php", str_contains($headerHtml, 'ruota-della-vita.php'));
assertCheck("Header contiene link a piramide-maslow.php", str_contains($headerHtml, 'piramide-maslow.php'));

$footerHtml = file_get_contents(__DIR__ . '/../_footer.php');
assertCheck("Footer contiene link a recensioni.php", str_contains($footerHtml, 'recensioni.php'));
assertCheck("Footer contiene link a dashboard.php", str_contains($footerHtml, 'dashboard.php'));
assertCheck("Footer contiene link a ruota-della-vita.php", str_contains($footerHtml, 'ruota-della-vita.php'));
assertCheck("Footer contiene link a piramide-maslow.php", str_contains($footerHtml, 'piramide-maslow.php'));

// 8. Bonifica Terminologica Rigorosa & Conformità Governance
echo "\n8. Bonifica Terminologica Rigorosa & Conformità Governance:\n";
$filesToCheck = [
    __DIR__ . '/../recensioni.php',
    __DIR__ . '/../testimonianze.php',
    __DIR__ . '/../ruota-della-vita.php',
    __DIR__ . '/../piramide-maslow.php',
    __DIR__ . '/../dashboard.php',
    __DIR__ . '/../templates/_reviews_ticker.php',
    __DIR__ . '/../modules/reviews/ReviewsService.php',
    __DIR__ . '/../data/recensioni_club_italia.json'
];

$bannedTerms = ['magico', 'magic', 'giorgian putanu', '81plus', 'grazia nicosia', '347 884 4271', '3478844271'];
$bannedFound = 0;

foreach ($filesToCheck as $f) {
    if (!file_exists($f)) continue;
    $content = mb_strtolower(file_get_contents($f));
    foreach ($bannedTerms as $term) {
        if (str_contains($content, $term)) {
            echo "  [FAIL] File " . basename($f) . " contiene termine vietato: {$term}\n";
            $bannedFound++;
        }
    }
}
assertCheck("Zero violazioni terminologiche nei nuovi moduli", $bannedFound === 0);

echo "\n=== RIEPILOGO TEST: {$passCount} PASS, {$failCount} FAIL ===\n";
exit($failCount > 0 ? 1 : 0);
