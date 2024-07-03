<?php

namespace ILIAS\Mail\Folder;

class MailFolderData
{
    public const TYPE_ROOT = 'root';
    public const TYPE_INBOX = 'inbox';
    public const TYPE_TRASH = 'trash';
    public const TYPE_DRAFTS = 'drafts';
    public const TYPE_SENT = 'sent';
    public const TYPE_LOCAL = 'local';
    public const TYPE_USER = 'user_folder';

    public const ALL_TYPES = [
        self::TYPE_ROOT,
        self::TYPE_INBOX,
        self::TYPE_TRASH,
        self::TYPE_DRAFTS,
        self::TYPE_SENT,
        self::TYPE_LOCAL,
        self::TYPE_USER
    ];

    public function __construct(
        private readonly int $folder_id,
        private readonly int $user_id,
        private readonly string $type,
        private readonly string $title
    ) {
    }

    public function getFolderId(): int
    {
        return $this->folder_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isInbox(): bool
    {
        return $this->type === self::TYPE_INBOX;
    }

    public function isDrafts(): bool
    {
        return $this->type === self::TYPE_DRAFTS;
    }

    public function isSent(): bool
    {
        return $this->type === self::TYPE_SENT;
    }
    public function isTrash(): bool
    {
        return $this->type === self::TYPE_TRASH;
    }

    public function isUserRootFolder(): bool
    {
        return $this->type === self::TYPE_LOCAL;
    }

    public function isUserFolder(): bool
    {
        return $this->type === self::TYPE_USER;
    }

    public function hasIncomingMails(): bool
    {
        return !$this->isDrafts() && !$this->isSent();
    }

    public function hasOutgoingMails(): bool
    {
        return $this->isDrafts() || $this->isSent();
    }


}
