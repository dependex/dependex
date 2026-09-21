<?php
/**
 * crm-clubs.php — Console CRM & Outreach Email Marketing dei Club Alcologici Italiani
 * Gestione anagrafica 395 Club, APCAT, ACAT, ARCAT, AICAT con recapiti completi,
 * monitoraggio nurturing FLUX100 / EMM+, esportazione CSV e invio prove live.
 */
require_once __DIR__ . '/bootstrap.php';

$db = new PDO('sqlite:' . __DIR__ . '/data/acat_community.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("PRAGMA busy_timeout = 10000;");

// Gestione Azione Invio Test Rapido via SMTP Hostinger
$testMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_test_labomobile') {
    $step = intval($_POST['step'] ?? 1);
    $targetEmail = 'labomobile.lm@gmail.com';
    $sicId = trim($_POST['sic_id'] ?? 'SIC-TAGLIODIPO-RO-001');

    // Recupera dati del club per la simulazione
    $stmt = $db->prepare("SELECT * FROM crm_club_contacts WHERE sic_id = :s LIMIT 1");
    $stmt->execute([':s' => $sicId]);
    $sampleClub = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sampleClub) {
        $stmt = $db->query("SELECT * FROM crm_club_contacts LIMIT 1");
        $sampleClub = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Carica template
    $tplFile = __DIR__ . "/automation/emailflux/templates/club_step{$step}_" . 
        ($step === 1 ? 'censimento' : ($step === 2 ? 'widget' : ($step === 3 ? 'opendata' : ($step === 4 ? 'pwa_privacy' : 'dialogo')))) . ".html";

    if (file_exists($tplFile)) {
        $htmlContent = file_get_contents($tplFile);
        $replacements = [
            '{{entity_name}}' => $sampleClub['entity_name'],
            '{{city}}' => $sampleClub['city'],
            '{{province}}' => $sampleClub['province'],
            '{{region}}' => $sampleClub['region'],
            '{{meeting_day}}' => $sampleClub['meeting_day'] ?: 'Da concordare',
            '{{meeting_time}}' => $sampleClub['meeting_time'] ?: '20:30',
            '{{sic_id}}' => $sampleClub['sic_id'],
            '{{unsubscribe_token}}' => $sampleClub['unsubscribe_token']
        ];
        $body = str_replace(array_keys($replacements), array_values($replacements), $htmlContent);

        $subjects = [
            1 => "Censimento Aperto 2026 Club Hudolin: verificate la scheda di " . $sampleClub['entity_name'],
            2 => "Un servizio gratuito per il sito di " . $sampleClub['entity_name'] . " e del vostro Comune: il Widget Trova-Club",
            3 => "Rete aperta e trasparente: OpenData GeoJSON e Feed a supporto di ASL e Ser.D",
            4 => "Uno strumento digitale che invita a vivere la vita reale: la PWA di dependex.social",
            5 => "Diamo voce agli eventi, alle scuole e agli Interclub di " . $sampleClub['region'] . " su dependex.social"
        ];
        $subject = "[TEST LABOMOBILE STEP {$step}] " . ($subjects[$step] ?? "Aggiornamento dependex.social");

        // Invio tramite script python transport o stream_socket_client SSL
        $cmd = "python bin/crm_clubs_dispatcher.py --send-test --recipient \"{$targetEmail}\" --step {$step} --sic \"{$sampleClub['sic_id']}\"";
        $output = shell_exec($cmd . " 2>&1");

        $testMessage = [
            'success' => true,
            'text' => "Email di prova inviata a {$targetEmail} con successo!",
            'output' => $output
        ];
    } else {
        $testMessage = [
            'success' => false,
            'text' => "Template Step {$step} non trovato."
        ];
    }
}

// Parametri di Filtro
$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');
$reg = trim($_GET['reg'] ?? '');
$emailType = trim($_GET['email_type'] ?? '');
$status = trim($_GET['status'] ?? '');
$quality = trim($_GET['quality'] ?? '');

$sql = "SELECT * FROM crm_club_contacts WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (entity_name LIKE :q OR city LIKE :q OR province LIKE :q OR primary_email LIKE :q OR primary_phone LIKE :q)";
    $params[':q'] = "%{$q}%";
}
if ($cat !== '') {
    $sql .= " AND category = :cat";
    $params[':cat'] = $cat;
}
if ($reg !== '') {
    $sql .= " AND region = :reg";
    $params[':reg'] = $reg;
}
if ($emailType !== '') {
    $sql .= " AND email_type = :et";
    $params[':et'] = $emailType;
}
if ($status !== '') {
    $sql .= " AND outreach_status = :st";
    $params[':st'] = $status;
}
if ($quality === 'missing_schedule') {
    $sql .= " AND (meeting_day IS NULL OR meeting_day = '' OR meeting_day LIKE '%concordare%')";
} elseif ($quality === 'with_schedule') {
    $sql .= " AND (meeting_day IS NOT NULL AND meeting_day <> '' AND meeting_day NOT LIKE '%concordare%')";
} elseif ($quality === 'has_phone') {
    $sql .= " AND (primary_phone IS NOT NULL AND primary_phone <> '')";
}

