<?php
$yourName = '';
if (! empty($_GET['name'])) {
    $yourName = $_GET['name'];
}
?>
<!DOCTYPE html>
<html lang="ru">
    
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Занятие 1 - Приветствие.</title>
</head>

<body>
    <h1>Первое занятие.</h1>
    <?php if ($yourName == ''): ?>
    <form action="index.php" method="get">
        <p>Как Тебя зовут?</p>
        <input type="text" name="name" value="">
        <input type="submit" value="Приятно познакомиться">
    </form>
    <?php else: ?>
    <p>Приветствую, <?=$yourName?></p>
    <?php endif; ?>
</body>

</html>
