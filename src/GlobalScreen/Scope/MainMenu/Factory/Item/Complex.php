<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Glyph;
use ILIAS\UI\Component\Symbol\Icon;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class Complex
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Complex extends AbstractChildItem implements hasContent, hasTitle, hasSymbol, supportsAsynchronousLoading
{

    /**
     * @var Closure
     */
    private $content_wrapper;
    /**
     * @var
     */
    private $content;
    /**
     * @var string
     */
    private $title = '';
    /**
     * @var Symbol
     */
    private $symbol;
    /**
     * @var bool
     */
    private $supports_async_loading = false;


    /**
     * @inheritDoc
     */
    public function withContentWrapper(Closure $content_wrapper) : hasContent
    {
        $clone = clone($this);
        $clone->content_wrapper = $content_wrapper;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withContent(Component $ui_component) : hasContent
    {
        $clone = clone($this);
        $clone->content = $ui_component;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getContent() : Component
    {
        if ($this->content_wrapper !== null) {
            $wrapper = $this->content_wrapper;

            return $wrapper();
        }

        return $this->content;
    }


    /**
     * @param string $title
     *
     * @return Complex
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        // bugfix mantis 25526: make aria labels mandatory
        if (($symbol instanceof Icon\Icon || $symbol instanceof Glyph\Glyph)
            && ($symbol->getAriaLabel() === "")
        ) {
            throw new \LogicException("the symbol's aria label MUST be set to ensure accessibility");
        }

        $clone = clone($this);
        $clone->symbol = $symbol;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getSymbol() : Symbol
    {
        return $this->symbol;
    }


    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return $this->symbol instanceof Symbol;
    }


    /**
     * @inheritDoc
     */
    public function withSupportsAsynchronousLoading(bool $supported) : supportsAsynchronousLoading
    {
        $clone = clone($this);
        $clone->supports_async_loading = $supported;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function supportsAsynchronousLoading() : bool
    {
        return $this->supports_async_loading;
    }
}
