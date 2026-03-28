<?php

declare(strict_types=1);

// подключаемся к БД
// @var pdo PDO
$pdo = $pdo ?? require_once $appRoot . "pdo.inc.php";

$quiz = new Quiz(['id' => 1], $pdo);
$questions = $quiz->getQuestionsAsArray();

$fullPath = dirname($appRoot) . DIRECTORY_SEPARATOR . 'cache';
$cookieid = isset($_COOKIE['quize']) ? $_COOKIE['quize'] : '';

// нельзя доверять всему что пришло из интернета, поэтому
// удалим из куки, который прислал браузер все лишние символы
// (в данном случае не важно, но в некоторых случаях нужно
//  делать проверку на лишние символы и если они есть - останавливать работу программы)
$cookieid = preg_replace('/[^0-9A-Z\.quize]+/u', '', trim($cookieid));

// массив для результатов пользователя
$userAnswers = [
    // questionid => answerid
];

// массив для обобщенных результатов
$total = [
    // $questionId => ['votes' => N, 'answers'=> ['variantId1' => M1, 'variantId2' => M3, ...) ]
];

// если кука есть проверим, голосовал ли человек
if ($cookieid > '') {
    // запросим все данные по ответам пользователя
    $sql = "SELECT * FROM q_useranswers WHERE userId = :cookieid";
    $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    $result = $sth->execute(['cookieid' => $cookieid]);

    if ($result) {
        // пройдемся по всем полученным данным и заполним массив с ответами пользователя
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $questionId = $row['questionId'];
            $answerId = $row['variantId'];

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
    $sql = "SELECT questionId, variantId, count(id) as vote FROM q_useranswers GROUP BY questionId, variantId;";
    $sth = $pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
    $result = $sth->execute();

    if ($result) {
        // пройдемся по всем полученным данным и заполним массив с ответами пользователя
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $questionId = $row['questionId'];
            $answerId = $row['variantId'];
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
            $total[$questionId]['answers'][$answerId] = [
                'answer' => $questions[$questionId]['variants'][$answerId],
                'votes' => $vote
            ];

            $total[$questionId]['votes'] += $vote;
        }
    }

    // сохраним результат в специальный файл - кеш
    file_put_contents($fullPath . DIRECTORY_SEPARATOR . 'vote.cache', serialize($total));
} else {
    $total = unserialize(file_get_contents($fullPath . DIRECTORY_SEPARATOR . 'vote.cache'));
}

require_once $appRoot . 'views/results.tpl.php';
