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

namespace ILIAS\UI\Component\Question;

/**
 * Interface Factory
 *
 * @package ILIAS\UI\Component\Question
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose:
     *     A canvas embeds a question
     * ---
     * @return \ILIAS\UI\Component\Question\Canvas\Factory
     **/
    public function canvas(): Canvas\Factory;

    /**
     * ---
     * description:
     *   purpose:
     *     A question presents an assessment question
     * ---
     * @return \ILIAS\UI\Component\Question\Presentation\Factory
     **/
    public function presentation(): Presentation\Factory;

    /**
     * ---
     * description:
     *   purpose:
     *     A grader gets a signal with a user solution and returns a signal with grading information
     * ---
     * @return \ILIAS\UI\Component\Question\Grader\Factory
     **/
    public function grader(): Grader\Factory;
}
