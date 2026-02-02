<?php

/**
 * обработка ответа от пользователя
 * все результаты храним в отдельных файлах в специальном каталоге.
 * если каталога нет, то создадим его.
 */

declare(strict_types=1);

// если ответа нет, то перенаправим пользователя на страницу с формой
if (empty($_POST['answers'])) {
    header('Location: index.php');
    die(0);
}

// подключаемся к БД
// @var pdo PDO
$pdo = require_once "pdo.inc.php";

// подключим файл с массивом вопросов
require_once 'questions.php';

$fullPath = __DIR__ . DIRECTORY_SEPARATOR . 'vote';

// если куки нет, то создадим ее
if (empty($_COOKIE['quize'])) {
    $abc = [
        '1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
        'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'O','I', 'P',
        'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z',
        'X', 'C', 'V', 'B', 'N', 'M'
    ];
    // перемешаем элементы массива
    shuffle($abc);
    // объединим все элементы массива
    $abc = implode('', $abc);
    // возьмем 20 первых символов
    $abc = substr($abc, 0, 20);
    // добавим получившуюся строку к текущему времени с миллисекундами
    $cookieid = 'quize' . microtime(true) . $abc;

    // отправим куки браузеру (куки будет хранится в браузере 30 дней)
    setcookie("quize", $cookieid, time() + (3600 * 24 * 30), "/", "", false, true);

    $_COOKIE['quize'] = $cookieid;
} else {
    $cookieid = preg_replace('/[^0-9A-Z.]/iu', '', $_COOKIE['quize']);

    // если кука есть, то проверим ее, а вдруг нам что-то странное передали
    // если в куке есть что-то кроме букв, цифр и запятой,
    // то значит нам что-то подсунули и мы можем завершить работу
    if (preg_match('/[^0-9A-Zquize\.]+/u', $cookieid)) {
        // можно конечно еще очистить куку,
        // но если человек ее намеренно портит, то зачем ему облегчать жизнь?
        die('Error!');
    }
}

try {
    // если есть ответы от пользователя,
    // то запишем их в БД
    if (! empty($_POST['answers']) && is_array($_POST['answers'])) {
        // проверим, вдруг уже пользователь голосовал и тогда нам нужно изменить его предыдущее голосование.
        $sql = "SELECT id, questionid, variantid FROM q_useranswers WHERE userid = :cookieid LIMIT 100";
        $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $result = $sth->execute(['cookieid' => $cookieid]);
        if ($result) {
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                // если пользователь уже отвечал на вопрос, то удалим его ответ из массива
                if (isset($_POST['answers'][$row['questionid']])) {
                    // если пользователь изменил ответ, то обновим его и в БД
                    if ($row['variantid'] != $_POST['answers'][$row['questionid']]) {
                        $sql = "UPDATE q_useranswers SET variantid=:variantid WHERE id=:answerid AND userid=:cookieid";
                        $sth = $pdo->prepare($sql);
                        $result = $sth->execute([
                            'cookieid' => $cookieid,
                            'answerid' => $row['id'],
                            'variantid' => $_POST['answers'][$row['questionid']]
                        ]);
                    }
                    unset($_POST['answers'][$row['questionid']]);
                }
            }
        }

        foreach ($_POST['answers'] as $questionId => $variantId) {
            // все данные, которые приходят через интернет, нужно проверять на валидность
            // поэтому переведем номера вопросов и вариантов ответов в целое число
            $questionId = intval($questionId);
            $variantId = intval($variantId);

            // если пользователь не голосовал, нам нужен запрос на добавление данных
            $sql = "INSERT INTO q_useranswers (userid, questionid, variantid) VALUES (:cookieid,:questionid,:variantid)";
            $sth = $pdo->prepare($sql);
            $result = $sth->execute([
                'cookieid' => $cookieid,
                'questionid' => $questionId,
                'variantid' => $variantId
            ]);
            $answerId = $pdo->lastInsertId();
        }
    }

    if (file_exists($fullPath . DIRECTORY_SEPARATOR . 'vote.cache')) {
        // удалим кеш, чтобы при следующем просмотре результатов
        // данные пересчитались заново
        unlink($fullPath . DIRECTORY_SEPARATOR . 'vote.cache');
    }

    header('Location: result.php');
} catch (PDOException $e) {
    // сюда попадем если будет какая-то ошибка при работе с БД
    die('Шеф, с базой непонятки: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
} catch (Exception $e) {
    // сюда попадем если будет какая-то не предвиденная ошибка (исключение)
    die('Шеф, все пропало: ' . $e->getMessage() . '. File: ' . $e->getFile() . ' Line: ' . $e->getLine());
}
