<?php
/**
 * DEPENDEX.SOCIAL - Event Visual & Printable Posters
 * Protocollo Karpathy: SPEC -> VERIFIER -> ENVIRONMENT
 * Formats: A4, A5, STORY, SQUARE - Clean Print Engine
 */
require_once 'bootstrap.php';
$u = require_login();

$sic = trim($_GET['event'] ?? '');
$fmt = strtoupper(trim($_GET['format'] ?? 'A4'));
$event = null;

if ($sic !== '') {
    $st = db()->prepare('SELECT * FROM events WHERE sic_id = ?');
    $st->execute([$sic]);
    $event = $st->fetch();
}

if (!$event && ($sic === 'SIC-EVT-ACAT-BP-2026-COMM' || empty($sic))) {
    $event = [
        'sic_id' => 'SIC-EVT-ACAT-BP-2026-COMM',
        'type' => 'FORMAZIONE',
        'title' => 'A Scuola di Comunicazione e Resilienza — 1° Livello',
        'description' => 'Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri. Corso esperienziale di 3 giornate con Adelmo Di Salvatore per chi vive situazioni di dipendenza in famiglia.',
        'starts_at' => '2026-10-09 14:30:00',
        'ends_at' => '2026-10-11 13:00:00',
        'venue' => "Oratorio San Francesco d'Assisi",
        'comune' => 'Taglio di Po',
        'address' => 'Vicolo San Francesco 1, Taglio di Po (RO)'
    ];
}

$classes = ['A4' => 'a4', 'A5' => 'a5', 'STORY' => 'story', 'SQUARE' => 'square'];
$cl = $classes[$fmt] ?? 'a4';
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= h(mb_substr($event['description'] ?? 'Locandina evento Dependex', 0, 150)) ?>">
    <title><?= h($event['title']) ?> · Visual Locandina · DEPENDEX</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #070709;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100dvh;
        }
        .toolbar {
            width: 100%;
            background: #11141d;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .toolbar a, .toolbar button {
            color: #ffffff;
            text-decoration: none;
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.2);
            cursor: pointer;
            transition: all 0.2s;
        }
        .toolbar a:hover, .toolbar button:hover {
            background: #D4AF37;
            color: #000000;
        }
        .sheet {
            margin: 20px auto;
            background: linear-gradient(145deg, #070709, #181b22 58%, #D4AF37);
            color: white;
            position: relative;
            overflow: hidden;
            padding: 7%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(212,175,55,0.4);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .a4 { width: 210mm; min-height: 297mm; }
        .a5 { width: 148mm; min-height: 210mm; }
        .story { width: 108mm; min-height: 192mm; }
        .square { width: 180mm; min-height: 180mm; }

        .brand { font-size: 24px; font-weight: 900; letter-spacing: .18em; color: #D4AF37; }
        .payoff { color: #FFF2B2; font-weight: 900; letter-spacing: .12em; font-size: 14px; margin-top: 4px; }
        .title {
            font-size: clamp(32px, 5vw, 64px);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: -.03em;
            color: #FFFFFF;
            margin: 20px 0 16px 0;
        }
        .desc {
            font-size: clamp(14px, 2vw, 18px);
            line-height: 1.5;
            opacity: 0.92;
            margin-bottom: 24px;
        }
        .meta { font-size: 20px; font-weight: 800; color: #FFF2B2; line-height: 1.4; }
        .location { font-size: 16px; opacity: 0.85; margin-top: 6px; }
        .sic { font-family: monospace; font-size: 12px; color: #a1a1aa; margin-top: 12px; }

        @media (max-width: 800px) {
            .sheet {
                width: 95vw !important;
                min-height: auto !important;
                padding: 1.5rem;
                margin: 1rem auto;
            }
            .title { font-size: 28px; }
            .meta { font-size: 18px; }
        }

        @media print {
            .toolbar { display: none !important; }
            body { background: #fff !important; }
            .sheet {
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                height: 100% !important;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <a href="graphic-studio.php">← Torna a Graphic Studio</a>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="event-visual.php?event=<?= urlencode($event['sic_id']) ?>&format=A4">A4</a>
            <a href="event-visual.php?event=<?= urlencode($event['sic_id']) ?>&format=A5">A5</a>
            <a href="event-visual.php?event=<?= urlencode($event['sic_id']) ?>&format=STORY">Story</a>
            <a href="event-visual.php?event=<?= urlencode($event['sic_id']) ?>&format=SQUARE">Square</a>
            <button onclick="window.print()" style="background: #D4AF37; color: #000;">🖨️ Stampa</button>
        </div>
    </div>

    <div class="sheet <?= $cl ?>">
        <div>
            <div class="brand">DEPENDEX</div>
            <div class="payoff">AL CLUB. COL CLUB.</div>
        </div>

        <div>
            <h1 class="title"><?= h($event['title']) ?></h1>
            <p class="desc"><?= h($event['description']) ?></p>
        </div>

        <div>
            <div class="meta">
                📅 <?= h($event['starts_at']) ?><br>
                📍 <?= h($event['venue']) ?>
            </div>
            <p class="location"><?= h($event['address'] ?? $event['comune'] ?? '') ?></p>
            <div class="sic">Codice Identificativo: <?= h($event['sic_id']) ?></div>
        </div>
    </div>
</body>
</html>