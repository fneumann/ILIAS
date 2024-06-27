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

use Exception;
use ilMailBoxQuery;
use ilMailUserCache;
use ilSearchSettings;
use ilMailSearchResult;
use ilMailLuceneSearcher;

class MailFolderSearch
{
    public function __construct(
        private readonly MailFolderData $folder,
        private readonly MailFilterData $filter,
        private readonly bool $lucene_enabled,
    ) {
    }

    private ?array $records;
    private int $total_count = 0;
    private int $total_unread = 0;

    /**
     * @return MailRecordData[]
     */
    public function getRecords(
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
    ): array {
        $this->read($range, $order);
        return $this->records;
    }

    public function getTotalCount(): int
    {
        $this->count();
        return $this->total_count;

    }

    public function getTotalCountRead(): int
    {
        $this->count();
        return $this->total_count - $this->total_unread;
    }

    public function getTotalCountUnread(): int
    {
        $this->count();
        return $this->total_unread;
    }

    protected function shouldUseLuceneSearch(): bool
    {
        return ($this->lucene_enabled && (
                !empty($this->filter->getSender()) ||
                !empty($this->filter->getRecipients()) ||
                !empty($this->filter->getSubject()) ||
                !empty($this->filter->getBody()) ||
                !empty($this->filter->getAttachment())
            ));
    }



    /**
     * Prepare the mailbox query for count() and read()
     * refactored from \ilMailFolderTableGUI::fetchTableData in ILIAS 9
     */
    private function prepare(): void
    {
        if ($this->shouldUseLuceneSearch()) {
            $query_parser = new \ilMailLuceneQueryParser($this->filter['mail_filter'] ?? '');
            $query_parser->setFields([
                'title' => $this->filter->getSubject(),
                'content' => $this->filter->getBody(),
                'mattachment' => $this->filter->getAttachment(),
                'msender' => $this->filter->getSender(),
            ]);
            $query_parser->parse();

            $result = new ilMailSearchResult();
            $searcher = new ilMailLuceneSearcher($query_parser, $result);
            $searcher->search($this->folder->getUserId(), $this->folder->getFolderId());

            if (!$result->getIds()) {
                return;
            }

            ilMailBoxQuery::$filtered_ids = $result->getIds();
            ilMailBoxQuery::$filter = [
                'mail_filter_only_unread' => $this->filter['mail_filter_only_unread'] ?? false,
                'mail_filter_only_with_attachments' => $this->filter['mail_filter_only_with_attachments'] ?? false,
            ];
        } else {
            ilMailBoxQuery::$filter = $this->filter;
        }

        if (
            isset(ilMailBoxQuery::$filter['mail_filter_only_unread']) &&
            ($this->isDraftFolder() || $this->isSentFolder())
        ) {
            unset(ilMailBoxQuery::$filter['mail_filter_only_unread']);
        }

        if (isset(ilMailBoxQuery::$filter['mail_filter_only_with_attachments']) && $this->isDraftFolder()) {
            unset(ilMailBoxQuery::$filter['mail_filter_only_with_attachments']);
        }


        ilMailBoxQuery::$folderId = $this->folder->getFolderId();
        ilMailBoxQuery::$userId = $this->folder->getUserId();

    }

    /**
     * Count the found mails
     * refactored from \ilMailFolderTableGUI::fetchTableData in ILIAS 9
     * @throws Exception
     */
    private function count(): void
    {
        if (isset($this->total_count)) {
            return;
        }

        $this->prepare();
    }


