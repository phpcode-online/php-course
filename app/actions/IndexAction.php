<?php
declare(strict_types=1);

class IndexAction extends BaseAction
{
    public $userAnswers = [];
    public $questions = [];

    public function run()
    {
        $this->userAnswers = [];
        // если мы ранее голосовали, то считаем из файла результаты голосования.
        if ($this->cookieId > '') {
            $this->userAnswers = UserAnswers::getUserAnswers($this->cookieId, $this->quiz, $this->pdo);
        }

        require_once $this->appPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'quize.tpl.php';
    }
}
