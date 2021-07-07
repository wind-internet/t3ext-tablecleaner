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
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

/**
 * Class HiddenAdditionalFieldProvider
 */
class HiddenAdditionalFieldProvider extends BaseAdditionalFieldProvider
{
    /**
     * Render additional information fields within the scheduler backend.
     *
     * @param  array $taskInfo
     * @param  Hidden $task : task object
     * @param  SchedulerModuleController $schedulerModule : reference to the calling object (BE module of the Scheduler)
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

        $tables = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithHiddenAndTstamp();

        // tables
        if (empty($taskInfo['tables'])) {
            $taskInfo['tables'] = [];
            if ($schedulerModule->CMD === 'add') {
                // In case of new task, set some defaults
                if (in_array('sys_log', $tables)) {
                    $taskInfo['tables'][] = 'sys_log';
                }
                if (in_array('sys_history', $tables)) {
                    $taskInfo['tables'][] = 'sys_history';
                }
                $taskInfo['markAsDeleted'] = 1;
            } elseif ($schedulerModule->CMD === 'edit') {
                // In case of editing the task, set to currently selected value
                $taskInfo['tables'] = $task->getTables();
            }
        }

        $additionalFields['tables'] = $this->getTablesField($taskInfo, $task, $schedulerModule);
        $additionalFields['dayLimit'] = $this->getDayLimitField($taskInfo, $task, $schedulerModule);

        // Don't delete, but mark as deleted
        if (empty($taskInfo['markAsDeleted'])) {
            if ($schedulerModule->CMD === 'add') {
                $taskInfo['markAsDeleted'] = '';
            } else {
                $taskInfo['markAsDeleted'] = $task->getMarkAsDeleted();
            }
        }

        $fieldId = 'task_markAsDeleted';
        $fieldCode = '<input type="checkbox" name="tx_scheduler[markAsDeleted]" id="' .
            $fieldId . '" value="1" ' . ((int)$taskInfo['markAsDeleted'] ? ' checked="checked"' : '') . '/>';
        $additionalFields[$fieldId] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.hidden.markAsDeleted',
            'cshKey' => 'tablecleaner',
            'cshLabel' => 'task_markAsDeleted',
        ];

        $additionalFields['limit'] = $this->getLimitField($taskInfo, $task, $schedulerModule);
        $additionalFields['optimize'] = $this->getOptimizeField($taskInfo, $task, $schedulerModule);

        return $additionalFields;
    }
}
