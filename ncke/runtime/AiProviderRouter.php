<?php
declare(strict_types=1);
require_once __DIR__.'/AiProviderResolver.php';

final class AiProviderRouter {
    private AiProviderResolver $resolver;
    private array $capabilities = [
        'groq'=>['chat','reasoning','fast'],
        'gemini'=>['chat','reasoning','vision','multimodal'],
        'openai'=>['chat','reasoning','vision','embeddings','audio'],
        'anthropic'=>['chat','reasoning','vision'],
        'mistral'=>['chat','reasoning','embeddings'],
        'openrouter'=>['chat','routing'],
        'cohere'=>['rerank','embeddings'],
        'huggingface'=>['embeddings','local_models'],
        'assemblyai'=>['audio','transcription'],
        'elevenlabs'=>['audio','tts']
    ];

    public function __construct(AiProviderResolver $resolver){$this->resolver=$resolver;}

    public function registry(): array {
        $status=$this->resolver->status();$out=[];
        foreach($status as $provider=>$meta){
            $out[$provider]=[
                'ready'=>(bool)$meta['ready'],
                'capabilities'=>$this->capabilities[$provider]??['unknown'],
                'credentials'=>$meta['credentials']
            ];
        }
        return $out;
    }

    public function select(string $capability='chat',array $preferred=[]): ?array {
        $registry=$this->registry();
        $order=$preferred ?: ['groq','gemini','openai','anthropic','mistral','openrouter','cohere','huggingface'];
        foreach($order as $provider){
            if(empty($registry[$provider]['ready']))continue;
            if(!in_array($capability,$registry[$provider]['capabilities'],true))continue;
            return ['provider'=>$provider,'secret'=>$this->resolver->getSecret($provider),'capabilities'=>$registry[$provider]['capabilities']];
        }
        return null;
    }

    public function safeStatus(): array {
        $r=$this->registry();
        foreach($r as &$p) unset($p['secret']);
        return $r;
    }
}
