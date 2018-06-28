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

use MichielRoos\Tablecleaner\Task\DeletedAdditionalFieldProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 */
class DeletedAdditionalFieldProviderTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{
    /**
     * @var DeletedAdditionalFieldProvider
     */
    protected $subject;

    protected function setUp()
    {
        $this->subject = GeneralUtility::makeInstance(DeletedAdditionalFieldProvider::class);
    }

    /**
     * @test
     */
    public function canBeInstantiated()
    {
        static::assertInstanceOf(DeletedAdditionalFieldProvider::class, $this->subject);
    }

    /**
     * @test
     * @dataProvider getTableOptionsDataProvider
     * @param mixed $availableTables
     * @param mixed $selectedTables
     * @param mixed $expected
     */
    public function getTableOptionsReturnsOptionList($availableTables, $selectedTables, $expected)
    {
        $className = $this->buildAccessibleProxy(DeletedAdditionalFieldProvider::class);
        $class = GeneralUtility::makeInstance($className);
        static::assertSame($expected, $class->_call('getTableOptions', $availableTables, $selectedTables));
    }

    public function getTableOptionsDataProvider()
    {
        return [
            'pages with pages selected' => [['pages'], ['pages'], '<option value="pages" selected="selected">pages</option>'],
            'pages and sys log with pages selected' => [['pages', 'sys_log'], ['pages'], '<option value="pages" selected="selected">pages</option><option value="sys_log">sys_log</option>'],
            'pages and sys log with sys_log selected' => [['pages', 'sys_log'], ['sys_log'], '<option value="pages">pages</option><option value="sys_log" selected="selected">sys_log</option>'],
        ];
    }
}
