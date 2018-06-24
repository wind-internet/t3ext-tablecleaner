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
 * Class DeletedAdditionalFieldProvider
 */
class DeletedAdditionalFieldProvider implements AdditionalFieldProviderInterface
{
    /**
     * Render additional information fields within the scheduler backend.
     *
     * @param  array $taskInfo
     * @param  Deleted $task : task object
     * @param  SchedulerModuleController $schedulerModule : reference to the calling
     *    object (BE module of the Scheduler)
     *
     * @internal  param array $taksInfo : array information of task to return
     * @return  array      additional fields
     * @see interfaces/tx_scheduler_AdditionalFieldProvider#getAdditionalFields(
     *    $taskInfo, $task, $schedulerModule
     * )
     */
    public function getAdditionalFields(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    ) {
        $additionalFields = [];

        // tables
        if (empty($taskInfo['deletedTables'])) {
            $taskInfo['deletedTables'] = [];
            if ($schedulerModule->CMD === 'add') {
                // In case of new task, set some defaults
                $tablesWithDeleted = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithDeletedAndTstamp();
                if (in_array('sys_log', $tablesWithDeleted)) {
                    $taskInfo['deletedTables'][] = 'sys_log';
                }
                if (in_array('sys_history', $tablesWithDeleted)) {
                    $taskInfo['deletedTables'][] = 'sys_history';
                }
            } elseif ($schedulerModule->CMD == 'edit') {
                // In case of editing the task, set to currently selected value
                $taskInfo['deletedTables'] = $task->getTables();
            }
        }

        $fieldName = 'tx_scheduler[deletedTables][]';
        $fieldId = 'task_deletedTables';
        $fieldOptions = $this->getTableOptions($taskInfo['deletedTables']);
        $fieldHtml =
            '<select name="' . $fieldName . '" id="' . $fieldId . '" class="wide" size="10" multiple="multiple">' .
            $fieldOptions .
            '</select>';

        $additionalFields[$fieldId] = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.tables',
            'cshKey' => 'tablecleaner',
            'cshLabel' => $fieldId,
        ];

        // daylimit
        if (empty($taskInfo['deletedDayLimit'])) {
            if ($schedulerModule->CMD == 'add') {
                $taskInfo['deletedDayLimit'] = '31';
            } else {
                $taskInfo['deletedDayLimit'] = $task->getDayLimit();
            }
        }

        $fieldId = 'task_deletedDayLimit';
        $fieldCode = '<input type="text" name="tx_scheduler[deletedDayLimit]" id="' .
            $fieldId . '" value="' . htmlspecialchars($taskInfo['deletedDayLimit']) . '"/>';
        $additionalFields[$fieldId] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.deleted.dayLimit',
            'cshKey' => 'tablecleaner',
            'cshLabel' => $fieldId,
        ];

        // limit
        if (empty($taskInfo['limit'])) {
            if ($schedulerModule->CMD == 'add') {
                $taskInfo['limit'] = '10000';
            } else {
                $taskInfo['limit'] = $task->getLimit();
            }
        }

        $fieldId = 'task_limit';
        $fieldCode = '<input type="text" name="tx_scheduler[limit]" id="' .
            $fieldId . '" value="' . htmlspecialchars($taskInfo['limit']) . '"/>';
        $additionalFields[$fieldId] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.deleted.limit',
            'cshKey' => 'tablecleaner',
            'cshLabel' => $fieldId,
        ];

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
        $additionalFields[$fieldId] = [
            'code' => $fieldCode,
            'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.optimizeOption',
            'cshKey' => 'tablecleaner',
            'cshLabel' => $fieldId,
        ];

        return $additionalFields;
    }

    /**
     * Build select options of available tables and set currently selected tables
     *
     * @param array $selectedTables Selected tables
     *
     * @return string HTML of selectbox options
     */
    protected function getTableOptions(array $selectedTables)
    {
        $options = [];

        $availableTables = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithDeletedAndTstamp();
        foreach ($availableTables as $tableName) {
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
     * @param array $submittedData : reference to the array containing the data
     *    submitted by the user
     * @param SchedulerModuleController :
     *    reference to the calling object (BE module of the Scheduler)
     *
     * @return bool True if validation was ok (or selected class is not
     *    relevant), FALSE otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $isValid = true;

        if (is_array($submittedData['deletedTables'])) {
            $tables = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithDeletedAndTstamp();
            foreach ($submittedData['deletedTables'] as $table) {
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
                $GLOBALS['LANG']->sL(
                    'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.noTables'
                ),
                FlashMessage::ERROR
            );
        }

        if ($submittedData['deletedDayLimit'] <= 0) {
            $isValid = false;
            $schedulerModule->addMessage(
                $GLOBALS['LANG']->sL(
                    'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.invalidNumberOfDays'
                ),
                FlashMessage::ERROR
            );
        }

        if (!MathUtility::canBeInterpretedAsInteger($submittedData['limit'])) {
            $isValid = false;
            $schedulerModule->addMessage(
                $GLOBALS['LANG']->sL(
                    'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.general.invalidLimit'
                ),
                FlashMessage::ERROR
            );
        }

        return $isValid;
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches.
     *
     * @param array $submittedData : array containing the data submitted by the user
     * @param AbstractTask $task : reference to the current task object
     *
     * @return   void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        /** @var $task Base */
        $task->setDayLimit((int)$submittedData['deletedDayLimit']);
        $task->setLimit((int)$submittedData['limit']);
        $task->setOptimizeOption($submittedData['optimizeOption'] === 'checked');
        $task->setTables($submittedData['deletedTables']);
    }
}
