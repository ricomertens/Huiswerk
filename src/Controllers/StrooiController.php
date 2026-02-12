<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;

class StrooiController extends AbstractController
{
    public function index(): ResponseInterface
    {
        // =========================
        // 1. Vast ingestelde locatie (Sneek)
        // =========================
        $locatie = 'sneek';

        $json = file_get_contents(
            "https://weerlive.nl/api/weerlive_api_v2.php?key=a1d5da060b&locatie={$locatie}"
        );

        $data = json_decode($json, true);
        $weer = $data['liveweer'][0] ?? [];

        $plaats = $weer['plaats'] ?? 'Sneek';
        $temperatuur = (float) ($weer['temp'] ?? 0);
        $soortWeer = strtolower($weer['samenv'] ?? 'onbekend');

        // =========================
        // 2. Statische wegenlijst
        // =========================
        $wegen = [
    [
        'ID' => 1,
        'Naam' => 'Lemmerweg',
        'Locatie' => 'Sneek',
        'Strooiduur' => 45,
        'Frequenties' => [
            -5 => 4,
            -2 => 3,
            0  => 2,
        ]
    ],
    [
        'ID' => 2,
        'Naam' => 'Oude Koemarkt',
        'Locatie' => 'Sneek',
        'Strooiduur' => 30,
        'Frequenties' => [
            -4 => 3,
            -1 => 2,
            1  => 1,
        ]
    ],
    [
        'ID' => 3,
        'Naam' => 'Stationsstraat',
        'Locatie' => 'Sneek',
        'Strooiduur' => 35,
        'Frequenties' => [
            -4 => 3,
            -1 => 2,
            1  => 1,
        ]
    ],
    [
        'ID' => 4,
        'Naam' => 'Westersingel',
        'Locatie' => 'Sneek',
        'Strooiduur' => 40,
        'Frequenties' => [
            -5 => 4,
            -2 => 2,
            0  => 1,
        ]
    ],
    [
        'ID' => 5,
        'Naam' => 'Bolswarderweg',
        'Locatie' => 'Sneek',
        'Strooiduur' => 50,
        'Frequenties' => [
            -6 => 4,
            -3 => 3,
            -1 => 2,
        ]
    ],
    [
        'ID' => 6,
        'Naam' => 'Rijksweg A7 (afrit Sneek)',
        'Locatie' => 'Sneek',
        'Strooiduur' => 60,
        'Frequenties' => [
            -6 => 5,
            -3 => 3,
            -1 => 2,
        ]
    ]
];

        $totaalMinuten = 0;
        $berekeningen = [];

        // =========================
        // 3. Berekening per weg
        // =========================
        foreach ($wegen as $weg) {

            $frequentie = 0;

            foreach ($weg['Frequenties'] as $temp => $freq) {
                if ($temperatuur <= $temp) {
                    $frequentie = $freq;
                    break;
                }
            }

            if ($frequentie === 0 && !str_contains($soortWeer, 'sneeuw')) {
                $berekeningen[] = [
                    'ID' => $weg['ID'],
                    'Naam' => $weg['Naam'],
                    'Locatie' => $weg['Locatie'],
                    'Actie' => 'Geen actie',
                    'Minuten' => 0,
                    'Frequentie' => 0
                ];
                continue;
            }

            $minuten = $frequentie * $weg['Strooiduur'];
            $totaalMinuten += $minuten;

            $berekeningen[] = [
                'ID' => $weg['ID'],
                'Naam' => $weg['Naam'],
                'Locatie' => $weg['Locatie'],
                'Actie' => "{$frequentie}x strooien",
                'Minuten' => $minuten,
                'Frequentie' => $frequentie
            ];
        }

        // =========================
        // 4. Strooiwagens berekenen
        // =========================
        $minutenPerWagen = 240;
        $strooiwagens = (int) ceil($totaalMinuten / $minutenPerWagen);

        // =========================
        // 5. Render view
        // =========================
        return $this->render('strooi/index', [
            'plaats' => $plaats,
            'temperatuur' => $temperatuur,
            'soortWeer' => $soortWeer,
            'berekeningen' => $berekeningen,
            'totaalMinuten' => $totaalMinuten,
            'strooiwagens' => $strooiwagens
        ]);
    }
}