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

namespace ILIAS\UI\Component\Question\Presentation;

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
     *     An active question supports interactions for answering the question
     * ---
     * @return \ILIAS\UI\Component\Question\Presentation\Active
     **/
    public function active(): Active;

    /**
     * ---
     * description:
     *   purpose:
     *     An inactive question shows an empty question or a user solution
     * ---
     * @return \ILIAS\UI\Component\Question\Presentation\Inactive
     **/
    public function inactive(): Inactive;

}
