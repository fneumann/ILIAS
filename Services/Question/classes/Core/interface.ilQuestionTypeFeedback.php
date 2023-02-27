<?php

interface ilQuestionTypeFeedback
{
    public function toJSON(): string;

    public static function fromJSON(string $json): ilQuestionTypeFeedback;
}