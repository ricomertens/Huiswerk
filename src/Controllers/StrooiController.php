<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database\Database;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use PDO;

class StrooiController
{
    public function index(): ResponseInterface
    {
        // =========================
        // 1. Weer ophalen
        // =========================
        $json = file_get_contents(
            "https://weerlive.nl/api/weerlive_api_v2.php?key=a1d5da060b&locatie=sneek"
        );

        $data = json_decode($json, true);
        $weer = $data['liveweer'][0] ?? [];

        $plaats = $weer['plaats'] ?? 'Onbekend';
        $temperatuur = (float) ($weer['temp'] ?? 10);
        $soortWeer = strtolower($weer['samenv'] ?? 'onbekend');

        // =========================
        // 2. Database
        // =========================
        $db = Database::connect();

        // Alle wegen ophalen
        $wegenStmt = $db->query("SELECT * FROM wegen");
        $wegen = $wegenStmt->fetchAll(PDO::FETCH_ASSOC);

        $totaalMinuten = 0;
        $output  = "Plaats: {$plaats}\n";
        $output .= "Temperatuur: {$temperatuur} °C\n\n";
        $output .= "Berekening per weg:\n";

        // =========================
        // 3. Per weg berekenen
        // =========================
        foreach ($wegen as $weg) {

            // Frequentie ophalen voor deze weg en temperatuur
            $freqStmt = $db->prepare("
                SELECT frequentie 
                FROM strooifrequenties
                WHERE Weg_ID = :weg_id
                AND Temperatuur >= :temp
                ORDER BY Temperatuur ASC
                LIMIT 1
            ");

            $freqStmt->execute([
                'weg_id' => $weg['ID'],
                'temp' => $temperatuur
            ]);

            $frequentie = $freqStmt->fetchColumn();

            // Geen strooien nodig
            if ($frequentie === false || 
               ($frequentie == 0 && !str_contains($soortWeer, 'sneeuw'))) {
                $output .= "- {$weg['Naam']}: Geen actie\n";
                continue;
            }

            $minuten = $frequentie * $weg['Strooiduur'];
            $totaalMinuten += $minuten;

            $output .= "- {$weg['Naam']}: {$frequentie}x strooien ({$minuten} min)\n";
        }

        // =========================
        // 4. Strooiwagens
        // =========================
        $minutenPerWagen = 240;
        $strooiwagens = (int) ceil($totaalMinuten / $minutenPerWagen);

        $output .= "\nTotale strooitijd: {$totaalMinuten} minuten\n";
        $output .= "Benodigde strooiwagens: {$strooiwagens}\n";

        // =========================
        // 5. Output
        // =========================
        $stream = Utils::streamFor("<pre>{$output}</pre>");
        return (new GuzzleResponse())->withBody($stream);
    }
}
