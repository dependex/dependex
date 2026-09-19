<?php
/**
 * bin/update-cat-intel-v3.php
 * Integrazione dati OSINT V3: ACAT ToCentro (Torino), ACAT Torino Est (VoltoWeb),
 * APCAT Trentino (Via Sighele Scipio 5, Trento), arricchimento note territoriali Ser.D / ASL.
 */

declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

$pdo = db();
echo "== AGGIORNAMENTO OSINT RETE CAT V3 ==\n";

// 1. ACAT ToCentro (Torino)
$stTo = $pdo->prepare("UPDATE dependex_world_registry SET 
    entity_name = 'ACAT ToCentro (Torino Centro)',
    address = :address,
    postal_code = '10128',
    city = 'Torino',
    province = 'TO',
    region = 'Piemonte',
    phone = :phone,
    notes = :notes,
    source_type = 'Città della Salute e della Scienza di Torino',
    status = 'ACTIVE_VERIFIED_2026',
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-N6JQ9EQG-HEHHZHPA-M' OR entity_name LIKE '%ToCentro%'");
$stTo->execute([
    ':address' => 'Via Massena, 55',
    ':phone' => '339 5926249 / 346 1315143',
    ':notes' => 'Sede presso la Città della Salute e della Scienza di Torino. Incontri multifamiliari settimanali.'
]);
echo "Aggiornato ACAT ToCentro (Via Massena 55, Torino)\n";

// 2. ACAT Torino Est ODV
$stToEst = $pdo->prepare("UPDATE dependex_world_registry SET 
    entity_name = 'ACAT Torino Est ODV',
    website = 'https://www.voltoweb.it',
    city = 'Torino',
    province = 'TO',
    region = 'Piemonte',
    notes = :notes,
    source_type = 'VoltoWeb Torino',
    status = 'ACTIVE_VERIFIED_2026',
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-6261QW0C-Y2T2RPD4-C' OR entity_name LIKE '%Torino Est%'");
$stToEst->execute([
    ':notes' => 'Attiva sul territorio di Torino Est in sinergia con il Centro Servizi VoltoWeb.'
]);
echo "Aggiornato ACAT Torino Est (VoltoWeb)\n";

// 3. APCAT Trentino ODV - Sede Via Sighele Scipio 5, Trento
$stTn = $pdo->prepare("UPDATE dependex_world_registry SET 
    entity_name = 'APCAT Trentino ODV (Centro Studi CSDPA)',
    address = :address,
    postal_code = '38122',
    city = 'Trento',
    province = 'TN',
    region = 'Trentino-Alto Adige',
    phone = '0461 914451',
    email = 'csdpa@apcattrentino-centrostudi.it',
    website = 'http://www.apcattrentino-centrostudi.it',
    notes = :notes,
    status = 'ACTIVE_VERIFIED_2026',
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-33Y9KF0M-SNASNV7Q-A'");
$stTn->execute([
    ':address' => 'Via Sighele Scipio, 5',
    ':notes' => 'Sede a Trento. Coordinamento dei Club del Trentino e formazione continua con il Centro Studi CSDPA.'
]);
echo "Aggiornato APCAT Trentino (Via Sighele Scipio 5, Trento)\n";

// 4. ARCAT Liguria - Riferimento elenco incontri e servitori
$pdo->prepare("UPDATE dependex_world_registry SET 
    notes = 'Portale regionale con elenco dei Club (CAT), sedi, orari di riunione settimanale e contatti dei Servitori-Insegnanti su arcat-liguria.com.',
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-SAPSPJXD-51WPHBC5-B'")->execute();
echo "Aggiornato ARCAT Liguria con specifiche su elenco servitori\n";

// 5. Normalizzazione note per regioni a prevalenza Ser.D / ASL
$regioneSerd = [
    'SIC-EK5ZJH8T-GETTY2FP-W' => 'Contatti e accoglienza prevalentemente tramite i Ser.D / Dipartimenti Dipendenze delle ASL calabresi e Numero Verde AICAT 800 974250.', // Calabria
    'SIC-B43BB5EB-6ZXFME7V-D' => 'Gruppi multifamiliari attivi su Napoli, Salerno e province; riferimento tramite Ser.D locali, email arcatcampania@gmail.com o Numero Verde AICAT 800 974250.', // Campania
    'SIC-77AZHNGD-B4V7TSQH-S' => 'Presenza dell\'ACAT Ciociaria (Frosinone); per Roma e province contatti tramite Ser.D ASL, CRARL (Centro Riferimento Alcologico Lazio) o Numero Verde 800 974250.', // Lazio
    'SIC-00GPNEFM-VBWTZRVD-K' => 'Club attivi sul territorio; riferimento regionale Caltanissetta, Ser.D locali o Numero Verde AICAT 800 974250.', // Sicilia
    'SIC-JQ09PERZ-53Q5953G-1' => 'Riferimento regionale ARCAT Marche (Campofilone, FM) o tramite Ser.D delle AST marchigiane e Numero Verde 800 974250.', // Marche
    'SIC-MWD6NTHF-BQR1SX9Q-1' => 'Riferimento tramite Servizio per le Dipendenze (Ser.D) dell\'Azienda USL Valle d\'Aosta e Numero Verde 800 974250.' // Valle d'Aosta
];

foreach ($regioneSerd as $sic => $note) {
    $pdo->prepare("UPDATE dependex_world_registry SET notes = ?, updated_at = CURRENT_TIMESTAMP WHERE sic_id = ?")->execute([$note, $sic]);
}
echo "Aggiornate note operative Ser.D / ASL per regioni Centro-Sud e Valle d'Aosta\n";

echo "== COMPLETATO CON SUCCESSO ==\n";
