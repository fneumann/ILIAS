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
use ilMailUserCache;
use ilSearchSettings;
use ilMailSearchResult;
use ilMailLuceneSearcher;
use ilMailLuceneQueryParser;
use ILIAS\Mail\Message\MailBoxQuery;
use ILIAS\Mail\Message\MailRecordData;

class MailFolderSearch
{
    private MailBoxQuery $mailbox_query;
    private ?ilMailLuceneSearcher $lucene_searcher = null;
    private ?ilMailSearchResult $lucene_result = null;
    private ?array $filtered_ids = null;
    private ?int $count = null;
    private ?int $unread = null;

    public function __construct(
        private readonly MailFolderData $folder,
        private readonly MailFilterData $filter,
        private readonly bool $lucene_enabled,
    ) {
        global $DIC;

        $this->mailbox_query = (new MailBoxQuery(
            $DIC->database(),
            $this->folder->getUserId()
        ))
            ->withFolderId($this->folder->getFolderId())
            ->withSender($this->filter->getSender())
            ->withSubject($this->filter->getSubject())
            ->withRecipients($this->filter->getRecipients())
            ->withBody($this->filter->getBody())
            ->withPeriodStart($this->filter->getPeriodStart())
            ->withPeriodEnd($this->filter->getPeriodEnd());

        if ($this->lucene_enabled && (
            !empty($this->filter->getSender()) ||
                !empty($this->filter->getRecipients()) ||
                !empty($this->filter->getSubject()) ||
                !empty($this->filter->getBody()) ||
                !empty($this->filter->getAttachment())
        )) {
            $query_parser = new ilMailLuceneQueryParser('');
            $query_parser->setFields([
                'title' => $this->filter->getSubject(),
                'content' => $this->filter->getBody(),
                'mattachment' => $this->filter->getAttachment(),
                'msender' => $this->filter->getSender(),
            ]);
            $query_parser->parse();

            $this->lucene_result = new ilMailSearchResult();
            $this->lucene_searcher = new ilMailLuceneSearcher($query_parser, $this->lucene_result);
        }
    }

    public function getCount(): int
    {
        if (!isset($this->count)) {
            $this->count = $this->mailbox_query->withFilteredIds($this->getFilteredIds())->count();
        }
        return $this->count;
    }

    public function getUnread(): int
    {
        if (!isset($this->unread)) {
            $this->unread = $this->mailbox_query->withFilteredIds($this->getFilteredIds())->countUnread();
        }
        return $this->unread;
    }

    /**
     * @return MailRecordData[]
     */
    public function getRecords(
        int $limit,
        int $offset,
        ?string $order_column,
        ?string $order_direction
    ): array {

        $records = $this->mailbox_query
            ->withFilteredIds($this->getFilteredIds())
            ->withLimit($limit)
            ->withOffset($offset)
            ->withOrderColumn($order_column)
            ->withOrderDirection($order_direction)
            ->query(true);

        if ($this->folder->hasIncomingMails()) {
            $user_ids = [];
            foreach ($records as $record) {
                if ($record->getSenderId() && $record->getSenderId() !== ANONYMOUS_USER_ID) {
                    $user_ids[$record->getSenderId()] = $record->getSenderId();
                }
            }
            ilMailUserCache::preloadUserObjects($user_ids);
        }
        return $records;
    }

    protected function getFilteredIds(): ?array
    {
        if (!isset($this->filtered_ids)
            && isset($this->lucene_result)
            && isset($this->lucene_searcher)
        ) {
            $this->lucene_searcher->search($this->folder->getUserId(), $this->folder->getFolderId());
            $this->filtered_ids = $this->lucene_result->getIds();
        }
        return $this->filtered_ids;
    }
}
