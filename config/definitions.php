<?php

use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Psr7\HttpFactory;
use Framework\Template\PlatesRenderer;
use Doctrine\ORM\EntityManagerInterface;
use Framework\Template\RendererInterface;
use Psr\Http\Message\ResponseFactoryInterface;

return [
    ResponseFactoryInterface::class => DI\create(HttpFactory::class),
    RendererInterface::class => DI\create(PlatesRenderer::class),
    EntityManagerInterface::class => function () {

        $paths = [dirname(__DIR__) . "/src/Entities"];

        $config = ORMSetup::createAttributeMetadataConfiguration($paths, true);

        $params = [
            "driver" => "pdo_mysql",
            "host" => $_ENV["DB_HOST"],
            "dbname" => $_ENV["DB_NAME"],
            "user" => $_ENV["DB_USER"],
            "password" => $_ENV["DB_PASSWORD"]
        ];

        $connection = DriverManager::getConnection($params, $config);

        return new EntityManager($connection, $config);
    }
];