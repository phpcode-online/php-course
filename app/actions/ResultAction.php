<?php

declare(strict_types=1);

class ResultAction
{
    public $pdo;
    public $fullPath;
    public $questions = [];
    public $quiz;
    public $cookieId;
    public $userAnswers = [];
    public $total = [];

    public function run()
    {

        // подключаемся к БД
        // @var pdo PDO
        $this->pdo = getPdo();

        $this->quiz = new Quiz(['id' => 1], $this->pdo);
        $this->questions = $this->quiz->getQuestionsAsArray();

        $this->fullPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'cache';
        $this->cookieId = isset($_COOKIE['quize']) ? $_COOKIE['quize'] : '';

        // нельзя доверять всему что пришло из интернета, поэтому
        // удалим из куки, который прислал браузер все лишние символы
        // (в данном случае не важно, но в некоторых случаях нужно
        //  делать проверку на лишние символы и если они есть - останавливать работу программы)
        $this->cookieId = preg_replace('/[^0-9A-Z\.quize]+/u', '', trim($this->cookieId));

        // массив для результатов пользователя
        $this->userAnswers = [
            // questionid => answerid
        ];

        // массив для обобщенных результатов
        $this->total = [
            // $questionId => ['votes' => N, 'answers'=> ['variantId1' => M1, 'variantId2' => M3, ...) ]
        ];

        // если кука есть проверим, голосовал ли человек
        if ($this->cookieId > '') {
            // запросим все данные по ответам пользователя
            $this->userAnswers = UserAnswers::getUserAnswers($this->cookieId, $this->questions, $this->pdo);
        }

        // если кеша нет, то создадим его
        if (!file_exists($this->fullPath . DIRECTORY_SEPARATOR . 'vote.cache')) {
            // переложим подсчет результатов на плечи БД
            $sql = "SELECT questionId, variantId, count(id) as vote FROM q_useranswers GROUP BY questionId, variantId;";
            $sth = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
            $result = $sth->execute();

            if ($result) {
                // пройдемся по всем полученным данным и заполним массив с ответами пользователя
                while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $questionId = $row['questionId'];
                    $answerId = $row['variantId'];
                    $vote = $row['vote'];

                    // если вопроса с указанным кодом нет, то пропустим
                    if (
                        empty($this->questions[$questionId])
                        || empty($this->questions[$questionId]['variants'][$answerId])
                    ) {
                        continue;
                    }

                    // если мы еще не заполняли количество ответов для этого вопроса,
                    // то подготовим массиы
                    if (empty($this->total[$questionId])) {
                        $this->total[$questionId] = [
                            'votes' => 0,
                            'question' => $this->questions[$questionId]['question'],
                            'answers' => []
                        ];
                    }

                    // из БД мы берем уже подсчитанное число голосов, поэтому сразу
                    // его запишем в ответы и прибавим его же к общему числу
                    $this->total[$questionId]['answers'][$answerId] = [
                        'answer' => $this->questions[$questionId]['variants'][$answerId],
                        'votes' => $vote
                    ];

                    $this->total[$questionId]['votes'] += $vote;
                }
            }

            // сохраним результат в специальный файл - кеш
            file_put_contents($this->fullPath . DIRECTORY_SEPARATOR . 'vote.cache', serialize($this->total));
        } else {
            $this->total = unserialize(file_get_contents($this->fullPath . DIRECTORY_SEPARATOR . 'vote.cache'));
        }

        require_once dirname(__DIR__) . '/views/results.tpl.php';
    }
}
