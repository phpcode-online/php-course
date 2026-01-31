<?php

declare(strict_types=1);

require_once 'questions.php';

$fullPath = __DIR__;
$cookieid = isset($_COOKIE['quize']) ? $_COOKIE['quize'] : '';

// нельзя доверять всему что пришло из интернета, поэтому
// удалим из куки, который прислал браузер все лишние символы
// (в данном случае не важно, но в некоторых случаях нужно
//  делать проверку на лишние символы и если они есть - останавливать работу программы)
$cookieid = preg_replace('/[^0-9A-Z\.quize]+/u', '', trim($cookieid));

// массив для результатов пользователя
$userAnswers = array(
    // questionid => answerid
);

// массив для обобщенных результатов
$total = array(
    // $questionid => array(vote => N, arVariants=> array(variantid1 => M1, variantid2 => M3, ...) )
);

// подключаемся к БД
// @var pdo PDO
$pdo = $pdo ?? require_once "pdo.inc.php";

// если кука есть проверим, голосовал ли человек
if ($cookieid > '') {

    // запросим все данные по ответам пользователя
    $sql = "SELECT * FROM q_useranswers WHERE userid = :cookieid";
    $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    $result = $sth->execute(['cookieid' => $cookieid]);

    if ($result) {
        // пройдемся по всем полученным данным и заполним массив с ответами пользователя
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $questionId = $row['questionid'];
            $answerId = $row['variantid'];

            // если вопроса с указанным кодом нет, то пропустим
            if (
                empty($questions[$questionId])
                || empty($questions[$questionId]['variants'][$answerId])
            ) {
                continue;
            }

            $userAnswers[$questionId] = $answerId;
        }
    }
}

// если кеша нет, то создадим его
if (! file_exists($fullPath . DIRECTORY_SEPARATOR . 'vote.cache')) {

    $userAnswerId = 0;
    // так как ответов может быть много, то начнем запрашивать их порциями, по 500 штук.
    // Как только вернется меньше 500 записей, значит мы получили последнюю запись
    // и можно выходить из цикла. Чтобы не было повторных записей, мы отсортируем все записи по id
    // и запрашивать будет только те, которые больше последнего полученного id
    do {
        $sql = "SELECT * FROM q_useranswers WHERE id > :userAnswerId ORDER BY id asc LIMIT 500";
        $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $result = $sth->execute(['useranswerid' => $userAnswerId]);

        $cntRows = 0;
        if ($result) {

            // пройдемся по всем полученным данным и заполним массив с ответами пользователя
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $cntRows ++;
                $userAnswerId = $row['id'];
                $questionId = $row['questionid'];
                $answerId = $row['variantid'];

                // если вопроса с указанным кодом нет, то пропустим
                if (
                    empty($questions[$questionId])
                    || empty($questions[$questionId]['variants'][$answerId])
                ) {
                    continue;
                }

                if (empty($total[$questionId])) {
                    $total[$questionId] = [
                        'votes' => 0,
                        'question' => $questions[$questionId]['question'],
                        'answers' => []
                    ];
                }

                if (empty($total[$questionId]['answers'][$answerId])) {
                    $total[$questionId]['answers'][$answerId] = [
                        'answer' => $questions[$questionId]['variants'][$answerId],
                        'votes' => 0
                    ];
                }

                $total[$questionId]['answers'][$answerId]['votes']++;
                $total[$questionId]['votes']++;
            }
        }
    } while ($cntRows >= 500);

    // сохраним результат в специальный файл - кеш
    file_put_contents($fullPath . DIRECTORY_SEPARATOR . 'vote.cache', serialize($total));
} else {
    $total = unserialize(file_get_contents($fullPath . DIRECTORY_SEPARATOR . 'vote.cache'));
}

require_once 'results.tpl.php';
