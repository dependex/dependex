<?php
declare(strict_types=1);
require_once __DIR__.'/../bootstrap.php';

final class KnowledgeMarkdownBuilder {
    private string $root;
    private PDO $pdo;

    public function __construct(string $root, PDO $pdo){
        $this->root=rtrim($root,'/');
        $this->pdo=$pdo;
    }

    public function buildAll(): array {
        $written=[];
        $written[]=$this->write('generated/PROJECT_OVERVIEW.md',$this->projectOverview());
        $written[]=$this->write('generated/DATABASE_SCHEMA.md',$this->databaseSchema());
        $written[]=$this->write('generated/NETWORK_KNOWLEDGE.md',$this->networkKnowledge());
        $written[]=$this->write('generated/NCKE_ARCHITECTURE.md',$this->nckeArchitecture());
        $written[]=$this->write('system/AI_SECURITY_POLICY.md',$this->aiSecurityPolicy());
        return $written;
    }

    private function write(string $rel,string $content): string {
        $path=$this->root.'/'.$rel;
        if(!is_dir(dirname($path))) mkdir(dirname($path),0770,true);
        file_put_contents($path,$content);
        return $path;
    }

    private function projectOverview(): string {
        return "# OLTRE / DEPENDEX — Project Brain Overview\n\n".
        "- OLTRE.social: piattaforma italiana.\n".
        "- DEPENDEX.social: ecosistema internazionale.\n".
        "- Payoff: AL CLUB. COL CLUB.\n".
        "- DEPENDEX: Dipendenza · Identità · Persona · Equilibrio · Network · Dialogo · Evoluzione · X/Trasformazione.\n".
        "- DRX: Dialogo · Relazioni · eXperienza.\n".
        "- Core: Hudolin, community, Club, famiglie, Academy, DAO, Lifestyle, Social Impact, Network, Neuralog/Cortex.\n\n".
        "## Regola cognitiva\nOgni modifica strutturale del progetto deve aggiornare il Knowledge Archive Markdown e l'indice NCKE.\n";
    }

    private function databaseSchema(): string {
        $tables=$this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
        $md="# Database Schema — Live Snapshot\n\n";
        foreach($tables as $t){
            $safe=str_replace('"','""',$t);
            $cols=$this->pdo->query('PRAGMA table_info("'.$safe.'")')->fetchAll(PDO::FETCH_ASSOC);
            $md.="## `{$t}`\n\n| Campo | Tipo | Null | PK |\n|---|---|---:|---:|\n";
            foreach($cols as $c){
                $md.="| `{$c['name']}` | {$c['type']} | ".($c['notnull']?'NO':'YES')." | {$c['pk']} |\n";
            }
            $md.="\n";
        }
        return $md;
    }

    private function networkKnowledge(): string {
        $stats=$this->pdo->query("SELECT network_level,COUNT(*) c FROM dependex_world_registry GROUP BY network_level ORDER BY c DESC")->fetchAll(PDO::FETCH_ASSOC);
        $countries=$this->pdo->query("SELECT COUNT(DISTINCT country) FROM dependex_world_registry WHERE country IS NOT NULL AND country<>''")->fetchColumn();
        $md="# DEPENDEX Global Network — Live Knowledge\n\n".
            "- Paesi nel registry: **{$countries}**\n\n## Nodi per livello\n\n";
        foreach($stats as $s) $md.="- {$s['network_level']}: {$s['c']}\n";
        $md.="\nLa gerarchia normalizzata è WORLD → CONTINENT → COUNTRY/NATIONAL → REGION/STATE → PROVINCE/DISTRICT → TERRITORIAL/LOCAL ASSOCIATION → LOCAL CLUB → FAMILY → USER.\n";
        return $md;
    }

    private function nckeArchitecture(): string {
        return "# Neuralog-Cortex Knowledge Engine (NCKE)\n\n".
        "NCKE è implementato in modalità **adaptive** per funzionare subito su hosting PHP/SQLite.\n\n".
        "## Core immediato\n- SQLite FTS5 full-text\n- Neuralog graph\n- metadata search\n- hybrid fusion\n- Markdown PageIndex\n- temporal/statistical analysis\n\n".
        "## Adapter progressivi\n- Vector DB\n- Neo4j\n- external reranker\n- WebSocket\n- MCP\n- observability stack\n- Kubernetes/cloud-native deployment\n\n".
        "Il sistema degrada elegantemente: senza vector DB usa FTS5 + grafo; senza LLM restituisce retrieval tracciabile.\n";
    }

    private function aiSecurityPolicy(): string {
        return "# AI / Secrets Security Policy\n\n".
        "1. I valori dei file `.env` NON vengono mai ingeriti da Neuralog/Cortex.\n".
        "2. Le API key non vengono salvate in Markdown, DB, log, HTML o JavaScript.\n".
        "3. Il Provider Resolver espone a Cortex solo provider READY/NOT READY e fingerprint non reversibili.\n".
        "4. Prompt injection non può autorizzare lettura dei secret.\n".
        "5. I dati sensibili personali restano esclusi dalla knowledge condivisa; gli insight community devono essere aggregati/de-identificati.\n";
    }
}

$builder=new KnowledgeMarkdownBuilder(__DIR__.'/..',db());
$files=$builder->buildAll();
if(PHP_SAPI==='cli') echo json_encode(['ok'=>true,'files'=>$files],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