    /**
     * Read the found mails
     * refactored from \ilMailFolderTableGUI::fetchTableData in ILIAS 9
     * @throws Exception
     */
    private function read(
        \ILIAS\Data\Range $range,
        \ILIAS\Data\Order $order,
    ): void
    {
        if (isset($this->records)) {
            return;
        }

        $result = null;

        if ($this->shouldUseLuceneSearch()) {
            $query_parser = new \ilMailLuceneQueryParser($this->filter['mail_filter'] ?? '');
            $query_parser->setFields([
                'title' => $this->filter->getSubject(),
                'content' => $this->filter->getBody(),
                'mattachment' => $this->filter->getAttachment(),
                'msender' => $this->filter->getSender(),
            ]);
            $query_parser->parse();

            $result = new ilMailSearchResult();
            $searcher = new ilMailLuceneSearcher($query_parser, $result);
            $searcher->search($this->folder->getUserId(), $this->folder->getFolderId());

            if (!$result->getIds()) {
                return;
            }

            ilMailBoxQuery::$filtered_ids = $result->getIds();
            ilMailBoxQuery::$filter = [
                'mail_filter_only_unread' => $this->filter['mail_filter_only_unread'] ?? false,
                'mail_filter_only_with_attachments' => $this->filter['mail_filter_only_with_attachments'] ?? false,
            ];
        } else {
            ilMailBoxQuery::$filter = $this->filter;
        }

        if (
            isset(ilMailBoxQuery::$filter['mail_filter_only_unread']) &&
            ($this->isDraftFolder() || $this->isSentFolder())
        ) {
            unset(ilMailBoxQuery::$filter['mail_filter_only_unread']);
        }

        if (isset(ilMailBoxQuery::$filter['mail_filter_only_with_attachments']) && $this->isDraftFolder()) {
            unset(ilMailBoxQuery::$filter['mail_filter_only_with_attachments']);
        }


        ilMailBoxQuery::$folderId = $this->folder->getFolderId();
        ilMailBoxQuery::$userId = $this->folder->getUserId();


        ilMailBoxQuery::$limit = $this->getLimit();
        ilMailBoxQuery::$offset = $this->getOffset();
        ilMailBoxQuery::$orderDirection = $this->getOrderDirection();
        ilMailBoxQuery::$orderColumn = $this->getOrderField();
        $data = ilMailBoxQuery::_getMailBoxListData();

        if ($data['set'] === [] && $this->getOffset() > 0) {
            $this->resetOffset();

            ilMailBoxQuery::$limit = $this->getLimit();
            ilMailBoxQuery::$offset = $this->getOffset();
            $data = ilMailBoxQuery::_getMailBoxListData();
        }


        if (!$this->isDraftFolder() && !$this->isSentFolder()) {
            $user_ids = [];
            foreach ($data['set'] as $mail) {
                if ($mail['sender_id'] && $mail['sender_id'] !== ANONYMOUS_USER_ID) {
                    $user_ids[$mail['sender_id']] = $mail['sender_id'];
                }
            }

            ilMailUserCache::preloadUserObjects($user_ids);
        }


        foreach ($data['set'] as $key => $mail) {
            if (is_array($this->getSelectedItems()) &&
                in_array($mail['mail_id'], $this->getSelectedItems(), false)
            ) {
                $mail['checked'] = ' checked="checked" ';
            }

            $mail['txt_select_mail_with_subject'] = sprintf(
                $this->lng->txt('select_mail_with_subject_x'),
                htmlspecialchars($mail['m_subject'] ?? '')
            );

            if ($this->isDraftFolder() || $this->isSentFolder()) {
                $mail['rcp_to'] = $mail['mail_login'] = ilUtil::htmlencodePlainString(
                    $this->_parentObject->umail->formatNamesForOutput((string) $mail['rcp_to']),
                    false
                );
            } elseif ($mail['sender_id'] === ANONYMOUS_USER_ID) {
                $mail['img_sender'] = ilUtil::getImagePath('logo/HeaderIconAvatar.svg');
                $mail['from'] =
                $mail['mail_login'] =
                $mail['alt_sender'] =
                    htmlspecialchars(ilMail::_getIliasMailerName());
            } else {
                $user = ilMailUserCache::getUserObjectById($mail['sender_id']);

                if ($user !== null) {
                    $mail['img_sender'] = $user->getPersonalPicturePath('xxsmall');
                    $mail['from'] = $mail['mail_login'] = $mail['alt_sender'] = htmlspecialchars(
                        $user->getPublicName()
                    );
                } else {
                    $mail['img_sender'] = '';
                    $mail['from'] = $mail['mail_login'] = trim(($mail['import_name'] ?? '') . ' ('
                        . $this->lng->txt('user_deleted') . ')');
                }
            }

            if ($this->isDraftFolder()) {
                $this->ctrl->setParameterByClass(
                    ilMailFormGUI::class,
                    'mail_id',
                    $mail['mail_id']
                );
                $this->ctrl->setParameterByClass(
                    ilMailFormGUI::class,
                    'mobj_id',
                    $this->_currentFolderId
                );
                $this->ctrl->setParameterByClass(
                    ilMailFormGUI::class,
                    'type',
                    ilMailFormGUI::MAIL_FORM_TYPE_DRAFT
                );
                $link_mark_as_read = $this->ctrl->getLinkTargetByClass(ilMailFormGUI::class);
                $this->ctrl->clearParametersByClass(ilMailFormGUI::class);
            } else {
                $this->ctrl->setParameter($this->getParentObject(), 'mail_id', $mail['mail_id']);
                $this->ctrl->setParameter($this->getParentObject(), 'mobj_id', $this->_currentFolderId);
                $link_mark_as_read = $this->ctrl->getLinkTarget($this->getParentObject(), 'showMail');
                $this->ctrl->clearParameters($this->getParentObject());
            }
            $css_class = $mail['m_status'] === 'read' ? 'mailread' : 'mailunread';

            if ($result instanceof ilMailSearchResult) {
                $search_result = [];
                foreach ($result->getFields($mail['mail_id']) as $content) {
                    if ('title' === $content[0]) {
                        $mail['msr_subject_link_read'] = $link_mark_as_read;
                        $mail['msr_subject_mailclass'] = $css_class;
                        $mail['msr_subject'] = $content[1];
                    } else {
                        $search_result[] = $content[1];
                    }
                }
                $mail['msr_data'] = implode('', array_map(static function ($value): string {
                    return '<p>' . $value . '</p>';
                }, $search_result));

                if (!isset($mail['msr_subject']) || !$mail['msr_subject']) {
                    $mail['msr_subject_link_read'] = $link_mark_as_read;
                    $mail['msr_subject_mailclass'] = $css_class;
                    $mail['msr_subject'] = htmlspecialchars($mail['m_subject'] ?? '');
                }
                $mail['msr_subject_read_unread'] = $mail['m_status'] === 'read' ? $this->lng->txt('mail_is_read') : $this->lng->txt('mail_is_unread');
            } else {
                $mail['mail_link_read'] = $link_mark_as_read;
                $mail['mailclass'] = $css_class;
                if ($mail['m_subject']) {
                    $mail['mail_subject'] = htmlspecialchars($mail['m_subject']);
                } else {
                    $mail['mail_subject'] = $this->lng->txt('mail_no_subject');
                }
                $mail['mail_subject_read_unread'] = $mail['m_status'] === 'read' ? $this->lng->txt('mail_is_read') : $this->lng->txt('mail_is_unread');
            }

            $mail['mail_date'] = ilDatePresentation::formatDate(
                new ilDateTime($mail['send_time'], IL_CAL_DATETIME)
            );

            $mail['attachment_indicator'] = '';
            if (is_array($mail['attachments']) && $mail['attachments'] !== []) {
                $this->ctrl->setParameter($this->getParentObject(), 'mail_id', (int) $mail['mail_id']);
                if ($this->isDraftFolder()) {
                    $this->ctrl->setParameter($this->getParentObject(), 'type', ilMailFormGUI::MAIL_FORM_TYPE_DRAFT);
                }
                $this->ctrl->setParameter($this->getParentObject(), 'mobj_id', $this->_currentFolderId);
                $mail['attachment_indicator'] = $this->uiRenderer->render(
                    $this->uiFactory->symbol()->glyph()->attachment(
                        $this->ctrl->getLinkTarget($this->getParentObject(), 'deliverAttachments')
                    )
                );
                $this->ctrl->clearParameters($this->getParentObject());
            }

            $mail['actions'] = $this->formatActionsDropDown($mail);

            $data['set'][$key] = $mail;
        }



        $this->total_count = (int) $data['cnt'];
        $this->total_unread = (int) $data['cnt_unread'];
    }

}
