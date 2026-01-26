<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
        <title><?php if ($cntTry > 0) { ?>Попытка #<?php echo $cntTry + 1; } ?>Игра в кости.</title>
    </head>
    <body>
        <h1>Игра в кости</h1>
        <?php if ($userDice < 1) { ?>
        <p>В форме ниже выберите число, которое по Вашему мнению выпадет на кубике и нажмите кнопку "Старт"</p>
        <?php } else { ?>
            <?php if ($playerWin) { ?>
            <h1>Вы выиграли! Ура! </h1>
            <?php } else { ?>
            <h2>Не переживайте, в другой раз получится.</h2>
            <?php } ?>
        <?php } ?>

        <?php if (sizeof($tries) < 5) { ?>
        <form method="POST">
            <input type="hidden" name="try" value="<?=base64_encode(json_encode($tries)); ?>">
            <select name="dice">
                <option value="0">Выберите номер</option>
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
            </select>
            <input type="submit" value="<?= ($cntTry > 0 ? 'Еще раз' : 'Старт') ?>">
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
                    <td><?= $item['player'] ?></td>
                    <td><?= $item['casino'] ?></td>
                    <td><?= ($item['win'] ? 'Да' : 'Нет') ?></td>
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