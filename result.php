<?php

declare(strict_types=1);

require_once 'questions.php';

$fullPath = __DIR__ . DIRECTORY_SEPARATOR . 'vote';
$cookieid = isset($_COOKIE['quize']) ? $_COOKIE['quize'] : '';
if (preg_match('/[^0-9A-Zquize\.]+/u', $cookieid)) {
    die("Error !!!");
}
// все входящие данные нужно санировать
$cookieid = preg_replace('/[^0-9A-Zquize\.]+/u', '', $cookieid);
// если файл с результатами существует, то обработаем его
$total = [
    // $questionid => [vote => N, variants=> [variantid1 => M1, variantid2 => M3, ...] ]
];

$userAnswers = [
    // questionid => answerid
];

// найдем в результатах данные голосования пользователя
// для этого нужно пробежаться по всем файлам в каталоге vote
if (file_exists($fullPath) && is_dir($fullPath)) {
    $d = dir($fullPath);
    while ($cookieFile = $d->read()) {
        if (substr($cookieFile, 0, 1) == '.') {
            continue;
        }
        $usercookieid = str_replace('.txt', '', $cookieFile);

        // считали весь файл с результатом конкретного пользователя
        $result = file_get_contents($fullPath . DIRECTORY_SEPARATOR . $cookieFile);
        // файл это строка - разобьем ее на части используя перенос строки "\n" (PHP_EOL) в качестве разделителя
        $result = unserialize($result);

        // пробежимся по получившемуся маcсиву
        foreach ($result as $questionid => $answerid) {
            $questionid = (int)$questionid;

            // если вопроса с указанным кодом нет, то пропустим
            if (empty($questions[$questionid])) {
                continue;
            }
            if (empty($questions[$questionid]['variants'][$answerid])) {
                continue;
            }

            // если кука из браузера совпадает с тем что записано в файле
            if ($cookieid == $usercookieid) {
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
        } // end foreach
    } // end while
    $d->close();
}

require_once 'results.tpl.php';
