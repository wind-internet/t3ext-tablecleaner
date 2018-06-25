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
 * Class Expired
 */
class Expired extends Base
{

    /**
     * Language labels
     * @var array
     */
    protected $labels = [
        'additionalInformation' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.expired.additionalInformation',
    ];

    /**
     * Function executed from the Scheduler.
     *
     * @return bool
     */
    public function execute()
    {
        $successfullyExecuted = true;

        foreach ($this->tables as $table) {
            $GLOBALS['TYPO3_DB']->sql_query(sprintf(
                'DELETE FROM %s WHERE %s LIMIT %d',
                $table,
                $this->getWhereClause($table),
                $this->limit
            ));
            $error = $this->optimizeTable($table);
            if ($error) {
                $successfullyExecuted = false;
            }
        }

        return $successfullyExecuted;
    }
}
