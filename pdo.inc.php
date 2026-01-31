<?php

// опишем к какой БД будем подключаться и по какому адресу она находится
// в данном случай БД типа mysql названная quizeproject

$dbConfig = $dbConfig ?? require_once 'config.db.php';

$dsn = 'mysql:dbname=' . $dbConfig['db'] . ';host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'];
$dbUser = $dbConfig['user'];
$dbPasswd = $dbConfig['pass'];
$dbOptions = $dbConfig['options'];

try {
    // подключаемся к БД
    $pdo =  new PDO(
        $dsn,
        $dbUser,
        $dbPasswd,
        $dbOptions
    );

    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    // сюда попадем если будет какая-то ошибка при работе с БД
    die('Шеф, с базой непонятки: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
} catch (Exception $e) {
    // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
    die('Шеф, все пропало: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
}

return $pdo;
