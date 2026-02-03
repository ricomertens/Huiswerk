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
        // de weersomstandigheden ophalen via de api
        $json = file_get_contents(
            "https://weerlive.nl/api/weerlive_api_v2.php?key=a1d5da060b&locatie=sneek"
        );

        $data = json_decode($json, true);
        $weer = $data['liveweer'][0] ?? [];

        $plaats = $weer['plaats'] ?? 'Onbekend';
        $temperatuur = (float)($weer['temp'] ?? 10);
        $soortWeer = strtolower($weer['samenv'] ?? 'onbekend');

        // de wegen en informatie
        $wegen = [
            [
                'id' => 1,
                'naam' => 'A7',
                'locatie' => 'Snelweg',
                'duur' => 30, // minuten per strooibeurt
                'frequentie' => [
                    -4 => 3,
                    -1 => 2,
                    1  => 1
                ]
            ],
            [
                'id' => 2,
                'naam' => 'N354',
                'locatie' => 'Provinciale weg',
                'duur' => 25,
                'frequentie' => [
                    -4 => 3,
                    -1 => 2,
                    1  => 1
                ]
            ],
            [
                'id' => 3,
                'naam' => 'Centrum Sneek',
                'locatie' => 'Stad',
                'duur' => 20,
                'frequentie' => [
                    -4 => 2,
                    -1 => 1,
                    1  => 1
                ]
            ],
        ];

        // bepaling via temperatuur
        function bepaalFrequentie(float $temp, array $frequenties): int
        {
            if ($temp <= -4) {
                return $frequenties[-4] ?? 0;
            }
            if ($temp <= -1) {
                return $frequenties[-1] ?? 0;
            }
            if ($temp <= 1) {
                return $frequenties[1] ?? 0;
            }
            return 0;
        }

        // het berekenen van het aantal strooibeurten en totale tijd
        $totaalMinuten = 0;
        $output  = "Plaats: {$plaats}\n";
        $output .= "Temperatuur: {$temperatuur} °C\n";
        $output .= "Weer: {$soortWeer}\n\n";
        $output .= "Berekening per weg:\n";

        foreach ($wegen as $weg) {
            $aantalKeer = bepaalFrequentie($temperatuur, $weg['frequentie']);

            // Als het niet vriest of sneeuwt, hoeft er niet gestrooid te worden
            if ($aantalKeer === 0 && !str_contains($soortWeer, 'sneeuw')) {
                $output .= "- {$weg['naam']}: Geen actie\n";
                continue;
            }

            $minuten = $aantalKeer * $weg['duur'];
            $totaalMinuten += $minuten;

            $output .= "- {$weg['naam']}: {$aantalKeer}x strooien ({$minuten} minuten)\n";
        }

        // berekenen van het aantal strooiwagens
        $minutenPerWagen = 240; // 4 uur per dag
        $aantalWagens = (int) ceil($totaalMinuten / $minutenPerWagen);

        $output .= "\nTotale strooitijd: {$totaalMinuten} minuten\n";
        $output .= "Aantal strooiwagens nodig: {$aantalWagens}\n";

        // output
        $stream = Utils::streamFor("<pre>{$output}</pre>");
        return (new GuzzleResponse())->withBody($stream);
    }
}
