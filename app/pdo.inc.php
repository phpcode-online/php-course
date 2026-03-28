<?php

// Метод для получения объекта PDO для работы с базой данных
function getPDO()
{
    static $pdo = null;

    if ($pdo === null) {
        // подключаемся к БД только при первом вызове функции
        $dbConfig = require_once __DIR__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'config.db.php';

        // определим массив параметров для инициализации подключения к БД
        $pdoOptions = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            //PDO::MYSQL_ATTR_MAX_BUFFER_SIZE => 4*1024*1024
        );

        $dsn = 'mysql:dbname=' . $dbConfig['db'] . ';host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'];
        $dbUser = $dbConfig['user'];   // имя пользователя для логина в БД
        $dbPasswd = $dbConfig['pass']; // пароль

        try {
            // подключаемся к БД
            $pdo =  new PDO(
                $dsn,
                $dbUser,
                $dbPasswd,
                $pdoOptions
            );

            $pdo->exec("SET NAMES 'utf8mb4';SET sql_mode = '';");
        } catch (PDOException $e) {
            // сюда попадем если будет какая-то ошибка при работе с БД
            die('Шеф, с базой непонятки: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        } catch (Exception $e) {
            // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
            die('Шеф, все пропало: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        }
    }

    return $pdo;
}
