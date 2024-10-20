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

namespace ILIAS\ResourceStorage\Flavour\Engine;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
trait PHPMemoryLimit
{
    public function getSizeLimitInBytes(): int
    {
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            $memory_limit = match ($matches[2]) {
                'K' => $matches[1] * 1024,
                'M' => $matches[1] * 1024 * 1024,
                'G' => $matches[1] * 1024 * 1024 * 1024,
                default => $memory_limit,
            };
        }
        return (int) $memory_limit;
    }
}
