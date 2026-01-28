<?php

declare(strict_types=1);

require_once 'questions.php';

// если кука есть, то перебросим на результат голосования
if (! empty($_COOKIE['quize'])) {
    header('Location: result.php');
    exit;
}

// показываем страницу с вопросами
require_once "quize.tpl.php";
