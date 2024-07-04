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

namespace ILIAS\Mail\Message;

use ilDBInterface;
use ilMail;
use ilDBConstants;
use DateTimeImmutable;

/**
 * Mail query class.
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ServicesMail
 *
 */
class MailBoxQuery
{
    public const ORDER_COLUMNS = ['from', 'm_subject', 'send_time', 'rcp_to'];
    public const ORDER_DIRECTIONS = ['ASC', 'DESC'];

    private ?int $folder_id = null;
    private ?string $sender = null;
    private ?string $recipients = null;
    private ?string $subject = null;
    private ?string $body = null;
    private ?bool $unread = null;
    private ?bool $system = null;
    private ?bool $attachment = null;
    private ?bool $period_start = null;
    private ?bool $period_end = null;
    public ?array $filtered_ids = null;

    private int $limit = 0;
    private int $offset = 0;
    private string $order_direction = '';
    private string $order_column = '';

    public function __construct(
        private readonly ilDBInterface $db,
        private readonly int $user_id,
    ) {
    }

    public function withFolderId(?int $folder_id): MailBoxQuery
    {
        $clone = clone $this;
        $clone->folder_id = $folder_id;
        return $clone;
    }

    public function withSender(?string $sender): MailBoxQuery
    {
        $clone = clone $this;
        $clone->sender = $sender;
        return $clone;
    }

    public function withRecipients(?string $recipients): MailBoxQuery
    {
        $clone = clone $this;
        $clone->recipients = $recipients;
        return $clone;
    }

    public function withSubject(?string $subject): MailBoxQuery
    {
        $clone = clone $this;
        $clone->subject = $subject;
        return $clone;
    }

    public function withBody(?string $body): MailBoxQuery
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function withUnread(?bool $unread): MailBoxQuery
    {
        $clone = clone $this;
        $clone->unread = $unread;
        return $clone;
    }

    public function withSystem(?bool $system): MailBoxQuery
    {
        $clone = clone $this;
        $clone->system = $system;
        return $clone;
    }

    public function withAttachment(?bool $attachment): MailBoxQuery
    {
        $clone = clone $this;
        $clone->attachment = $attachment;
        return $clone;
    }

    public function withPeriodStart(?bool $period_start): MailBoxQuery
    {
        $clone = clone $this;
        $clone->period_start = $period_start;
        return $clone;
    }

    public function withPeriodEnd(?bool $period_end): MailBoxQuery
    {
        $clone = clone $this;
        $clone->period_end = $period_end;
        return $clone;
    }

    /**
     * @param int[]|null $filtered_ids
     */
    public function withFilteredIds(?array $filtered_ids): MailBoxQuery
    {
        $clone = clone $this;
        $clone->filtered_ids = $filtered_ids;
        return $clone;
    }

    public function withLimit(int $limit): MailBoxQuery
    {
        $clone = clone $this;
        $clone->limit = $limit;
        return $clone;
    }

    public function withOffset(int $offset): MailBoxQuery
    {
        $clone = clone $this;
        $clone->offset = $offset;
        return $clone;
    }

    public function withOrderDirection(string $order_direction): MailBoxQuery
    {
        $order_direction = strtoupper($order_direction);
        if (!in_array($order_direction, self::ORDER_DIRECTIONS)) {
            $order_direction = '';
        }

        $clone = clone $this;
        $clone->order_direction = $order_direction;
        return $clone;
    }

    public function withOrderColumn(string $order_column): MailBoxQuery
    {
        $order_column = strtolower($order_column);
        if (!in_array($order_column, self::ORDER_COLUMNS)) {
            $order_column = '';
        }

        $clone = clone $this;
        $clone->order_column = $order_column;
        return $clone;
    }

    /**
     * Count the number of unread mails with applied filter
     */
    public function countUnread(): int
    {
        return $this->withUnread(true)->count();
    }

    /**
     * Count the number of all mails with applied filter
     */
    public function count(): int
    {
        if ($this->filtered_ids === []) {
            return 0;
        }

        $query = 'SELECT COUNT(mail_id) cnt FROM mail '
            . 'LEFT JOIN usr_data ON usr_id = sender_id '
            . 'WHERE user_id = %s '
            . 'AND ((sender_id > 0 AND sender_id IS NOT NULL '
            . 'AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
            . $this->getFilterCondition();

        $res = $this->db->queryF($query, ['integer'], [$this->user_id]);

        if ($row = $this->db->fetchAssoc($res)) {
            return (int) $row['cnt'];
        }
        return 0;
    }

