<?php

declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data;

use ILIAS\Refinery\ConstraintViolationException;
use PHPUnit\Framework\TestCase;

require_once("vendor/composer/vendor/autoload.php");

class PositiveIntegerTest extends TestCase
{
    /**
     * @throws ConstraintViolationException
     */
    public function testCreatePositiveInteger(): void
    {
        $integer = new PositiveInteger(6);
        $this->assertSame(6, $integer->getValue());
    }

    public function testNegativeIntegerThrowsException(): void
    {
        $this->expectNotToPerformAssertions();

        try {
            $integer = new PositiveInteger(-6);
        } catch (ConstraintViolationException $exception) {
            return;
        }
        $this->fail();
    }

    /**
     * @throws ConstraintViolationException
     */
    public function testMaximumIntegerIsAccepted(): void
    {
        $integer = new PositiveInteger(PHP_INT_MAX);
        $this->assertSame(PHP_INT_MAX, $integer->getValue());
    }
}
