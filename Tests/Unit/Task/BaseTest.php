<?php
namespace MichielRoos\Tablecleaner\Tests\Unit\Task;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use MichielRoos\Tablecleaner\Task\Base;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 */
class BaseTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var Base
     */
    protected $subject;

    protected function setUp()
    {
        $this->subject = GeneralUtility::makeInstance(Base::class);
    }

    /**
     * @test
     */
    public function canBeInstantiated()
    {
        static::assertInstanceOf(Base::class, $this->subject);
    }

    /**
     * @test
     */
    public function getTablesInitiallyReturnsNull()
    {
        static::assertNull($this->subject->getTables());
    }
}
