<!DOCTYPE html>
<html>
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Урок 12 - разрешаем изменять свой вариант голосования</title>
</head>
<body>
    <h1>Урок 12.</h1>
    <form action="process.php" method="POST">
        <?php foreach ($questions as $questionid => $question) { ?>
        <p><?=$question['question']?></p>
        <ul type="none">
            <?php foreach ($question['variants'] as $variantid => $variant) { ?>
            <li>
                <label><input type="radio" name="answers[<?=$questionid?>]" value="<?=$variantid?>" <?=(isset($userAnswers[$questionid]) && $userAnswers[$questionid] == $variantid ? 'checked="checked"' : '')?>><?=$variant?></label>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>

        <input type="submit" value="Проголосовать">
    </form>
</body>
</html>