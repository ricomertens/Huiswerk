<?php

namespace Src\Database;

use PDO;
use PDOException;

class Database
{
    public static function connect(): PDO
    {
        try {
            return new PDO(
                "mysql:host=localhost;dbname=strooiwagen;charset=utf8",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );
        } catch (PDOException $e) {
            die("Database connectie mislukt: " . $e->getMessage());
        }
    }
}
