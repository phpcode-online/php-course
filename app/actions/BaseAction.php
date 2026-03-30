<?php
declare(strict_types=1);

class BaseAction
{
    public $appPath;
    public $cookieId;
    public $quiz;

    public $pdo;

    public function __construct()
    {
        $this->appPath = dirname(__DIR__);

        if (! empty($_COOKIE['quize'])) {
            $this->cookieId = $_COOKIE['quize'];
            // если есть какие-то символы кроме цифр, больших букв латинского алфавита
            // знакак точка и маленьких букв "q", "u", "i", "z", "e" то это ошибка
            if (preg_match('/[^0-9A-Z\.quize]+/u', $this->cookieId)) {
                // можно конечно еще очистить куку,
                // но если человек ее намеренно портит, то зачем ему облегчать жизнь?
                die('Голосование завершено');
            }
        }

        $this->pdo = getPDO();

        // подключаем список вопросов
        $this->quiz = new Quiz($this->pdo);
        $this->quiz->id = 1;
    }
}
