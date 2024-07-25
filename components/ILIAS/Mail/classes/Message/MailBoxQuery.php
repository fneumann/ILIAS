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
use DateTimeZone;
use ilTimeZone;
use ILIAS\Data\Order;

/**
 * Database query for mails of a user
 */
class MailBoxQuery
{
    private const DEFAULT_ORDER_COLUMN = MailBoxOrderColumn::SEND_TIME;
    private const DEFAULT_ORDER_DIRECTION = Order::ASC;

    private ilDBInterface $db;
    private ?int $folder_id = null;
    private ?string $sender = null;
    private ?string $recipients = null;
    private ?string $subject = null;
    private ?string $body = null;
    private ?bool $is_unread = null;
    private ?bool $is_system = null;
    private ?bool $has_attachment = null;
    private ?DateTimeImmutable $period_start = null;
    private ?DateTimeImmutable $period_end = null;
    private ?array $filtered_ids = null;
    private int $limit = 999999;
    private int $offset = 0;

    private MailBoxOrderColumn $order_column = self::DEFAULT_ORDER_COLUMN;
    private string $order_direction = self::DEFAULT_ORDER_DIRECTION;

    public function __construct(
        private readonly int $user_id,
    ) {
        global $DIC;
        $this->db = $DIC->database();
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

    public function withIsUnread(?bool $is_unread): MailBoxQuery
    {
        $clone = clone $this;
        $clone->is_unread = $is_unread;
        return $clone;
    }

    public function withIsSystem(?bool $is_system): MailBoxQuery
    {
        $clone = clone $this;
        $clone->is_system = $is_system;
        return $clone;
    }

    public function withHasAttachment(?bool $has_attachment): MailBoxQuery
    {
        $clone = clone $this;
        $clone->has_attachment = $has_attachment;
        return $clone;
    }

    public function withPeriodStart(?DateTimeImmutable $period_start): MailBoxQuery
    {
        $clone = clone $this;
        $clone->period_start = $period_start;
        return $clone;
    }

    public function withPeriodEnd(?DateTimeImmutable $period_end): MailBoxQuery
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

    public function withOrderColumn(?MailBoxOrderColumn $order_column): MailBoxQuery
    {
        $clone = clone $this;
        if (isset($order_column)) {
            $clone->order_column = $order_column;
        } else {
            $clone->order_column = self::DEFAULT_ORDER_COLUMN;
        }
        return $clone;
    }

    public function withOrderDirection(?string $order_direction): MailBoxQuery
    {
        $clone = clone $this;
        if (in_array($order_direction, [Order::ASC, Order::DESC])) {
            $clone->order_direction = $order_direction;
        } else {
            $clone->order_direction = self::DEFAULT_ORDER_DIRECTION;
        }
        return $clone;
    }


    /**
     * Count the number of unread mails with applied filter
     */
    public function countUnread(): int
    {
        return $this->withIsUnread(true)->count();
    }

    /**
     * Count the number of all mails with applied filter
     */
    public function count(): int
    {
        if ($this->filtered_ids === []) {
            return 0;
        }

        $query = 'SELECT COUNT(mail_id) cnt '
            . $this->getFrom()
            . $this->getWhere();

        $res = $this->db->query($query);
        if ($row = $this->db->fetchAssoc($res)) {
            return (int) $row['cnt'];
        }
        return 0;
    }

    /**
     * Get a list of mail ids
     * @return int[]
     */
    public function queryMailIds(): array
    {
        if ($this->filtered_ids === []) {
            return [];
        }

        $query = 'SELECT mail_id '
            . $this->getFrom()
            . $this->getWhere();

        $ids = [];
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $ids[] = (int) $row['mail_id'];
        }
        return $ids;
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
        if ($this->order_column === MailBoxOrderColumn::FROM) {
            // Because of the user id of automatically generated mails and ordering issues we have to do some magic
            $firstname_selection = '
				,(CASE
					WHEN (usr_id = ' . ANONYMOUS_USER_ID . ') THEN firstname 
					ELSE ' . $this->db->quote(ilMail::_getIliasMailerName(), 'text') . '
				END) fname
			';
        }

        $attachment_selection = '';

        $query = 'SELECT ' . $fields . $firstname_selection
                . $this->getFrom()
                . $this->getWhere();

        if ($this->order_column === MailBoxOrderColumn::FROM) {
            $query .= ' ORDER BY '
                    . ' fname ' . $this->order_direction . ', '
                    . ' lastname ' . $this->order_direction . ', '
                    . ' login ' . $this->order_direction . ', '
                    . ' import_name ' . $this->order_direction;
        } else {
            $query .= ' ORDER BY ' . $this->order_column->value . ' ' . $this->order_direction;
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
                isset($row['send_time']) ? new DateTimeImmutable($row['send_time']) : null,
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

    private function getFrom()
    {
        return ' FROM mail '
            . 'LEFT JOIN usr_data ON usr_id = sender_id '
            . 'AND ((sender_id > 0 AND sender_id IS NOT NULL '
            . 'AND usr_id IS NOT NULL) OR (sender_id = 0 OR sender_id IS NULL)) ';
    }

    private function getWhere(): string
    {
        $parts = [];

        // minimum condition: only mailbox of the given user
        $parts[] = 'user_id = ' . $this->db->quote($this->user_id, 'integer');

        $text_conditions = [
            [$this->sender, 'CONCAT(CONCAT(firstname, lastname), login)'],
            [$this->recipients, 'CONCAT(CONCAT(rcp_to, rcp_cc), rcp_bcc)'],
            [$this->subject, 'm_subject'],
            [$this->body, 'm_message'],
        ];

        foreach ($text_conditions as $cond) {
            if (!empty($cond[0])) {
                $parts[] = $this->db->like(
                    $cond[1],
                    'text',
                    '%%' . $cond[0] . '%%',
                    false
                );
            }
        }

        if (isset($this->folder_id)) {
            $parts[] = 'folder_id = ' . $this->db->quote($this->folder_id, 'integer');
        }

        if ($this->is_unread === true) {
            $parts[] = 'm_status = ' . $this->db->quote('unread', 'text');
        } elseif ($this->is_unread === false) {
            $parts[] = 'm_status != ' . $this->db->quote('unread', 'text');
        }

        if ($this->is_system === true) {
            $parts[] = 'sender_id = ' . $this->db->quote(ANONYMOUS_USER_ID, ilDBConstants::T_INTEGER);
        } elseif ($this->is_system === false) {
            $parts[] = 'sender_id != ' . $this->db->quote(ANONYMOUS_USER_ID, ilDBConstants::T_INTEGER);
        }

        if ($this->has_attachment === true) {
            $parts[] = '(attachments != ' . $this->db->quote(serialize(null), 'text')
                            . ' AND attachments != ' . $this->db->quote(serialize([]), 'text') . ')';
        } elseif ($this->has_attachment === false) {
            $parts[] = '(attachments = ' . $this->db->quote(serialize(null), 'text')
                            . '  OR attachments = ' . $this->db->quote(serialize([]), 'text') . ')';
        }

        if (!empty($this->period_start)) {
            $parts[] = 'send_time >= ' . $this->db->quote(
                // convert to server time zone (set by ilias initialisation)
                $this->period_start->setTimezone(new DateTimeZone(date_default_timezone_get()))
                                   ->format('Y-m-d H:i:s'),
                'timestamp'
            );
        }
        if (!empty($this->period_end)) {
            $parts[] = 'send_time <= ' . $this->db->quote(
                // convert to server time zone (set by ilias initialisation)
                $this->period_end->setTimezone(new DateTimeZone(date_default_timezone_get()))
                                 ->format('Y-m-d H:i:s'),
                'timestamp'
            );
        }

        if (!empty($this->filtered_ids)) {
            $parts[] = $this->db->in(
                'mail_id',
                $this->filtered_ids,
                false,
                'integer'
            ) . ' ';
        }

        if ($parts !== []) {
            return ' WHERE ' . implode(' AND ', $parts);
        }
        return '';
    }
}
