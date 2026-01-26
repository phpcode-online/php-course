<!DOCTYPE html>
<html>
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Урок 8 - Результат голосования.</title>
</head>
<body>

    <?php if (! empty($_POST['answers']) && is_array($_POST['answers'])) { ?>
        <?php echo "Вы проголосовали:<br>\n"; ?>
        <?php foreach ($_POST['answers'] as $questionid => $variantid) { ?>
            <?=$questions[$questionid]['variants'][$variantid]?><br>
        <?php } ?>
    <?php } else { ?>
        <?php echo "Ваши ответы не найдены\n"; ?>
    <?php } ?>
    <p>
    <a href="index.php">Вернуться к голосованию</a>
    </p>
</body>
</html>