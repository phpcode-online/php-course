<!DOCTYPE html>
<html>
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Урок 9 - Голосование.</title>
</head>
<body>
    <h1>Урок 9</h1>
    <form action="process.php" method="POST">
        <?php foreach ($questions as $questionid => $question) { ?>
        <p><?=$question['question']?></p>
        <ul type="none">
            <?php foreach ($question['variants'] as $variantid => $variant) { ?>
            <li>
                <label><input type="radio" name="answers[<?=$questionid?>]" value="<?=$variantid?>"><?=$variant?></label>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>

        <input type="submit" value="Проголосовать">
    </form>
</body>
</html>