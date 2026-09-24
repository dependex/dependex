<?php
/**
 * TEST E2E: COLLANA PUBBLICAZIONI AMAZON KDP & METODO HUDOLIN
 * Verifica Catalogo 8 Volumi, Copertine su Disco, Ticker Home Page e Governance
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../modules/books/BooksCatalogService.php';

use Dependex\Books\BooksCatalogService;

echo "=== TEST E2E: COLLANA PUBBLICAZIONI HUDOLIN & TICKER HOME ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(string $desc, bool $cond): void {
    global $passCount, $failCount;
    if ($cond) {
        echo "  [PASS] $desc\n";
        $passCount++;
    } else {
        echo "  [FAIL] $desc\n";
        $failCount++;
    }
}

// 1. VERIFICA CATALOGO BOOKS CATALOG SERVICE
echo "1. Verifica BooksCatalogService:\n";
$books = BooksCatalogService::getAll();
assertCheck("Restituiti esattamente 8 volumi nel catalogo", count($books) === 8);

$expectedTitles = [
    '52 Settimane di Cambiamento',
    'Diario del Club',
    'SAT I — Workbook',
    'SAT II — Workbook di aggiornamento',
    'SAT III — Workbook della Comunità',
    'Quaderno della Famiglia',
    'Diario del Servitore-Insegnante',
    'Il Mio Diario di Crescita Esponenziale'
];

foreach ($expectedTitles as $expTitle) {
    $found = false;
    foreach ($books as $b) {
        if ($b['title'] === $expTitle) {
            $found = true;
            break;
        }
    }
    assertCheck("Volume '$expTitle' presente nel catalogo", $found);
}

// 2. VERIFICA COPERTINE ESISTENTI SU DISCO
echo "\n2. Verifica File Fisici Copertine (WebP e JPG):\n";
foreach ($books as $b) {
    $webpPath = __DIR__ . '/../' . $b['cover_webp'];
    $jpgPath = __DIR__ . '/../' . $b['cover_jpg'];
    assertCheck("Copertina WebP esiste per '{$b['title']}': {$b['cover_webp']}", file_exists($webpPath) && filesize($webpPath) > 10000);
    assertCheck("Copertina JPG esiste per '{$b['title']}': {$b['cover_jpg']}", file_exists($jpgPath) && filesize($jpgPath) > 10000);
    assertCheck("Link Amazon definito e valido per '{$b['title']}'", !empty($b['amazon_url']) && str_starts_with($b['amazon_url'], 'https://www.amazon.it/'));
    assertCheck("Prezzo cartaceo definito per '{$b['title']}'", !empty($b['price_paperback']));
}

// 3. VERIFICA COMPONENTE TICKER TEMPLATES/_BOOKS_TICKER.PHP
echo "\n3. Verifica Ticker Templates/_books_ticker.php:\n";
ob_start();
require __DIR__ . '/../templates/_books_ticker.php';
$tickerHtml = ob_get_clean();

assertCheck("Ticker renderizza la sezione dx-books-ticker-section", str_contains($tickerHtml, 'dx-books-ticker-section'));
assertCheck("Ticker contiene il track scorrevole dx-books-ticker-track", str_contains($tickerHtml, 'dx-books-ticker-track'));
assertCheck("Ticker contiene card per 52 Settimane", str_contains($tickerHtml, '52 Settimane di Cambiamento'));
assertCheck("Ticker contiene card per SAT I", str_contains($tickerHtml, 'SAT I — Workbook'));
assertCheck("Ticker contiene card per Diario del Club", str_contains($tickerHtml, 'Diario del Club'));
assertCheck("Ticker include stile con pausa su hover/focus", str_contains($tickerHtml, 'animation-play-state: paused'));
assertCheck("Ticker include link Amazon Prime", str_contains($tickerHtml, 'btn-amazon-kdp'));

// 4. VERIFICA HOME PAGE INDEX.PHP CON TICKER INCLUSO
echo "\n4. Verifica Home Page index.php:\n";
$indexContent = file_get_contents(__DIR__ . '/../index.php');
assertCheck("index.php include '_books_ticker.php'", str_contains($indexContent, '_books_ticker.php'));

// 5. VERIFICA PAGINA PUBBLICAZIONI.PHP
echo "\n5. Verifica Pagina pubblicazioni.php:\n";
$pubContent = file_get_contents(__DIR__ . '/../pubblicazioni.php');
assertCheck("pubblicazioni.php esiste ed è leggibile", strlen($pubContent) > 2000);
assertCheck("pubblicazioni.php menziona Mirco Pregnolato come autore", str_contains($pubContent, 'Mirco Pregnolato'));
assertCheck("pubblicazioni.php include tutti i volumi del catalogo", str_contains($pubContent, 'BooksCatalogService::getAll()'));
assertCheck("pubblicazioni.php contiene barra filtri interattiva", str_contains($pubContent, 'dx-filter-btn'));
assertCheck("pubblicazioni.php contiene campo di ricerca libri", str_contains($pubContent, 'bookSearchInput'));
assertCheck("pubblicazioni.php contiene Schema.org Book CollectionPage", str_contains($pubContent, '"@type" => "CollectionPage"') && str_contains($pubContent, '"@type" => "Book"'));
assertCheck("pubblicazioni.php contiene link BeWay.Life e viaggi esperienziali", str_contains($pubContent, 'viaggi-esperienziali.php'));

// 6. VERIFICA LIBRI-KDP.PHP ALIAS
echo "\n6. Verifica libri-kdp.php:\n";
$libriContent = file_get_contents(__DIR__ . '/../libri-kdp.php');
assertCheck("libri-kdp.php include pubblicazioni.php", str_contains($libriContent, 'pubblicazioni.php'));

// 7. VERIFICA GOVERNANCE LESSICALE E DEONTOLOGICA
echo "\n7. Verifica Governance Lessicale (Bonifica Termini):\n";
$filesToScan = [
    __DIR__ . '/../modules/books/BooksCatalogService.php',
    __DIR__ . '/../templates/_books_ticker.php',
    __DIR__ . '/../pubblicazioni.php',
    __DIR__ . '/../libri-kdp.php',
    __DIR__ . '/../offers.php',
    __DIR__ . '/../index.php'
];

$bannedTerms = ['magico', 'magic', 'm.a.g.i.c.', 'giorgian putanu', '81plus'];
$bannedFound = 0;

foreach ($filesToScan as $f) {
    $content = strtolower(file_get_contents($f));
    foreach ($bannedTerms as $term) {
        if (str_contains($content, $term)) {
            echo "  [FAIL] Trovato termine vietato '$term' in " . basename($f) . "\n";
            $bannedFound++;
        }
    }
}
assertCheck("Zero termini vietati riscontrati nei file", $bannedFound === 0);

echo "\n=== RIEPILOGO TEST PUBBLICAZIONI: $passCount PASS, $failCount FAIL ===\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
