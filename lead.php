<?php
/**
 * DEPENDEX & OLTRE — LEAD MAGNET DELIVERY & ORIENTATION
 * Pagine e form dedicati per l'erogazione dei magneti gratuiti:
 * 1. Cassetta Attrezzi Primo Giorno
 * 2. Ricerca Club per Città/CAP (con i 3 Club più vicini)
 * 3. Guida per i Familiari ("Chi ama qualcuno che beve")
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/email-engine.php';

$magnet = $_GET['magnet'] ?? 'cassetta';
$submitted = false;
$userEmail = '';
$resultClubs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $nome = trim((string)($_POST['nome'] ?? ''));
    $city = trim((string)($_POST['citta'] ?? ''));
    $magnetId = $_POST['magnet_id'] ?? $magnet;

    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $userEmail = $email;
        $submitted = true;

        // Se è stata indicata una città, estraiamo i club corrispondenti dall'API interna
        if ($city) {
            $pdo = db();
            $stmt = $pdo->prepare("
                SELECT sic_id, entity_name, region, province, address, meeting_day, meeting_time 
                FROM network_entities 
                WHERE province LIKE ? OR region LIKE ? OR address LIKE ? OR entity_name LIKE ?
                LIMIT 3
            ");
            $like = '%' . $city . '%';
            $stmt->execute([$like, $like, $like, $like]);
            $resultClubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Traccia l'evento comportamentale
        email_os_track_event('lead_created', $email, [
            'nome' => $nome,
            'citta' => $city,
            'magnet_id' => $magnetId,
            'clubs_found' => count($resultClubs),
            'source' => "lead_magnet_{$magnetId}"
        ]);

        // Iscrizione contatto ed arruolamento nel Welcome Flow
        email_os_enroll_contact($email, $nome, '', "magnet_{$magnetId}");
    }
}

$pageTitle = 'Risorse Gratuite & Orientamento · DEPENDEX';
require '_header.php';
?>

<div class="luxury-backdrop" style="min-height: 85vh; padding: 40px 16px;">
  <div style="max-width: 800px; margin: 0 auto;">

    <?php if ($submitted): ?>
      <!-- STATO SUCCESSO -->
      <div class="lux-metallic-card" style="background: #0b0f19; border: 2px solid #D4AF37; border-radius: 16px; padding: 40px 32px; text-align: center;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: rgba(212,175,55,0.15); border: 1px solid #D4AF37; color: #D4AF37; margin-bottom: 20px;">
          <?=dx_icon('check-circle', '', 36)?>
        </div>

        <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; margin: 0 0 12px 0;">
          Risorsa Inviata con Successo
        </h1>

        <p style="color: #cbd5e1; font-size: 1.1rem; line-height: 1.6; margin-bottom: 24px;">
          Abbiamo inviato il materiale richiesto alla tua casella di posta: <strong><?=htmlspecialchars($userEmail)?></strong>.<br>
          <small style="color: #94a3b8;">Controlla anche la cartella aggiornamenti o promozioni per confermare la ricezione.</small>
        </p>

        <?php if (!empty($resultClubs)): ?>
          <div style="text-align: left; background: rgba(255,255,255,0.03); border: 1px solid rgba(212,175,55,0.3); border-radius: 12px; padding: 24px; margin-bottom: 28px;">
            <h3 style="color: #D4AF37; margin-top: 0; display: flex; align-items: center; gap: 8px;">
              <?=dx_icon('map-pin', '', 18)?> I Club più vicini a te (<?=count($resultClubs)?> trovati):
            </h3>
            <?php foreach ($resultClubs as $c): ?>
              <div style="border-bottom: 1px solid rgba(255,255,255,0.06); padding: 12px 0;">
                <div style="font-weight: 700; color: #FFFFFF;"><?=htmlspecialchars($c['entity_name'])?></div>
                <div style="font-size: 0.9rem; color: #94a3b8;">
                  <?=htmlspecialchars($c['address'])?> (<?=htmlspecialchars($c['province'])?>, <?=htmlspecialchars($c['region'])?>)
                </div>
                <div style="font-size: 0.85rem; color: #D4AF37; margin-top: 4px;">
                  Riunione: <strong><?=htmlspecialchars($c['meeting_day'])?> alle <?=htmlspecialchars($c['meeting_time'])?></strong> · Ingresso libero e gratuito
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
          <a href="metodo.php" class="btn primary" style="padding: 12px 28px; text-decoration: none; border-radius: 12px;">
            Scopri il Metodo in 5 Fasi
          </a>
          <a href="world-club-explorer.php" class="btn-rainbow-outline" style="padding: 12px 24px; text-decoration: none; border-radius: 12px;">
            Esplora Tutti i 361 Club
          </a>
        </div>
      </div>

    <?php else: ?>

      <!-- FORM DI ACQUISIZIONE LEAD MAGNET -->
      <div class="lux-metallic-card" style="background: #0b0f19; border: 1px solid rgba(212,175,55,0.35); border-radius: 16px; padding: 40px 32px;">
        
        <div style="text-align: center; margin-bottom: 32px;">
          <div class="gold-glow-badge" style="margin-bottom: 12px; display: inline-flex;">
            <?=dx_icon('shield-check', '', 14)?>
            <span style="margin-left: 6px;">100% GRATUITO · NESSUN OBBLIGO · ZERO SPAM</span>
          </div>

          <?php if ($magnet === 'famiglia'): ?>
            <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; margin: 0 0 10px 0;">
              Guida Operativa per i Familiari
            </h1>
            <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.6;">
              "Amo qualcuno che si sta distruggendo: cosa dire stasera e cosa non dire mai."<br>
              Una bussola pratica per proteggere te stesso e i tuoi figli, senza alimentare il loop della colpa.
            </p>
          <?php elseif ($magnet === 'club'): ?>
            <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; margin: 0 0 10px 0;">
              Trova la Sedia più Vicina a Te
            </h1>
            <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.6;">
              Indica la tua città o provincia: ti invieremo i 3 Club territoriali più vicini con indirizzo verificato, giorno e orario esatto di riunione.
            </p>
          <?php else: ?>
            <h1 style="font-family: var(--font-serif); font-size: 2.2rem; color: #FFFFFF; margin: 0 0 10px 0;">
              La Cassetta Attrezzi del Primo Giorno
            </h1>
            <p style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.6;">
              Le 7 cose concrete da fare nelle prossime 24 ore per non restare solo con l'ansia e riprendere il controllo con lucidità.
            </p>
          <?php endif; ?>
        </div>

        <form method="POST" action="lead.php?magnet=<?=htmlspecialchars($magnet)?>">
          <input type="hidden" name="magnet_id" value="<?=htmlspecialchars($magnet)?>">

          <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.9rem; color: #cbd5e1; margin-bottom: 6px;">Il tuo nome o nickname:</label>
            <input type="text" name="nome" placeholder="Come preferisci essere chiamato" style="width: 100%; padding: 12px 14px; background: #020617; border: 1px solid #334155; border-radius: 8px; color: #f8fafc; font-size: 0.95rem;">
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.9rem; color: #cbd5e1; margin-bottom: 6px;">Il tuo indirizzo email riservato (*):</label>
            <input type="email" name="email" required placeholder="Inserisci la tua email..." style="width: 100%; padding: 12px 14px; background: #020617; border: 1px solid #334155; border-radius: 8px; color: #f8fafc; font-size: 0.95rem;">
          </div>

          <div style="margin-bottom: 24px;">
            <label style="display: block; font-size: 0.9rem; color: #cbd5e1; margin-bottom: 6px;">Città o Provincia (per indicarti il Club più vicino):</label>
            <input type="text" name="citta" placeholder="Es. Milano, Roma, Rovigo, Bologna..." style="width: 100%; padding: 12px 14px; background: #020617; border: 1px solid #334155; border-radius: 8px; color: #f8fafc; font-size: 0.95rem;">
          </div>

          <div style="margin-bottom: 28px;">
            <label style="display: flex; align-items: flex-start; gap: 10px; color: #cbd5e1; font-size: 0.88rem; line-height: 1.5; cursor: pointer;">
              <input type="checkbox" name="privacy_accepted" value="1" required checked style="accent-color: var(--neon-cyan); width: 18px; height: 18px; margin-top: 2px;">
              <span>Accetto l'Informativa Privacy. I miei dati saranno utilizzati esclusivamente per inviarmi la risorsa richiesta e le comunicazioni del Club. Posso disiscrivermi in 1-click in qualsiasi momento.</span>
            </label>
          </div>

          <button type="submit" class="btn primary" style="width: 100%; font-size: 1.05rem; letter-spacing: 0.5px; border-radius: 14px; min-height: 52px;">
            <?php if ($magnet === 'famiglia'): ?>
              Scarica Subito la Guida Famiglia
            <?php elseif ($magnet === 'club'): ?>
              Trova i 3 Club Più Vicini
            <?php else: ?>
              Ricevi la Cassetta Attrezzi del Primo Giorno
            <?php endif; ?>
          </button>
        </form>

      </div>

    <?php endif; ?>

  </div>
</div>

<?php require '_footer.php'; ?>
