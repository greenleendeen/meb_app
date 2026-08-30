<?php
require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

$config = new \Doctrine\DBAL\Configuration();

try {
    $connection = DriverManager::getConnection([
        'url' => 'mysql://menuisxusermeb:MpBddMebapp1980@menuisxusermeb.mysql.db:3306/menuisxusermeb',
    ], $config);

    $result = $connection->fetchAllAssociative('SHOW TABLES;');
    echo '<pre>';
    print_r($result);
    echo '</pre>';
} catch (\Exception $e) {
    echo 'Erreur BDD : '.$e->getMessage();
}