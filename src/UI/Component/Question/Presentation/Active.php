<?php

namespace ILIAS\UI\Component\Question\Presentation;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Component\Triggerer;
use ILIAS\UI\Component\Signal;

interface Active extends Component, Triggerable, Triggerer
{
    public function __construct(
        \ilQuestionBaseSettings $base_settings,
        \ilQuestionTypeSettings $type_settings,
    );

    public function getBaseSettings() : \ilQuestionBaseSettings;
    public function getTypeSettings() : \ilQuestionTypeSettings;


    /**
     * Register the signal for the canvas to receive the user solution
     * The signal is triggered by the presentation
     * It must carry the JSON representation of a user input
     * After receiving the canvas should update its own JSON representation of the user solution
     */
    public function withReceiveSolutionSignal(Signal $signal);


    /**
     * Provide the signal to show a user solution
     * This signal is triggered by the canvas to send a user solution to the presentation
     */
    public function getShowSolutionSignal() : Signal;


    /**
     * Provide the signal to show a type specific feedback
     * This signal is triggered by the canvas to send a feedback to the presentation
     */
    public function getShowFeedbackSignal() : Signal;


    /**
     * Provide the signal to send the current user solution
     * This signal is triggered by the canvas to request a user solution from the presentation
     * The presentation should repond by triggering a ReceiveSolution for the canvas
     */
    public function getProvideSolutionSignal() : Signal;
}