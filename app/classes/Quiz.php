<?php

class Quiz
{
    private $id;
    private $questions = [];
    private $pdo;

    public function __construct($params, PDO $pdo = null)
    {
        if (isset($params['id'])) {
            $this->id = $params['id'];
        }
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
            $sth->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Questions', [[], $this->pdo]);

            while ($question = $sth->fetch()) {
                $this->questions[$question->getId()] = $question;
            }


            // загружаем варианты ответов
            $sql = "SELECT * FROM q_variants WHERE quizeId = :quizeId";
            $sth = $this->pdo->prepare($sql);
            $sth->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Variants', [[], $this->pdo]);
            $sth->execute(['quizeId' => $this->id]);

            while ($row = $sth->fetch()) {
                if (isset($this->questions[$row->getQuestionId()])) {
                    $this->questions[$row->getQuestionId()]->addVariant(
                        $row->getId(),
                        $row->getVariant()
                    );
                }
            }
        } catch (PDOException $e) {
            throw new Exception('Ошибка загрузки вопросов: ' . $e->getMessage());
        }
    }

    public function getQuestions()
    {
        return $this->questions;
    }

    public function getQuestion($id)
    {
        return $this->questions[$id] ?? null;
    }

    // для совместимости со старыми шаблонами возвращаем массив
    public function getQuestionsAsArray()
    {
        $result = [];
        foreach ($this->questions as $id => $question) {
            $result[$id] = $question->toArray();
        }
        return $result;
    }
}
