<?php
require_once __DIR__ . '/../bootstrap.php';

$pdo = db();
$stmt = $pdo->query("SELECT * FROM event_bookings");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== ISCRITTI EVENTO TAGLIO DI PO (TOTALE: " . count($rows) . ") ===\n\n";

foreach ($rows as $i => $r) {
    echo "------------------------------------------------------------\n";
    echo "ISCRITTO #" . ($i + 1) . "\n";
    echo "ID Prenotazione: " . ($r['id'] ?? 'N/D') . "\n";
    echo "Codice / Booking Ref: " . ($r['booking_ref'] ?? ($r['code'] ?? 'N/D')) . "\n";
    echo "Nome Completo: " . ($r['first_name'] ?? '') . " " . ($r['last_name'] ?? ($r['name'] ?? 'N/D')) . "\n";
    echo "Email: " . ($r['email'] ?? 'N/D') . "\n";
    echo "Telefono: " . ($r['phone'] ?? ($r['telefono'] ?? 'N/D')) . "\n";
    echo "Ruolo / Associazione: " . ($r['role'] ?? ($r['ruolo'] ?? 'N/D')) . "\n";
    echo "Esigenze Dietetiche: " . ($r['dietary_notes'] ?? ($r['dieta'] ?? 'N/D')) . "\n";
    echo "Quota / Importo: €" . ($r['amount'] ?? ($r['quota'] ?? 'N/D')) . "\n";
    echo "Metodo Pagamento: " . ($r['payment_method'] ?? 'N/D') . "\n";
    echo "Stato Pagamento: " . ($r['payment_status'] ?? ($r['status'] ?? 'N/D')) . "\n";
    echo "Data Iscrizione: " . ($r['created_at'] ?? ($r['date'] ?? 'N/D')) . "\n";
    echo "Note / Dettagli: " . ($r['notes'] ?? 'N/D') . "\n";
    echo "Raw Data: " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}
echo "------------------------------------------------------------\n";
