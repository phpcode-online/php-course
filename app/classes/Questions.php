<?php

class Questions
{
    private $id;
    private $quizeId;
    private $question;
    private $variants = [];
    private $pdo;

    public function __construct($params = [], PDO $pdo = null)
    {
        if (isset($params['id'])) {
            $this->id = $params['id'];
        }
        if (isset($params['quizeId'])) {
            $this->quizeId = $params['quizeId'];
        }
        if (isset($params['question'])) {
            $this->question = $params['question'];
        }
        if (isset($params['variants'])) {
            $this->variants = $params['variants'];
        }

        if ($pdo !== null) {
            $this->pdo = $pdo;
        }
    }

    public function addVariant($variantId, $variantText)
    {
        $this->variants[$variantId] = $variantText;
    }

    // методы для получения данных
    public function getId()
    {
        return $this->id;
    }

    public function getQuizId()
    {
        return $this->quizId;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getVariants()
    {
        return $this->variants;
    }

    // преобразование в массив для совместимости со старыми шаблонами
    public function toArray()
    {
        return [
            'question' => $this->question,
            'variants' => $this->variants
        ];
    }
}
