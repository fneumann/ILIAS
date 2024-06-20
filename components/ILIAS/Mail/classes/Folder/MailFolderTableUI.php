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

use ilStr;
use ilDatePresentation;
use ilUtil;
use ilDateTime;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\Column\Column as TableColumn;
use ILIAS\UI\Component\Table\Action\Action as TableAction;
use ILIAS\UI\URLBuilder;

class MailFolderTableUI implements \ILIAS\UI\Component\Table\DataRetrieval
{
    private \ILIAS\UI\URLBuilder $url_builder;
    private \ILIAS\UI\URLBuilderToken $action_parameter_token;
    private \ILIAS\UI\URLBuilderToken $row_id_token;

    private MailFolderRecords $records;

    /**
     * @param list<array{"checked": bool, "filename": string, "filesize": int, "filecreatedate": int}> $records
     */
    public function __construct(
        private readonly \ilMailFolderGUI $parent_gui,
        private readonly string $parent_cmd,
        private readonly string $parent_handler_cmd,
        private readonly int $mail_folder_id,
        private readonly bool $is_trash_folder,
        private readonly bool $is_sent_folder,
        private readonly bool $is_drafts_folder,
        private readonly array $selected_mail_ids,
        private readonly \ILIAS\UI\Factory $ui_factory,
        private readonly \ILIAS\UI\Renderer $ui_renderer,
        private readonly \ilLanguage $lng,
        private readonly \ilCtrlInterface $ctrl,
        private readonly \Psr\Http\Message\ServerRequestInterface $http_request,
        private readonly \ILIAS\Data\Factory $df
    ) {
        $form_action = $this->df->uri(
            \ilUtil::_getHttpPath() . '/' .
            $this->ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd)
        );

        [   $this->url_builder,
            $this->action_parameter_token,
            $this->row_id_token
        ] = (new URLBuilder($form_action))->acquireParameters(
            ['mail', 'folder'],
            'table_action',
        );
    }

    public function isTrashFolder()
    {
        return $this->is_trash_folder;
    }

    public function getNumberOfMails(): int
    {
        return 0;
    }

    public function get(): DataTable
    {
        return $this->ui_factory
            ->table()
            ->data(
                $this->lng->txt('attachment'),
                $this->getColumnDefinition(),
                $this
            )
            ->withId(self::class)
            ->withActions($this->getActions())
            ->withRequest($this->http_request);
    }

    private function getTitle(): string
    {

    }

    /**
     * @return array<string, TableColumn>
     */
    private function getColumnDefinition(): array
    {
        return [
            'filename' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('mail_file_name'))
                ->withIsSortable(true),
            'filesize' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('mail_file_size'))
                ->withIsSortable(true),
            'filecreatedate' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('create_date'))
                ->withIsSortable(true),
        ];
    }

    /**
     * @return array<string, TableAction>
     */
    private function getActions(): array
    {
        $actions = [];

        $actions['saveAttachments'] = $this->ui_factory->table()->action()->multi(
            $this->lng->txt('adopt'),
            $this->url_builder->withParameter($this->action_parameter_token, 'saveAttachments'),
            $this->row_id_token
        );

        return $actions;
    }

    /**
     * @return MailFolderRecords
     */
    private function getRecords(\ILIAS\Data\Range $range, \ILIAS\Data\Order $order): MailFolderRecords
    {
        $records = $this->records;

        [$order_field, $order_direction] = $order->join([], static function ($ret, $key, $value) {
            return [$key, $value];
        });

        usort($records, static function (array $left, array $right) use ($order_field): int {
            if ($order_field === 'filename') {
                return ilStr::strCmp($left[$order_field], $right[$order_field]);
            }

            return $left[$order_field] <=> $right[$order_field];
        });

        if ($order_direction === 'DESC') {
            $records = array_reverse($records);
        }

        $records = array_slice($records, $range->getStart(), $range->getLength());

        return $records;
    }

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        foreach ($this->getRecords($range, $order) as $item) {
            $record = [
                'filename' => $item['filename'],
                'filesize' => ilUtil::formatSize($item['filesize'], 'long'),
                'filecreatedate' => ilDatePresentation::formatDate(new ilDateTime($item['filecreatedate'], IL_CAL_UNIX))
            ];

            yield $row_builder
                ->buildDataRow(urlencode($record['filename']), $record);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->records);
    }
}