    /**
     * Query for mail data with applied filter
     * @param bool $short get only data that is needed for a listing
     * @return MailRecordData[]
     */
    public function query($short): array
    {
        if ($this->filtered_ids === []) {
            return [];
        }

        if ($short) {
            $fields = 'mail_id, user_id, folder_id, sender_id, send_time, m_status, m_subject, import_name, rcp_to, attachments';
        } else {
            $fields = 'mail.*';
        }

        $firstname_selection = '';
        if ($this->order_column === 'from') {
            // Because of the user id of automatically generated mails and ordering issues we have to do some magic
            $firstname_selection = '
				,(CASE
					WHEN (usr_id = ' . ANONYMOUS_USER_ID . ') THEN firstname 
					ELSE ' . $this->db->quote(ilMail::_getIliasMailerName(), 'text') . '
				END) fname
			';
        }

        $query = 'SELECT ' . $fields . $firstname_selection . ' FROM mail '
               . 'LEFT JOIN usr_data ON usr_id = sender_id '
               . 'AND ((sender_id > 0 AND sender_id IS NOT NULL '
               . 'AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) '
               . 'WHERE user_id = ' . $this->db->quote($this->user_id, 'integer')
               . $this->getFilterCondition() . ' ';

        if ($this->order_column === 'from') {
            $query .= ' ORDER BY '
                    . ' fname ' . $this->order_direction . ', '
                    . ' lastname ' . $this->order_direction . ', '
                    . ' login ' . $this->order_direction . ', '
                    . ' import_name ' . $this->order_direction;
        } elseif ($this->order_column !== '') {
            $query .= ' ORDER BY ' . $this->order_column . ' ' . $this->order_direction;
        } else {
            $query .= ' ORDER BY send_time DESC';
        }

        $this->db->setLimit($this->limit, $this->offset);
        $res = $this->db->query($query);

        $set = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $set[] = new MailRecordData(
                isset($row['mail_id']) ? (int) $row['mail_id'] : 0,
                isset($row['user_id']) ? (int) $row['user_id'] : 0,
                isset($row['folder_id']) ? (int) $row['folder_id'] : 0,
                isset($row['sender_id']) ? (int) $row['sender_id'] : null,
                isset($row['send_time']) ? (string) $row['send_time'] : null,
                isset($row['m_status']) ? (string) $row['m_status'] : null,
                isset($row['m_subject']) ? (string) $row['m_subject'] : null,
                isset($row['import_name']) ? (string) $row['import_name'] : null,
                isset($row['use_placeholders']) ? (bool) $row['use_placeholders'] : false,
                isset($row['m_message']) ? (string) $row['m_message'] : null,
                isset($row['rcp_to']) ? (string) $row['rcp_to'] : null,
                isset($row['rcp_cc']) ? (string) $row['rcp_cc'] : null,
                isset($row['rcp_bcc']) ? (string) $row['rcp_bcc'] : null,
                isset($row['attachments']) ? (array) unserialize(
                    stripslashes($row['attachments']),
                    ['allowed_classes' => false]
                ) : [],
                isset($row['tpl_ctx_id']) ? (string) $row['tpl_ctx_id'] : null,
                isset($row['tpl_ctx_params']) ? (string) $row['tpl_ctx_params'] : null
            );
        }
        return $set;
    }

    private function getFilterCondition(): string
    {
        $filter_parts = [];

        $text_conditions = [
            [$this->sender, 'CONCAT(CONCAT(firstname, lastname), login)'],
            [$this->recipients, 'CONCAT(CONCAT(rcp_to, rcp_cc), rcp_bcc)'],
            [$this->subject, 'm_subject'],
            [$this->body, 'm_message'],
        ];

        foreach ($text_conditions as $cond) {
            if (!empty($cond[0])) {
                $filter_parts[] = $this->db->like(
                    $cond[1],
                    'text',
                    '%%' . $cond[0] . '%%',
                    false
                );
            }
        }

        if (isset($this->folder_id)) {
            $filter_parts[] = 'folder_id = ' . $this->db->quote($this->folder_id, 'integer');
        }

        if ($this->unread === true) {
            $filter_parts[] = 'm_status = ' . $this->db->quote('unread', 'text');
        } elseif ($this->unread === false) {
            $filter_parts[] = 'm_status != ' . $this->db->quote('unread', 'text');
        }

        if ($this->attachment === true) {
            $filter_parts[] = '(attachments != ' . $this->db->quote(serialize(null), 'text')
                            . ' AND attachments != ' . $this->db->quote(serialize([]), 'text') . ')';
        } elseif ($this->attachment === false) {
            $filter_parts[] = '(attachments = ' . $this->db->quote(serialize(null), 'text')
                            . '  OR attachments = ' . $this->db->quote(serialize([]), 'text') . ')';
        }

        if ($this->system === true) {
            $filter_parts[] = 'sender_id = ' . $this->db->quote(ANONYMOUS_USER_ID, ilDBConstants::T_INTEGER);
        } elseif ($this->system === false) {
            $filter_parts[] = 'sender_id != ' . $this->db->quote(ANONYMOUS_USER_ID, ilDBConstants::T_INTEGER);
        }

        if (!empty($this->period_start)) {
            $filter_parts[] = 'send_time >= ' . $this->db->quote(
                (new DateTimeImmutable(
                    '@' . $this->period_start
                ))->format('Y-m-d 00:00:00'),
                'timestamp'
            );
        }

        if (!empty($this->period_end)) {
            $filter_parts[] = 'send_time <= ' . $this->db->quote(
                (new DateTimeImmutable(
                    '@' . $this->period_start
                ))->format('Y-m-d 23:59:59'),
                'timestamp'
            );
        }

        if (!empty($this->filtered_ids)) {
            $filter_parts[] = $this->db->in(
                'mail_id',
                $this->filtered_ids,
                false,
                'integer'
            ) . ' ';
        }

        if ($filter_parts !== []) {
            return ' AND ' . implode(' AND ', $filter_parts);
        }
        return '';
    }

}
