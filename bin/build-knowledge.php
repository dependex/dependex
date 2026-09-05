#!/usr/bin/env php
<?php
require_once dirname(__DIR__).'/bootstrap.php';

$root=dirname(__DIR__);
$knowledge=$root.'/knowledge';
$inbox=$knowledge.'/neuralog/inbox';
@mkdir($inbox,0775,true);

function kb_write(string $rel,string $body): void {
    global $knowledge,$inbox;
    $p=$knowledge.'/'.$rel; @mkdir(dirname($p),0775,true);
    file_put_contents($p,rtrim($body)."\n");
    $flat=str_replace(['/','\\'],['__','__'],$rel);
    file_put_contents($inbox.'/'.$flat,rtrim($body)."\n");
}
function kb_table_summary(PDO $pdo,string $table): string {
    try{$n=(int)$pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();}catch(Throwable $e){$n=0;}
    return "- `$table`: $n record\n";
}

$pdo=db();
$now=date('c');
$tables=[];
foreach($pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name") as $r)$tables[]=$r['name'];

$stats="# Database live snapshot\n\nGenerato: `$now`\n\n";
foreach($tables as $t)$stats.=kb_table_summary($pdo,$t);
kb_write('03_data/LIVE_DATABASE_SNAPSHOT.md',$stats);

try{
 $r=$pdo->query("SELECT COUNT(*) nodes,
   SUM(CASE WHEN network_level='LOCAL_CLUB' THEN 1 ELSE 0 END) clubs,
   COUNT(DISTINCT CASE WHEN country<>'' THEN country END) countries
   FROM dependex_world_registry")->fetch();
 $geo="# World Registry live\n\n- Nodi: {$r['nodes']}\n- Club locali: {$r['clubs']}\n- Paesi: {$r['countries']}\n\n";
 foreach($pdo->query("SELECT COALESCE(geo_accuracy,'UNKNOWN') k,COUNT(*) n FROM dependex_world_registry WHERE network_level='LOCAL_CLUB' GROUP BY COALESCE(geo_accuracy,'UNKNOWN') ORDER BY n DESC") as $x)
   $geo.="- {$x['k']}: {$x['n']}\n";
 kb_write('03_data/LIVE_WORLD_REGISTRY.md',$geo);
}catch(Throwable $e){}

try{
 $ranks="# Rank live\n\n";
 foreach($pdo->query("SELECT name,threshold_drx FROM ranks ORDER BY rank_order") as $r)$ranks.="- {$r['name']}: {$r['threshold_drx']} DRX\n";
 kb_write('03_data/LIVE_RANKS.md',$ranks);
}catch(Throwable $e){}

$mods=[
 'auth'=>'Login, recovery, sessioni e ACL.',
 'club'=>'Club Hub, membership, famiglie e moltiplicazione.',
 'academy'=>'SAT, sensibilizzazione, corsi, progressi e attestati.',
 'drx'=>'Ledger DRX, daily, sobrietà, missioni, reward e rank eligibility.',
 'dao'=>'Forum, proposte, voti, scope e governance.',
 'network'=>'Tree, graph, World Explorer e geolocalizzazione.',
 'lifestyle'=>'Diario, Ruota della Vita, check-in e missioni.',
 'events'=>'Eventi, registrazioni, check-in e reward.',
 'finance'=>'Tesoreria, quote, spese, donazioni e rendiconti.',
 'research'=>'OSINT mondiale, fonti, confidence, status e backlog.',
 'neuralog'=>'Knowledge graph, RAG, memoria, ingest, quality e bus.',
 'cortex'=>'AI orchestration, chat e uso della knowledge base.'
];
foreach($mods as $k=>$v)kb_write('02_modules/LIVE_'.strtoupper($k).'.md',"# Modulo ".strtoupper($k)."\n\n$v\n\nSnapshot generato automaticamente: `$now`.\n");

kb_write('09_changelog/LIVE_LAST_BUILD.md',"# Ultimo aggiornamento cognitivo\n\nRigenerato automaticamente: `$now`.\n\nLa knowledge base è viva: questo file prova l'ultima sincronizzazione del progetto con Neuralog/Cortex.\n");
echo json_encode(['ok'=>true,'knowledge'=>$knowledge,'inbox'=>$inbox,'generated_at'=>$now],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
