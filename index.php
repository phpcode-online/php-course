<?php
declare(strict_types=1);

// инициализируем переменную пустой строкой
$yourName = '';
if (! empty($_GET['name'])) {
    // так как HTML-тегов в имени быть не может и ничего кроме букв,
    // тире и пробела тоже - то удалим все лишнее

    // записать это можно вот так ...
    $yourName = trim($_GET['name']); // убираем пробелы с боков
    $yourName = strip_tags($yourName); // удаляем все html-теги
    $yourName = preg_replace('/[^a-zа-яЁё `-]/iu', '', $yourName); // удаляем все символы, кроме букв латинского и русского алфавита, а также пробела и символа тире и апострофа

    // ... или так
    //$yourName = preg_replace('/[^a-zа-яЁё `-]/iu', '', strip_tags(trim($_GET['name'])));
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type' />
    <title>Занятие 1 - Приветствие.</title>
</head>

<body>
    <h1>Первое занятие.</h1>
    <?php if ($yourName == '') { ?>
    <form action="index.php" method="get">
        <p>Как Тебя зовут?</p>
        <input type="text" name="name" value="">
        <input type="submit" value="Приятно познакомиться">
    </form>
    <?php } else { ?>
    <p>Приветствую, <?= htmlentities($yourName) ?></p>
    <?php } ?>
</body>
</html>