<?php
declare(strict_types=1);

final class SecureEnvDiscovery {
    private array $allowedRoots;
    private array $loaded = [];
    private array $meta = [];

    public function __construct(array $roots) {
        $this->allowedRoots = array_values(array_filter(array_map(
            fn($p)=>realpath($p) ?: $p, $roots
        )));
    }

    public function discover(): array {
        $patterns = ['.env','.env.local','.env.production','.env.production.local','.env.ai','.env.secrets'];
        $files = [];
        foreach($this->allowedRoots as $root){
            if(!is_dir($root)) continue;
            foreach($patterns as $name){
                $p = $root.DIRECTORY_SEPARATOR.$name;
                if(is_file($p)) $files[] = $p;
            }
            foreach(glob($root.DIRECTORY_SEPARATOR.'.env.*') ?: [] as $p){
                if(is_file($p)) $files[] = $p;
            }
        }
        $files = array_values(array_unique($files));
        usort($files, fn($a,$b)=>$this->priority($a)<=>$this->priority($b));
        return $files;
    }

    private function priority(string $p): int {
        $n=basename($p);
        return match($n){
            '.env'=>10,
            '.env.local'=>20,
            '.env.production'=>30,
            '.env.production.local'=>40,
            default=>25
        };
    }

    public function load(): array {
        foreach($this->discover() as $file){
            $this->loadFile($file);
        }
        return $this->meta;
    }

    private function loadFile(string $file): void {
        $real=realpath($file);
        if(!$real || !$this->insideAllowedRoot($real)) return;
        $lines=@file($real, FILE_IGNORE_NEW_LINES);
        if(!$lines) return;
        foreach($lines as $line){
            $line=trim($line);
            if($line==='' || str_starts_with($line,'#') || !str_contains($line,'=')) continue;
            [$key,$value]=explode('=',$line,2);
            $key=trim($key);
            if(!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/',$key)) continue;
            $value=trim($value);
            if((str_starts_with($value,'"')&&str_ends_with($value,'"'))||(str_starts_with($value,"'")&&str_ends_with($value,"'"))){
                $value=substr($value,1,-1);
            }
            // Later/higher-priority files override earlier values.
            $_ENV[$key]=$value;
            $_SERVER[$key]=$value;
            putenv($key.'='.$value);
            $this->loaded[$key]=true;
        }
        $this->meta[]=[
            'file'=>basename($real),
            'path_hash'=>hash('sha256',$real),
            'keys_count'=>count($this->loaded)
        ];
    }

    private function insideAllowedRoot(string $file): bool {
        foreach($this->allowedRoots as $root){
            $rr=realpath($root);
            if($rr && str_starts_with($file,$rr.DIRECTORY_SEPARATOR)) return true;
        }
        return false;
    }

    public function safeInventory(): array {
        $out=[];
        foreach(array_keys($this->loaded) as $k){
            $out[]=['name'=>$k,'present'=>true,'value'=>'[REDACTED]'];
        }
        return $out;
    }
}
