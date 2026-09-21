<?php
/**
 * widget-generator.php — Generatore di Codice Widget Embed per Comuni, ASL, Ser.D e Consulte
 * Strumento gratuito di utilità pubblica per incorporare la directory e mappa 2D dei Club CAT
 * nei portali istituzionali della Pubblica Amministrazione.
 */
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$brand = site_brand();
$db = db();

$pageTitle = 'Generatore Widget Trova-Club per Comuni, ASL & Enti Territoriali · DEPENDEX';
$metaDesc = 'Genera gratuitamente il codice iframe del Trova-Club per il sito web del tuo Comune, ASL, Ser.D o Distretto Sanitario. Zero cookie di profilazione, accessibile e responsive.';
$canonicalUrl = 'https://' . ($brand['domain'] ?? 'dependex.social') . '/widget-generator.php';

$breadcrumbs = [
    'Home' => '/',
    'OpenData & Territorio' => 'api-opendata-geojson.php',
    'Generatore Widget Comuni & ASL' => 'widget-generator.php'
];

// Estrazione Regioni e Province dal censimento
$regions = $db->query("SELECT DISTINCT region FROM crm_club_contacts WHERE region <> '' ORDER BY region ASC")->fetchAll(PDO::FETCH_COLUMN);
$provinces = $db->query("SELECT DISTINCT province FROM crm_club_contacts WHERE province <> '' ORDER BY province ASC")->fetchAll(PDO::FETCH_COLUMN);
$totalClubs = (int)$db->query("SELECT COUNT(*) FROM crm_club_contacts")->fetchColumn();

require '_header.php';
?>

