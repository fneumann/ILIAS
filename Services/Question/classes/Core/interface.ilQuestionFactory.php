<?php

/**
 * Factory for all classes needed to implements a question type
 */
interface ilQuestionFactory
{
    /**
     * Get a unique string identifier of the question type
     */
    public function getTypeTag(): string;

    /**
     * Get a translated title of the question type that can be used in lists
     */
    public function getTypeTranslation(): string;

}