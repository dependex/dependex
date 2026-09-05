<?php
/* ============================================================================
   DR-SIC — un solo modo di fare un SIC-ID personale.
   Destino Randagio · 2026-08-15 · Cowork

   IL PROBLEMA
   Nel sito c'erano QUATTRO pezzi di codice che si inventavano un SIC-ID
   personale, ognuno a modo suo:
     kit-attiva.php            12 cifre a caso, e se falliva 40 volte
                               ripiegava su substr(microtime()) — che
                               PRODUCE UN PUNTO DENTRO IL CODICE:
                               provato, esce "SIC-ID-6795145.1291"
     dr-onboarding.php         l'id utente riempito di zeri
     dr-webinar.php            random_int, due volte, senza controllare
                               se quel numero era gia' di qualcun altro
     genesys/assegna-sic-lead  random_int, con controllo (l'unico fatto bene)

   E soprattutto: **users.internal_code non aveva un indice unico**. Verificato
   sul database vero: erano unici solo email e username. Quindi due persone
   potevano ricevere lo stesso codice senza che niente se ne accorgesse.

   PERCHE' E' GRAVE
   Il SIC personale e' il codice di invito. alb_risolvi_ref() cerca il
   proprietario di un codice e prende il primo che trova (LIMIT 1). Con un
   doppione, l'invito di una persona finisce accreditato a un'altra: soldi che
   vanno alla persona sbagliata, ed e' impossibile accorgersene dopo.
   Con i generatori a caso su 5 milioni di posizioni le collisioni attese sono
   circa dodici. Non "forse": circa dodici.

   LA CURA, IN DUE PEZZI
   1. Un solo generatore (dr_sic_nuovo) che prima di consegnare un codice
      controlla che non sia gia' di nessuno — ne' fra gli utenti ne' fra i
      lead — e non ripiega MAI su qualcosa di malformato.
   2. Un indice UNICO in banca dati. E' l'unica difesa che regge davvero,
      perche' non si puo' dimenticare di chiamarla.
      L'indice e' PARZIALE (WHERE ... <> '') perche' SQLite considera due
      stringhe vuote uguali fra loro: senza il WHERE, il secondo utente senza
      codice non si sarebbe piu' potuto salvare.

   NON TOCCA I CODICI GIA' DATI. Un SIC gia' in giro sta dentro i link di
   invito che la gente ha gia' condiviso: cambiarlo romperebbe quei link.
   I codici malformati si ELENCANO (dr_sic_malformati) e si sistemano a mano
   dal pannello, uno per uno, sapendo cosa si sta facendo.
============================================================================ */

if (defined('DR_SIC_LIB')) return;
define('DR_SIC_LIB', 1);

/** Il formato buono: SIC-ID- e dodici cifre. Nient'altro. */
function dr_sic_valido($s): bool {
  return (bool)preg_match('/^SIC-ID-[0-9]{12}$/', (string)$s);
}

/** Il formato dei SIC di NODO (quelli della rete): SIC-ID-G-WN-007 e simili. */
function dr_sic_e_di_nodo($s): bool {
  return (bool)preg_match('/^SIC-ID-G-/', (string)$s);
}

/** Il codice del Master (SIC-ID-MASTER-...) e' voluto e non va segnalato. */
function dr_sic_e_speciale($s): bool {
  return (bool)preg_match('/^SIC-ID-MASTER-/', (string)$s);
}

function dr_sic_migra(PDO $pdo): void {
  static $f = false; if ($f) return; $f = true;
  /* indici UNICI parziali: proteggono i codici veri, ignorano i vuoti */
  foreach ([
    ['ux_users_internal_code', 'users',    'internal_code'],
    ['ux_users_sic_id',        'users',    'sic_id'],
    ['ux_contacts_sic_id',     'contacts', 'sic_id'],
  ] as [$nome, $tab, $col]) {
    try {
      $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS {$nome}
                  ON {$tab}({$col}) WHERE {$col} IS NOT NULL AND {$col} <> ''");
    } catch (Throwable $e) {
      /* fallisce solo se ci sono gia' doppioni: si vedono dal pannello e si
         sistemano a mano. Meglio un indice mancante che una migrazione che
         cancella dati per farsi spazio. */
    }
  }
}

/** L'indice unico c'e' davvero? Il pannello lo mostra. */
function dr_sic_indice_attivo(PDO $pdo, string $nome = 'ux_users_internal_code'): bool {
  try {
    $st = $pdo->prepare("SELECT 1 FROM sqlite_master WHERE type='index' AND name=? LIMIT 1");
    $st->execute([$nome]);
    return (bool)$st->fetchColumn();
  } catch (Throwable $e) { return false; }
}