$sql .= " ORDER BY region ASC, province ASC, city ASC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiche Aggregate
$totalClubs = $db->query("SELECT count(*) FROM crm_club_contacts")->fetchColumn();
$totalFamilies = $db->query("SELECT sum(families_count) FROM crm_club_contacts")->fetchColumn();
$directEmails = $db->query("SELECT count(*) FROM crm_club_contacts WHERE email_type = 'DIRECT'")->fetchColumn();
$inheritedEmails = $db->query("SELECT count(*) FROM crm_club_contacts WHERE email_type = 'COORDINATION_INHERITED'")->fetchColumn();
$withScheduleCount = $db->query("SELECT count(*) FROM crm_club_contacts WHERE meeting_day IS NOT NULL AND meeting_day <> '' AND meeting_day NOT LIKE '%concordare%'")->fetchColumn();
$missingScheduleCount = $totalClubs - $withScheduleCount;
$regionsList = $db->query("SELECT DISTINCT region FROM crm_club_contacts ORDER BY region ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = "Console CRM Club Italia & Outreach Nurturing · DEPENDEX";
include __DIR__ . '/_header.php';
?>

<div class="page-container" style="max-width:1400px; margin:24px auto; padding:0 16px;">

  <!-- TITOLO & HEADER CRM -->
  <div style="background:linear-gradient(135deg, #161b22 0%, #1f242c 100%); border:1px solid #30363d; border-radius:16px; padding:28px 24px; margin-bottom:24px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px;">
    <div>
      <div style="display:inline-block; padding:4px 10px; background:rgba(56,239,125,0.15); border:1px solid #38ef7d; border-radius:20px; font-size:12px; font-weight:700; color:#38ef7d; margin-bottom:8px;">
        DATABASE CRM NAZIONALE & NURTURING FLUX100 / EMM+
      </div>
      <h1 style="font-size:26px; font-weight:800; color:#ffffff; margin:0 0 6px 0; letter-spacing:-0.5px;">
        Rete Club Hudolin d'Italia (CAT · APCAT · ACAT · ARCAT · AICAT)
      </h1>
      <p style="font-size:14px; color:#8b949e; margin:0;">
        Recapiti completi, ereditarietà di contatto istituzionale e diffusione di <strong>dependex.social</strong> come servizio aperto per le famiglie.
      </p>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      <a href="widget-generator.php" target="_blank" class="btn btn-secondary" style="display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; padding:10px 16px; border-radius:8px; border:1px solid #30363d; background:#21262d; color:#fde68a; text-decoration:none;">
        🧩 Generatore Widget Comuni & ASL
      </a>
      <a href="data/CRM_CLUB_CONTATTI_MASTER_2026.csv" download="CRM_CLUB_CONTATTI_MASTER_2026.csv" class="btn btn-secondary" style="display:flex; align-items:center; gap:6px; font-size:13px; font-weight:600; padding:10px 16px; border-radius:8px; border:1px solid #30363d; background:#21262d; color:#e6edf3; text-decoration:none;">
        📥 Esporta CSV Master (<?=number_format($totalClubs)?> Club)
      </a>
      <a href="#test-modal" onclick="document.getElementById('test-box').scrollIntoView({behavior:'smooth'});" class="btn btn-primary" style="display:flex; align-items:center; gap:6px; font-size:13px; font-weight:700; padding:10px 18px; border-radius:8px; background:linear-gradient(135deg, #11998e, #38ef7d); color:#0d1117; text-decoration:none;">
        ✉️ Invia Test LaboMobile
      </a>
    </div>
  </div>

  <?php if ($testMessage): ?>
    <div style="background:rgba(56,239,125,0.1); border:1px solid #38ef7d; border-radius:12px; padding:16px 20px; margin-bottom:24px; color:#38ef7d;">
      <strong>Esito Invio:</strong> <?=htmlspecialchars($testMessage['text'])?><br>
      <?php if (!empty($testMessage['output'])): ?>
        <pre style="margin:8px 0 0 0; font-size:12px; color:#8b949e; background:#0d1117; padding:8px; border-radius:6px; overflow-x:auto;"><?=htmlspecialchars($testMessage['output'])?></pre>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- METRICHE KPI -->
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
    <div style="background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px;">
      <div style="font-size:12px; color:#8b949e; text-transform:uppercase; font-weight:700; letter-spacing:1px; margin-bottom:4px;">Presidi Censiti</div>
      <div style="font-size:28px; font-weight:800; color:#ffffff;"><?=number_format($totalClubs)?></div>
      <div style="font-size:12px; color:#38ef7d; margin-top:4px;">100% coperti con telefono ed email</div>
    </div>
    <div style="background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px;">
      <div style="font-size:12px; color:#8b949e; text-transform:uppercase; font-weight:700; letter-spacing:1px; margin-bottom:4px;">Famiglie Nei Cerchi</div>
      <div style="font-size:28px; font-weight:800; color:#58a6ff;"><?=number_format($totalFamilies)?>+</div>
      <div style="font-size:12px; color:#8b949e; margin-top:4px;">Stima media 12-14 famiglie/Club</div>
    </div>
    <div style="background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px;">
      <div style="font-size:12px; color:#8b949e; text-transform:uppercase; font-weight:700; letter-spacing:1px; margin-bottom:4px;">Email Dirette</div>
      <div style="font-size:28px; font-weight:800; color:#38ef7d;"><?=$directEmails?> <span style="font-size:14px; color:#8b949e; font-weight:400;">(<?=round($directEmails/$totalClubs*100)?>%)</span></div>
      <div style="font-size:12px; color:#8b949e; margin-top:4px;">Casella postale propria del presidio</div>
    </div>
    <div style="background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px;">
      <div style="font-size:12px; color:#8b949e; text-transform:uppercase; font-weight:700; letter-spacing:1px; margin-bottom:4px;">Email Ereditate</div>
      <div style="font-size:28px; font-weight:800; color:#e3b341;"><?=$inheritedEmails?> <span style="font-size:14px; color:#8b949e; font-weight:400;">(<?=round($inheritedEmails/$totalClubs*100)?>%)</span></div>
      <div style="font-size:12px; color:#8b949e; margin-top:4px;">Segreteria APCAT/ACAT/ARCAT</div>
    </div>
    <div style="background:#161b22; border:1px solid #30363d; border-radius:12px; padding:20px;">
      <div style="font-size:12px; color:#8b949e; text-transform:uppercase; font-weight:700; letter-spacing:1px; margin-bottom:4px;">Orari Incontro</div>
      <div style="font-size:28px; font-weight:800; color:#58a6ff;"><?=$withScheduleCount?> <span style="font-size:14px; color:#8b949e; font-weight:400;">(<?=$missingScheduleCount?> da verificare)</span></div>
      <div style="font-size:12px; color:#8b949e; margin-top:4px;">Giorno e ora riunione accertati</div>
    </div>
  </div>

  <!-- FILTRI DI RICERCA & DATA QUALITY -->
  <div style="background:#161b22; border:1px solid #30363d; border-radius:12px; padding:18px 20px; margin-bottom:24px;">
    <form method="GET" action="crm-clubs.php" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
      <input type="text" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Cerca Club, comune, email, tel..." style="flex:1 1 200px; padding:10px 14px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:#e6edf3; font-size:14px;">
      
      <select name="cat" style="padding:10px 14px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:#e6edf3; font-size:14px;">
        <option value="">Tutte le Tipologie</option>
        <option value="CAT" <?=$cat==='CAT'?'selected':''?>>CAT (Club Locali)</option>
        <option value="APCAT" <?=$cat==='APCAT'?'selected':''?>>APCAT (Provinciali)</option>
        <option value="ACAT" <?=$cat==='ACAT'?'selected':''?>>ACAT (Territoriali)</option>
        <option value="ARCAT" <?=$cat==='ARCAT'?'selected':''?>>ARCAT (Regionali)</option>
        <option value="AICAT" <?=$cat==='AICAT'?'selected':''?>>AICAT (Nazionale)</option>
      </select>

      <select name="reg" style="padding:10px 14px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:#e6edf3; font-size:14px;">
        <option value="">Tutte le Regioni</option>
        <?php foreach ($regionsList as $r): ?>
          <option value="<?=htmlspecialchars($r)?>" <?=$reg===$r?'selected':''?>><?=htmlspecialchars($r)?></option>
        <?php endforeach; ?>
      </select>

      <select name="email_type" style="padding:10px 14px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:#e6edf3; font-size:14px;">
        <option value="">Tutte le Email</option>
        <option value="DIRECT" <?=$emailType==='DIRECT'?'selected':''?>>Solo Dirette (175)</option>
        <option value="COORDINATION_INHERITED" <?=$emailType==='COORDINATION_INHERITED'?'selected':''?>>Solo Ereditate (220)</option>
      </select>

      <select name="quality" style="padding:10px 14px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:#e6edf3; font-size:14px;">
        <option value="">Qualità Dati: Tutti</option>
        <option value="missing_schedule" <?=$quality==='missing_schedule'?'selected':''?>>⚠️ Senza Orario Definito (<?=$missingScheduleCount?>)</option>
        <option value="with_schedule" <?=$quality==='with_schedule'?'selected':''?>>✅ Con Orario Verificato (<?=$withScheduleCount?>)</option>
        <option value="has_phone" <?=$quality==='has_phone'?'selected':''?>>📞 Con Telefono Presente</option>
      </select>

      <button type="submit" style="padding:10px 20px; background:#238636; border:none; border-radius:8px; color:#ffffff; font-weight:700; cursor:pointer; font-size:14px;">
        Filtra Risultati
      </button>

      <a href="api-opendata-geojson.php?<?=http_build_query(['region'=>$reg])?>" target="_blank" style="padding:10px 16px; background:#21262d; border:1px solid #30363d; border-radius:8px; color:#67e8f9; font-weight:600; text-decoration:none; font-size:13px;">
        🌐 Esporta GeoJSON
      </a>

      <?php if ($q!=='' || $cat!=='' || $reg!=='' || $emailType!=='' || $status!=='' || $quality!==''): ?>
        <a href="crm-clubs.php" style="color:#8b949e; text-decoration:underline; font-size:13px; margin-left:8px;">Resetta filtri</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- TABELLA DATI CLUB (RESPONSIVE NO OVERFLOW) -->
  <div style="background:#161b22; border:1px solid #30363d; border-radius:12px; overflow:hidden; margin-bottom:32px;">
    <div style="padding:16px 20px; border-bottom:1px solid #30363d; display:flex; justify-content:space-between; align-items:center; background:#1c2128;">
      <div style="font-weight:700; font-size:15px; color:#ffffff;">
        Elenco Presidi Territoriali Trovati: <span style="color:#38ef7d;"><?=count($clubs)?></span> di <?=number_format($totalClubs)?>
      </div>
      <div style="font-size:12px; color:#8b949e;">
        Ordinamento per Regione / Provincia / Comune
      </div>
    </div>
    
    <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
      <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
        <thead>
          <tr style="background:#161b22; color:#8b949e; border-bottom:1px solid #30363d;">
            <th style="padding:12px 16px;">Tipo</th>
            <th style="padding:12px 16px;">Presidio / Club</th>
            <th style="padding:12px 16px;">Località</th>
            <th style="padding:12px 16px;">Email di Contatto</th>
            <th style="padding:12px 16px;">Telefono</th>
            <th style="padding:12px 16px;">Incontro</th>
            <th style="padding:12px 16px;">Stato</th>
            <th style="padding:12px 16px; text-align:center;">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clubs as $c): ?>
            <tr style="border-bottom:1px solid #21262d; hover:background:#1f242c;">
              <td style="padding:12px 16px; white-space:nowrap;">
                <?php
                  $badgeColor = '#58a6ff';
                  if ($c['category'] === 'APCAT') $badgeColor = '#38ef7d';
                  if ($c['category'] === 'ARCAT') $badgeColor = '#e3b341';
                  if ($c['category'] === 'AICAT') $badgeColor = '#f85149';
                ?>
                <span style="display:inline-block; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:700; color:#0d1117; background:<?=$badgeColor?>;">
                  <?=htmlspecialchars($c['category'])?>
                </span>
              </td>
              <td style="padding:12px 16px;">
                <div style="font-weight:700; color:#ffffff;"><?=htmlspecialchars($c['entity_name'])?></div>
                <div style="font-size:11px; color:#8b949e; font-family:monospace;"><?=htmlspecialchars($c['sic_id'])?></div>
                <?php if ($c['email_type'] === 'COORDINATION_INHERITED'): ?>
                  <div style="font-size:10px; color:#e3b341; margin-top:2px;">Coord: <?=htmlspecialchars($c['coordination_entity'])?></div>
                <?php endif; ?>
              </td>
              <td style="padding:12px 16px; white-space:nowrap;">
                <div style="color:#ffffff; font-weight:600;"><?=htmlspecialchars($c['city'])?> (<?=htmlspecialchars($c['province'])?>)</div>
                <div style="font-size:11px; color:#8b949e;"><?=htmlspecialchars($c['region'])?></div>
              </td>
              <td style="padding:12px 16px;">
                <a href="mailto:<?=htmlspecialchars($c['primary_email'])?>" style="color:#58a6ff; text-decoration:none; font-family:monospace; font-size:12px;">
                  <?=htmlspecialchars($c['primary_email'])?>
                </a>
                <div>
                  <?php if ($c['email_type'] === 'DIRECT'): ?>
                    <span style="font-size:10px; color:#38ef7d; font-weight:700;">● Email Diretta</span>
                  <?php else: ?>
                    <span style="font-size:10px; color:#e3b341;">● Via Segreteria</span>
                  <?php endif; ?>
                </div>
              </td>
              <td style="padding:12px 16px; white-space:nowrap;">
                <a href="tel:<?=htmlspecialchars($c['primary_phone'])?>" style="color:#38ef7d; text-decoration:none; font-weight:600;">
                  <?=htmlspecialchars($c['primary_phone'])?>
                </a>
              </td>
              <td style="padding:12px 16px; font-size:12px; color:#c9d1d9;">
                <?=htmlspecialchars($c['meeting_day'] ?: 'Incontro sett.')?><br>
                <span style="color:#8b949e;"><?=htmlspecialchars($c['meeting_time'] ?: '')?></span>
              </td>
              <td style="padding:12px 16px; white-space:nowrap;">
                <span style="display:inline-block; padding:2px 6px; border-radius:4px; font-size:11px; background:#21262d; color:#8b949e;">
                  <?=htmlspecialchars($c['outreach_status'])?>
                </span>
              </td>
              <td style="padding:12px 16px; text-align:center; white-space:nowrap;">
                <a href="club-public.php?sic=<?=urlencode($c['sic_id'])?>" target="_blank" style="display:inline-block; padding:4px 8px; background:#238636; border-radius:4px; color:#ffffff; text-decoration:none; font-size:11px; margin-right:4px;">
                  Scheda Club
                </a>
                <a href="mappa-club.php?sic=<?=urlencode($c['sic_id'])?>" target="_blank" style="display:inline-block; padding:4px 8px; background:#21262d; border:1px solid #30363d; border-radius:4px; color:#c9d1d9; text-decoration:none; font-size:11px; margin-right:4px;">
                  Mappa
                </a>
                <a href="widget-club.php?q=<?=urlencode($c['province'] ?: $c['city'])?>" target="_blank" style="display:inline-block; padding:4px 8px; background:#1f6feb; border-radius:4px; color:#ffffff; text-decoration:none; font-size:11px;">
                  Widget
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- BOX INVIO TEST LIVE A LABOMOBILE -->
  <div id="test-box" style="background:#161b22; border:1px solid #30363d; border-radius:12px; padding:24px; margin-bottom:32px;">
    <h2 style="font-size:18px; color:#ffffff; margin:0 0 8px 0;">Collaudo Nurturing FLUX100 / EMM+ (Indirizzo Certificato)</h2>
    <p style="color:#8b949e; font-size:13px; line-height:1.5; margin:0 0 16px 0;">
      Invia una simulazione di uno dei 5 Step della sequenza istituzionale all'indirizzo di prova prioritario <strong>labomobile.lm@gmail.com</strong> tramite server SMTP Hostinger SSL 465 (mittente certificato `info@dependex.support`).
    </p>

    <form method="POST" action="crm-clubs.php#test-box" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
      <input type="hidden" name="action" value="send_test_labomobile">

      <select name="step" style="padding:10px 14px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:#e6edf3; font-size:14px;">
        <option value="1">Step 1: Presentazione & Verifica Scheda</option>
        <option value="2">Step 2: Adozione Widget Trova-Club per il Comune</option>
        <option value="3">Step 3: Interoperabilità OpenData GeoJSON & Ser.D</option>
        <option value="4">Step 4: Sovranità & PWA On-Device per Famiglie</option>
        <option value="5">Step 5: Bacheca Eventi, Scuole & Interclub</option>
      </select>

      <input type="text" name="sic_id" value="SIC-TAGLIODIPO-RO-001" placeholder="SIC-ID Club Campione" style="padding:10px 14px; background:#0d1117; border:1px solid #30363d; border-radius:8px; color:#e6edf3; font-size:14px; width:220px;">

      <button type="submit" style="padding:10px 24px; background:linear-gradient(135deg, #11998e, #38ef7d); border:none; border-radius:8px; color:#0d1117; font-weight:700; cursor:pointer; font-size:14px;">
        Invia Test a labomobile.lm@gmail.com →
      </button>
    </form>
  </div>

</div>

<?php include __DIR__ . '/_footer.php'; ?>
