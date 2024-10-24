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
 * Class ilTestRandomQuestionSetStagingPoolQuestionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetStagingPoolQuestionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetStagingPoolQuestion $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetStagingPoolQuestion($this->createMock(ilDBInterface::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetStagingPoolQuestion::class, $this->testObj);
    }

    public function testTestId(): void
    {
        $testId = 5;
        $this->testObj->setTestId($testId);
        $this->assertEquals($testId, $this->testObj->getTestId());
    }

    public function testPoolId(): void
    {
        $poolId = 5;
        $this->testObj->setPoolId($poolId);
        $this->assertEquals($poolId, $this->testObj->getPoolId());
    }

    public function testQuestionId(): void
    {
        $questionId = 5;
        $this->testObj->setQuestionId($questionId);
        $this->assertEquals($questionId, $this->testObj->getQuestionId());
    }
}
