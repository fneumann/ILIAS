<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Question\Canvas;

use ILIAS\UI\Component\Question as Question;
use ILIAS\UI\Component\Question\Canvas\Active;
use ILIAS\UI\Component\Question\Canvas\Inactive;


class Factory implements Question\Canvas\Factory
{

    public function active() : Active
    {
        throw new \ILIAS\UI\NotImplementedException();
    }

    public function inactive() : Inactive
    {
        return new \ILIAS\UI\Implementation\Component\Question\Canvas\Inactive();
    }
}
