<?php

declare(strict_types=1);

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
 ********************************************************************
 */

/**
 * Class ilDefaultLanguageDetector
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup components/ILIAS/Language
 */
class ilDefaultLanguageDetector implements ilLanguageDetector
{
    protected ilIniFile $ini;

    public function __construct(ilIniFile $ini)
    {
        $this->ini = $ini;
    }

    /**
     * Returns the detected ISO2 language code
     */
    public function getIso2LanguageCode(): string
    {
        return $this->ini->readVariable("language", "default");
    }
}
