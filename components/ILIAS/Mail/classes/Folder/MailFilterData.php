<?php

namespace ILIAS\Mail\Folder;

class MailFilterData
{
    public function __construct(
        private readonly ?string $sender,
        private readonly ?string $recipients,
        private readonly ?string $subject,
        private readonly ?string $body,
        private readonly ?string $attachment,
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


}
