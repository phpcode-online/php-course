<?php

declare(strict_types=1);

// подключаемся к БД
// @var pdo PDO
$pdo = require_once $appRoot . "pdo.inc.php";

// подключаем список вопросов
$quiz = new Quiz(['id' => 1], $pdo);
$questions = $quiz->getQuestionsAsArray();

$fullPath = __DIR__;
$userAnswers = [];

// если мы ранее голосовали, то считаем из файла результаты голосования.
if (! empty($_COOKIE['quize'])) {
    $cookieId = $_COOKIE['quize'];
    // если есть какие-то символы кроме цифр, больших букв латинского алфавита
    // знакак точка и маленьких букв "q", "u", "i", "z", "e" то это ошибка
    if (preg_match('/[^0-9A-Z\.quize]+/u', $cookieId)) {
        // можно конечно еще очистить куку,
        // но если человек ее намеренно портит, то зачем ему облегчать жизнь?
        die('Голосование завершено');
    }

    $userAnswers = UserAnswers::getUserAnswers($cookieId, $questions, $pdo);
}

// подключаем скрипт который отвечает за вывод вопросов
require_once $appRoot . 'views/quize.tpl.php';
