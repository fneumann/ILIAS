<?php

namespace ILIAS\UI\Component\Question\Grader;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Component\Triggerer;
use ILIAS\UI\Component\Signal;

interface Standard extends Component, Triggerable, Triggerer
{
    public function __construct(
        \ilQuestionBaseSettings $base_settings,
        \ilQuestionTypeSettings $type_settings,
    );

    public function getBaseSettings() : \ilQuestionBaseSettings;
    public function getTypeSettings() : \ilQuestionTypeSettings;


    /**
     * Register the signal for the canvas to receive the feedback
     * The signal is triggered by the grader after receiving a user solution
     * It must carry the JSON representation of a feedback
     * After receiving the canvas should trigger the ShowFeedback signal of its presentation
     */
    public function withReceiveFeedbackSignal(Signal $signal);


    /**
     * Provide the signal to request a feedback for a user solution
     * This signal is triggered by the canvas to request a feedback from the grader
     * The grader should repond by triggering a ReceiveFeedback for the canvas
     */
    public function getProvideFeedbackSignal() : Signal;

}