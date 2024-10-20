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

namespace ILIAS\MetaData\Copyright;

use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Legacy\Legacy;

interface RendererInterface
{
    /**
     * Returns a string in a legacy UI component if only a string can be returned.
     * @return Image[]|Link[]|Legacy[]
     */
    public function toUIComponents(CopyrightDataInterface $copyright): array;

    public function toString(CopyrightDataInterface $copyright): string;
}
