<?php
/**
 * Test Suite: Psychological Onboarding & Empathic Communication
 * Verifica del salto strategico: 4 porte d'ingresso, abbattimento barriere,
 * sezione "Le domande che forse ti vergogni a fare", firma UX "Da dove vuoi iniziare",
 * manifesto "Dal digitale al reale" e grammatica comune senza linguaggio "contro".
 */

$totalChecks = 0;
$passedChecks = 0;

function it_should($condition, $description) {
    global $totalChecks, $passedChecks;
    $totalChecks++;
    if ($condition) {
        $passedChecks++;
        echo "  [PASS] $description\n";
    } else {
        echo "  [FAIL] $description\n";
    }
}

echo "=== TEST SUITE: ONBOARDING PSICOLOGICO & RASSICURAZIONE EMPATICA ===\n\n";

// 1. Verifica 4 Porte d'ingresso psicologiche e Manifesto in index.php
echo "1. Verifica Hero & 4 Porte d'Ingresso in index.php:\n";
ob_start();
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__ . '/../index.php';
$indexOutput = ob_get_clean();

it_should(strpos($indexOutput, 'Non devi sapere già tutto') !== false, 'Contiene il titolo manifesto "Non devi sapere già tutto"');
it_should(strpos($indexOutput, 'Puoi semplicemente fare una domanda') !== false, 'Contiene la rassicurazione "Puoi semplicemente fare una domanda"');
it_should(strpos($indexOutput, "Non serve avere un'etichetta per cercare una comunità") !== false, 'Contiene la frase distintiva "Non serve avere un\'etichetta per cercare una comunità"');
it_should(strpos($indexOutput, 'CERCO AIUTO') !== false, 'Porta 1 presente: CERCO AIUTO');
it_should(strpos($indexOutput, 'CERCO UN CLUB') !== false, 'Porta 2 presente: CERCO UN CLUB');
it_should(strpos($indexOutput, 'CERCO DI CAPIRE') !== false, 'Porta 3 presente: CERCO DI CAPIRE');
it_should(strpos($indexOutput, 'VOGLIO PARTECIPARE') !== false, 'Porta 4 presente: VOGLIO PARTECIPARE');
it_should(strpos($indexOutput, 'Non so da dove cominciare') !== false, 'Descrizione Porta 1: Non so da dove cominciare');

// 2. Verifica Firma UX: "Da dove vuoi iniziare?"
echo "\n2. Verifica Firma UX 'Da dove vuoi iniziare?' in index.php:\n";
it_should(strpos($indexOutput, 'Da dove vuoi iniziare?') !== false, 'Presenza del selettore firma "Da dove vuoi iniziare?"');
it_should(strpos($indexOutput, 'Da me') !== false, 'Tessera 1 presente: Da me');
it_should(strpos($indexOutput, 'Dalla famiglia') !== false, 'Tessera 2 presente: Dalla famiglia');
it_should(strpos($indexOutput, 'Dal territorio') !== false, 'Tessera 3 presente: Dal territorio');
it_should(strpos($indexOutput, 'Dal Club') !== false, 'Tessera 4 presente: Dal Club');
it_should(strpos($indexOutput, 'Come funziona') !== false, 'Tessera 5 presente: Come funziona');
it_should(strpos($indexOutput, 'Dalla rete') !== false, 'Tessera 6 presente: Dalla rete');

// 3. Verifica Sezione: "Le domande che forse ti vergogni a fare"
echo "\n3. Verifica Sezione 'Le domande che forse ti vergogni a fare' in index.php:\n";
it_should(strpos($indexOutput, 'Le domande che forse ti vergogni a fare') !== false, 'Presenza della sezione domande intime in index.php');
it_should(strpos($indexOutput, 'E se beve solo mio marito o mia moglie?') !== false, 'Domanda familiare presente');
it_should(strpos($indexOutput, 'E se vengo ma non me la sento di parlare?') !== false, 'Domanda timore parola presente');
it_should(strpos($indexOutput, 'E se ho già provato altre volte ed è andata male?') !== false, 'Domanda ricadute presente');
it_should(strpos($indexOutput, 'Mi giudicheranno per quello che ho fatto?') !== false, 'Domanda giudizio presente');
it_should(strpos($indexOutput, 'domande-frequenti.php') !== false, 'Link alla pagina completa domande-frequenti.php presente');

// 4. Verifica Manifesto "Dal Digitale al Reale" & "Il Primo Passo"
echo "\n4. Verifica Manifesti 'Dal Digitale al Reale' e 'Il Primo Passo':\n";
it_should(strpos($indexOutput, 'DEPENDEX è digitale. La comunità è reale.') !== false, 'Manifesto "DEPENDEX è digitale. La comunità è reale"');
it_should(strpos($indexOutput, 'Non devi cambiare tutta la tua vita oggi.') !== false, 'Narrativa "Non devi cambiare tutta la tua vita oggi"');
it_should(strpos($indexOutput, 'Poi decidi tu') !== false, 'Conclusione "Poi decidi tu"');
it_should(strpos($indexOutput, 'Dal digitale al reale. Dalla persona alla comunità.') !== false, 'Claim identitario di chiusura presente');

