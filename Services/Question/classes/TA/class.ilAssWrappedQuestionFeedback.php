<?php

class ilAssWrappedQuestionFeedback extends ilAssQuestionFeedback
{

    public function getSpecificAnswerFeedbackTestPresentation(
        int $questionId,
        int $questionIndex,
        int $answerIndex
    ) : string {
        return '';
    }

    public function completeSpecificFormProperties(ilPropertyFormGUI $form) : void
    {
    }

    public function initSpecificFormProperties(ilPropertyFormGUI $form) : void
    {
    }

    public function saveSpecificFormProperties(ilPropertyFormGUI $form) : void
    {
    }

    public function getSpecificAnswerFeedbackContent(int $questionId, int $questionIndex, int $answerIndex) : string
    {
        return '';
    }

    public function getAllSpecificAnswerFeedbackContents(int $questionId) : string
    {
        return '';
    }

    public function saveSpecificAnswerFeedbackContent(
        int $questionId,
        int $questionIndex,
        int $answerIndex,
        string $feedbackContent
    ) : int {
        return -1;
    }

    public function deleteSpecificAnswerFeedbacks(
        int $questionId,
        bool $isAdditionalContentEditingModePageObject
    ) : void {
    }

    protected function duplicateSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId) : void
    {
    }

    protected function isSpecificAnswerFeedbackId(int $feedbackId) : bool
    {
        return false;
    }

    protected function syncSpecificFeedback(int $originalQuestionId, int $duplicateQuestionId) : void
    {
    }

    public function getSpecificAnswerFeedbackExportPresentation(
        int $questionId,
        int $questionIndex,
        int $answerIndex
    ) : string {
        return '';
    }

    public function importSpecificAnswerFeedback(
        int $questionId,
        int $questionIndex,
        int $answerIndex,
        string $feedbackContent
    ) : void {
    }
}