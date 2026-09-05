# Company Brain dentro WordPress

Si può fare, e senza plugin. Ma prima le cose che non tornano, perché sono
quelle che ti fanno perdere il pomeriggio.

## Le tre cose da sapere prima

1. **WordPress non usa PDO, usa `wpdb` (mysqli).** Non puoi passargli il suo
   oggetto database. Devi aprire un PDO tuo sulle *stesse credenziali*: le
   tabelle restano nello stesso database, quindi la regola "un solo database"
   è rispettata anche se la connessione è una seconda.
2. **La cartella `data/` non deve stare sotto `wp-content/uploads/`**, che è
   servita dal web. Mettila fuori dalla document root, oppure lascia il
   `.htaccess` che nega tutto (funziona solo su Apache: su nginx la regola va
   nella configurazione del server).
3. **Il cron di WordPress parte solo se qualcuno visita il sito.** Per
   l'ingestione usa un cron vero di sistema che chiama `php bin/brain ingest --all`.
   Se ti affidi a `wp_cron`, su un sito poco visitato la conoscenza resta ferma.

## Il ponte, in pratica

Nel `functions.php` del tema figlio (o meglio in un mu-plugin):

```php
// wp-content/mu-plugins/company-brain.php
add_action('plugins_loaded', function () {

    // 1. PDO sulle stesse credenziali di WordPress
    $GLOBALS['BRAIN_PDO'] = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    // 2. chi è admin, secondo WordPress
    function brain_host_is_admin(): bool { return current_user_can('manage_options'); }

    // 3. il cervello
    require_once WP_CONTENT_DIR . '/company-brain/brain.php';
});
```

In `config/brain.local.json` conviene un prefisso che non litighi con niente:

```json
{ "db": { "table_prefix": "wp_brain_", "use_host_pdo": true } }
```

## Far mangiare al cervello i contenuti del sito

```php
// ogni volta che un articolo o una pagina viene salvato
add_action('save_post', function ($post_id, $post) {
    if (wp_is_post_revision($post_id) || $post->post_status !== 'publish') { return; }
    brain_ingest_text(
        wp_strip_all_tags($post->post_title . "\n\n" . $post->post_content),
        [
            'path'       => 'wp/' . $post->post_type . '/' . $post_id,
            'title'      => $post->post_title,
            'source'     => 'wordpress',
            'section'    => 'document',
            'visibility' => 'public',
        ]
    );
}, 10, 2);

// e quando viene cestinato, se ne dimentica
add_action('trashed_post', function ($post_id) {
    brain_forget_path('wp/post/' . $post_id);
});
```

## Uno shortcode di ricerca

```php
add_shortcode('brain_search', function () {
    $q = isset($_GET['bq']) ? sanitize_text_field(wp_unslash($_GET['bq'])) : '';
    $out = '<form method="get"><input name="bq" value="' . esc_attr($q) . '"><button>cerca</button></form>';
    foreach (brain_search($q, ['admin' => false, 'n' => 5]) as $r) {
        $out .= '<div class="brain-hit"><b>' . esc_html(brain_source_label($r)) . '</b><br>'
              . esc_html(brain_cut($r['content'], 300)) . '</div>';
    }
    return $out;
});
```

## Cosa NON fare

- Non esporre `ui/console.php` senza chiave: si difende da sola, ma non c'è
  motivo di lasciarla raggiungibile dal web pubblico.
- Non ingerire `wp-config.php` né la cartella `wp-content/uploads` intera: la
  guardia sui segreti blocca `.env` e le chiavi, ma su un sito WordPress il
  materiale sensibile ha spesso nomi normalissimi. Scegli tu cosa dare in pasto.
- Non promuovere in blocco i nodi a `public`. Nascono riservati apposta.