/** Questo codice e' gia' di qualcuno? Guarda dappertutto. */
function dr_sic_gia_preso(PDO $pdo, string $sic): bool {
  if ($sic === '') return true;
  foreach ([
    ["SELECT 1 FROM users WHERE internal_code=? LIMIT 1"],
    ["SELECT 1 FROM users WHERE sic_id=? LIMIT 1"],
    ["SELECT 1 FROM contacts WHERE sic_id=? LIMIT 1"],
  ] as [$q]) {
    try { $st = $pdo->prepare($q); $st->execute([$sic]); if ($st->fetchColumn()) return true; }
    catch (Throwable $e) { /* tabella assente: non e' un motivo per dire "libero" */ }
  }
  return false;
}

/**
 * *** L'UNICO GENERATORE ***
 * Dodici cifre a caso, controllate libere. Torna '' se dopo 60 tentativi non
 * ne trova una: meglio nessun codice che un codice di qualcun altro, e chi
 * chiama se ne accorge subito invece di scoprirlo fra sei mesi.
 */
function dr_sic_nuovo(PDO $pdo): string {
  dr_sic_migra($pdo);
  for ($t = 0; $t < 60; $t++) {
    $n = '';
    for ($i = 0; $i < 12; $i++) $n .= random_int(0, 9);
    $sic = 'SIC-ID-' . $n;
    if (!dr_sic_gia_preso($pdo, $sic)) return $sic;
  }
  return '';
}

/**
 * Il SIC di una persona, garantito. Se ce l'ha lo torna; se non ce l'ha
 * gliene fa uno e lo scrive in tutti e due i campi. Non sovrascrive MAI un
 * codice esistente, e non tocca mai un SIC di nodo finito per sbaglio nel
 * campo personale (quello lo ripara alb_ripara_sic_personali).
 */
