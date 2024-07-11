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
use ILIAS\Mail\Message\MailRecordData;
use ilMail;
use ilMailUserCache;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\DateFormat\DateFormat;
use DateTimeImmutable;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Link\Link;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use DateTimeZone;

class MailFolderTableUI implements \ILIAS\UI\Component\Table\DataRetrieval
{
    private \ILIAS\UI\URLBuilder $url_builder;
    private \ILIAS\UI\URLBuilderToken $action_parameter_token;
    private \ILIAS\UI\URLBuilderToken $row_id_token;

    /** @var string[] */
    private array $avatars = [];

    public function __construct(
        private readonly \ilMailFolderGUI $parent_gui,
        private readonly string $parent_cmd,
        private readonly MailFolderData $folder,
        private readonly MailFolderSearch $search,
        private readonly array $selected_mail_ids,
        private readonly ilMail $mail,
        private readonly Factory $ui_factory,
        private readonly Renderer $ui_renderer,
        private readonly \ilLanguage $lng,
        private readonly \ilCtrlInterface $ctrl,
        private readonly \Psr\Http\Message\ServerRequestInterface $http_request,
        private readonly \ILIAS\Data\Factory $df,
        private readonly Refinery $refinery,
        private readonly DateFormat $date_format,
        private readonly DateTimeZone $user_time_zone
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
            'entry_ids'
        );
    }

    public function get(): DataTable
    {
        return $this->ui_factory
            ->table()
            ->data(
                $this->getTitle(),
                $this->getColumnDefinition(),
                $this
            )
            ->withId(self::class)
            ->withActions($this->getActions())
            ->withRequest($this->http_request);
    }

    /**
     * @return TableColumn[]
     */
    private function getColumnDefinition(): array
    {
        $columns = [
            'status' => $this->ui_factory
                ->table()
                ->column()
                ->statusIcon($this->lng->txt('status'))
                ->withIsSortable(true),

            'avatar' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('user_avatar'))
                ->withIsSortable(true),

            'sender' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('sender'))
                ->withIsSortable(true),

            'recipients' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('recipients'))
                ->withIsSortable(true),

            'subject' => $this->ui_factory
                ->table()
                ->column()
                ->link($this->lng->txt('subject'))
                ->withIsSortable(true),

            'attachments' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('attachments'))
                ->withIsSortable(true),

            'date' => $this->ui_factory
                ->table()
                ->column()
                ->date($this->lng->txt('date'), $this->df->dateFormat()->withTime24($this->date_format))
                ->withIsSortable(true),
        ];

        if ($this->folder->hasOutgoingMails()) {
            unset($columns['status']);
            unset($columns['avatar']);
            unset($columns['sender']);
        } else {
            unset($columns['recipients']);
        }

        return $columns;
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

    public function getRows(
        \ILIAS\UI\Component\Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {

        $order_columns = [
            'subject' => 'm_subject',
            'sender' => 'from',
            'recipients' => 'rcp_to',
            'date' => 'send_time',
            'attachments' => 'attachments'
        ];

        [$order_column, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);

        foreach ($this->search->getRecords(
            $range->getLength(),
            $range->getStart(),
            $order_columns[$order_column] ?? '',
            $order_direction
        ) as $record) {

            $this->ctrl->setParameter($this->parent_gui, 'mail_id', $record->getMailId());

            if ($this->folder->hasIncomingMails()) {
                $data = [
                    'status' => $this->getStatus($record),
                    'avatar' => $this->getAvatar($record),
                    'sender' => $this->getSender($record),
                    'subject' => $this->getSubject($record),
                    'attachments' => $this->getAttachments($record),
                    'date' => $this->getDate($record)
                ];
            } else {
                $data = [
                    'recipients' => $this->getRecipients($record),
                    'subject' => $this->getSubject($record),
                    'attachments' => $this->getAttachments($record),
                    'date' => $this->getDate($record)
                ];
            }

            yield $row_builder->buildDataRow((string) $record->getMailId(), $data);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return $this->search->getCount();
    }

    private function getTitle(): string
    {
        return sprintf(
            '%s: %s %s (%s %s)',
            $this->folder->getTitle(),
            $this->search->getCount(),
            $this->lng->txt('mail_s'),
            $this->search->getUnread(),
            $this->lng->txt('unread')
        );
    }

    private function getAvatar(MailRecordData $record): string
    {
        if (!array_key_exists($record->getSenderId(), $this->avatars)) {
            $user = ilMailUserCache::getUserObjectById($record->getSenderId());
            $this->avatars[$record->getSenderId()] = isset($user)
                ? $this->ui_renderer->render($user->getAvatar())
                : '';
        }
        return $this->avatars[$record->getSenderId()];
    }

    private function getStatus(MailRecordData $record): Icon
    {
        // todo: use better icons when available
        return $record->isRead()
            ? $this->ui_factory->symbol()->icon()->custom('assets/images/browser/blank.png', $this->lng->txt('read'))
            : $this->ui_factory->symbol()->icon()->standard('mail', $this->lng->txt('unread'));
    }

    private function getSender(MailRecordData $record): string
    {
        if ($record->getSenderId() == ANONYMOUS_USER_ID) {
            return ilMail::_getIliasMailerName();
        }
        if (!empty($user = ilMailUserCache::getUserObjectById($record->getSenderId()))) {
            return $user->getPublicName();
        }
        return trim(($record->getImportName() ?? '') . ' (' . $this->lng->txt('user_deleted') . ')');
    }

    private function getRecipients(MailRecordData $record): string
    {
        return $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
            $this->mail->formatNamesForOutput((string) $record->getRcpTo())
        );
    }

    private function getSubject(MailRecordData $record): Link
    {
        return $this->ui_factory->link()->standard(
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($record->getSubject()),
            $this->ctrl->getLinkTarget($this->parent_gui, 'showMail')
        );
    }

    private function getDate(MailRecordData $record): ?DateTimeImmutable
    {
        return empty($record->getSendTime()) ? null : $record->getSendTime()->setTimezone($this->user_time_zone);
    }

    private function getAttachments(MailRecordData $record): string
    {
        return $record->hasAttachments()
            ? $this->ui_renderer->render($this->ui_factory->symbol()->glyph()->attachment())
            : '';
    }
}
