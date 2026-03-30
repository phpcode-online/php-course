<!DOCTYPE html>
<html>
<head>
    <meta content='text/html; charset=UTF-8' http-equiv='Content-Type'/>
    <title>Урок 30 - оптимизации</title>
</head>
<body>
    <h1>Урок 30 - оптимизации</h1>
    <form action="/process/" method="POST">
        <?php foreach ($this->quiz->getQuestions() as $questionId => $question) { ?>
        <p><?=$question->question?></p>
        <ul type="none">
            <?php foreach ($question->variants as $variantId => $variant) { ?>
            <li>
                <label><input
                    type="radio"
                    name="answers[<?=$questionId?>]"
                    value="<?=$variantId?>"
                    <?=(isset($this->userAnswers[$questionId]) && $this->userAnswers[$questionId]->variantId == $variantId ? 'checked="checked"' : '')?>><?=$variant->variant?></label>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>

        <input type="submit" value="Проголосовать">
    </form>
</body>
</html>