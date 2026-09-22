<?php
require_once __DIR__ . '/bootstrap.php';

$u = current_user();
$brand = site_brand();
$sic = trim((string)($_GET['sic'] ?? $_GET['id'] ?? $_GET['club'] ?? ($_POST['sic_id'] ?? '')));

$updateMessage = null;
$updateError = null;

// Gestione Aggiornamento Self-Service Referente / Servitore-Insegnante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'self_service_update') {
    csrf_check();
    $day = trim((string)($_POST['meeting_day'] ?? ''));
    $time = trim((string)($_POST['meeting_time'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $servitore = trim((string)($_POST['servitore_insegnante'] ?? ''));
    $notes = trim((string)($_POST['notes'] ?? ''));

    if ($sic !== '') {
        $db = db();
        try {
            // Aggiorna crm_club_contacts
            $stmtCrm = $db->prepare("
                UPDATE crm_club_contacts 
                SET meeting_day = COALESCE(NULLIF(?, ''), meeting_day),
                    meeting_time = COALESCE(NULLIF(?, ''), meeting_time),
                    primary_phone = COALESCE(NULLIF(?, ''), primary_phone),
                    primary_email = COALESCE(NULLIF(?, ''), primary_email),
                    address = COALESCE(NULLIF(?, ''), address),
                    servitore_insegnante = COALESCE(NULLIF(?, ''), servitore_insegnante),
                    notes = COALESCE(NULLIF(?, ''), notes),
                    outreach_status = 'VERIFIED',
                    updated_at = CURRENT_TIMESTAMP
                WHERE sic_id = ?
            ");
            $stmtCrm->execute([$day, $time, $phone, $email, $address, $servitore, $notes, $sic]);

            // Aggiorna cat_clubs_italy
            $stmtCat = $db->prepare("
                UPDATE cat_clubs_italy 
                SET meeting_day = COALESCE(NULLIF(?, ''), meeting_day),
                    meeting_time = COALESCE(NULLIF(?, ''), meeting_time),
                    phone = COALESCE(NULLIF(?, ''), phone),
                    email = COALESCE(NULLIF(?, ''), email),
                    address = COALESCE(NULLIF(?, ''), address),
                    servitore_insegnante = COALESCE(NULLIF(?, ''), servitore_insegnante),
                    notes = COALESCE(NULLIF(?, ''), notes),
                    status = 'ACTIVE',
                    updated_at = CURRENT_TIMESTAMP
                WHERE sic_id = ?
            ");
            $stmtCat->execute([$day, $time, $phone, $email, $address, $servitore, $notes, $sic]);

            // Aggiorna dependex_world_registry se presente
            $meetingStr = trim("$day $time");
            if ($meetingStr !== '') {
                $db->prepare("
                    UPDATE dependex_world_registry 
                    SET meeting = ?,
                        phone = COALESCE(NULLIF(?, ''), phone),
                        email = COALESCE(NULLIF(?, ''), email),
                        address = COALESCE(NULLIF(?, ''), address)
                    WHERE sic_id = ?
                ")->execute([$meetingStr, $phone, $email, $address, $sic]);
            }

            audit($u ? $u['sic_id'] : 'PUBLIC_SERVITORE', 'UPDATE_CLUB_VERIFICATION', $sic, [
                'meeting_day' => $day,
                'meeting_time' => $time,
                'servitore' => $servitore
            ]);

            $updateMessage = "Scheda del Club verificata e aggiornata con successo! Le nuove informazioni sono subito visibili alle famiglie e sulla Mappa 2D.";
        } catch (Throwable $e) {
            $updateError = "Errore durante l'aggiornamento: " . $e->getMessage();
        }
    }
}

// Ricerca per sic_id o ID numerico nel registro mondiale
$st = db()->prepare("SELECT * FROM dependex_world_registry WHERE sic_id = ? OR id = ? LIMIT 1");
$st->execute([$sic, is_numeric($sic) ? (int)$sic : 0]);
$c = $st->fetch();

// Fallback: ricerca nel CRM Club Italia se non presente nel world registry
if (!$c && $sic !== '') {
    $cst = db()->prepare("SELECT * FROM crm_club_contacts WHERE sic_id = ? OR id = ? LIMIT 1");
    $cst->execute([$sic, is_numeric($sic) ? (int)$sic : 0]);
    $raw = $cst->fetch();
    if ($raw) {
        $c = [
            'id' => $raw['id'],
            'sic_id' => $raw['sic_id'],
            'entity_name' => $raw['entity_name'],
            'original_name' => $raw['entity_name'],
            'network_level' => $raw['level'],
            'city' => $raw['city'],
            'province' => $raw['province'],
            'region' => $raw['region'],
            'address' => $raw['address'],
            'postal_code' => $raw['cap'] ?? '',
            'phone' => $raw['primary_phone'],
            'email' => $raw['primary_email'],
            'website' => $raw['website'],
            'meeting' => trim(($raw['meeting_day'] ?? '') . ' ' . ($raw['meeting_time'] ?? '')),
            'meeting_day' => $raw['meeting_day'] ?? '',
            'meeting_time' => $raw['meeting_time'] ?? '',
            'servitore_insegnante' => $raw['servitore_insegnante'] ?? '',
            'notes' => $raw['notes'] ?? '',
            'families_count' => $raw['families_count'] ?? 12,
            'parent_sic_id' => '',
            'latitude' => 45.0,
            'longitude' => 12.0
        ];
    }
}

if (!$c) {
    http_response_code(404);
    $pageTitle = 'Club Non Trovato';
    require __DIR__ . '/_header.php';
    ?>
    <section class="container py-5 text-center" style="max-width:700px;margin:3rem auto;">
      <div class="card p-4 p-md-5" style="background:rgba(20,26,42,0.95);border:1px solid rgba(212,175,55,0.3);border-radius:18px;">
        <span style="font-size:3rem;display:block;margin-bottom:1rem;">🔍</span>
        <h1 style="color:#FFFFFF;font-size:1.8rem;margin-bottom:12px;">Scheda Club Non Trovata</h1>
        <p style="color:#cbd5e1;line-height:1.6;">L'identificativo richiesto non corrisponde ad alcun Club o Associazione nel registro ufficiale.</p>
        <div style="margin-top:2rem;">
          <a href="/world-club-explorer.php" class="btn" style="background:linear-gradient(135deg,#D4AF37,#b38f2a);color:#000;font-weight:700;padding:12px 24px;border-radius:10px;text-decoration:none;">
            <?=dx_icon('search','',16)?> Esplora la Directory dei Club
          </a>
        </div>
      </div>
    </section>
    <?php
    require __DIR__ . '/_footer.php';
    exit;
}

// Gestione Contact Bridge Zero Barriere "Vorrei partecipare al prossimo incontro"
$contactBridgeSuccess = null;
$contactBridgeError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact_bridge_request') {
    csrf_check();
    
    // Anti-spam honeypot
    if (!empty($_POST['website_url'])) {
        $contactBridgeSuccess = "Grazie! La tua richiesta di partecipazione è stata registrata.";
    } else {
        $contactName = trim((string)($_POST['contact_name'] ?? 'Una persona / Famiglia'));
        if ($contactName === '') {
            $contactName = 'Una persona / Famiglia (Riservato)';
        }
        $contactPhone = trim((string)($_POST['contact_phone'] ?? ''));
        $contactEmail = trim((string)($_POST['contact_email'] ?? ''));
        $contactMethod = trim((string)($_POST['contact_method'] ?? 'DIRETTO'));
        $messageText = trim((string)($_POST['message'] ?? ''));
        if ($messageText === '') {
            $messageText = 'Vorrei partecipare al prossimo incontro del Club.';
        }

        try {
            $db = db();
            $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . 'dependex_salt_2026');

            $stInq = $db->prepare("
                INSERT INTO crm_club_inquiries (sic_id, entity_name, contact_name, contact_phone, contact_email, contact_method, message, status, ip_hash, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'NEW', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $stInq->execute([
                $c['sic_id'] ?: $sic,
                $c['entity_name'],
                $contactName,
                $contactPhone,
                $contactEmail,
                $contactMethod,
                $messageText,
                $ipHash
            ]);

            // Invio Notifica Email al Referente del Club e alla Segreteria Centrale
            $toClubEmail = !empty($c['email']) ? $c['email'] : 'info@dependex.support';
            $meetingInfo = trim(($c['meeting_day'] ?? '') . ' ' . ($c['meeting_time'] ?? ''));
            $clubLoc = implode(' · ', array_filter([$c['address'] ?? '', $c['city'] ?? '', $c['province'] ?? '']));

            $subject = "Nuova Richiesta di Partecipazione al Club: " . $c['entity_name'];
            $body = "Gentile Referente / Servitore-Insegnante,\n\n"
                  . "Una persona o famiglia del tuo territorio ha espresso il desiderio di partecipare al prossimo incontro del vostro Club tramite il Contact Bridge 'Zero Barriere' di Dependex.social.\n\n"
                  . "RIFERIMENTI RISERVATI DELLA RICHIESTA:\n"
                  . "-----------------------------------------\n"
                  . "• Club: " . $c['entity_name'] . "\n"
                  . "• Sede: " . ($clubLoc ?: 'Territoriale') . "\n"
                  . "• Incontro: " . ($meetingInfo ?: 'Settimanale') . "\n"
                  . "• Nome / Riferimento: " . $contactName . "\n"
                  . "• Recapito fornito: " . ($contactPhone ?: ($contactEmail ?: 'Partecipazione diretta di persona')) . "\n"
                  . "• Canale preferito: " . $contactMethod . "\n"
                  . "• Messaggio: " . $messageText . "\n"
                  . "-----------------------------------------\n\n"
                  . "Grazie per la vostra opera e accoglienza solidale.\n"
                  . "Rete Territoriale Dependex.social · info@dependex.support\n"
                  . "Numero Verde Nazionale AICAT: 800 974250";

            if (function_exists('send_event_email_async')) {
                send_event_email_async($toClubEmail, $subject, $body);
            } else {
                @mail($toClubEmail, $subject, $body, "From: info@dependex.support\r\nReply-To: info@dependex.support\r\nX-Mailer: PHP/" . phpversion());
            }

            // Invio Notifica Telegram (se configurato canale o bot)
            $tgText = "🤝 <b>Nuova Partecipazione al Club!</b>\n\n"
                    . "<b>Club:</b> " . htmlspecialchars($c['entity_name']) . " (" . htmlspecialchars($c['city'] ?? '') . ")\n"
                    . "<b>Nome:</b> " . htmlspecialchars($contactName) . "\n"
                    . "<b>Recapito:</b> " . htmlspecialchars($contactPhone ?: ($contactEmail ?: 'Presenza diretta')) . "\n"
                    . "<b>Canale:</b> " . htmlspecialchars($contactMethod) . "\n"
                    . "<b>Messaggio:</b> " . htmlspecialchars($messageText);
            send_telegram_notification($tgText);

            audit($u ? $u['sic_id'] : 'PUBLIC_GUEST', 'CONTACT_BRIDGE_PARTICIPATION_REQUEST', $c['sic_id'], [
                'entity' => $c['entity_name'],
                'method' => $contactMethod
            ]);

            $contactBridgeSuccess = "Grazie di cuore! La tua intenzione di partecipare è stata trasmessa al referente del Club nella massima riservatezza. Ti aspettiamo con gioia: sarai accolto a braccia aperte, senza alcun giudizio o obbligo di parlare.";
        } catch (Throwable $e) {
            $contactBridgeError = "Si è verificato un errore durante l'invio. Puoi comunque contattare direttamente il referente o chiamare il Numero Verde 800 974250.";
        }
    }
}

// Export vCard (.vcf) per salvataggio istantaneo nella rubrica dello smartphone
if (isset($_GET['vcard'])) {
    $cleanFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $c['entity_name'] ?: 'Club_Territoriale');
    header('Content-Type: text/vcard; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $cleanFilename . '.vcf"');
    echo "BEGIN:VCARD\r\n";
    echo "VERSION:3.0\r\n";
    echo "FN:" . $c['entity_name'] . "\r\n";
    echo "ORG:Rete Hudolin - Club Alcologico Territoriale\r\n";
    if (!empty($c['phone'])) {
        echo "TEL;TYPE=VOICE,WORK:" . preg_replace('/[^0-9+]/', '', $c['phone']) . "\r\n";
    }
    if (!empty($c['email'])) {
        echo "EMAIL;TYPE=INTERNET,PREF:" . $c['email'] . "\r\n";
    }
    if (!empty($c['address']) || !empty($c['city'])) {
        echo "ADR;TYPE=WORK:;;" . ($c['address'] ?? '') . ";" . ($c['city'] ?? '') . ";" . ($c['province'] ?? '') . ";" . ($c['postal_code'] ?? '') . ";Italia\r\n";
    }
    echo "URL:" . ('https://' . ($brand['domain'] ?? 'dependex.social') . '/club/' . urlencode($c['sic_id'])) . "\r\n";
    echo "NOTE:Club Territoriale Metodo Hudolin. Incontri settimanali gratuiti e riservati per famiglie.\\nCodice SIC: " . $c['sic_id'] . "\r\n";
    echo "END:VCARD\r\n";
    exit;
}

// SEO & Meta
$pageTitle = $c['entity_name'] . ' · Club Alcologico Territoriale ' . ($c['city'] ? 'a ' . $c['city'] : '');
$locParts = array_filter([$c['city'], $c['province'], $c['region']]);
$metaDesc = 'Scheda informativa di ' . $c['entity_name'] . ' (' . implode(', ', $locParts) . '). Incontri gratuiti per famiglie e persone, metodo Hudolin, accoglienza e sobrietà senza giudizio.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/club/' . urlencode($c['sic_id']);

// Ricerca dell'ente genitore (es. ACAT territoriale per un CAT locale)
$parentEntity = null;
if (!empty($c['parent_sic_id'])) {
    $pst = db()->prepare("SELECT * FROM dependex_world_registry WHERE sic_id = ? LIMIT 1");
    $pst->execute([$c['parent_sic_id']]);
    $parentEntity = $pst->fetch();
}

// Ricerca dei Club figli (se questo nodo è un'ACAT territoriale)
$childClubs = [];
if (in_array($c['network_level'], ['TERRITORIAL', 'REGIONAL'], true)) {
    $cst = db()->prepare("SELECT * FROM dependex_world_registry WHERE parent_sic_id = ? ORDER BY city ASC, entity_name ASC LIMIT 50");
    $cst->execute([$c['sic_id']]);
    $childClubs = $cst->fetchAll();
}

// Preparazione Dati Strutturati Schema.org JSON-LD
$schemaOrg = [
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => ["CommunityCenter", "NGO"],
            "@id" => $canonicalUrl . "/#organization",
            "name" => $c['entity_name'],
            "alternateName" => $c['original_name'] ?: $c['entity_name'],
            "description" => $metaDesc,
            "url" => $canonicalUrl,
            "telephone" => $c['phone'] ?: "+39-800-974250",
            "email" => $c['email'] ?: "info@dependex.support",
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => $c['address'] ?: "Presidio Territoriale",
                "addressLocality" => $c['city'] ?: ($c['province'] ?: "Italia"),
                "addressRegion" => $c['region'] ?: "IT",
                "postalCode" => $c['postal_code'] ?: "",
                "addressCountry" => "IT"
            ]
        ],
        [
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Home",
                    "item" => "https://" . ($brand['domain'] ?? 'dependex.social') . "/"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => "Directory Club",
                    "item" => "https://" . ($brand['domain'] ?? 'dependex.social') . "/world-club-explorer.php"
                ],
                [
                    "@type" => "ListItem",
                    "position" => 3,
                    "name" => $c['region'] ?: "Italia",
                    "item" => "https://" . ($brand['domain'] ?? 'dependex.social') . "/world-club-explorer.php?region=" . urlencode($c['region'])
                ],
                [
                    "@type" => "ListItem",
                    "position" => 4,
                    "name" => $c['entity_name'],
                    "item" => $canonicalUrl
                ]
            ]
        ]
    ]
];