<div class="container py-4" style="max-width: 1300px; margin: 0 auto; padding: 0 1rem;">

  <!-- BANNER HEADER ISTITUZIONALE -->
  <section class="p-4 p-md-5 mb-4" style="background: linear-gradient(135deg, rgba(20,26,45,0.95), rgba(10,13,22,0.98)); border: 1px solid rgba(6, 182, 212, 0.35); border-radius: 20px; box-shadow: var(--rainbow-glow);">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <span class="badge" style="background: rgba(6,182,212,0.15); color: #67e8f9; border: 1px solid rgba(6,182,212,0.3); font-size: 0.76rem; padding: 4px 10px; border-radius: 12px; font-weight: 700;">
            BENE COMUNE DIGITALE
          </span>
          <span style="font-size: 0.76rem; color: #86efac; background: rgba(34,197,94,0.12); padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(34,197,94,0.3);">
            ● Zero Costi · Zero Cookie Profilazione
          </span>
          <span style="font-size: 0.76rem; color: #fde68a; background: rgba(253,230,138,0.12); padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(253,230,138,0.3);">
            Conforme Standard AgID & GDPR
          </span>
        </div>
        <h1 style="font-size: clamp(1.6rem, 3.2vw, 2.3rem); font-weight: 800; color: #ffffff; margin: 0 0 8px; font-family: var(--font-serif); line-height: 1.2;">
          Generatore Widget Trova-Club per Comuni & ASL
        </h1>
        <p style="color: #cbd5e1; font-size: 0.95rem; line-height: 1.6; margin: 0;">
          Consenti ai cittadini, alle famiglie e agli operatori sociali del tuo territorio di localizzare in tempo reale il Club Alcologico Territoriale più vicino, direttamente dalle pagine del portale del tuo Comune o Distretto Sanitario.
        </p>
      </div>

      <div class="col-lg-4 text-lg-end">
        <div class="d-inline-flex flex-column gap-2 text-start p-3" style="background: rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.1); border-radius: 14px; min-width: 220px;">
          <div style="font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Rete Censita Attiva</div>
          <div style="font-size: 1.5rem; font-weight: 800; color: #38ef7d; font-family: var(--font-serif);">
            <?=number_format($totalClubs)?> Presidi
          </div>
          <div style="font-size: 0.75rem; color: #cbd5e1;">Copertura nazionale 100% verificata</div>
        </div>
      </div>
    </div>
  </section>

  <!-- SEZIONE PRINCIPALE A DUE COLONNE: CONFIGURATORE & ANTEPRIMA -->
  <div class="row g-4 mb-4">
    
    <!-- COLONNA SINISTRA: CONFIGURATORE PARAMETRI -->
    <div class="col-lg-5">
      <div class="p-4 h-100" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(224, 169, 109, 0.3); border-radius: 20px;">
        <div class="d-flex align-items-center gap-2 mb-3">
          <?=dx_icon('sliders', 'text-neon-gold', 20)?>
          <h2 style="font-size: 1.2rem; font-weight: 800; color: #ffffff; margin: 0; font-family: var(--font-serif);">
            Personalizza il tuo Widget
          </h2>
        </div>
        <p style="font-size: 0.85rem; color: #cbd5e1; margin-bottom: 20px;">
          Scegli l'ambito geografico e lo stile visivo più adatto al layout del tuo sito web istituzionale.
        </p>

        <form id="widgetForm" onsubmit="return false;" class="d-flex flex-column gap-3">
          <!-- REGIONE -->
          <div>
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">
              Regione di Riferimento
            </label>
            <select id="cfgRegion" class="form-select" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" onchange="updateWidgetCode()">
              <option value="">-- Tutta Italia (Nazionale) --</option>
              <?php foreach ($regions as $r): ?>
                <option value="<?=h($r)?>" <?=$r==='Veneto'?'selected':''?>><?=h($r)?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- PROVINCIA -->
          <div>
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">
              Provincia (Sigla)
            </label>
            <select id="cfgProvince" class="form-select" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" onchange="updateWidgetCode()">
              <option value="">-- Tutte le Province della Regione --</option>
              <?php foreach ($provinces as $p): ?>
                <option value="<?=h($p)?>" <?=$p==='RO'?'selected':''?>><?=h($p)?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- CITTA / COMUNE -->
          <div>
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">
              Comune o Parola Chiave (Opzionale)
            </label>
            <input type="text" id="cfgQuery" class="form-control" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" placeholder="es. Taglio di Po, Rovigo, Adria..." value="Taglio di Po" oninput="updateWidgetCode()">
            <small style="font-size: 0.72rem; color: #94a3b8;">Lascia vuoto per mostrare tutti i Club dell'ambito provinciale/regionale.</small>
          </div>

          <!-- TEMA GRAFICO -->
          <div>
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">
              Tema Visivo
            </label>
            <div class="d-flex gap-3">
              <label style="flex:1; padding:10px; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.15); border-radius:8px; cursor:pointer; text-align:center;">
                <input type="radio" name="theme" value="light" checked onchange="updateWidgetCode()">
                <div style="font-size: 0.85rem; font-weight: 700; color: #ffffff; margin-top: 4px;">Light Istituzionale</div>
                <small style="font-size: 0.7rem; color: #cbd5e1;">Ideale per portali PA bianchi</small>
              </label>
              <label style="flex:1; padding:10px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.15); border-radius:8px; cursor:pointer; text-align:center;">
                <input type="radio" name="theme" value="dark" onchange="updateWidgetCode()">
                <div style="font-size: 0.85rem; font-weight: 700; color: #67e8f9; margin-top: 4px;">Dark Elegance</div>
                <small style="font-size: 0.7rem; color: #cbd5e1;">Sfondi scuri ad alto contrasto</small>
              </label>
            </div>
          </div>

          <!-- ALTEZZA WIDGET -->
          <div>
            <label style="font-size: 0.8rem; color: #cbd5e1; font-weight: 600; display: block; margin-bottom: 4px;">
              Altezza del Riquadro
            </label>
            <select id="cfgHeight" class="form-select" style="background: #141a2d; color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px;" onchange="updateWidgetCode()">
              <option value="550">550 px (Compatto)</option>
              <option value="650" selected>650 px (Standard Consigliato)</option>
              <option value="750">750 px (Ampio)</option>
            </select>
          </div>
        </form>

        <!-- SNIPPET DI CODICE PRONTO DA COPIARE -->
        <div class="mt-4 pt-3 border-top border-secondary">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <label style="font-size: 0.8rem; color: #fde68a; font-weight: 700; text-transform: uppercase;">
              Codice HTML da Copiare (Embed Iframe)
            </label>
            <button type="button" class="btn small" style="background: #238636; color: #fff; font-weight: 700; border-radius: 6px; padding: 4px 10px; font-size: 0.75rem;" onclick="copyEmbedCode()">
              <?=dx_icon('copy', '', 12)?> <span id="copyBtnText">Copia Codice</span>
            </button>
          </div>
          <textarea id="embedSnippet" readonly rows="5" class="form-control" style="background: #0d1117; color: #38ef7d; font-family: monospace; font-size: 0.78rem; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; resize: none;"></textarea>
          <small style="font-size: 0.72rem; color: #94a3b8; display: block; margin-top: 6px;">
            Incolla questo snippet in qualsiasi pagina HTML, articolo Joomla, blocco WordPress o template Drupal.
          </small>
        </div>

      </div>
    </div>

    <!-- COLONNA DESTRA: ANTEPRIMA DAL VIVO -->
    <div class="col-lg-7">
      <div class="p-4 h-100" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 20px; display: flex; flex-direction: column;">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex align-items-center gap-2">
            <?=dx_icon('eye', 'text-neon-cyan', 20)?>
            <h2 style="font-size: 1.2rem; font-weight: 800; color: #ffffff; margin: 0; font-family: var(--font-serif);">
              Anteprima dal Vivo (Live Preview)
            </h2>
          </div>
          <span id="previewUrlLabel" style="font-size: 0.72rem; color: #67e8f9; font-family: monospace;"></span>
        </div>

        <!-- CONTENITORE IFRAME RESPONSIVE -->
        <div style="flex: 1; min-height: 550px; background: rgba(0,0,0,0.3); border: 2px dashed rgba(255,255,255,0.15); border-radius: 16px; overflow: hidden; position: relative;">
          <iframe id="previewIframe" src="widget-club.php?region=Veneto&province=RO&q=Taglio+di+Po&theme=light" style="width: 100%; height: 650px; border: none; border-radius: 14px;" title="Anteprima Widget Trova-Club"></iframe>
        </div>
      </div>
    </div>

  </div>

  <!-- SEZIONE VANTAGGI & LINEE GUIDA PER LA PUBBLICA AMMINISTRAZIONE -->
  <section class="p-4 p-md-5 my-4" style="background: rgba(12, 16, 28, 0.95); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px;">
    <h3 style="font-family: var(--font-serif); font-size: 1.35rem; color: #ffffff; font-weight: 800; margin: 0 0 16px;">
      Garanzie Istituzionali & Vantaggi Tecnici per la PA
    </h3>
    <div class="row g-3">
      <div class="col-md-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
          <div style="font-weight: 700; color: #86efac; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('shield', 'text-success', 16)?> Privacy & Cookie Zero
          </div>
          <p style="font-size: 0.84rem; color: #cbd5e1; margin: 0; line-height: 1.5;">
            Il widget non installa alcun cookie di terze parti né tracciatori commerciali. Non richiede consensi privacy o banner GDPR aggiuntivi nel portale dell'Ente.
          </p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
          <div style="font-weight: 700; color: #67e8f9; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('refresh-cw', 'text-neon-cyan', 16)?> Aggiornato Automaticamente
          </div>
          <p style="font-size: 0.84rem; color: #cbd5e1; margin: 0; line-height: 1.5;">
            Quando un Club del territorio aggiorna giorno, orario o sede di incontro, le modifiche si riflettono istantaneamente nel widget del Comune senza alcun intervento dei tecnici comunali.
          </p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="p-3 h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px;">
          <div style="font-weight: 700; color: #fde68a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <?=dx_icon('heart', 'text-neon-gold', 16)?> Accessibilità AgID
          </div>
          <p style="font-size: 0.84rem; color: #cbd5e1; margin: 0; line-height: 1.5;">
            Colori ad alto contrasto, navigabilità da tastiera e pulsanti touch target conformi (>=44px), fruibili da anziani e persone con limitazioni sensoriali.
          </p>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
