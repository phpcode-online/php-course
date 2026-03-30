<?php

class Questions
{
    /** @var int */
    public $id;
    /** @var int */
    public $quizeId;
    /** @var string */
    public $question;
    /** @var Variants[] */
    public $variants = [];

    public $pdo;

    public function __construct(PDO $pdo = null)
    {
        if ($pdo !== null) {
            $this->pdo = $pdo;
        }
    }

    public function addVariant(Variants $variant)
    {
        $this->variants[$variant->id] = $variant;
    }

    public function getVariantName($variantId)
    {
        return $this->variants[$variantId]->variant ?? '';
    }
}
