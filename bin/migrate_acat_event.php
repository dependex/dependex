<?php
/**
 * Migration & Registration for ACAT Basso Polesine Event
 */
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$pdo = db();

// Ensure columns exist in events table
$cols = [
    'image_url TEXT',
    'organizer TEXT',
    'trainer TEXT',
    'registration_deadline TEXT',
    'ends_at DATETIME',
    'capacity INTEGER DEFAULT 30',
    'price_eur REAL DEFAULT 10.0',
    'address TEXT',
    'comune TEXT'
];

foreach ($cols as $col) {
    try {
        $pdo->exec("ALTER TABLE events ADD COLUMN " . $col);
    } catch (Throwable $e) {
        // column already exists
    }
}

// Create event_bookings table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS event_bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sic_id TEXT UNIQUE NOT NULL,
        event_sic_id TEXT NOT NULL,
        user_sic_id TEXT,
        first_name TEXT,
        last_name TEXT,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        role_type TEXT,
        dietary_notes TEXT,
        num_seats INTEGER DEFAULT 1,
        total_amount REAL DEFAULT 10.0,
        payment_method TEXT DEFAULT 'ON_SITE',
        payment_status TEXT DEFAULT 'PENDING',
        payment_tx_id TEXT,
        status TEXT DEFAULT 'CONFIRMED',
        notes TEXT,
        ip_address TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

// Add any missing columns to existing event_bookings table
$bookingCols = [
    'first_name TEXT',
    'last_name TEXT',
    'payment_status TEXT DEFAULT "PENDING"',
    'payment_tx_id TEXT',
    'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP'
];
foreach ($bookingCols as $bcol) {
    try {
        $pdo->exec("ALTER TABLE event_bookings ADD COLUMN " . $bcol);
    } catch (Throwable $e) {
        // column already exists
    }
}

$sic = 'SIC-EVT-ACAT-BP-2026-COMM';
$check = $pdo->prepare('SELECT sic_id FROM events WHERE sic_id = ?');
$check->execute([$sic]);
$exists = $check->fetchColumn();

$desc = 'Impara a comunicare senza litigare e a non farti caricare dai problemi degli altri. Corso di formazione esperienziale rivolto a chi vive in famiglia una situazione di dipendenza, operatori, volontari e membri dei Club Alcologici Territoriali. Tre giornate con Adelmo Di Salvatore per acquisire strumenti pratici da usare già dal lunedì.';

$venue = 'Oratorio San Francesco d\'Assisi';
$address = 'Vicolo San Francesco 1, Taglio di Po (RO)';
$organizer = 'ACAT Basso Polesine O.D.V. & Coordinamento A.C.A.T. Polesane';
$trainer = 'Adelmo Di Salvatore (Psichiatra, Psicoterapeuta, Formatore Metodo Hudolin)';
$imageUrl = 'assets/img/events/locandina-ufficiale-oratorio.jpeg';

if (!$exists) {
    $stmt = $pdo->prepare('INSERT INTO events (sic_id, type, title, description, starts_at, ends_at, venue, comune, address, visibility, rank_required, drx_reward, status, capacity, price_eur, source_url, image_url, organizer, trainer, registration_deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $sic,
        'FORMAZIONE',
        'A Scuola di Comunicazione e Resilienza — 1° Livello',
        $desc,
        '2026-10-09 14:30:00',
        '2026-10-11 13:00:00',
        $venue,
        'Taglio di Po',
        $address,
        'PUBLIC',
        'SEME',
        100,
        'PUBLISHED',
        30,
        10.00,
        'event-detail.php?event=' . $sic,
        $imageUrl,
        $organizer,
        $trainer,
        '2026-10-01 23:59:59'
    ]);
    echo "INSERTED_EVENT_OK\n";
} else {
    $stmt = $pdo->prepare('UPDATE events SET type=?, title=?, description=?, starts_at=?, ends_at=?, venue=?, comune=?, address=?, visibility=?, rank_required=?, drx_reward=?, status=?, capacity=?, price_eur=?, source_url=?, image_url=?, organizer=?, trainer=?, registration_deadline=? WHERE sic_id=?');
    $stmt->execute([
        'FORMAZIONE',
        'A Scuola di Comunicazione e Resilienza — 1° Livello',
        $desc,
        '2026-10-09 14:30:00',
        '2026-10-11 13:00:00',
        $venue,
        'Taglio di Po',
        $address,
        'PUBLIC',
        'SEME',
        100,
        'PUBLISHED',
        30,
        10.00,
        'event-detail.php?event=' . $sic,
        $imageUrl,
        $organizer,
        $trainer,
        '2026-10-01 23:59:59',
        $sic
    ]);
    echo "UPDATED_EVENT_OK\n";
}
