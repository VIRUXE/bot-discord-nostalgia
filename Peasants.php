<?php

namespace Peasants;

class Insult
{
    const INCONVENIENT  = 'INCONVENIENT';
    const DISRESPECTFUL = 'DISRESPECTFUL';
    const RUDE          = 'RUDE';
    const SERIOUS       = 'SERIOUS';
    const POLITE        = 'POLITE';
    const ADVERTISEMENT = 'ADVERTISEMENT';
    const UNREASONABLE  = 'UNREASONABLE';
    const DISMISSIVE    = 'DISMISSIVE';
    const AGGRESSIVE    = 'AGGRESSIVE';
    const CONDESCENDING = 'CONDESCENDING';

    public static function getRandomInsult($category)
    {
        $insults = [
            'INCONVENIENT' => [
                'Não seja tão inconveniente.',
                'Isso é meio exagerado, não acha?',
            ],
            'DISRESPECTFUL' => [
                'Sem respeito, hein?',
                'Não precisa ser tão indiferente.',
            ],
            'RUDE' => [
                'Qual é a sua, cara?',
                'Cala a boca!',
            ],
            'SERIOUS' => [
                'Sério que você precisa falar assim?',
                'Seu amador!',
            ],
            'POLITE' => [
                'Mantenha a educação.',
                'Vocês não têm noção.',
            ],
            'ADVERTISEMENT' => [
                'Aqui não é lugar para divulgação.',
            ],
            'UNREASONABLE' => [
                'Isso é meio exagerado, não acha?',
            ],
            'DISMISSIVE' => [
                'Não precisa ser tão indiferente.',
            ],
            'AGGRESSIVE' => [
                'Calma! Não precisa ser tão agressivo.',
            ],
            'CONDESCENDING' => [
                'Ser condescendente não ajuda.',
            ],
        ];

        return $insults[$category][array_rand($insults[$category])];
    }
}

class Peasant
{
    public static function isAskingForIP($messageContent) {
        return preg_match('/\b(qual (o|é o) )?(ip)((\s*do)?\s*servidor)?\b/i', $messageContent) > 0;
    }

    public static function isAdmin($userId) {
        return in_array($userId, [
            '159298655361171456', // VIRUXE
            '476400568030461952', // Oupier
            '460850874512834571'  // Experimental
        ]);
    }

    public static function isSayingShit($messageContent) {
        $bad_phrases = [
            ['/(servid(?:o|)r|server) (ruim|horr[íi]vel|inst(?:a|á)vel)/i', Insult::INCONVENIENT],
            ['/(p(?:e|é)ssimo|cheio d(?:e|)) (?:servid(?:o|)r|server|bugs?)/i', Insult::RUDE],
            ['/admi?n?|emi?r) (incompetentes?|ruins?|pnc|lixo(?:so)?|de merda|imund[ao]|desorganizados?)/i', Insult::DISRESPECTFUL],
            ['/modera(?:ç|c)[aã]o fraca/i', Insult::INCONVENIENT],
            ['/n(?:a|ã)o ouvem os jogadores|suporte ruim|(staff|equipe) despreparad[ao]|sem corre(?:ç|c)[aã]o d(?:e|) bugs|n(?:a|ã)o resolvem problemas|falhas? constantes|bugs? ignorados?|rid(?:í|i)culo|n(?:ã|a)o sabe o que fala|voc(?:ê|e) é (burro|uma anta)|voc(?:ê|e) é (inútil|um inútil)|voc(?:ê|e) n(?:ã|a)o tem no(?:ç|c)[aã]o|seu inutil/i', Insult::DISRESPECTFUL],
            ['/m(?:a|á) administra(?:ç|c)[aã]o|isso (n(?:ã|a)o|jamais) vai funcionar/i', Insult::INCONVENIENT],
            ['/voc(?:ê|e) é (um )?(fracasso|fracassado)/i', Insult::INCONVENIENT],
            ['/voc(?:ê|e) n(?:ã|a)o sabe de nada|isso é est[uú]pido|cal(?:a|e) a boca|seu amador/i', Insult::SERIOUS],
            ['/vai pra (pqp|puta que pariu)/i', Insult::AGGRESSIVE],
            ['/que merda|vsf(?:d)?|staff (lixo|merda|bosta)|server (lixo|merda|bosta)/i', Insult::RUDE],
            ['/\b\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}:\d{1,5}\b|/discord(?:.gg|app.com/invite)/[\w-]+/i', Insult::ADVERTISEMENT],
        ];        

        foreach ($bad_phrases as $bad_phrase)
            if (preg_match($bad_phrase[0], $messageContent)) return Insult::getRandomInsult($bad_phrase[1]);
    
        return false;
    }
}