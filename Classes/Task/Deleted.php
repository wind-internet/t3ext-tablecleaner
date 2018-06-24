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
 * Class Deleted
 */
class Deleted extends Base
{
    /**
     * Function executed from the Scheduler.
     *
     * @return bool
     */
    public function execute()
    {
        $successfullyExecuted = true;

        foreach ($this->tables as $table) {
            $where = 'deleted = 1 AND ' . $this->getWhereClause($table);
            $GLOBALS['TYPO3_DB']->sql_query(sprintf(
                'DELETE FROM %s WHERE %s LIMIT %d',
                $table,
                $where,
                $this->limit
            ));
            $error = $GLOBALS['TYPO3_DB']->sql_error();
            if (!$error && $this->optimizeOption) {
                $GLOBALS['TYPO3_DB']->sql_query('OPTIMIZE TABLE ' . $table);
                $error = $GLOBALS['TYPO3_DB']->sql_error();
            }
            if ($error) {
                $successfullyExecuted = false;
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
            'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.deleted.additionalInformation'
        );

        return sprintf($string, (int)$this->dayLimit, implode(', ', $this->tables), (int)$this->limit);
    }
}
