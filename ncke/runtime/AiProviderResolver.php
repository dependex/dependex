<?php
declare(strict_types=1);

final class AiProviderResolver {
    private array $inventory = [];

    public function __construct(array $env){
        foreach($env as $k=>$v){
            if(!is_string($k) || !is_string($v) || trim($v)==='') continue;
            $provider=$this->inferProvider($k,$v);
            if(!$provider) continue;
            $this->inventory[$provider][]=[
                'env_name'=>$k,
                'fingerprint'=>substr(hash('sha256',$v),0,12),
                'secret'=>$v
            ];
        }
    }

    private function inferProvider(string $name,string $value): ?string {
        $n=strtoupper($name);
        $rules=[
            'groq'=>['GROQ','GSK_'],
            'gemini'=>['GEMINI','GOOGLE_AI','GENERATIVE_AI','AIza'],
            'openai'=>['OPENAI','GPT','SK-PROJ','SK-'],
            'anthropic'=>['ANTHROPIC','CLAUDE','SK-ANT'],
            'mistral'=>['MISTRAL'],
            'openrouter'=>['OPENROUTER'],
            'cohere'=>['COHERE'],
            'huggingface'=>['HUGGINGFACE','HF_TOKEN'],
            'assemblyai'=>['ASSEMBLYAI'],
            'elevenlabs'=>['ELEVENLABS']
        ];
        foreach($rules as $provider=>$tokens){
            foreach($tokens as $t){
                if(str_contains($n,strtoupper($t)) || str_starts_with($value,$t)) return $provider;
            }
        }
        // Generic API key can still be surfaced as an unmapped candidate.
        if(str_contains($n,'API') && str_contains($n,'KEY')) return 'unknown';
        return null;
    }

    public function status(): array {
        $out=[];
        foreach($this->inventory as $provider=>$items){
            $out[$provider]=[
                'ready'=>count($items)>0,
                'credentials'=>array_map(fn($x)=>[
                    'env_name'=>$x['env_name'],
                    'fingerprint'=>$x['fingerprint']
                ],$items)
            ];
        }
        return $out;
    }

    public function getSecret(string $provider): ?string {
        return $this->inventory[$provider][0]['secret'] ?? null;
    }
}
