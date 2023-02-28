<?php

namespace ILIAS\UI\Implementation\Component\Question\Canvas;

use ILIAS\UI\Component\Question\Canvas\Inactive as InactiveCanvas;
use ILIAS\UI\Component\Question\Presentation\Inactive as InactivePresentation;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

class Inactive implements InactiveCanvas
{
    use ComponentHelper;

    protected ?InactivePresentation $presentation = null;
    protected ?ComponentRenderer $presentation_rederer = null;


    public function getPresentation() : ?InactivePresentation
    {
        return $this->presentation;
    }

    public function getPresentationRenderer() : ?ComponentRenderer
    {
        return $this->presentation_rederer;
    }

    public function withPresentation(InactivePresentation $presentation) : InactiveCanvas
    {
        $clone = clone $this;
        $clone->presentation = $presentation;
        return $clone;
    }

    public function withPresentationRenderer(ComponentRenderer $renderer) : InactiveCanvas
    {
        $clone = clone $this;
        $clone->presentation_rederer = $renderer;
        return $clone;
    }
}