if (!empty($c['latitude']) && !empty($c['longitude'])) {
    $schemaOrg['@graph'][0]['geo'] = [
        "@type" => "GeoCoordinates",
        "latitude" => (float)$c['latitude'],
        "longitude" => (float)$c['longitude']
    ];
}

require __DIR__ . '/_header.php';
?>

<!-- Schema.org JSON-LD Inserito nel corpo pagina -->
<script type="application/ld+json">
<?=json_encode($schemaOrg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)?>
</script>

<main class="container" style="max-width:1100px;margin:1.5rem auto 4rem;padding:0 1rem;">

  <!-- BREADCRUMBS -->
  <nav aria-label="breadcrumb" style="margin-bottom:1.5rem;font-size:0.86rem;color:#94a3b8;">
    <a href="/" style="color:#D4AF37;text-decoration:none;">Home</a>
    <span style="margin:0 6px;">/</span>
    <a href="/world-club-explorer.php" style="color:#D4AF37;text-decoration:none;">Directory Club</a>
    <?php if ($c['region']): ?>
      <span style="margin:0 6px;">/</span>
      <a href="/world-club-explorer.php?region=<?=urlencode($c['region'])?>" style="color:#D4AF37;text-decoration:none;"><?=h($c['region'])?></a>
    <?php endif; ?>
    <span style="margin:0 6px;">/</span>
    <span style="color:#e2e8f0;"><?=h($c['entity_name'])?></span>
  </nav>

  <?php if ($updateMessage): ?>
    <div class="p-3 mb-4 d-flex align-items-center gap-3" style="background:rgba(34,197,94,0.15); border:1px solid #22c55e; border-radius:12px; color:#ffffff;">
      <?=dx_icon('check-circle', 'text-success', 22)?>
      <div style="font-size:0.95rem; font-weight:600;"><?=h($updateMessage)?></div>
    </div>
  <?php endif; ?>

  <?php if ($updateError): ?>
    <div class="p-3 mb-4 d-flex align-items-center gap-3" style="background:rgba(239,68,68,0.15); border:1px solid #ef4444; border-radius:12px; color:#ffffff;">
      <?=dx_icon('alert-circle', 'text-danger', 22)?>
      <div style="font-size:0.95rem; font-weight:600;"><?=h($updateError)?></div>
    </div>
  <?php endif; ?>

  <!-- HERO CARD DEL CLUB -->
  <section class="card p-4 p-md-5 mb-4" style="background:radial-gradient(ellipse at top, rgba(28,36,58,0.95), rgba(12,16,26,0.98));border:1px solid rgba(212,175,55,0.4);border-radius:20px;box-shadow:0 12px 40px rgba(0,0,0,0.6);">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:16px;">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;flex-wrap:wrap;">
          <?php $statusMeta = \Dependex\Clubs\ClubMetricsService::resolveClubStatus($c); ?>
          <span class="badge" style="<?=$statusMeta['badge_style']?>font-size:0.8rem;padding:4px 10px;border-radius:6px;font-weight:700;" title="<?=h($statusMeta['description'])?>">
            <?=dx_icon('shield', '', 12)?> <?=h($statusMeta['label'])?>
          </span>
          <span class="badge" style="background:rgba(212,175,55,0.18);color:#ffd700;border:1px solid #ffd700;font-size:0.8rem;padding:4px 10px;border-radius:6px;font-weight:700;">
            <?=h($c['network_level'])?>
          </span>
          <span class="badge" style="background:rgba(255,255,255,0.08);color:#cbd5e1;font-size:0.8125rem;padding:4px 8px;border-radius:6px;">
            Codice: <?=h($c['sic_id'])?>
          </span>
        </div>
        <h1 style="color:#FFFFFF;font-size:2.2rem;font-weight:900;margin:0 0 8px;font-family:var(--font-serif);line-height:1.2;">
          <?=h($c['entity_name'])?>
        </h1>
        <p style="color:#38bdf8;font-size:1.1rem;font-weight:600;margin:0;">
          <?=dx_icon('map-pin', '', 16)?> <?=h(implode(' · ', array_filter([$c['address'], $c['city'], $c['province'], $c['region']])))?>
        </p>
      </div>

      <!-- BOTTONE DI CONDIVISIONE RAPIDA FAMIGLIARE -->
      <?php
      $shareText = rawurlencode("Ti invio le informazioni su questo Club Alcologico Territoriale: " . $c['entity_name'] . " (" . $c['city'] . ") - " . $canonicalUrl);
      ?>
      <div style="align-self:flex-start;">
        <a href="https://api.whatsapp.com/send?text=<?=$shareText?>" target="_blank" rel="noopener" class="btn" style="background:rgba(37,211,102,0.15);color:#25D366;border:1px solid rgba(37,211,102,0.5);font-size:0.85rem;padding:8px 14px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-weight:700;">
          <?=dx_icon('message-circle', '', 16)?> Condividi su WhatsApp
        </a>
      </div>
    </div>

    <!-- BANNER RASSICURAZIONE -->
    <div style="background:rgba(255,255,255,0.03);border-left:4px solid #D4AF37;padding:12px 18px;border-radius:0 10px 10px 0;margin:1.8rem 0;color:#e2e8f0;font-size:0.95rem;line-height:1.5;">
      <b>Incontri Settimanali Gratuiti & Senza Pregiudizio:</b> Il Club è una comunità aperta alle famiglie e ai singoli, fondata sul rispetto, sull'amicizia e sulla riservatezza assoluta. Non ci sono quote di iscrizione né etichette.
    </div>

    <!-- ======================================================== -->
    <!-- POTENZIAMENTO 2: CONTACT BRIDGE ZERO BARRIERE PER I CLUB -->
    <!-- ======================================================== -->
    <div class="card p-4 p-md-4 mb-4" id="contact-bridge-card" style="background: radial-gradient(ellipse at top left, rgba(16,36,56,0.95), rgba(10,14,24,0.98)); border: 1.5px solid rgba(0, 212, 255, 0.45); border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.45);">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
          <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(0,212,255,0.12);color:#00d4ff;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:750;letter-spacing:0.04em;text-transform:uppercase;margin-bottom:8px;border:1px solid rgba(0,212,255,0.3);">
            <span style="width:8px;height:8px;border-radius:50%;background:#00d4ff;box-shadow:0 0 8px #00d4ff;"></span>
            <span>Contact Bridge · Zero Barriere</span>
          </div>
          <h2 style="color:#ffffff;font-size:clamp(1.25rem, 2.5vw, 1.6rem);font-weight:800;margin:0 0 6px;line-height:1.3;">
            Vorrei partecipare al prossimo incontro
          </h2>
          <p style="color:#cbd5e1;font-size:0.92rem;margin:0;max-width:720px;line-height:1.5;">
            Nessun modulo formale né scheda di iscrizione. Puoi semplicemente far sapere al referente del Club che sarai presente in forma anonima o riservata, oppure contattarlo su WhatsApp.
          </p>
        </div>
      </div>

      <?php if ($contactBridgeSuccess): ?>
        <div class="p-3 mt-3 d-flex align-items-center gap-3" style="background:rgba(34,197,94,0.18); border:1px solid #22c55e; border-radius:12px; color:#ffffff;">
          <?=dx_icon('check-circle', 'text-success', 24)?>
          <div style="font-size:0.95rem; font-weight:600;"><?=h($contactBridgeSuccess)?></div>
        </div>
      <?php endif; ?>

      <?php if ($contactBridgeError): ?>
        <div class="p-3 mt-3 d-flex align-items-center gap-3" style="background:rgba(239,68,68,0.18); border:1px solid #ef4444; border-radius:12px; color:#ffffff;">
          <?=dx_icon('alert-circle', 'text-danger', 24)?>
          <div style="font-size:0.95rem; font-weight:600;"><?=h($contactBridgeError)?></div>
        </div>
      <?php endif; ?>

      <!-- OPZIONI DI CONTATTO E PARTECIPAZIONE ZERO ATTRITO -->
      <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:1.25rem;">
        <?php
        $cleanPhone = preg_replace('/[^0-9+]/', '', $c['phone'] ?? '');
        if ($cleanPhone):
          $waText = rawurlencode("Salve, ho visto la scheda del vostro Club (" . $c['entity_name'] . ") su Dependex e vorrei partecipare al prossimo incontro con la mia famiglia.");
        ?>
          <a href="https://wa.me/<?=ltrim($cleanPhone, '+')?>?text=<?=$waText?>" target="_blank" rel="noopener" class="btn" style="background:linear-gradient(135deg, #25D366, #128C7E);color:#070a12;font-weight:750;padding:10px 18px;border-radius:10px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:0.9rem;box-shadow:0 4px 15px rgba(37,211,102,0.3);min-height:44px;">
            <?=dx_icon('message-circle', '', 18)?>
            <span>Scrivi su WhatsApp al Referente</span>
          </a>
        <?php endif; ?>

        <button type="button" onclick="toggleContactBridgeForm()" class="btn" style="background:rgba(0,212,255,0.15);color:#00d4ff;border:1px solid #00d4ff;font-weight:700;padding:10px 18px;border-radius:10px;display:inline-flex;align-items:center;gap:8px;font-size:0.9rem;cursor:pointer;min-height:44px;">
          <?=dx_icon('heart', '', 18)?>
          <span>Avvisa con 1 Tocco che Verrai (Riservato)</span>
        </button>

        <a href="tel:800974250" class="btn" style="background:rgba(255,255,255,0.06);color:#ffd700;border:1px solid rgba(255,215,0,0.3);font-weight:700;padding:10px 18px;border-radius:10px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:0.9rem;min-height:44px;">
          <?=dx_icon('phone', '', 16)?>
          <span>Numero Verde AICAT: 800 974250</span>
        </a>
      </div>

      <!-- MODULO RISERVATO A BASSA SOGLIA -->
      <div id="contactBridgeBox" style="display: <?=$contactBridgeSuccess ? 'none' : 'none'?>; margin-top: 1.5rem; background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.12); border-radius: 14px; padding: 20px;">
        <form method="post" action="#contact-bridge-card">
          <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
          <input type="hidden" name="action" value="contact_bridge_request">
          <input type="hidden" name="sic_id" value="<?=h($c['sic_id'] ?: $c['id'])?>">
          <!-- Honeypot anti-bot -->
          <div style="display:none;" aria-hidden="true">
            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label style="font-size: 0.82rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Come preferisci farti chiamare? (Facoltativo)</label>
              <input type="text" name="contact_name" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="es. Marco, Una famiglia, o lascia vuoto">
            </div>

            <div class="col-md-6">
              <label style="font-size: 0.82rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Recapito per risposta o conferma (WhatsApp / Telefono o Email)</label>
              <input type="text" name="contact_phone" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="es. Cellulare o email (lascia vuoto se vieni direttamente)">
            </div>

            <div class="col-md-6">
              <label style="font-size: 0.82rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Canale preferito di accoglienza</label>
              <select name="contact_method" class="form-select form-select-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;">
                <option value="WHATSAPP">Messaggio WhatsApp</option>
                <option value="TELEFONO">Telefonata Riservata</option>
                <option value="EMAIL">Email</option>
                <option value="DIRETTO">Verrò direttamente all'incontro (avviso di presenza)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label style="font-size: 0.82rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Vuoi farci sapere qualcosa prima? (Facoltativo)</label>
              <input type="text" name="message" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="es. Saremo in due, è la nostra prima volta">
            </div>
          </div>

          <div class="mt-3 text-end d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span style="font-size:0.78rem;color:#94a3b8;">
              <?=dx_icon('shield', 'text-amber', 12)?> Nessun obbligo di parlare né schedatura. Massimo rispetto della privacy.
            </span>
            <button type="submit" class="btn" style="background:linear-gradient(135deg,#00d4ff,#0077ff);color:#070a12;font-weight:800;padding:8px 20px;border-radius:8px;border:none;font-size:0.88rem;cursor:pointer;min-height:44px;">
              <?=dx_icon('send', '', 14)?> Invia Avviso Riservato di Partecipazione
            </button>
          </div>
        </form>
      </div>
    </div>

    <script>
    function toggleContactBridgeForm(forceOpen = false) {
      const box = document.getElementById('contactBridgeBox');
      if (!box) return;
      if (forceOpen) {
        box.style.display = 'block';
      } else {
        box.style.display = (box.style.display === 'none' || box.style.display === '') ? 'block' : 'none';
      }
      if (box.style.display === 'block') {
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }
    </script>

    <!-- AZIONI PRINCIPALI DELLA PORTA D'INGRESSO DEL CLUB -->
    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:1.5rem;">
      <a href="#scheda-operativa" class="btn-community-primary" style="padding:10px 20px;font-size:0.95rem;">
        <?=dx_icon('users', '', 18)?>
        <span>Conosci questo Club</span>
      </a>

      <?php if (!empty($c['phone'])): ?>
        <a href="tel:<?=h(preg_replace('/[^0-9+]/', '', $c['phone']))?>" class="btn-community-outline" style="padding:10px 18px;font-size:0.95rem;">
          <?=dx_icon('phone', 'text-emerald', 18)?> Chiama: <?=h($c['phone'])?>
        </a>
      <?php endif; ?>

      <!-- PULSANTE AGGIORNAMENTO SELF-SERVICE PER SERVITORE-INSEGNANTE -->
      <a href="#aggiorna-scheda" onclick="toggleServitoreBox(true)" class="btn-community-outline" style="padding:10px 18px;font-size:0.95rem;border-color:rgba(253,230,138,0.4);color:#fde68a;">
        <?=dx_icon('edit', 'text-neon-gold', 16)?> Sei il Servitore? Aggiorna Scheda
      </a>

      <?php if (!empty($c['latitude']) && !empty($c['longitude'])): ?>
        <a href="https://www.google.com/maps/dir/?api=1&destination=<?=urlencode($c['latitude'] . ',' . $c['longitude'])?>" target="_blank" rel="noopener" class="btn-community-outline" style="padding:10px 18px;font-size:0.95rem;">
          <?=dx_icon('navigation', 'text-amber', 16)?> Come Arrivare (Maps)
        </a>
      <?php endif; ?>

      <!-- SCARICA CONTATTO VCARD .VCF PER RUBRICA SMARTPHONE -->
      <a href="?sic=<?=urlencode($c['sic_id'] ?: $c['id'])?>&vcard=1" class="btn-community-outline" style="padding:10px 18px;font-size:0.95rem;" title="Salva recapiti del Club nella rubrica del telefono">
        <?=dx_icon('download', 'text-amber', 16)?> Salva in Rubrica (.vcf)
      </a>

      <!-- CONDIVIDI SCHEDA TRAMITE WEB SHARE API O APPUNTI -->
      <button type="button" onclick="window.dxShareClub('<?=addslashes(h($c['entity_name']))?>', 'Informazioni sul Club Territoriale: <?=addslashes(h($c['entity_name']))?>', '<?=addslashes($canonicalUrl)?>')" class="btn-community-outline" style="padding:10px 18px;font-size:0.95rem;cursor:pointer;background:transparent;">
        <?=dx_icon('share-2', 'text-cyan', 16)?> Condividi Scheda
      </button>

      <a href="/parla-con-noi.php" class="btn-community-outline" style="padding:10px 18px;font-size:0.95rem;">
        <?=dx_icon('message-circle', 'text-cyan', 16)?> Vuoi parlare prima con noi?
      </a>
    </div>
  </section>

  <!-- DETTAGLI E INFORMAZIONI OPERATIVE -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%, 300px),1fr));gap:20px;margin-bottom:2.5rem;">
    
    <!-- SCHEDA DATI TECNICI -->
    <div class="card p-4" id="scheda-operativa" style="background:rgba(18,24,38,0.95);border:1px solid rgba(255,255,255,0.1);border-radius:16px;">
      <h3 style="color:#FFFFFF;font-size:1.15rem;margin:0 0 16px;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:10px;display:flex;align-items:center;gap:8px;">
        <?=dx_icon('info', 'text-amber', 18)?> Scheda Operativa & Incontri
      </h3>
      <dl style="display:grid;grid-template-columns:110px 1fr;gap:12px;margin:0;font-size:0.92rem;">
        <dt style="color:#cbd5e1;font-weight:600;">Comune:</dt>
        <dd style="color:#FFFFFF;margin:0;font-weight:700;"><?=h($c['city'] ?: '—')?></dd>

        <dt style="color:#cbd5e1;font-weight:600;">Provincia:</dt>
        <dd style="color:#FFFFFF;margin:0;"><?=h($c['province'] ?: '—')?> (<?=h($c['region'] ?: 'Italia')?>)</dd>

        <dt style="color:#cbd5e1;font-weight:600;">Sede:</dt>
        <dd style="color:#FFFFFF;margin:0;"><?=h($c['address'] ?: 'Contattare il referente territoriale')?></dd>

        <dt style="color:#cbd5e1;font-weight:600;">Incontri:</dt>
        <dd style="color:#00ff77;margin:0;font-weight:700;">
          <?=h(trim(($c['meeting_day'] ?? '') . ' ' . ($c['meeting_time'] ?? '')) ?: 'Settimanale (Orario su richiesta)')?>
        </dd>

        <dt style="color:#cbd5e1;font-weight:600;">Telefono:</dt>
        <dd style="color:#FFFFFF;margin:0;"><?=h($c['phone'] ?: '800 974250 (Numero Verde AICAT)')?></dd>

        <dt style="color:#cbd5e1;font-weight:600;">Email:</dt>
        <dd style="color:#FFFFFF;margin:0;"><?=h($c['email'] ?: 'info@dependex.support')?></dd>
      </dl>

      <?php if (!empty($c['notes'])): ?>
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.08);font-size:0.88rem;color:#cbd5e1;">
          <b style="color:#D4AF37;display:block;margin-bottom:4px;">Note territoriali:</b>
          <?=nl2br(h($c['notes']))?>
        </div>
      <?php endif; ?>
    </div>

    <!-- BOX COSA SUCCEDE AL PRIMO INCONTRO -->
    <div class="card p-4" style="background:rgba(18,24,38,0.95);border:1px solid rgba(212,175,55,0.3);border-radius:16px;">
      <h3 style="color:#FFFFFF;font-size:1.15rem;margin:0 0 16px;border-bottom:1px solid rgba(255,255,255,0.1);padding-bottom:10px;display:flex;align-items:center;gap:8px;">
        <?=dx_icon('heart', 'text-neon-green', 18)?> Cosa succede al primo incontro?
      </h3>
      <ul style="margin:0;padding-left:1.2rem;color:#cbd5e1;font-size:0.9rem;line-height:1.6;">
        <li style="margin-bottom:8px;"><b>Nessun obbligo di parlare:</b> puoi semplicemente ascoltare le esperienze delle altre famiglie.</li>
        <li style="margin-bottom:8px;"><b>Partecipazione con i famigliari:</b> il metodo Hudolin coinvolge l'intera famiglia e le persone vicine.</li>
        <li style="margin-bottom:8px;"><b>Assoluto rispetto:</b> non verrai giudicato né catalogato. Al Club siamo tutti compagni di cammino.</li>
        <li><b>Completamente gratuito:</b> non sono previsti costi di iscrizione o tariffe.</li>
      </ul>
      <div style="margin-top:1.4rem;background:rgba(212,175,55,0.1);padding:10px 14px;border-radius:8px;border:1px solid rgba(212,175,55,0.25);font-size:0.84rem;color:#ffd700;">
        <?=dx_icon('check-circle', '', 14)?> Presenza costante di un <b>Servitore Insegnante</b> formato secondo il Metodo Hudolin.
      </div>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MODULO SELF-SERVICE: VERIFICA & AGGIORNAMENTO SCHEDA      -->
  <!-- ======================================================== -->
  <section class="card p-4 p-md-5 mb-4" id="aggiorna-scheda" style="background: radial-gradient(ellipse at top, rgba(20,26,45,0.95), rgba(10,13,22,0.98)); border: 1px solid rgba(224, 169, 109, 0.4); border-radius: 20px; box-shadow: var(--rainbow-glow);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
      <div>
        <div class="badge-neon-rainbow mb-2" style="font-size: 0.72rem;">
          <span class="dot"></span>
          <span style="color:#fde68a;">CENSIMENTO APERTO 2026 · RETE HUDOLIN</span>
        </div>
        <h3 style="font-family: var(--font-serif); font-size: clamp(1.3rem, 2.5vw, 1.8rem); color: #ffffff; font-weight: 800; margin: 0 0 6px;">
          Sei il Servitore-Insegnante di questo Club?
        </h3>
        <p style="color: #cbd5e1; font-size: 0.92rem; line-height: 1.5; margin: 0;">
          Aiutaci a garantire informazioni puntuali alle famiglie in cerca di accoglienza. Conferma o aggiorna giorno, orario e recapiti di riunione.
        </p>
      </div>
      <button type="button" class="btn small" onclick="toggleServitoreBox()" style="border: 1px solid rgba(253,230,138,0.4); color: #fde68a; border-radius: 10px; font-size: 0.82rem;">
        <?=dx_icon('edit', 'text-neon-gold', 14)?> Mostra / Nascondi Modulo
      </button>
    </div>

    <div id="servitoreFormBox" style="display: <?=$updateMessage ? 'none' : 'block'?>; margin-top: 1.5rem; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 24px;">
      <form method="post" action="#aggiorna-scheda">
        <input type="hidden" name="<?=CSRF_KEY?>" value="<?=h(csrf_token())?>">
        <input type="hidden" name="action" value="self_service_update">
        <input type="hidden" name="sic_id" value="<?=h($c['sic_id'])?>">

        <div class="row g-3">
          <div class="col-md-6">
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Giorno della Riunione Settimanale</label>
            <select name="meeting_day" class="form-select form-select-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;">
              <?php
              $allDays = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica', 'Da concordare'];
              $currentDay = $c['meeting_day'] ?? '';
              ?>
              <option value="">-- Seleziona Giorno --</option>
              <?php foreach ($allDays as $d): ?>
                <option value="<?=h($d)?>" <?=stripos((string)$currentDay, $d)!==false ? 'selected' : ''?>><?=h($d)?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Orario di Incontro (es. 20:30)</label>
            <input type="text" name="meeting_time" value="<?=h($c['meeting_time'] ?? '20:30')?>" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="es. 20:30">
          </div>

          <div class="col-md-12">
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Indirizzo Esatto / Sede di Riunione</label>
            <input type="text" name="address" value="<?=h($c['address'] ?? '')?>" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="Via, Piazza, Numero civico, Parrocchia o Centro civico">
          </div>

          <div class="col-md-6">
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Telefono di Riferimento per le Famiglie</label>
            <input type="tel" name="phone" value="<?=h($c['phone'] ?? '')?>" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="es. 340 1234567">
          </div>

          <div class="col-md-6">
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Email di Contatto Club / Referente</label>
            <input type="email" name="email" value="<?=h($c['email'] ?? '')?>" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="es. club@dominio.it">
          </div>

          <div class="col-md-6">
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Nome Servitore-Insegnante / Referente (Opzionale)</label>
            <input type="text" name="servitore_insegnante" value="<?=h($c['servitore_insegnante'] ?? '')?>" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="Nome e Cognome o solo Nome">
          </div>

          <div class="col-md-6">
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">Indicazioni Utili per Nuovi Partecipanti (Opzionale)</label>
            <input type="text" name="notes" value="<?=h($c['notes'] ?? '')?>" class="form-control form-control-sm" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="es. Ingresso laterale, citofonare Sala Club">
          </div>
        </div>

        <div class="mt-4 text-end">
          <button type="submit" class="btn-rainbow-neon small" style="padding: 10px 24px; font-size: 0.9rem; font-weight: 700;">
            <?=dx_icon('check-circle', '', 16)?> Salva e Convalida Scheda Club
          </button>
        </div>
      </form>
    </div>
  </section>

  <script>
  function toggleServitoreBox(forceOpen = false) {
    const box = document.getElementById('servitoreFormBox');
    if (!box) return;
    if (forceOpen) {
      box.style.display = 'block';
    } else {
      box.style.display = (box.style.display === 'none') ? 'block' : 'none';
    }
  }
  </script>

  <!-- GERARCHIA TERRITORIALE E COLLEGAMENTI -->
  <?php if ($parentEntity): ?>
    <section class="card p-4 mb-4" style="background:rgba(18,24,38,0.95);border:1px solid rgba(0,212,255,0.3);border-radius:16px;">
      <h3 style="color:#00d4ff;font-size:1.05rem;margin:0 0 10px;font-weight:700;display:flex;align-items:center;gap:8px;">
        <?=dx_icon('layers', '', 16)?> Associazione Territoriale di Riferimento (ACAT)
      </h3>
      <p style="color:#cbd5e1;font-size:0.9rem;margin-bottom:12px;">
        Questo Club locale è coordinato da: <b><?=h($parentEntity['entity_name'])?></b>
      </p>
      <a href="/club/<?=urlencode($parentEntity['sic_id'])?>" class="btn" style="background:rgba(0,212,255,0.15);color:#00d4ff;border:1px solid #00d4ff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:0.9rem;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
        <?=dx_icon('arrow-right', '', 14)?> Visualizza la Scheda ACAT di Riferimento
      </a>
    </section>
  <?php endif; ?>

  <?php if (!empty($childClubs)): ?>
    <section class="card p-4 mb-4" style="background:rgba(18,24,38,0.95);border:1px solid rgba(0,255,119,0.3);border-radius:16px;">
      <h3 style="color:#00ff77;font-size:1.15rem;margin:0 0 14px;font-weight:800;display:flex;align-items:center;gap:8px;">
        <?=dx_icon('users', '', 18)?> Club Territoriali Coordinati (<?=count($childClubs)?> Club)
      </h3>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%, 260px),1fr));gap:12px;">
        <?php foreach ($childClubs as $ch): ?>
          <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);padding:12px;border-radius:10px;display:flex;flex-direction:column;justify-content:space-between;">
            <div>
              <b style="color:#FFFFFF;display:block;font-size:0.95rem;margin-bottom:4px;"><?=h($ch['entity_name'])?></b>
              <span style="color:#cbd5e1;font-size:0.85rem;"><?=h($ch['city'] ?: $ch['province'])?></span>
            </div>
            <div style="margin-top:10px;">
              <a href="/club/<?=urlencode($ch['sic_id'])?>" style="color:#00ff77;font-size:0.85rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                Dettagli Club <?=dx_icon('arrow-right', '', 12)?>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- SUPPORTO CENTRALE & NUMERO VERDE -->
  <section class="card p-4 text-center" style="background:rgba(10,14,24,0.9);border:1px solid rgba(212,175,55,0.25);border-radius:16px;margin-top:2rem;">
    <h4 style="color:#FFFFFF;margin:0 0 8px;font-size:1.1rem;font-weight:700;">Hai bisogno di assistenza o orientamento?</h4>
    <p style="color:#cbd5e1;font-size:0.9rem;max-width:640px;margin:0 auto 16px;">
      Se il numero del Club locale non risponde o hai bisogno di capire quale Club sia più adatto alla tua situazione familiare:
    </p>
    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:14px;align-items:center;">
      <a href="tel:800974250" class="btn" style="background:rgba(212,175,55,0.15);color:#ffd700;border:1px solid #ffd700;font-weight:800;padding:10px 18px;border-radius:8px;text-decoration:none;">
        <?=dx_icon('phone', '', 16)?> Numero Verde Nazionale AICAT: 800 974250
      </a>
      <a href="/world-club-explorer.php" class="btn" style="background:rgba(255,255,255,0.08);color:#FFFFFF;border:1px solid rgba(255,255,255,0.2);padding:10px 18px;border-radius:8px;text-decoration:none;">
        <?=dx_icon('map', '', 16)?> Torna alla Mappa Completa
      </a>
    </div>
  </section>

</main>

<?php require __DIR__ . '/_footer.php'; ?>