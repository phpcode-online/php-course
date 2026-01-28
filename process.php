<?php

/**
 * обработка ответа от пользователя
 * все результаты храним в отдельных файлах в специальном каталоге.
 * если каталога нет, то создадим его.
 */

declare(strict_types=1);

// если ответа нет, то перенаправим пользователя на страницу с формой
if (empty($_POST['answers'])) {
    header('Location: index.php');
    die(0);
}

// подключим файл с массивом вопросов
require_once 'questions.php';

$fullPath = __DIR__ . DIRECTORY_SEPARATOR . 'vote';

// если куки нет, то создадим ее
if (empty($_COOKIE['quize'])) {
    /////
    // создадим уникальную строку:
    do {
        $abc = [
            '1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
            'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'O','I', 'P',
            'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z',
            'X', 'C', 'V', 'B', 'N', 'M'
        ];
        // перемешаем элементы массива
        shuffle($abc);
        // объединим все элементы массива
        $abc = implode('', $abc);
        // возьмем 20 первых символов
        $abc = substr($abc, 0, 20);
        // добавим получившуюся строку к текущему времени с миллисекундами
        $cookieid = 'quize' . microtime(true) . $abc;
    } while (file_exists($fullPath . DIRECTORY_SEPARATOR . $cookieid . '.txt'));

    // отправим куки браузеру (куки будет хранится в браузере 30 дней)
    setcookie("quize", $cookieid, time() + (3600 * 24 * 30), "/", "", false, true);

    $_COOKIE['quize'] = $cookieid;
} else {
    $cookieid = preg_replace('/[^0-9A-Z.]/iu', '', $_COOKIE['quize']);

    // если кука есть, то проверим ее, а вдруг нам что-то странное передали
    // если в куке есть что-то кроме букв, цифр и запятой,
    // то значит нам что-то подсунули и мы можем завершить работу
    if (preg_match('/[^0-9A-Zquize\.]+/u', $cookieid)) {
        // можно конечно еще очистить куку,
        // но если человек ее намеренно портит, то зачем ему облегчать жизнь?
        die('Error!');
    }
    // если кука есть, то должен существовать и файл, если его нет - то значит это ошибка.
    if (! file_exists($fullPath . DIRECTORY_SEPARATOR . $cookieid . '.txt')) {
        die($fullPath . DIRECTORY_SEPARATOR . $cookieid . '.txt');
        // удалим куку для которой нет файла с результатом
        // тогда при следующем голосовании для этого пользователя создастся новая кука
        setcookie("quize", $cookieid, time() - (3600 * 24 * 30), "/", "", false, true);
        die('Error !!');
    }
}

$fileName = $fullPath . DIRECTORY_SEPARATOR . $cookieid . '.txt';

// если нет каталога для хранения результатов голосования, то создадим его
if (! file_exists($fullPath) && ! mkdir($fullPath)) {
    die('Cannot create vote directory: ' . $fullPath);
}

// если есть ответы от пользователя,
// то запишем их в отдельный файл c именем куки, в формате: код вопроса;код ответа
if (! empty($_POST['answers']) && is_array($_POST['answers'])) {
    // откроем файл на создание (если файл уже существует, то он будет перезаписан)
    $f = fopen($fileName, 'w');
    fwrite($f, serialize($_POST['answers']));
    fclose($f);
}

header('Location: result.php');
