<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\TopParentItemRenderer;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class TopParentItem extends AbstractBaseItem implements isItem, hasSymbol, hasTitle, isParent, hasContentLanguage
{
    use ContentLanguage;

    /**
     * @var isChild[]
     */
    protected array $children = [];
    protected ?Symbol $symbol = null;
    protected string $title = "";

    /**
     * @inheritDoc
     */
    public function __construct(IdentificationInterface $provider_identification)
    {
        parent::__construct($provider_identification);
        $this->renderer = new TopParentItemRenderer();
    }

    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol): hasSymbol
    {
        $clone = clone($this);
        $clone->symbol = $symbol;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSymbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @inheritDoc
     */
    public function hasSymbol(): bool
    {
        return ($this->symbol instanceof Symbol);
    }

    /**
     * @inheritDoc
     */
    public function withTitle(string $title): hasTitle
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     */
    public function withChildren(array $children): isParent
    {
        $clone = clone($this);
        $clone->children = $children;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function appendChild(isChild $child): isParent
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasChildren(): bool
    {
        return $this->children !== [];
    }
}
