<?php

declare(strict_types=1);

// подключаемся к БД
// @var pdo PDO
$pdo = $pdo ?? require_once $appRoot . "pdo.inc.php";

$questions = require_once $appRoot . 'questions.php';

$fullPath = dirname($appRoot) . DIRECTORY_SEPARATOR . 'cache';
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
if (!file_exists($fullPath . DIRECTORY_SEPARATOR . 'vote.cache')) {
    // переложим подсчет результатов на плечи БД
    $sql = "SELECT questionid, variantid, count(id) as vote FROM q_useranswers GROUP BY questionid, variantid;";
    $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    $result = $sth->execute();

    if ($result) {
        // пройдемся по всем полученным данным и заполним массив с ответами пользователя
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $questionId = $row['questionid'];
            $answerId = $row['variantid'];
            $vote = $row['vote'];

            // если вопроса с указанным кодом нет, то пропустим
            if (
                empty($questions[$questionId])
                || empty($questions[$questionId]['variants'][$answerId])
            ) {
                continue;
            }

            // если мы еще не заполняли количество ответов для этого вопроса,
            // то подготовим массиы
            if (empty($total[$questionId])) {
                $total[$questionId] = [
                    'votes' => 0,
                    'question' => $questions[$questionId]['question'],
                    'answers' => []
                ];
            }

            // из БД мы берем уже подсчитанное число голосов, поэтому сразу
            // его запишем в ответы и прибавим его же к общему числу
            $total[$questionId]['answers'][$answerId] = array(
                'answer' => $questions[$questionId]['variants'][$answerId],
                'votes' => $vote
            );

            $total[$questionId]['votes'] += $vote;
        }
    }

    // сохраним результат в специальный файл - кеш
    file_put_contents($fullPath . DIRECTORY_SEPARATOR . 'vote.cache', serialize($total));
} else {
    $total = unserialize(file_get_contents($fullPath . DIRECTORY_SEPARATOR . 'vote.cache'));
}

require_once $appRoot . 'views/results.tpl.php';
