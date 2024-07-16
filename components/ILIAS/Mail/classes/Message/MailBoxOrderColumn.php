<?php

namespace ILIAS\Mail\Message;

enum MailBoxOrderColumn: string
{
    case FROM = 'from';
    case STATUS = 'm_status';
    case SUBJECT= 'm_subject';
    case SEND_TIME = 'send_time';
    case RCP_TO = 'rcp_to';
    case ATTACHMENTS = 'attachments';

}