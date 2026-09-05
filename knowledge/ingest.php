<?php
declare(strict_types=1);
require_once __DIR__.'/../bootstrap.php';

function ncke_id(string $seed): string {
    return 'NCKE-'.strtoupper(substr(hash('sha256',$seed.'|'.microtime(true).'|'.random_bytes(8)),0,20));
}
function chunk_markdown(string $text,int $maxChars=2200): array {
    $parts=preg_split('/(?=^#{1,4}\s)/m',$text) ?: [$text];
    $chunks=[];
    foreach($parts as $part){
        $part=trim($part); if($part==='') continue;
        $section='';
        if(preg_match('/^#{1,4}\s+(.+)$/m',$part,$m)) $section=trim($m[1]);
        while(mb_strlen($part)>$maxChars){
            $cut=mb_substr($part,0,$maxChars);
            $pos=max(mb_strrpos($cut,"\n\n") ?: 0, mb_strrpos($cut,". ") ?: 0);
            if($pos<800)$pos=$maxChars;
            $chunks[]=[$section,trim(mb_substr($part,0,$pos))];
            $part=trim(mb_substr($part,$pos));
        }
        if($part!=='')$chunks[]=[$section,$part];
    }
    return $chunks;
}
function ingest_markdown_tree(string $root): array {
    $pdo=db();$countDocs=0;$countChunks=0;
    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
    foreach($it as $f){
        if(!$f->isFile()||strtolower($f->getExtension())!=='md')continue;
        $path=$f->getPathname();$rel=str_replace($root.DIRECTORY_SEPARATOR,'',$path);
        if(preg_match('/(^|\/)\.env/i',$rel))continue;
        $content=file_get_contents($path) ?: '';
        // Secret-shaped material is redacted before indexing.
        $content=preg_replace('/(?i)\b(api[_ -]?key|secret|token|password)\b\s*[:=]\s*[^\s`]+/','${1}: [REDACTED]',$content);
        $hash=hash('sha256',$content);
        $st=$pdo->prepare("SELECT sic_id,content_hash,version FROM ncke_documents WHERE source_path=?");$st->execute([$rel]);$old=$st->fetch();
        if($old&&hash_equals($old['content_hash'],$hash))continue;
        if($old){
            $doc=$old['sic_id'];$ver=((int)$old['version'])+1;
            $pdo->prepare("UPDATE ncke_documents SET content_hash=?,version=?,updated_at=CURRENT_TIMESTAMP WHERE sic_id=?")->execute([$hash,$ver,$doc]);
            $chunkIds=$pdo->prepare("SELECT sic_id FROM ncke_chunks WHERE document_sic_id=?");$chunkIds->execute([$doc]);
            foreach($chunkIds->fetchAll(PDO::FETCH_COLUMN) as $cid)$pdo->prepare("DELETE FROM ncke_chunks_fts WHERE chunk_sic_id=?")->execute([$cid]);
            $pdo->prepare("DELETE FROM ncke_chunks WHERE document_sic_id=?")->execute([$doc]);
        }else{
            $doc=ncke_id('DOC|'.$rel);
            $title=basename($rel,'.md');
            $pdo->prepare("INSERT INTO ncke_documents(sic_id,source_path,title,content_hash) VALUES(?,?,?,?)")->execute([$doc,$rel,$title,$hash]);
        }
        foreach(chunk_markdown($content) as [$section,$body]){
            $cid=ncke_id('CHUNK|'.$rel.'|'.$section.'|'.$body);
            $pdo->prepare("INSERT INTO ncke_chunks(sic_id,document_sic_id,section,content,token_estimate) VALUES(?,?,?,?,?)")
                ->execute([$cid,$doc,$section,$body,(int)ceil(mb_strlen($body)/4)]);
            $pdo->prepare("INSERT INTO ncke_chunks_fts(chunk_sic_id,section,content) VALUES(?,?,?)")->execute([$cid,$section,$body]);
            $countChunks++;
        }
        $countDocs++;
    }
    return ['documents_updated'=>$countDocs,'chunks_written'=>$countChunks];
}
$r=ingest_markdown_tree(realpath(__DIR__.'/..'));
if(PHP_SAPI==='cli') echo json_encode(['ok'=>true]+$r,JSON_PRETTY_PRINT).PHP_EOL;
