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
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ilUIFilterService;
use ilLanguage;

class MailFilterUI
{
    private FilterComponent $filter;
    private ?string $sender;

    public function __construct(
        private readonly string $target_url,
        private readonly bool $lucene_enabled,
        private readonly MailFolderData $folder,
        private readonly Factory $ui_factory,
        private readonly ilUIFilterService $filter_service,
        private readonly ilLanguage $lng
    ) {
        $inputs = [];
        if ($this->folder->hasIncomingMails()) {
            $inputs['sender'] = $this->ui_factory->input()->field()->text($this->lng->txt('sender'));
        } else {
            $inputs['recipients'] = $this->ui_factory->input()->field()->text($this->lng->txt('recipients'));
        }

        $inputs['subject'] = $this->ui_factory->input()->field()->text($this->lng->txt('subject'));
        $inputs['body'] = $this->ui_factory->input()->field()->text($this->lng->txt('body'));

        if ($this->lucene_enabled) {
            $inputs['attachment'] = $this->ui_factory->input()->field()->text($this->lng->txt('attachment'));
        }

        $this->filter = $this->filter_service->standard(
            self::class,
            $this->target_url,
            //elements
            $inputs,
            // initially rendered
            array_map(fn($value) => true, $inputs),
            true,
            true
        );
    }

    /**
     * Get the filter UI component
     */
    public function get(): FilterComponent
    {
        return $this->filter;
    }

    /**
     * Get the user entered filter data
     */
    public function getData(): MailFilterData
    {
        $data = $this->filter_service->getData($this->filter);

        return new MailFilterData(
            isset($data['sender']) ? (string) $data['sender'] : null,
            isset($data['recipients']) ? (string) $data['recipients'] : null,
            isset($data['subject']) ? (string) $data['subject'] : null,
            isset($data['body']) ? (string) $data['body'] : null,
            isset($data['attachment']) ? (string) $data['body'] : null,
        );
    }
}
