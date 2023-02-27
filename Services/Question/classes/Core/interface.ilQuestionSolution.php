<?php

/**
 * Question type specific settings of a question
 * The structure is not predefined but it mut be able to convert it between JSON and pairs of string values
 */
interface ilQuestionSolution
{
    public function toJSON(): string;

    public static function fromJSON(string $json): ilQuestionSolution;

    /**
     * @return ilQuestionSolutionValuePair[]
     */
    public function toValuePairs(): array;

    /**
     * @param ilQuestionSolutionValuePair[] $pairs
     */
    public static function fromValuePairs(array $pairs) : ilQuestionSolution;


    public function isEmpty() : bool;
}