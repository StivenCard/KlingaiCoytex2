<?php

namespace App\Services;

class HintsService
{
    private array $hints = [
        'elegant' => 'Standing elegantly, with feet naturally together, right hand holding a black handbag, left hand hanging naturally, chin slightly raised, short hair slightly curled, delicate pink woolen short jacket with metal buckle decoration, white inner wear, black skirt, black stockings, black patent leather loafers, light green ground background, simple studio shot',

        'urban' => 'Relaxed standing pose, legs naturally crossed, one hand gently touching hair, reddish-brown medium-short hair, white loose hoodie, black shorts, sidewalk scene, green tree background, iron fence, natural light, casual everyday style',

        'energetic' => 'Dynamic athletic pose, one leg slightly forward, arms positioned naturally showing energy, bright sportswear, modern fitness attire, gym or outdoor sports setting, vibrant colors, confident expression',

        'sweet' => 'Gentle pose with soft smile, flowing hair, wearing pastel colored dress, delicate jewelry, soft natural lighting, floral or garden background, feminine and graceful appearance',

        'intellectual' => 'Professional confident pose, business attire, clean modern look, holding books or documents, office or library setting, sophisticated styling, neutral background, smart casual appearance'
    ];

    public function getAllHints(): array
    {
        return array_map(fn($key) => [
            'key' => $key,
            'name' => ucfirst($key),
            'prompt' => $this->hints[$key]
        ], array_keys($this->hints));
    }

    public function getPromptForHint(string $hint): string
    {
        return $this->hints[$hint] ?? '';
    }
}
