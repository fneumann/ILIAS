<?php

namespace ILIAS\Mail\Folder;

use DateTimeImmutable;

class MailFilterData
{
    public function __construct(
        private readonly ?string $sender,
        private readonly ?string $recipients,
        private readonly ?string $subject,
        private readonly ?string $body,
        private readonly ?string $attachment,
        private readonly ?DateTimeImmutable $period_start,
        private readonly ?DateTimeImmutable $period_end,
    ) {
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function getRecipients(): ?string
    {
        return $this->recipients;
    }


    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function getPeriodStart(): ?DateTimeImmutable
    {
        return $this->period_start;
    }

    public function getPeriodEnd(): ?DateTimeImmutable
    {
        return $this->period_end;
    }

}
