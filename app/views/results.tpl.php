<!DOCTYPE html>
<html>
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Урок 30 - оптимизации</title>
</head>
<body>

    <?php if (! empty($this->userAnswers) && is_array($this->userAnswers)) { ?>
        <p>Вы проголосовали:</p>
        <ul type="none">
        <?php foreach ($this->userAnswers as $questionId => $userAnswer) { ?>
            <li><?=$this->quiz->getQuestion($questionId)->question?>
            <b><?=$this->quiz->getQuestion($questionId)->getVariantName($userAnswer->variantId)?></b></li>
        <?php } ?>
        </ul>
    <?php } else { ?>
        <?php echo "Ваши ответы не найдены\n"; ?>
    <?php } ?>
    <br><br>
    <?php if (! empty($this->total)) { ?>
        <h3>Результаты голосования</h3>
        <?php foreach ($this->total as $question) { ?>
            <h4><?=$question['question']?></h4>
            <ul>
                <?php foreach ($question['answers'] as $answer) { ?>
                    <li><?=$answer['answer']->variant?> - <?=$answer['votes']?> <span style="color: #999999;">(<?= intval($answer['votes'] * 100.0 / $question['votes']) ?>%)</span></li>
                <?php } ?>
            </ul>
        <?php } ?>
    <?php } else { ?>
        <p>Еще никто не голосовал. Вы можете стать первым.</p>
    <?php } ?>
    <p>
        <a href="/">Вернуться к опросу</a>
    </p>
</body>
</html>