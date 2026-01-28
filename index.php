<?php

/**
 * Если уже голосовали то выводим как именно голосовал пользователь
 * PS: аналогично работает и сессия - в куках хранится уникальный ключ,
 * а в файле на сервере все данные
 **/

declare(strict_types=1);

require_once 'questions.php';

$fullPath = __DIR__ . DIRECTORY_SEPARATOR . 'vote';
$userAnswers = [];

// если мы ранее голосовали, то считаем результаты голосования.
if (! empty($_COOKIE['quize'])) {
    $cookieid = $_COOKIE['quize'];
    $cookieid = preg_replace('/[^0-9A-Zquize\.]+/u', '', $cookieid);

    if (file_exists($fullPath . DIRECTORY_SEPARATOR . $cookieid . '.txt')) {
        // считали весь файл с результатом конкретного пользователя
        $result = file_get_contents($fullPath . DIRECTORY_SEPARATOR . $cookieid . '.txt');
        $userAnswers = unserialize($result);
    }
}

require_once 'quize.tpl.php';
