<?php
namespace MichielRoos\Tablecleaner\Task;

/*****************************************************************************
 *  Copyright notice
 *
 *  ⓒ 2013 Michiel Roos <michiel@maxserv.nl>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is free
 *  software; you can redistribute it and/or modify it under the terms of the
 *  GNU General Public License as published by the Free Software Foundation;
 *  either version 2 of the License, or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *  or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 *  more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ****************************************************************************/

use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class Base
 */
class Base extends AbstractTask
{
    /**
     * Array of tables
     *
     * @var array
     */
    protected $tables;

    /**
     * Days
     *
     * @var int
     */
    protected $dayLimit;

    /**
     * Maximum amount of rows to remove per run
     *
     * @var int
     */
    protected $limit;

    /**
     * Optimize table option
     *
     * @var bool
     */
    protected $optimizeOption;

    /**
     * Mark as deleted
     *
     * @var bool
     */
    protected $markAsDeleted;

    /**
     * Language labels
     * @var array
     */
    protected $labels = [
        'additionalInformation' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.deleted.additionalInformation',
    ];

    /**
     * Get the value of the protected property tables.
     *
     * @return array of tables
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Set the value of the private property tables.
     *
     * @param array $tables
     *
     * @return void
     */
    public function setTables($tables)
    {
        $this->tables = $tables;
    }

    /**
     * Get the value of the protected property limit.
     *
     * @return int limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the value of the private property limit.
     *
     * @param int $limit Maximum amount of rows to remove per run
     *
     * @return void
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * Get the value of the protected property dayLimit.
     *
     * @return int dayLimit
     */
    public function getDayLimit()
    {
        return $this->dayLimit;
    }

    /**
     * Set the value of the private property dayLimit.
     *
     * @param int $dayLimit Number of days after which to remove the records
     *
     * @return void
     */
    public function setDayLimit($dayLimit)
    {
        $this->dayLimit = $dayLimit;
    }

    /**
     * Get the value of the protected property optimizeOption.
     *
     * @return int optimizeOption
     */
    public function getOptimizeOption()
    {
        return $this->optimizeOption;
    }

    /**
     * Set the value of the private property optimizeOption.
     *
     * @param int $optimizeOption Number of days after which to remove the records
     *
     * @return void
     */
    public function setOptimizeOption($optimizeOption)
    {
        $this->optimizeOption = $optimizeOption;
    }

    /**
     * @param bool $markAsDeleted
     *
     * @return $this to allow for chaining
     */
    public function setMarkAsDeleted($markAsDeleted)
    {
        $this->markAsDeleted = $markAsDeleted;

        return $this;
    }

    /**
     * @return bool
     */
    public function getMarkAsDeleted()
    {
        return $this->markAsDeleted;
    }

    /**
     * Get the where clause
     *
     * @param $table
     *
     * @return string
     */
    public function getWhereClause($table)
    {
        $excludePages = \MichielRoos\Tablecleaner\Utility\Base::fetchExcludedPages();
        $tablesWithPid = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithPid();
        $where = ' tstamp < ' . strtotime('-' . (int)$this->dayLimit . 'days');
        if (!empty($excludePages) && in_array($table, $tablesWithPid)) {
            if ($table === 'pages') {
                $where .= ' AND NOT uid IN(' . implode(',', $excludePages) . ')';
            } else {
                $where .= ' AND NOT pid IN(' . implode(',', $excludePages) . ')';
            }
        }

        return $where;
    }

    /**
     * Optimize table
     *
     * @param $table
     * @return string
     */
    public function optimizeTable($table)
    {
        $error = $GLOBALS['TYPO3_DB']->sql_error();
        if (!$error && $this->optimizeOption) {
            $GLOBALS['TYPO3_DB']->sql_query('OPTIMIZE TABLE ' . $table);
            $error = $GLOBALS['TYPO3_DB']->sql_error();
        }
        return $error;
    }
    /**
     * This is the main method that is called when a task is executed
     * It MUST be implemented by all classes inheriting from this one
     * Note that there is no error handling, errors and failures are expected
     * to be handled and logged by the client implementations.
     * Should return true on successful execution, false on error.
     *
     * @return bool   Returns true on successful execution, false on error
     */
    public function execute()
    {
        return true;
    }

    /**
     * Returns some additional information about the task in scheduler overview.
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        return sprintf(
            $GLOBALS['LANG']->sL($this->labels['additionalInformation']),
            (int)$this->dayLimit,
            implode(', ', $this->tables),
            (int)$this->limit
        );
    }
}
