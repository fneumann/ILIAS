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

namespace ILIAS\MetaData\Elements\Scaffolds;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Manipulator\ScaffoldProvider\ScaffoldProviderInterface;

interface ScaffoldableInterface
{
    /**
     * Scaffolds are used to mark where elements could potentially be created.
     * Adds all possible scaffolds to this element's sub-elements.
     * Scaffolds are added such that the order of elements as suggested by the structure
     * is preserved.
     */
    public function addScaffoldsToSubElements(
        ScaffoldProviderInterface $scaffold_provider
    ): void;

    /**
     * If possible, adds a scaffold with the given name to this element's sub-elements,
     * and returns it.
     * Scaffolds are added such that the order of elements as suggested by the structure
     * is preserved.
     * @return ElementInterface[]
     */
    public function addScaffoldToSubElements(
        ScaffoldProviderInterface $scaffold_provider,
        string $name
    ): ?ElementInterface;
}
