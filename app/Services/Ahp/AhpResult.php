<?php

namespace App\Services\Ahp;

class AhpResult
{
    public array $normalized;
    public array $weights;
    public float $lambdaMax;
    public float $CI;
    public float $CR;
    public bool $consistent;

    public function __construct(array $normalized, array $weights, float $lambdaMax, float $CI, float $CR, bool $consistent)
    {
        $this->normalized = $normalized;
        $this->weights = $weights;
        $this->lambdaMax = $lambdaMax;
        $this->CI = $CI;
        $this->CR = $CR;
        $this->consistent = $consistent;
    }
}
