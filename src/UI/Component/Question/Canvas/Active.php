<?php

namespace ILIAS\UI\Component\Question\Canvas;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerable;
use ILIAS\UI\Component\Onloadable;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Question\Presentation\Active as ActivePresentation;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Component\Triggerer;

interface Active extends Component, Triggerable, Triggerer
{
    /**
     * Get the question presentation
     */
    public function getPresentation(): ?ActivePresentation;

    /**
     * Get the question presentation renderer
     */
    public function getPresentationRenderer(): ?ComponentRenderer;

    /**
     * Inject the question presentation
     */
    public function withPresentation(ActivePresentation $presentation): Inactive;

    /**
     * Inject the question presentation renderer
     */
    public function withPresentationRenderer(ComponentRenderer $renderer) : Inactive;


    /**
     * Register the signal for the question presentation to show a user solution
     * This signal is triggered by the canvas to send a user solution to the presentation
     */
    public function withShowSolutionSignal(Signal $signal);


    /**
     * Register the signal for the question presentation to show a type specific feedback
     * This signal is triggered by the canvas to send a feedback to the presentation
     */
    public function withShowFeedbackSignal(Signal $signal);


    /**
     * Register the signal for the question presentation to provide a user solution
     * This signal is triggered by the canvas to request a user solution from the presentation
     * The presentation should repond by triggering a ReceiveSolution for the canvas
     */
    public function withProvideSolutionSignal(Signal $signal);


    /**
     * Register the signal for the grader to provide a feedback for the user solution
     * This signal is triggered by the canvas to request a feedback from the grader
     * The grader should repond by triggering a ReceiveFeedback for the canvas
     */
    public function withProvideFeedbackSignal(Signal $signal);


    /**
     * Provide the signal to receive the user solution
     * The signal is triggered by the presentation
     * It must carry the JSON representation of a user input
     * After receiving the canvas should update its own JSON representation of the user solution
     */
    public function getReceiveSolutionSignal(): Signal;


    /**
     * Provide the signal to receive the feedback for a user solution
     * The signal is triggered by the grader after receiving a user solution
     * It must carry the JSON representation of a feedback
     * After receiving the canvas should trigger the ShowFeedback signal of its presentation
     */
    public function getReceiveFeedbackSignal(): Signal;
}