function buildWidgetUrl() {
  const reg = document.getElementById('cfgRegion').value;
  const prov = document.getElementById('cfgProvince').value;
  const q = document.getElementById('cfgQuery').value.trim();
  const theme = document.querySelector('input[name="theme"]:checked').value;

  const domain = "<?=h($brand['domain'] ?? 'dependex.social')?>";
  const params = new URLSearchParams();

  if (reg) params.set('region', reg);
  if (prov) params.set('province', prov);
  if (q) params.set('q', q);
  params.set('theme', theme);

  return 'https://' + domain + '/widget-club.php?' + params.toString();
}

function updateWidgetCode() {
  const url = buildWidgetUrl();
  const height = document.getElementById('cfgHeight').value;

  const iframe = document.getElementById('previewIframe');
  iframe.src = url;
  iframe.style.height = height + 'px';

  document.getElementById('previewUrlLabel').innerText = url.replace('https://<?=h($brand['domain'] ?? 'dependex.social')?>', '');

  const snippet = `<!-- Inizio Widget DEPENDEX Trova-Club Territoriale -->\n<iframe src="${url}"\n        width="100%"\n        height="${height}"\n        frameborder="0"\n        loading="lazy"\n        title="Trova il Club Alcologico Territoriale più vicino · DEPENDEX"\n        style="border: 1px solid rgba(0,0,0,0.12); border-radius: 14px; width: 100%; max-width: 100%;">\n</iframe>\n<!-- Fine Widget DEPENDEX -->`;

  document.getElementById('embedSnippet').value = snippet;
}

function copyEmbedCode() {
  const snippet = document.getElementById('embedSnippet');
  snippet.select();
  snippet.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(snippet.value).then(() => {
    const btn = document.getElementById('copyBtnText');
    btn.innerText = 'Copiato!';
    setTimeout(() => { btn.innerText = 'Copia Codice'; }, 2500);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  updateWidgetCode();
});
</script>

<?php require '_footer.php'; ?>