// 5. Verifica Pagina Dedicata domande-frequenti.php
echo "\n5. Verifica Pagina Dedicata domande-frequenti.php & faq.php:\n";
ob_start();
$_SERVER['SCRIPT_NAME'] = '/domande-frequenti.php';
$_SERVER['REQUEST_URI'] = '/domande-frequenti.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
require __DIR__ . '/../domande-frequenti.php';
$faqOutput = ob_get_clean();

it_should(strlen($faqOutput) > 12000, 'domande-frequenti.php renderizza correttamente (> 12KB)');
it_should(strpos($faqOutput, '"@type": "FAQPage"') !== false || strpos($faqOutput, '"@type":"FAQPage"') !== false, 'Include Schema.org FAQPage per motori di ricerca');
it_should(strpos($faqOutput, 'E se non bevo più o bevo solo ogni tanto?') !== false, 'Include domanda su sobrietà preventiva');
it_should(strpos($faqOutput, 'Possono venire anche i miei figli?') !== false, 'Include domanda sull\'accoglienza dei figli');
it_should(strpos($faqOutput, 'Mi devo impegnare a frequentare per sempre') !== false, 'Include rassicurazione sulla libertà da vincoli');
it_should(file_exists(__DIR__ . '/../faq.php'), 'File alias faq.php presente');

// 6. Verifica parla-con-noi.php con le 4 Porte
echo "\n6. Verifica parla-con-noi.php con accoglienza empatica:\n";
ob_start();
$_SERVER['SCRIPT_NAME'] = '/parla-con-noi.php';
$_SERVER['REQUEST_URI'] = '/parla-con-noi.php?porta=aiuto';
$_GET['porta'] = 'aiuto';
require __DIR__ . '/../parla-con-noi.php';
$contactOutput = ob_get_clean();

it_should(strpos($contactOutput, 'Non devi sapere già tutto') !== false, 'parla-con-noi.php include il manifesto "Non devi sapere già tutto"');
it_should(strpos($contactOutput, "Non serve avere un'etichetta per cercare una comunità") !== false, 'parla-con-noi.php include "Non serve avere un\'etichetta"');
it_should(strpos($contactOutput, 'Non so da dove cominciare, vorrei solo un orientamento') !== false, 'Opzione selettore porta "AIUTO" presente');
it_should(strpos($contactOutput, 'Domande che forse ti vergogni a fare') !== false, 'Link a domande-frequenti.php presente nella hero di parla-con-noi');

// 7. Verifica Link in Header e Footer
echo "\n7. Verifica Integrazione Header, Footer, Sitemap e LLMs:\n";
$headerContent = file_get_contents(__DIR__ . '/../_header.php');
$footerContent = file_get_contents(__DIR__ . '/../_footer.php');
$sitemapContent = file_get_contents(__DIR__ . '/../sitemap.xml');
$llmsContent = file_get_contents(__DIR__ . '/../llms.txt');

it_should(strpos($headerContent, 'domande-frequenti.php') !== false, 'Header contiene link a domande-frequenti.php');
it_should(strpos($footerContent, 'domande-frequenti.php') !== false, 'Footer contiene link a domande-frequenti.php');
it_should(strpos($sitemapContent, 'domande-frequenti.php') !== false, 'Sitemap contiene URL domande-frequenti.php');
it_should(strpos($llmsContent, 'domande-frequenti.php') !== false, 'llms.txt contiene risorsa domande-frequenti.php');

// 8. Bonifica Terminologica e Privacy
echo "\n8. Bonifica Terminologica & Conformità Privacy:\n";
$filesToCheck = [
    __DIR__ . '/../index.php',
    __DIR__ . '/../domande-frequenti.php',
    __DIR__ . '/../faq.php',
    __DIR__ . '/../parla-con-noi.php'
];

$bannedWords = ['magico', 'magic', 'giorgian putanu', '81plus'];
$violations = 0;
foreach ($filesToCheck as $file) {
    $content = strtolower(file_get_contents($file));
    foreach ($bannedWords as $word) {
        if (strpos($content, $word) !== false) {
            echo "  [FAIL] Rilevata parola vietata '$word' in " . basename($file) . "\n";
            $violations++;
        }
    }
}
it_should($violations === 0, 'Zero violazioni terminologiche nei file modificati e generati');

echo "\n=== RIEPILOGO TEST: $passedChecks PASS, " . ($totalChecks - $passedChecks) . " FAIL ===\n";
exit($totalChecks === $passedChecks ? 0 : 1);
