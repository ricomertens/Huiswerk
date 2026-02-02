<?php
declare(strict_types=1);

namespace App\Controllers;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WegController
{
    public function calculate(ServerRequestInterface $request): ResponseInterface
    {
        // 1. Temperatuur van de dag (hardcoded voor nu)
        $temperature = -4;

        // 2. Wegen + instellingen
        $roads = [
            [
                "id" => 1,
                "naam" => "Stationsweg",
                "locatie" => "Sneek",
                "strooitijd" => 30,
                "regels" => [
                    ["min" => -10, "max" => -4, "keer" => 3],
                    ["min" => -3, "max" => -1, "keer" => 2],
                    ["min" => 0, "max" => 1, "keer" => 1],
                ]
            ],
            [
                "id" => 2,
                "naam" => "Dorpsstraat",
                "locatie" => "Bolsward",
                "strooitijd" => 20,
                "regels" => [
                    ["min" => -10, "max" => -2, "keer" => 2],
                    ["min" => -1, "max" => 1, "keer" => 1],
                ]
            ]
        ];

        // 3. Totale strooitijd berekenen
        $totalMinutes = 0;

        foreach ($roads as $road) {
            $times = 0;

            foreach ($road["regels"] as $regel) {
                if ($temperature >= $regel["min"] && $temperature <= $regel["max"]) {
                    $times = $regel["keer"];
                    break;
                }
            }

            $totalMinutes += $road["strooitijd"] * $times;
        }

        // 4. Strooiwagens berekenen
        $minutesPerTruck = 480; // 8 uur
        $trucksNeeded = (int) ceil($totalMinutes / $minutesPerTruck);

        // 5. Output
        $output = "
Temperatuur: {$temperature} °C
Totale strooitijd: {$totalMinutes} minuten
Benodigde strooiwagens: {$trucksNeeded}
";

        $response = new Response();
        return $response->withBody(Utils::streamFor($output));
    }
}