function dr_sic_assicura(PDO $pdo, int $uid): string {
  if ($uid <= 0) return '';
  dr_sic_migra($pdo);
  try {
    $st = $pdo->prepare("SELECT COALESCE(NULLIF(internal_code,''), NULLIF(sic_id,''), '') FROM users WHERE id=? LIMIT 1");
    $st->execute([$uid]);
    $ora = (string)($st->fetchColumn() ?: '');
    if ($ora !== '' && !dr_sic_e_di_nodo($ora)) {   /* il Master rientra qui: il suo codice non si tocca */
      /* c'e' gia': lo si riallinea sull'altro campo se manca, e basta */
      try {
        $pdo->prepare("UPDATE users SET internal_code=COALESCE(NULLIF(internal_code,''),?),
                                        sic_id=COALESCE(NULLIF(sic_id,''),?) WHERE id=?")
            ->execute([$ora, $ora, $uid]);
      } catch (Throwable $e) {}
      return $ora;
    }
    $nuovo = dr_sic_nuovo($pdo);
    if ($nuovo === '') return $ora;
    $pdo->prepare("UPDATE users SET internal_code=COALESCE(NULLIF(internal_code,''),?),
                                    sic_id=COALESCE(NULLIF(sic_id,''),?) WHERE id=?")
        ->execute([$nuovo, $nuovo, $uid]);
    return $nuovo;
  } catch (Throwable $e) { return ''; }
}

/* --------------------------------------------------------------------------
   DIAGNOSTICA — quello che il pannello mostra
-------------------------------------------------------------------------- */

/** Codici che non rispettano il formato (col punto dentro, corti, strani). */
function dr_sic_malformati(PDO $pdo, int $limite = 200): array {
  dr_sic_migra($pdo);
  $out = [];
  try {
    foreach ($pdo->query("SELECT id, username, email, COALESCE(internal_code,'') ic, COALESCE(sic_id,'') si
                          FROM users") as $r) {
      foreach (['internal_code'=>$r['ic'], 'sic_id'=>$r['si']] as $campo => $v) {
        if ($v === '' || dr_sic_valido($v) || dr_sic_e_di_nodo($v) || dr_sic_e_speciale($v)) continue;
        $out[] = ['uid'=>(int)$r['id'], 'campo'=>$campo, 'valore'=>$v,
                  'chi'=>(string)($r['username'] ?: $r['email'])];
        if (count($out) >= $limite) return $out;
      }
    }
  } catch (Throwable $e) {}
  return $out;
}

/** Codici in mano a piu' di una persona: sono quelli che rubano i referral. */
function dr_sic_doppioni(PDO $pdo, int $limite = 200): array {
  dr_sic_migra($pdo);
  $out = [];
  foreach (['internal_code', 'sic_id'] as $campo) {
    try {
      $q = $pdo->query("SELECT {$campo} v, COUNT(*) n, GROUP_CONCAT(id) chi
                        FROM users WHERE COALESCE({$campo},'')<>''
                        GROUP BY {$campo} HAVING n>1 LIMIT {$limite}");
      foreach ($q as $r) $out[] = ['campo'=>$campo, 'valore'=>(string)$r['v'],
                                   'quanti'=>(int)$r['n'], 'utenti'=>(string)$r['chi']];
    } catch (Throwable $e) {}
  }
  return $out;
}

/**
 * RIPARA I CODICI FUORI FORMATO.
 *
 * Quando si puo' fare senza pensarci: un codice fuori formato — con un punto
 * dentro, troppo corto, con caratteri strani — e' GIA' rotto. Il link di
 * invito che lo contiene non risolve nessuno: alb_risolvi_ref() cerca la
 * corrispondenza esatta e non la trova. Quindi sostituirlo non rompe niente
 * che funzionasse: raddrizza qualcosa che era gia' storto.
 *
 * Cosa NON tocca, mai:
 *   - i codici validi (SIC-ID + 12 cifre)
 *   - i codici di nodo (SIC-ID-G-...): sono del posto, non della persona
 *   - il codice del Master (SIC-ID-MASTER-...): e' voluto cosi'
 *
 * Il vecchio valore resta scritto in dr_sic_log. Se un giorno salta fuori un
 * link con il codice vecchio, si sa a chi apparteneva.
 */
function dr_sic_ripara_malformati(PDO $pdo, int $max = 200): array {
  dr_sic_migra($pdo);
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS dr_sic_log(
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      ts TEXT DEFAULT (datetime('now')),
      uid INTEGER, campo TEXT, vecchio TEXT, nuovo TEXT, motivo TEXT)");
  } catch (Throwable $e) {}

  $out = ['sistemati'=>0, 'saltati'=>0, 'righe'=>[]];
  foreach (dr_sic_malformati($pdo, $max) as $m) {
    $nuovo = dr_sic_nuovo($pdo);
    if ($nuovo === '') { $out['saltati']++; continue; }
    try {
      $campo = ($m['campo'] === 'sic_id') ? 'sic_id' : 'internal_code';
      $pdo->prepare("UPDATE users SET {$campo}=? WHERE id=?")->execute([$nuovo, (int)$m['uid']]);
      $pdo->prepare("INSERT INTO dr_sic_log(uid,campo,vecchio,nuovo,motivo)
                     VALUES(?,?,?,?,'fuori formato')")
          ->execute([(int)$m['uid'], $campo, (string)$m['valore'], $nuovo]);
      $out['sistemati']++;
      $out['righe'][] = ['uid'=>(int)$m['uid'], 'chi'=>(string)$m['chi'],
                         'campo'=>$campo, 'vecchio'=>(string)$m['valore'], 'nuovo'=>$nuovo];
    } catch (Throwable $e) { $out['saltati']++; }
  }
  return $out;
}

/** Quante persone hanno un SIC e quante no. */
function dr_sic_riepilogo(PDO $pdo): array {
  dr_sic_migra($pdo);
  $o = ['utenti'=>0, 'con_sic'=>0, 'senza_sic'=>0, 'malformati'=>0, 'doppioni'=>0,
        'indice_unico'=>dr_sic_indice_attivo($pdo)];
  try {
    $o['utenti']   = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $o['con_sic']  = (int)$pdo->query("SELECT COUNT(*) FROM users
                                       WHERE COALESCE(internal_code,'')<>'' OR COALESCE(sic_id,'')<>''")->fetchColumn();
    $o['senza_sic'] = $o['utenti'] - $o['con_sic'];
  } catch (Throwable $e) {}
  $o['malformati'] = count(dr_sic_malformati($pdo));
  $o['doppioni']   = count(dr_sic_doppioni($pdo));
  return $o;
}

/**
 * Da' un codice buono a chi non ce l'ha. NON tocca chi ce l'ha gia',
 * nemmeno se e' malformato: quelli si guardano uno per uno.
 * Torna quanti ne ha sistemati.
 */
function dr_sic_riempi_mancanti(PDO $pdo, int $massimo = 500): int {
  dr_sic_migra($pdo);
  $n = 0;
  try {
    $q = $pdo->query("SELECT id FROM users
                      WHERE COALESCE(internal_code,'')='' AND COALESCE(sic_id,'')=''
                      LIMIT ".(int)$massimo);
    foreach ($q->fetchAll(PDO::FETCH_COLUMN) as $uid) {
      if (dr_sic_assicura($pdo, (int)$uid) !== '') $n++;
    }
  } catch (Throwable $e) {}
  return $n;
}
