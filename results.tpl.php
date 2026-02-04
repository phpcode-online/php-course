<!DOCTYPE html>
<html>
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Урок 21 - загрузка в БД</title>
</head>
<body>

    <?php if (! empty($userAnswers) && is_array($userAnswers)) { ?>
        <p>Вы проголосовали:</p>
        <ul type="none">
        <?php foreach ($userAnswers as $questionid => $variantid) { ?>
            <li><?=$questions[$questionid]['question']?> <b><?=$questions[$questionid]['variants'][$variantid]?></b></li>
        <?php } ?>
        </ul>
    <?php } else { ?>
        <?php echo "Ваши ответы не найдены\n"; ?>
    <?php } ?>
    <br><br>
    <?php if (! empty($total)) { ?>
        <h3>Результаты голосования</h3>
        <?php foreach ($total as $question) { ?>
            <h4><?=$question['question']?></h4>
            <ul>
                <?php foreach ($question['answers'] as $answer) { ?>
                    <li><?=$answer['answer']?> - <?=$answer['votes']?> <span style="color: #999999;">(<?= intval($answer['votes'] * 100.0 / $question['votes']) ?>%)</span></li>
                <?php } ?>
            </ul>
        <?php } ?>
    <?php } else { ?>
        <p>Еще никто не голосовал. Вы можете стать первым.</p>
    <?php } ?>
    <p>
        <a href="index.php">Вернуться к опросу</a>
    </p>
</body>
</html>