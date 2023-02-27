<?php

namespace ILIAS\UI\Implementation\Component\Question\Presentation;

use ILIAS\UI\Component\Question\Canvas\Inactive as InactiveCanvas;
use ILIAS\UI\Component\Question\Presentation\Inactive as InactivePresentation;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Inactive implements InactivePresentation
{
    use ComponentHelper;

    protected \ilQuestionBaseSettings $base_settings;
    protected \ilQuestionTypeSettings $type_settings;
    protected ?\ilQuestionSolution $solution;
    protected ?\ilQuestionTypeFeedback $feedback;

    public function __construct(
        \ilQuestionBaseSettings $base_settings,
        \ilQuestionTypeSettings $type_settings,
        ?\ilQuestionSolution $solution,
        ?\ilQuestionTypeFeedback $feedback
    ) {
        $this->base_settings = $base_settings;
        $this->type_settings = $type_settings;
        $this->solution = $solution;
        $this->feedback = $feedback;
    }


    public function getBaseSettings() : \ilQuestionBaseSettings
    {
        return $this->base_settings;
    }


    public function getTypeSettings() : \ilQuestionTypeSettings
    {
        return $this->type_settings;
    }

    public function getSolution() : ?\ilQuestionSolution
    {
        return $this->solution;
    }

    public function getFeedback() : ?\ilQuestionTypeFeedback
    {
        return $this->feedback;
    }
}