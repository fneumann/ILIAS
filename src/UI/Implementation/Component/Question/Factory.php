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

namespace ILIAS\UI\Implementation\Component\Question;

use ILIAS\UI\Component\Question as IQuestion;
use ILIAS\UI\Component\Question\Canvas as ICanvas;
use ILIAS\UI\Component\Question\Presentation as IPresentation;
use ILIAS\UI\Component\Question\Grader as IGrader;

class Factory implements IQuestion\Factory
{
    public function canvas() : ICanvas\Factory
    {
        return new Canvas\Factory;
    }

    public function presentation() : IPresentation\Factory
    {
        return new Presentation\Factory();
    }

    public function grader() : IGrader\Factory
    {
        return new Grader\Factory();
    }
}
