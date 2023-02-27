<?php

interface ilQuestionGrader
{
    public function __construct(
        ilQuestionBaseSettings $base_settings,
        ilQuestionTypeSettings $type_settings
    );

    public function getReachedPoints(ilQuestionSolution $solution) : float;

    public function getTypeFeedback(ilQuestionSolution $solution) : ilQuestionTypeFeedback;

    public function getCorrectSolution() : ilQuestionSolution;
}