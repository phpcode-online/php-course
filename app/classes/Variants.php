<?php

class Variants
{
    private $id;
    private $quizeId;
    private $questionId;
    private $variant;
    private $pdo;

    public function __construct($params = [], PDO $pdo = null)
    {
        if (isset($params['id'])) {
            $this->id = $params['id'];
        }
        if (isset($params['quizeId'])) {
            $this->quizeId = $params['quizeId'];
        }
        if (isset($params['questionId'])) {
            $this->questionId = $params['questionId'];
        }
        if (isset($params['variant'])) {
            $this->variant = $params['variant'];
        }

        if ($pdo !== null) {
            $this->pdo = $pdo;
        }
    }

    // методы для получения данных
    public function getId()
    {
        return $this->id;
    }

    public function getQuizeId()
    {
        return $this->quizeId;
    }

    public function getQuestionId()
    {
        return $this->questionId;
    }

    public function getVariant()
    {
        return $this->variant;
    }
}
