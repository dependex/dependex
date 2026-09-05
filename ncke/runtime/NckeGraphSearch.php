<?php
declare(strict_types=1);

final class NckeGraphSearch {
    private PDO $pdo;
    public function __construct(PDO $pdo){$this->pdo=$pdo;}

    private function tokens(string $q): array {
        $x=preg_split('/[^\pL\pN_-]+/u',mb_strtolower($q)) ?: [];
        $stop=['il','lo','la','i','gli','le','di','a','da','in','con','su','per','un','una','e','o','che','del','della','dei','the','of','and'];
        return array_values(array_unique(array_filter($x,fn($t)=>mb_strlen($t)>=3&&!in_array($t,$stop,true))));
    }

    public function search(string $query,int $limit=10): array {
        $tokens=$this->tokens($query);if(!$tokens)return [];
        $where=[];$params=[];
        foreach(array_slice($tokens,0,8) as $t){
            $where[]="(LOWER(COALESCE(title,'')) LIKE ? OR LOWER(COALESCE(content,'')) LIKE ? OR LOWER(COALESCE(section,'')) LIKE ?)";
            $like='%'.$t.'%';array_push($params,$like,$like,$like);
        }
        $sql="SELECT id,title,section,content,path,source,visibility,weight,feedback_score
              FROM brain_nodes WHERE ".implode(' OR ',$where)." ORDER BY weight DESC,feedback_score DESC LIMIT ".(int)$limit;
        $st=$this->pdo->prepare($sql);$st->execute($params);$nodes=$st->fetchAll();
        $out=[];
        foreach($nodes as $n){
            $score=0;
            $hay=mb_strtolower(($n['title']??'').' '.($n['section']??'').' '.($n['content']??''));
            foreach($tokens as $t)if(str_contains($hay,$t))$score+=8;
            $score+=min(15,(int)($n['weight']??1)*2)+min(10,max(0,(int)($n['feedback_score']??0)));
            $out[]=[
              'kind'=>'brain_node','id'=>$n['id'],'title'=>$n['title']?:$n['section'],
              'section'=>$n['section'],'content'=>$n['content'],'source'=>$n['source']?:$n['path'],
              'score'=>$score
            ];
        }
        return $out;
    }

    public function worldEntities(string $query,int $limit=12): array {
        $tokens=$this->tokens($query);if(!$tokens)return [];
        $where=[];$params=[];
        foreach(array_slice($tokens,0,6) as $t){
            $where[]="(LOWER(COALESCE(entity_name,'')) LIKE ? OR LOWER(COALESCE(city,'')) LIKE ? OR LOWER(COALESCE(country,'')) LIKE ? OR LOWER(COALESCE(address,'')) LIKE ? OR LOWER(COALESCE(sic_id,'')) LIKE ?)";
            $like='%'.$t.'%';array_push($params,$like,$like,$like,$like,$like);
        }
        $sql="SELECT sic_id,entity_name,network_level,country,region,province,city,address,status,source_url,parent_sic_id
              FROM dependex_world_registry WHERE ".implode(' OR ',$where)." LIMIT ".(int)$limit;
        $st=$this->pdo->prepare($sql);$st->execute($params);
        $out=[];
        foreach($st->fetchAll() as $r){
            $children=0;
            $s=$this->pdo->prepare("SELECT COUNT(*) FROM dependex_world_edges WHERE source_sic_id=? AND relation='PARENT_OF'");
            $s->execute([$r['sic_id']]);$children=(int)$s->fetchColumn();
            $out[]=[
              'kind'=>'world_entity','id'=>$r['sic_id'],'title'=>$r['entity_name'],
              'section'=>$r['network_level'],
              'content'=>implode(' · ',array_filter([$r['city'],$r['region'],$r['province'],$r['country'],$r['address'],$r['status']])),
              'source'=>$r['source_url'],'score'=>55+min(20,$children*2),
              'children'=>$children,'parent_sic_id'=>$r['parent_sic_id']
            ];
        }
        return $out;
    }
}
