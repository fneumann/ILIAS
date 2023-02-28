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

    /**
     * Get the type specific question settings for a question id
     */
    public function getTypeSettings(int $question_id) : ilQuestionTypeSettings;

    /**
     * Get the class that calculate the reached points and the feedback
     * This is the backend version, implemented in php
     */
    public function getBackendGrader(
        ilQuestionBaseSettings $base_settings,
        ilQuestionTypeSettings $type_settings,
    ) : ilQuestionGrader;

    /**
     * Get the handler that converts solutions between frontend and backend
     */
    public function getSolutionHandler(): ilQuestionSolutionHandler;

    /**
     * Get the renderer for question type specific UI components
     */
    public function getRenderer() : ILIAS\UI\Implementation\Render\ComponentRenderer;

    /**
     * Get the inactive question presentation
     */
    public function getInactivePresentation(
        ilQuestionBaseSettings $base_settings,
        ilQuestionTypeSettings $type_settings,
        ?ilQuestionSolution $solution,
        ?ilQuestionTypeFeedback $feedback
    ) : ILIAS\UI\Component\Question\Presentation\Inactive;
}