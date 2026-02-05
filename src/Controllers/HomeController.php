<?php

declare(strict_types=1);

namespace App\Controllers;

use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\Response as guzzleResponse;
use Nyholm\Psr7\Response as nyholmResponse;
use Psr\Http\Message\ResponseInterface;

class HomeController
{
    public function index(): ResponseInterface
    {
        $stream = Utils::streamFor("Homepage");

        $response = new GuzzleResponse;
        $response = $response->withBody($stream);

        return $response;
    }
}