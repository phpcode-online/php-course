<?php
declare(strict_types=1);

// переменная в которой хранится булевый флаг - выиграл пользователь или нет.
// По умолчанию поставим этот флаг в значение FALSE - то есть не выиграл.
$playerWin = false;

// массив предыдущих бросков
$tries = [];

// счетчик количества побед
$cntWin = 0;

// если передан номер от пользователя, то запустим генератор случайных чисел
if (! empty($_POST['dice'])) {
    $rand = rand(1, 6); // занесем случайное число от 1 до 6 в переменную $rand

    // если переданное значение пользователем совпадает со случайным числом, то изменим флаг выигрыша на TRUE
    if ($_POST['dice'] == $rand) {
        $playerWin = true;
    }

    // если были предыдущие попытки, то занесем их в переменную $tries
    if (! empty($_POST['try'])) {
        // Данные о предыдущих попытках нам передаются в хитром виде:
        // сначала массив превращается в JSON строку, а потом эта строка кодируется в формат base64.
        // Для того чтобы достать массив с предыдущими попытками, нам нужно сделать все наоборот
        $tries = json_decode(base64_decode($_POST['try']), true);
    }

    // добавим текущий бросок $rand в конец массива $tries
    $tries[] = array('casino' => $rand, 'player' => intval($_POST['dice']), 'win' => $playerWin);
}

foreach ($tries as $item) {
    if ($item['win']) {
        $cntWin++;
    }
}

// количество попыток
$cntTry = sizeof($tries);

// приступим к формированию HTML странички с формой и другими элементами
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
        <title><?php if ($cntTry > 0) { ?>Попытка #<?php echo $cntTry + 1; } ?>Игра в кости.</title>
    </head>
    <body>
        <h1>Игра в кости</h1>
        <?php if (empty($_POST['dice'])) { ?>
        <p>В форме ниже выберите число, которое по Вашему мнению выпадет на кубике и нажмите кнопку "Старт"</p>
        <?php } else { ?>
            <?php if ($playerWin) {?>
            <h1>Вы выиграли! Ура! </h1>
            <?php } else { ?>
            <h2>Не переживайте, в другой раз получится.</h2>
            <?php } ?>
        <?php } ?>

        <?php if (sizeof($tries) < 5) { ?>
        <form method="POST">
            <input type="hidden" name="try" value="<?php echo base64_encode(json_encode($tries)); ?>">
            <select name="dice">
                <option value="0">Выберите номер</option>
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
            </select>
            <input type="submit" value="<?php if ($cntTry > 0) { ?>Еще раз<?php } else { ?>Старт<?php } ?>">
        </form>
        <?php } ?>

        <?php if (sizeof($tries) > 0) { ?>
            <h3>Предыдущие попытки</h3>
            <table cellpadding="5" border="1">
                <tr>
                    <th>Вы</th>
                    <th>Казино</th>
                    <th>Выиграли?</th>
                </tr>
            <?php foreach ($tries as $item) { ?>
                <tr>
                    <td><?php echo $item['player']?></td>
                    <td><?php echo $item['casino']?></td>
                    <td><?php echo $item['win'] ? 'Да' : 'Нет'?></td>
                </tr>
            <?php } ?>
            </table>

            <?php if ($cntTry - $cntWin > $cntWin) { ?>
            <p>Казино чаще выигрывает</p>
            <?php } else { ?>
            <p>Вам везет :)</p>
            <?php } ?>
        <?php } ?>
    </body>
</html>