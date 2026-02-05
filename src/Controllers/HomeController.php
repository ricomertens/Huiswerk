<?php

declare(strict_types=1);

namespace App\Controllers;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;

class HomeController
{
    public function index(): ResponseInterface
    {
        $json = file_get_contents(
            "https://weerlive.nl/api/weerlive_api_v2.php?key=a1d5da060b&locatie=sneek"
        );

        $data = json_decode($json, true);
        $weer = $data['liveweer'][0] ?? [];

        $plaats = $weer['plaats'] ?? 'Onbekend';
        $temperatuur = (float)($weer['temp'] ?? 10);
        $zicht = (int)($weer['zicht'] ?? 10);
        $soortWeer = strtolower($weer['samenv'] ?? 'onbekend');

        $alarmCode = (int)($weer['alarm'] ?? 0);
        $alarmTekst = match ($alarmCode) {
            1 => 'Code geel',
            2 => 'Code oranje',
            3 => 'Code rood',
            default => 'Code groen',
        };

        $wegen = [
            'A7',
            'N354',
            'Ring Sneek',
            'Industrieterrein Houkesloot',
            'Centrum Sneek'
        ];
        
        $output  = "Plaats: {$plaats}\n";
        $output .= "Temperatuur: {$temperatuur} °C\n";
        $output .= "Zicht: {$zicht} m\n";
        $output .= "Weer: {$soortWeer}\n";
        $output .= "Weercode: {$alarmTekst}\n\n";
        $output .= "Beslissing per weg:\n";

        foreach ($wegen as $weg) {
            $actie = 'Geen actie';

            if (str_contains($soortWeer, 'sneeuw')) {
                $actie = 'Sneeuwschuiver + Strooien';
            } elseif ($temperatuur <= 1) {
                $actie = 'Strooien';
            }

            $output .= "- {$weg}: {$actie}\n";
        }

        $stream = Utils::streamFor("<pre>{$output}</pre>");
        return (new GuzzleResponse)->withBody($stream);
    }
}