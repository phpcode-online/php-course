<!DOCTYPE html>
<html>
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Урок 29 - классы для работы с данными</title>
</head>
<body>
    <h1>Урок 29.</h1>
    <form action="/process/" method="POST">
        <?php foreach ($this->questions as $questionid => $question) { ?>
        <p><?=$question['question']?></p>
        <ul type="none">
            <?php foreach ($question['variants'] as $variantid => $variant) { ?>
            <li>
                <label><input
                    type="radio"
                    name="answers[<?=$questionid?>]"
                    value="<?=$variantid?>"
                    <?=(isset($this->userAnswers[$questionid]) && $this->userAnswers[$questionid] == $variantid ? 'checked="checked"' : '')?>><?=$variant?></label>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>

        <input type="submit" value="Проголосовать">
    </form>
</body>
</html>