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
        // 1. Locatie en weer ophalen
        // =========================
        $locatie = $_GET['locatie'] ?? 'sneek';
        $json = file_get_contents(
            "https://weerlive.nl/api/weerlive_api_v2.php?key=a1d5da060b&locatie={$locatie}"
        );

        $data = json_decode($json, true);
        $weer = $data['liveweer'][0] ?? [];

        $plaats = $weer['plaats'] ?? 'Onbekend';
        $temperatuur = (float) ($weer['temp'] ?? 0);
        $soortWeer = strtolower($weer['samenv'] ?? 'onbekend');

        // =========================
        // 2. Statische wegenlijst met strooifrequenties per temperatuur
        // =========================
        // Elke weg heeft: ID, Naam, Locatie, Strooiduur, Frequenties per temp
        $wegen = [
            [
                'ID' => 1,
                'Naam' => 'Hoofdstraat',
                'Locatie' => 'Sneek',
                'Strooiduur' => 30,
                'Frequenties' => [
                    -4 => 3,
                    -1 => 2,
                    0  => 1,
                ]
            ],
            [
                'ID' => 2,
                'Naam' => 'Dorpsweg',
                'Locatie' => 'Sneek',
                'Strooiduur' => 20,
                'Frequenties' => [
                    -3 => 2,
                    0  => 1
                ]
            ],
            [
                'ID' => 3,
                'Naam' => 'Ringweg',
                'Locatie' => 'Sneek',
                'Strooiduur' => 40,
                'Frequenties' => [
                    -5 => 4,
                    -2 => 2,
                    1  => 1
                ]
            ]
        ];

        $totaalMinuten = 0;
        $berekeningen = [];

        // =========================
        // 3. Berekening per weg
        // =========================
        foreach ($wegen as $weg) {
            // Zoek frequentie die past bij de huidige temperatuur
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
            'strooiwagens' => $strooiwagens,
            'locatie' => $locatie
        ]);
    }
}
