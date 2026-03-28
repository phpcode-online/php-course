<?php
declare(strict_types=1);

class IndexAction
{
    public $fullPath;
    public $userAnswers = [];
    public $questions = [];
    public $quiz;
    public $cookieId;

    public function __construct()
    {
    }

    public function run()
    {
        $pdo = getPDO();

        $this->fullPath = __DIR__;
        // подключаем список вопросов
        $this->quiz = new Quiz(['id' => 1], $pdo);
        $this->questions = $this->quiz->getQuestionsAsArray();

        $this->userAnswers = [];
        // если мы ранее голосовали, то считаем из файла результаты голосования.
        if (! empty($_COOKIE['quize'])) {
            $this->cookieId = $_COOKIE['quize'];
            // если есть какие-то символы кроме цифр, больших букв латинского алфавита
            // знакак точка и маленьких букв "q", "u", "i", "z", "e" то это ошибка
            if (preg_match('/[^0-9A-Z\.quize]+/u', $this->cookieId)) {
                // можно конечно еще очистить куку,
                // но если человек ее намеренно портит, то зачем ему облегчать жизнь?
                die('Голосование завершено');
            }

            $this->userAnswers = UserAnswers::getUserAnswers($this->cookieId, $this->questions, $pdo);
        }

        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'quize.tpl.php';
    }
}

