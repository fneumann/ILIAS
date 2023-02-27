<?php

/**
 * Question type specific settings of a question
 * The structure is not predefined but it mut be able to convert it to JSON
 */
interface ilQuestionTypeSettings
{
    public function toJSON(): string;
}