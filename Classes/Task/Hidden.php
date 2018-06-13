<?php
namespace MichielRoos\Tablecleaner\Task;

/**
 * ⓒ 2018 Michiel Roos <michiel@michielroos.com>
 * All rights reserved
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class Hidden
 * @package MichielRoos\Tablecleaner\Task
 */
class Hidden extends Base
{

    /**
     * Function executed from the Scheduler.
     *
     * @return boolean
     */
    public function execute()
    {
        $successfullyExecuted = true;

        foreach ($this->tables as $table) {
            $where = 'hidden = 1 AND ' . $this->getWhereClause($table);
            if ($this->markAsDeleted && in_array($table,
                    \MichielRoos\Tablecleaner\Utility\Base::getTablesWithDeletedAndTstamp())) {
                $GLOBALS['TYPO3_DB']->sql_query(sprintf(
                    'UPDATE %s SET tstamp = %s, deleted = 1 WHERE %s LIMIT %d',
                    $table,
                    $_SERVER['REQUEST_TIME'],
                    $where,
                    $this->limit
                ));
            } else {
                $GLOBALS['TYPO3_DB']->sql_query(sprintf(
                    'DELETE FROM %s WHERE %s LIMIT %d',
                    $table,
                    $where,
                    $this->limit
                ));
            }
            $error = $GLOBALS['TYPO3_DB']->sql_error();
            if ($error) {
                $successfullyExecuted = false;
            } elseif ($this->optimizeOption) {
                $GLOBALS['TYPO3_DB']->sql_query('OPTIMIZE TABLE ' . $table);
            }
        }
        return $successfullyExecuted;
    }

    /**
     * Returns some additional information about indexing progress, shown in
     * the scheduler's task overview list.
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        $string = $GLOBALS['LANG']->sL(
            'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.hidden.additionalInformation'
        );
        return sprintf($string, (int)$this->dayLimit, implode(', ', $this->tables), (int)$this->limit);
    }
}