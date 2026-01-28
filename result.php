<?php
declare(strict_types=1);

// если куки нет, то перебросим на голосование
if (empty($_COOKIE['quize'])) {
    header('Location: index.php');
    exit;
}

require_once 'questions.php';

// если файл с результатами существует, то обработаем его
$total = [
    // $questionid => [vote => N, variants=> [variantid1 => M1, variantid2 => M3, ...] ]
];

$cookie = $_COOKIE['quize'];
// найдем в результатах данные голосования пользователя
$userAnswers = [
    // questionid => answerid
];

if (file_exists('vote.txt')) {
    // считали весь файл (если файл большой, то может не влезть в память)
    $votes = file_get_contents('vote.txt');
    // файл это строка - разобьем ее на части используя перенос строки "\n" в качестве разделителя
    $votes = explode("\n", $votes);

    // пробежимся по получившемуся маcсиву
    foreach ($votes as $str) {
        // каждая строчка представляет собой пару, вопрос;ответ
        $pair = explode(';', $str);

        // если строчка не соответствует нашим критериям - то проигнорируем ее
        if (!isset($pair[0]) || !isset($pair[1]) || !isset($pair[2])) {
            continue;
        }
        $questionid = intval($pair[0]);
        $answerid = $pair[1];

        // если вопроса с указанным кодом нет, то пропустим
        if (empty($questions[$questionid])) {
            continue;
        }
        if (empty($questions[$questionid]['variants'][$answerid])) {
            continue;
        }

        // если кука из браузера совпадает с тем что записано в файле
        if ($cookie == $pair[2]) {
            $userAnswers[$questionid] = $answerid;
        }

        if (empty($total[$questionid])) {
            $total[$questionid] = [
                'votes' => 0,
                'question' => $questions[$questionid]['question'],
                'answers' => []
            ];
        }

        if (empty($total[$questionid]['answers'][$answerid])) {
            $total[$questionid]['answers'][$answerid] = [
                'answer' => $questions[$questionid]['variants'][$answerid],
                'votes' => 0
            ];
        }

        $total[$questionid]['answers'][$answerid]['votes']++;
        $total[$questionid]['votes']++;
    }
}

if (empty($userAnswers)) {
    // удалим куку, так как голосов нет
    setcookie("quize", '', time() - (3600 * 24 * 30), "/", "", false, true);
}

require_once "results.tpl.php";
