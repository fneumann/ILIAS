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

namespace ILIAS\Component\Dependencies;

/**
 * A dependency where the component needs something from the world.
 */
class In implements Dependency
{
    protected Name|string $name;
    protected array $dependant = [];

    public function __construct(
        protected InType $type,
        string $name,
        protected array $resolved_by = []
    ) {
        if ($type !== InType::INTERNAL) {
            $name = new Name($name);
        }
        $this->name = $name;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    public function __toString(): string
    {
        return $this->type->value . ": " . $this->name;
    }

    public function getType(): InType
    {
        return $this->type;
    }

    public function addDependant(Out $out): void
    {
        $this->dependant[(string) $out] = $out;
        $out->addDependency($this);
    }

    public function addResolution(Out $other): void
    {
        if ($this->type !== InType::SEEK && count($this->resolved_by) > 0) {
            throw new LogicException(
                "Dependency of type {$this->type} can only be resolved once."
            );
        }
        $this->resolved_by[] = $other;
        $other->addResolves($this);
    }

    public function getResolvedBy(): array
    {
        return $this->resolved_by;
    }
}
