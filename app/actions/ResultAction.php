<?php

declare(strict_types=1);

class ResultAction extends BaseAction
{
    public $fullPath;
    public $questions = [];
    public $userAnswers = [];
    public $total = [];

    public function run()
    {
        $this->fullPath = dirname($this->appPath) . DIRECTORY_SEPARATOR . 'cache';

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
            $this->userAnswers = UserAnswers::getUserAnswers($this->cookieId, $this->quiz, $this->pdo);
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
                    $question = $this->quiz->getQuestion($questionId);
                    $answerId = $row['variantId'];
                    $vote = $row['vote'];

                    // если вопроса с указанным кодом нет, то пропустим
                    if (
                        ! $question
                        || empty($question->variants[$answerId])
                    ) {
                        continue;
                    }

                    // если мы еще не заполняли количество ответов для этого вопроса,
                    // то подготовим массиы
                    if (empty($this->total[$questionId])) {
                        $this->total[$questionId] = [
                            'votes' => 0,
                            'question' => $question->question,
                            'answers' => []
                        ];
                    }

                    // из БД мы берем уже подсчитанное число голосов, поэтому сразу
                    // его запишем в ответы и прибавим его же к общему числу
                    $this->total[$questionId]['answers'][$answerId] = [
                        'answer' => $question->variants[$answerId],
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

        require_once $this->appPath . '/views/results.tpl.php';
    }
}
