<?php

declare(strict_types=1);

// подключаемся к БД
// @var pdo PDO
$pdo = require_once "pdo.inc.php";

// подключаем список вопросов
$questions = require_once 'questions.php';

$fullPath = __DIR__;
$userAnswers = [];

// если мы ранее голосовали, то считаем из файла результаты голосования.
if (! empty($_COOKIE['quize'])) {
    $cookieid = $_COOKIE['quize'];
    // если есть какие-то символы кроме цифр, больших букв латинского алфавита
    // знакак точка и маленьких букв "q", "u", "i", "z", "e" то это ошибка
    if (preg_match('/[^0-9A-Z\.quize]+/u', $cookieid)) {
        // можно конечно еще очистить куку,
        // но если человек ее намеренно портит, то зачем ему облегчать жизнь?
        die('Голосование завершено');
    }

    try {
        // запросим все данные по ответам пользователя
        $sql = "SELECT * FROM q_useranswers WHERE userid = :cookieid";
        $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $result = $sth->execute(['cookieid' => $cookieid]);

        if ($result) {
            // пройдемся по всем полученным данным и заполним массив с ответами пользователя
            while (($row = $sth->fetch(PDO::FETCH_ASSOC)) !== false) {
                $questionid = $row['questionid'];
                $answerid = $row['variantid'];

                // если вопроса с указанным кодом нет, то пропустим
                if (
                    empty($questions[$questionid])
                    || empty($questions[$questionid]['variants'][$answerid])
                ) {
                    continue;
                }

                $userAnswers[$questionid] = $answerid;
            }
        }
    } catch (PDOException $e) {
        // сюда попадем если будет какая-то ошибка при работе с БД
        die('Шеф, с базой непонятки: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
    } catch (Exception $e) {
        // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
        die('Шеф, все пропало: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
    }
}

// подключаем скрипт который отвечает за вывод вопросов
require_once 'quize.tpl.php';
