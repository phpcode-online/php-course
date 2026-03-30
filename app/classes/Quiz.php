<?php

class Quiz
{
    /** @var int */
    public $id;
    /** @var Questions[] */
    public $questions;
    /** @var PDO */
    public $pdo;

    public function __construct(PDO $pdo = null)
    {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        }

        $this->loadQuestions();
    }

    private function loadQuestions()
    {
        try {
            // загружаем вопросы
            $sql = "SELECT * FROM q_questions WHERE quizeId = :quizeId ORDER BY id";
            $sth = $this->pdo->prepare($sql);
            $sth->execute(['quizeId' => $this->id]);
            $sth->setFetchMode(PDO::FETCH_CLASS, 'Questions', [$this->pdo]);

            while ($question = $sth->fetch()) {
                $this->questions[$question->id] = $question;
            }

            // загружаем варианты ответов
            $sql = "SELECT * FROM q_variants WHERE quizeId = :quizeId";
            $sth = $this->pdo->prepare($sql);
            $sth->setFetchMode(PDO::FETCH_CLASS, 'Variants', [$this->pdo]);
            $sth->execute(['quizeId' => $this->id]);

            while ($variant = $sth->fetch()) {
                if (isset($this->questions[$variant->questionId])) {
                    $this->questions[$variant->questionId]->addVariant($variant);
                }
            }
        } catch (PDOException $e) {
            throw new Exception('Ошибка загрузки вопросов: ' . $e->getMessage());
        }
    }

    public function getQuestions()
    {
        if ($this->questions === null) {
            $this->loadQuestions();
        }
        return $this->questions;
    }

    public function getQuestion($id)
    {
        if ($this->questions === null) {
            $this->loadQuestions();
        }
        return $this->questions[$id] ?? null;
    }

}
