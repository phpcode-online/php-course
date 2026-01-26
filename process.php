<?php
declare(strict_types=1);

/**
 * обработка ответа от пользователя
 */

// если ответа нет, то перенаправим пользователя на страницу с формой
if (empty($_POST['answers'])) {
    header('Location: index.php');
    die(0);
}

// подключим файл с массивом вопросов
require_once 'questions.php';

// если есть ответы от пользователя,
// то запишем их в файл, в формате: код вопроса;код ответа
if (! empty($_POST['answers']) && is_array($_POST['answers'])) {
    $f = fopen('vote.txt', 'a');
    foreach ($_POST['answers'] as $questionid => $variantid) {
        fwrite($f, $questionid . ';' . $variantid . "\n");
    }
    fclose($f);
}

// если файл с результатами существует, то обработаем его
$total = array(
    // $questionid => array(vote => N, variants=> array(variantid1 => M1, variantid2 => M3, ...) )
);
if (file_exists('vote.txt')) {
    // считали весь файл (если файл большой, то может не влезть в память)
    $votes = file_get_contents('vote.txt');
    // файл это строка - разобьем ее на части используя перенос строки "\n" в качестве разделителя
    $votes = explode("\n", $votes);

    // пробежимся по получившемуся маcсиву
    foreach ($votes as $string) {
        // каждая строчка представляет собой пару, вопрос;ответ
        $pair = explode(';', $string);

        // если строчка не соответствует нашим критериям - то проигнорируем ее
        if (!isset($pair[0]) || !isset($pair[1])) {
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

        if (empty($total[$questionid])) {
            $total[$questionid] = array(
                'votes' => 0,
                'question' => $questions[$questionid]['question'],
                'answers' => array()
            );
        }

        if (empty($total[$questionid]['answers'][$answerid])) {
            $total[$questionid]['answers'][$answerid] = array(
                'answer' => $questions[$questionid]['variants'][$answerid],
                'votes' => 0
            );
        }

        $total[$questionid]['answers'][$answerid]['votes']++;
        $total[$questionid]['votes']++;
    }
}

require_once 'results.tpl.php';
