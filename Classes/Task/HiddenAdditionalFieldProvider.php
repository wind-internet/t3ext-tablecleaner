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
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;

/**
 * Class HiddenAdditionalFieldProvider
 * @package MichielRoos\Tablecleaner\Task
 */
class HiddenAdditionalFieldProvider implements AdditionalFieldProviderInterface
{

    /**
     * Render additional information fields within the scheduler backend.
     *
     * @param  array $taskInfo
     * @param  Hidden $task : task object
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

        $tables = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithHiddenAndTstamp();

        // tables
        if (empty($taskInfo['hiddenTables'])) {
            $taskInfo['hiddenTables'] = [];
            if ($schedulerModule->CMD === 'add') {
                // In case of new task, set some defaults
                if (in_array('sys_log', $tables)) {
                    $taskInfo['hiddenTables'][] = 'sys_log';
                }
                if (in_array('sys_history', $tables)) {
                    $taskInfo['hiddenTables'][] = 'sys_history';
                }
                $taskInfo['markAsDeleted'] = 1;
            } elseif ($schedulerModule->CMD === 'edit') {
                // In case of editing the task, set to currently selected value
                $taskInfo['hiddenTables'] = $task->getTables();
            }
        }

        $fieldName = 'tx_scheduler[hiddenTables][]';
        $fieldId = 'task_hiddenTables';
        $fieldOptions = $this->getTableOptions($tables, $taskInfo['hiddenTables']);
        $fieldHtml =
            '<select name="' . $fieldName . '" id="' . $fieldId . '" class="wide" size="10" multiple="multiple">' .
            $fieldOptions .
            '</select>';

        $additionalFields[$fieldId] = array(
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.tables',
            'cshKey' => 'tablecleaner',
            'cshLabel' => $fieldId,
        );

        // day limit
        if (empty($taskInfo['dayLimit'])) {
            if ($schedulerModule->CMD === 'add') {
                $taskInfo['dayLimit'] = '31';
            } else {
                $taskInfo['dayLimit'] = $task->getDayLimit();
            }
        }

        $fieldId = 'task_dayLimit';
        $fieldCode = '<input type="text" name="tx_scheduler[dayLimit]" id="' .
            $fieldId . '" value="' . htmlspecialchars($taskInfo['dayLimit']) . '"/>';
        $additionalFields[$fieldId] = array(
            'code' => $fieldCode,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.hidden.dayLimit',
            'cshKey' => 'tablecleaner',
            'cshLabel' => $fieldId,
        );

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
            $fieldId . '" value="1" ' . (intval($taskInfo['markAsDeleted']) ? ' checked="checked"' : '') . '/>';
        $additionalFields[$fieldId] = array(
            'code' => $fieldCode,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.markAsDeleted',
            'cshKey' => 'tablecleaner',
            'cshLabel' => 'task_markAsDeleted',
        );

        // 'Optimize table' option
        if ($taskInfo['optimizeOption'] !== 'checked') {
            $taskInfo['optimizeOption'] = '';
            if ($schedulerModule->CMD === 'edit' && $task->getOptimizeOption()) {
                $taskInfo['optimizeOption'] = 'checked';
            }
        }

        $fieldId = 'task_optimizeOption';
        $fieldCode = '<input type="checkbox" name="tx_scheduler[optimizeOption]" id="' .
            $fieldId . '" value="checked" ' . $taskInfo['optimizeOption'] . '/>';
        $additionalFields[$fieldId] = array(
            'code' => $fieldCode,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.optimizeOption',
            'cshKey' => 'tablecleaner',
            'cshLabel' => $fieldId,
        );

        return $additionalFields;
    }

    /**
     * Build select options of available tables and set currently selected tables
     *
     * @param array $tables Available tables
     * @param array $selectedTables Selected tables
     *
     * @return string HTML of selectbox options
     */
    protected function getTableOptions(array $tables, array $selectedTables)
    {
        $options = [];

        foreach ($tables as $tableName) {
            if (in_array($tableName, $selectedTables)) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $options[] =
                '<option value="' . $tableName . '"' . $selected . '>' .
                $tableName .
                '</option>';
        }

        return implode('', $options);
    }

    /**
     * This method checks any additional data that is relevant to the specific task.
     * If the task class is not relevant, the method is expected to return TRUE.
     *
     * @param   array $submittedData : reference to the array containing the
     *    data submitted by the user
     * @param SchedulerModuleController $schedulerModule :
     *    reference to the calling object (BE module of the Scheduler)
     *
     * @return   boolean      True if validation was ok (or selected class is
     *    not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $isValid = true;

        if (is_array($submittedData['hiddenTables'])) {
            $tables = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithHiddenAndTstamp();
            foreach ($submittedData['hiddenTables'] as $table) {
                if (!in_array($table, $tables)) {
                    $isValid = false;
                    $schedulerModule->addMessage(
                        $GLOBALS['LANG']->sL(
                            'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.invalidTables'
                        ),
                        FlashMessage::ERROR
                    );
                }
            }
        } else {
            $isValid = false;
            $schedulerModule->addMessage(
                $GLOBALS['LANG']->sL('LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.noTables'),
                FlashMessage::ERROR
            );
        }

        if ($submittedData['dayLimit'] <= 0) {
            $isValid = false;
            $schedulerModule->addMessage(
                $GLOBALS['LANG']->sL(
                    'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.invalidNumberOfDays'
                ),
                FlashMessage::ERROR
            );
        }

        $submittedData['markAsDeleted'] = intval($submittedData['markAsDeleted']);

        return $isValid;
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches.
     *
     * @param   array $submittedData : array containing the data submitted by
     *    the user
     * @param   AbstractTask $task : reference to the current task object
     *
     * @return   void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        /** @var $task tx_tablecleaner_tasks_Base */
        $task->setDayLimit(intval($submittedData['dayLimit']));
        $task->setMarkAsDeleted($submittedData['markAsDeleted']);
        $task->setOptimizeOption($submittedData['optimizeOption'] === 'checked');
        $task->setTables($submittedData['hiddenTables']);
    }
}
