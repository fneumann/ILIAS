<?php

Interface ilQuestionSolutionHandler
{
    /**
     * @param ilQuestionSolutionValuePair[] $pairs
     */
    public function getSolutionFromValuePairs(array $pairs) : ilQuestionSolution;

    /**
     * @return ilQuestionSolutionValuePair[]
     */
    public function getValuePairsFromSolution(ilQuestionSolution $solution) : array;


    public function getSolutionFromJSON(string $json) : ilQuestionSolution;


    public function getJSONFromSolution(ilQuestionSolution $solution) : string;
}