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

/**
 * Class ilTestQuestionSetConfigFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionSetConfigFactoryTest extends ilTestBaseTestCase
{
    private ilTestQuestionSetConfigFactory $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testObj = new ilTestQuestionSetConfigFactory(
            $this->createMock(ilTree::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ILIAS\Test\Logging\TestLogger::class),
            $this->createMock(ilComponentRepository::class),
            $this->getTestObjMock(),
            $this->createMock(\ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionSetConfigFactory::class, $this->testObj);
    }
}
