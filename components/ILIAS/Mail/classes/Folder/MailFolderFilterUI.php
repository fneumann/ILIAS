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

namespace ILIAS\Mail\Folder;

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ilUIFilterService;
use ilLanguage;

class MailFolderFilterUI
{
    private Standard $filter;

    public function __construct(
        private readonly string $target_url,
        private readonly Factory $ui_factory,
        private readonly ilUIFilterService $filter_service,
        private readonly ilLanguage $lng
    ) {
        $this->filter = $this->filter_service->standard(
            self::class,
            $this->target_url,
            //elements
            [
                'sender' => $this->ui_factory->input()->field()->text($this->lng->txt('sender')),
            ],
            // initially rendered
            [
                'sender' => true
            ],
            true,
            true
        );
    }


    public function get(): Standard
    {
        return $this->filter;
    }

}
