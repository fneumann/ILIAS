<?php

/**
 * Value pair that can be stored in the database for a part of a question solution
 */
class ilQuestionSolutionValuePair
{
    private ?string $value1;
    private ?string $value2;

    public function __construct(?string $value1, ?string $value2)
    {
        $this->value1 = $value1;
        $this->value2 = $value2;
    }

    /**
     * @return string|null
     */
    public function getValue1() : ?string
    {
        return $this->value1;
    }

    /**
     * @return string|null
     */
    public function getValue2() : ?string
    {
        return $this->value2;
    }

}