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
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class ExpiredAdditionalFieldProvider
 */
class ExpiredAdditionalFieldProvider extends BaseAdditionalFieldProvider
{
    /**
     * Language labels
     * @var array
     */
    protected  $labels = [
        'tables' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.tables',
        'dayLimit' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.expired.dayLimit',
        'deleted' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.limit',
        'optimize' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.optimize',
    ];

    /**
     * Render additional information fields within the scheduler backend.
     *
     * @param  array $taskInfo
     * @param  Expired $task : task object
     * @param  SchedulerModuleController $schedulerModule : reference to the calling
     *    object (BE module of the Scheduler)
     *
     * @internal  param array $taksInfo : array information of task to return
     * @return  array      additional fields
     * @see interfaces/tx_scheduler_AdditionalFieldProvider#getAdditionalFields(
     *    $taskInfo, $task, $schedulerModule
     * )
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];

        $tables = $this->getTables();
        // tables
        if (empty($taskInfo['tables'])) {
            $taskInfo['tables'] = [];
            if ($schedulerModule->CMD == 'add') {
                // In case of new task, set some defaults
                if (in_array('sys_log', $tables)) {
                    $taskInfo['tables'][] = 'sys_log';
                }
                if (in_array('sys_history', $tables)) {
                    $taskInfo['tables'][] = 'sys_history';
                }
            } elseif ($schedulerModule->CMD == 'edit') {
                // In case of editing the task, set to currently selected value
                $taskInfo['tables'] = $task->getTables();
            }
        }
        $additionalFields['tables'] = $this->getTablesField($taskInfo, $task, $schedulerModule);
        $additionalFields['dayLimit'] = $this->getDayLimitField($taskInfo, $task, $schedulerModule);
        $additionalFields['limit'] = $this->getLimitField($taskInfo, $task, $schedulerModule);
        $additionalFields['optimize'] = $this->getOptimizeField($taskInfo, $task, $schedulerModule);

        return $additionalFields;
    }

    /**
     * Get all tables containing hist,log,error,cache,stat in the name AND having a tstamp column
     *
     * @return array $tables  The tables
     */
    protected function getTables()
    {
        $tables = [];
        $resource = $GLOBALS['TYPO3_DB']->sql_query(
            "SELECT DISTINCT TABLE_NAME
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE (
				(TABLE_NAME LIKE '%log%' AND NOT (TABLE_NAME LIKE '%blog%') AND NOT (TABLE_NAME LIKE '%catalog%') AND NOT (TABLE_NAME LIKE '%dialog%'))
				OR TABLE_NAME LIKE '%hist%'
				OR TABLE_NAME LIKE '%error%'
				OR TABLE_NAME LIKE '%cache%'
				OR TABLE_NAME LIKE '%stat%'
			)
			AND COLUMN_NAME = 'tstamp'
			AND TABLE_SCHEMA =  '" . TYPO3_db . "'"
        );
        if ($resource instanceof \mysqli_result) {
            while ($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource)) {
                $tables[] = $result['TABLE_NAME'];
            }
        }

        return $tables;
    }
}
