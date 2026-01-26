<!DOCTYPE html>
<html>
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Урок 9 - Суммируем результаты.</title>
</head>
<body>

    <?php if (! empty($_POST['answers']) && is_array($_POST['answers'])) { ?>
        <p>Вы проголосовали:</p>
        <ul type="none">
        <?php foreach ($_POST['answers'] as $questionid => $variantid) { ?>
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
                    <li><?=$answer['answer']?> - <?=$answer['votes']?> <span style="color: #999999;">(<?=empty($question['votes']) ? 0 : intval($answer['votes']*100.0 / $question['votes'])?>%)</span></li>
                <?php } ?>
            </ul>
        <?php } ?>
    <?php } ?>
    <p>
    <a href="index.php">Вернуться к голосованию</a>
    </p>
</body>
</html>