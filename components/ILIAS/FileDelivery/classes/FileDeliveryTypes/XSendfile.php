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

namespace ILIAS\FileDelivery\FileDeliveryTypes;

use ILIAS\FileDelivery\ilFileDeliveryType;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\HTTP\Services;

/**
 * Class XSendfile
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 */
final class XSendfile implements ilFileDeliveryType
{
    use HeaderBasedDeliveryHelper;
    public const X_SENDFILE = 'X-Sendfile';
    public const X_SENDFILE_TEMPORARY = 'X-Sendfile-Temporary';
    private \ILIAS\HTTP\Services $httpService;


    /**
     * PHP constructor.
     *
     * @param Services $httpState
     *
     */
    public function __construct(Services $httpState)
    {
        $this->httpService = $httpState;
    }


    /**
     * @inheritDoc
     */
    public function doesFileExists(string $path_to_file): bool
    {
        return is_readable($path_to_file);
    }


    /**
     * @inheritdoc
     */
    public function prepare(string $path_to_file, ?FileStream $possible_stream): bool
    {
        //	Nothing has to be done here
        return true;
    }


    /**
     * @inheritdoc
     */
    public function deliver(string $path_to_file, bool $file_marked_to_delete): void
    {
        $delivery = function () use ($path_to_file): void {
            $response = $this->httpService->response()
                ->withHeader(self::X_SENDFILE, realpath($path_to_file));
            $this->httpService->saveResponse($response);
            $this->httpService->sendResponse();
        };

        if ($file_marked_to_delete) {
            $this->sendFileUnbufferedUsingHeaders($delivery);
        } else {
            $delivery();
        }
    }


    /**
     * @inheritdoc
     */
    public function supportsInlineDelivery(): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function supportsAttachmentDelivery(): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function supportsStreaming(): bool
    {
        return true;
    }


    /**
     * @inheritdoc
     */
    public function handleFileDeletion(string $path_to_file): bool
    {
        return unlink($path_to_file);
    }
}
