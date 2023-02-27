<?php

namespace ILIAS\UI\Component\Question\Canvas;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Question\Presentation\Inactive as InactivePresentation;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

interface Inactive extends Component
{
    /**
     * Get the question presentation
     */
    public function getPresentation(): ?InactivePresentation;

    /**
     * Get the question presentation renderer
     */
    public function getPresentationRenderer(): ?ComponentRenderer;

    /**
     * Inject the question presentation
     */
    public function withPresentation(InactivePresentation $presentation): Inactive;

    /**
     * Inject the question presentation renderer
     */
    public function withPresentationRenderer(ComponentRenderer $renderer) : Inactive;
}