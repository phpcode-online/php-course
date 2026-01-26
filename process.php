<?php
declare(strict_types=1);

/**
 * обработка ответа от пользователя
 */

// если ответа нет, то перенаправим пользователя на страницу с формой
if (empty($_POST['answers'])) {
    header('Location: index.php');
    die(0);
}

// подключим файл с массивом вопросов
require_once 'questions.php';

// если есть ответы от пользователя,
// то запишем их в файл, в формате: код вопроса;код ответа
if (! empty($_POST['answers']) && is_array($_POST['answers'])) {
    $f = fopen('vote.txt', 'a');
    foreach ($_POST['answers'] as $questionid => $variantid) {
        fwrite($f, $questionid . ';' . $variantid . "\n");
    }
    fclose($f);
}

require_once "results.tpl.php";
