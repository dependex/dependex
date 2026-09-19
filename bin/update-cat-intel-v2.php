<?php
/**
 * bin/update-cat-intel-v2.php
 * Aggiornamento OSINT V2: ARCAT Umbria, APCAT Trentino/Bolzano, ACAT Livenza,
 * ACAT Gardesana, ACAT Bassano-Asiago, ACAT Valchiampo e Numero Verde Nazionale AICAT 800 974250.
 */

declare(strict_types=1);
require_once __DIR__ . '/../bootstrap.php';

$pdo = db();
echo "== AGGIORNAMENTO OSINT RETE CAT V2 ==\n";

// 1. AICAT Nazionale - Aggiunta Numero Verde 800 974250
$st1 = $pdo->prepare("UPDATE dependex_world_registry SET 
    phone = :phone,
    notes = :notes,
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-A5AE0RB4-VN6Z09JB-X'");
$st1->execute([
    ':phone' => '800 974250 (Numero Verde) · 0432 123456',
    ':notes' => 'Organismo nazionale di coordinamento dei Club Alcologici Territoriali. Numero Verde gratuito: 800 974250.'
]);
echo "Aggiornato AICAT Nazionale con Numero Verde 800 974250\n";

// 2. ARCAT Umbria
$st2 = $pdo->prepare("UPDATE dependex_world_registry SET 
    entity_name = 'ARCAT Umbria ODV',
    website = 'https://www.arcatumbria.it',
    email = 'arcatumbria@gmail.com',
    phone = '340 8672029 / 347 1647419',
    city = 'Perugia',
    province = 'PG',
    region = 'Umbria',
    status = 'ACTIVE_VERIFIED_2026',
    notes = :notes,
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-DCM75P3Z-1HDQ3KPZ-6'");
$st2->execute([
    ':notes' => "Coordinamento regionale dei Club dell'Umbria. Sede a Perugia."
]);
echo "Aggiornato ARCAT Umbria\n";

// 3. APCAT Trentino ODV
$st3 = $pdo->prepare("UPDATE dependex_world_registry SET 
    email = 'csdpa@apcattrentino-centrostudi.it',
    phone = '0461 914451',
    notes = :notes,
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-33Y9KF0M-SNASNV7Q-A'");
$st3->execute([
    ':notes' => "Attiva con corsi e coordinamento locale dei Club del Trentino. Centro Studi CSDPA."
]);
echo "Aggiornato APCAT Trentino ODV\n";

// 4. APCAT Bolzano / Alto Adige
$checkBz = $pdo->prepare("SELECT sic_id FROM dependex_world_registry WHERE sic_id='SIC-H20ZR2EF-6B35QK4T-4' OR (region='Trentino-Alto Adige' AND city='Bolzano')");
$checkBz->execute();
$sicBz = $checkBz->fetchColumn();
if ($sicBz) {
    $stBz = $pdo->prepare("UPDATE dependex_world_registry SET 
        entity_name = 'APCAT Bolzano / Rete Alto Adige',
        city = 'Bolzano',
        province = 'BZ',
        notes = :notes,
        status = 'ACTIVE_VERIFIED_2026',
        updated_at = CURRENT_TIMESTAMP
        WHERE sic_id = ?");
    $stBz->execute([
        "Riferimenti territoriali in collaborazione con i Ser.D e associazioni convenzionate (HANDS Bolzano).",
        $sicBz
    ]);
    echo "Aggiornato APCAT Bolzano ($sicBz)\n";
}

// 5. ARCAT FVG - Link elenco completo
$stFvg = $pdo->prepare("UPDATE dependex_world_registry SET 
    source_url = 'https://www.arcatfvg.it/acat-e-cat',
    notes = :notes,
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-2KT4SVM9-3648P2M5-4'");
$stFvg->execute([
    ':notes' => "Coordina oltre 200 Club in Friuli Venezia Giulia. Elenco completo ACAT e CAT su arcatfvg.it/acat-e-cat"
]);
echo "Aggiornato ARCAT FVG con link arcatfvg.it/acat-e-cat\n";

// 6. ACAT Gardesana
$stGard = $pdo->prepare("UPDATE dependex_world_registry SET 
    website = 'https://www.acatgardesana.it',
    phone = '339 3333119',
    city = 'Lonato del Garda',
    province = 'BS',
    notes = :notes,
    status = 'ACTIVE_VERIFIED_2026',
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-7GRCE202-Q478HRVP-J' OR entity_name LIKE '%Gardesana%'");
$stGard->execute([
    ':notes' => "Club attivi a Lonato del Garda, Salò, Gardone, Bedizzole, Desenzano e Prevalle."
]);
echo "Aggiornato ACAT Gardesana con acatgardesana.it\n";

// 7. ACAT Bassano-Asiago
$stBass = $pdo->prepare("UPDATE dependex_world_registry SET 
    entity_name = 'ACAT Bassano-Asiago',
    website = 'https://www.acatbassano.it',
    email = 'info@acatbassano.it',
    phone = '0424 80379 / 346 2102858',
    city = 'Bassano del Grappa',
    province = 'VI',
    notes = :notes,
    status = 'ACTIVE_VERIFIED_2026',
    updated_at = CURRENT_TIMESTAMP
    WHERE sic_id = 'SIC-ACAT-BASSANO-VI' OR entity_name LIKE '%Bassano%'");
$stBass->execute([
    ':notes' => "Area pedemontana del Grappa e Altopiano dei Sette Comuni (Asiago)."
]);
echo "Aggiornato ACAT Bassano-Asiago con contatti completi\n";

// 8. Inserimento / Aggiornamento ACAT Livenza (Sacile / FVG)
$checkLivenza = $pdo->prepare("SELECT sic_id FROM dependex_world_registry WHERE entity_name LIKE '%Livenza%' OR website LIKE '%acatlivenza.it%'");
$checkLivenza->execute();
$sicLivenza = $checkLivenza->fetchColumn();
if ($sicLivenza) {
    $pdo->prepare("UPDATE dependex_world_registry SET 
        entity_name = 'ACAT Livenza',
        website = 'https://www.acatlivenza.it',
        email = 'info@acatlivenza.it',
        city = 'Sacile',
        province = 'PN',
        region = 'Friuli-Venezia Giulia',
        parent_sic_id = 'SIC-2KT4SVM9-3648P2M5-4',
        status = 'ACTIVE_VERIFIED_2026',
        updated_at = CURRENT_TIMESTAMP
        WHERE sic_id = ?")->execute([$sicLivenza]);
    echo "Aggiornato ACAT Livenza ($sicLivenza)\n";
} else {
    $insLiv = 'SIC-ACAT-LIVENZA-PN';
    $stLiv = $pdo->prepare("INSERT INTO dependex_world_registry (
        sic_id, entity_name, network_level, network_rank, rank_color, continent, country,
        region, province, city, website, email, status, parent_sic_id, source_url,
        source_type, language, notes, public_data_score, is_synthetic, created_at, updated_at
    ) VALUES (
        :sic, 'ACAT Livenza', 'TERRITORIAL', 2, '#F5DF88', 'Europe', 'Italy',
        'Friuli-Venezia Giulia', 'PN', 'Sacile', 'https://www.acatlivenza.it', 'info@acatlivenza.it',
        'ACTIVE_VERIFIED_2026', 'SIC-2KT4SVM9-3648P2M5-4', 'https://www.acatlivenza.it',
        'OSINT_DIRECTORY_2026', 'it', :notes, 90, 0,
        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    )");
    $stLiv->execute([
        ':sic' => $insLiv,
        ':notes' => "Club dell'area di Sacile e del bacino del fiume Livenza."
    ]);
    echo "Inserito ACAT Livenza ($insLiv)\n";
}

// 9. Inserimento / Aggiornamento ACAT Valchiampo ODV (Arzignano / Veneto)
$checkValchiampo = $pdo->prepare("SELECT sic_id FROM dependex_world_registry WHERE entity_name LIKE '%Valchiampo%' OR website LIKE '%acatvalchiampo.it%'");
$checkValchiampo->execute();
$sicValchiampo = $checkValchiampo->fetchColumn();
if ($sicValchiampo) {
    $pdo->prepare("UPDATE dependex_world_registry SET 
        entity_name = 'ACAT Valchiampo ODV',
        website = 'https://www.acatvalchiampo.it',
        email = 'acatvalchiampo@arcatveneto.it',
        city = 'Arzignano',
        province = 'VI',
        region = 'Veneto',
        parent_sic_id = 'SIC-3JXF0TMQ-BDRN8KQ4-F',
        notes = :notes,
        status = 'ACTIVE_VERIFIED_2026',
        updated_at = CURRENT_TIMESTAMP
        WHERE sic_id = :sic")->execute([
            ':notes' => "Elenco dei Club della Valle del Chiampo e comprensorio di Arzignano.",
            ':sic' => $sicValchiampo
        ]);
    echo "Aggiornato ACAT Valchiampo ($sicValchiampo)\n";
} else {
    $insVal = 'SIC-ACAT-VALCHIAMPO-VI';
    $stVal = $pdo->prepare("INSERT INTO dependex_world_registry (
        sic_id, entity_name, network_level, network_rank, rank_color, continent, country,
        region, province, city, website, email, status, parent_sic_id, source_url,
        source_type, language, notes, public_data_score, is_synthetic, created_at, updated_at
    ) VALUES (
        :sic, 'ACAT Valchiampo ODV', 'TERRITORIAL', 2, '#F5DF88', 'Europe', 'Italy',
        'Veneto', 'VI', 'Arzignano', 'https://www.acatvalchiampo.it', 'acatvalchiampo@arcatveneto.it',
        'ACTIVE_VERIFIED_2026', 'SIC-3JXF0TMQ-BDRN8KQ4-F', 'https://www.acatvalchiampo.it',
        'OSINT_DIRECTORY_2026', 'it', :notes, 90, 0,
        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    )");
    $stVal->execute([
        ':sic' => $insVal,
        ':notes' => "Elenco dei Club della Valle del Chiampo e comprensorio di Arzignano."
    ]);
    echo "Inserito ACAT Valchiampo ($insVal)\n";
}

// 10. Sincronizzazione gerarchica
$pdo->query("UPDATE dependex_world_registry SET direct_children = (
    SELECT COUNT(*) FROM dependex_world_registry child WHERE child.parent_sic_id = dependex_world_registry.sic_id
)");

echo "== COMPLETATO CON SUCCESSO ==\n";
