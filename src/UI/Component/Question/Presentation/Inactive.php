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

use ILIAS\UI\Component\Component;

/**
 * Interface for Inactive Question Presentations
 * @package ILIAS\UI\Component\Question
 */
interface Inactive extends Component
{
    public function __construct(
        \ilQuestionBaseSettings $base_settings,
        \ilQuestionTypeSettings $type_settings,
        ?\ilQuestionSolution $solution,
        ?\ilQuestionTypeFeedback $feedback
    );

    public function getBaseSettings() : \ilQuestionBaseSettings;
    public function getTypeSettings() : \ilQuestionTypeSettings;
    public function getSolution() : ?\ilQuestionSolution;
    public function getFeedback() : ?\ilQuestionTypeFeedback;
}