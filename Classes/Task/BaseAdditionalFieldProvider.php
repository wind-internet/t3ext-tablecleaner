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
 * Class BaseAdditionalFieldProvider
 */
class BaseAdditionalFieldProvider implements AdditionalFieldProviderInterface
{
    /**
     * Language labels
     * @var array
     */
    protected $labels = [
        'tables' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.tables',
        'dayLimit' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.deleted.dayLimit',
        'deleted' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.limit',
        'optimize' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.optimize',
    ];

    /**
     * Context sensitive help id's
     * @var array
     */
    protected $contextSensitiveHelpIds = [
        'tables' => 'deletedTables',
        'dayLimit' => 'deletedDayLimit',
        'limit' => 'limit',
        'optimize' => 'optimize',
    ];

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
    )
    {
        $additionalFields = [];

        // tables
        if (empty($taskInfo['tables'])) {
            $taskInfo['tables'] = [];
            if ($schedulerModule->CMD === 'add') {
                // In case of new task, set some defaults
                $tablesWithDeleted = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithDeletedAndTstamp();
                if (in_array('sys_log', $tablesWithDeleted)) {
                    $taskInfo['tables'][] = 'sys_log';
                }
                if (in_array('sys_history', $tablesWithDeleted)) {
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
     * Get dayLimit field
     *
     * @param array $taskInfo
     * @param $task
     * @param SchedulerModuleController $schedulerModule
     * @return array
     */
    protected function getDayLimitField(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    )
    {
        if (empty($taskInfo['dayLimit'])) {
            if ($schedulerModule->CMD == 'add') {
                $taskInfo['dayLimit'] = '31';
            } else {
                $taskInfo['dayLimit'] = $task->getDayLimit();
            }
        }

        $fieldCode = '<input type="text" name="tx_scheduler[dayLimit]" id="' . $this->contextSensitiveHelpIds['dayLimit'] . '" value="' . htmlspecialchars($taskInfo['dayLimit']) . '"/>';
        return [
            'code' => $fieldCode,
            'label' => $this->labels['dayLimit'],
            'cshKey' => 'tablecleaner',
            'cshLabel' => $this->contextSensitiveHelpIds['dayLimit'],
        ];
    }

    /**
     * Get limit field
     *
     * @param array $taskInfo
     * @param $task
     * @param SchedulerModuleController $schedulerModule
     * @return array
     */
    protected function getLimitField(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    )
    {
        if (empty($taskInfo['limit'])) {
            if ($schedulerModule->CMD == 'add') {
                $taskInfo['limit'] = '10000';
            } else {
                $taskInfo['limit'] = $task->getLimit();
            }
        }

        $fieldCode = '<input type="text" name="tx_scheduler[limit]" id="' . $this->contextSensitiveHelpIds['dayLimit'] . '" value="' . htmlspecialchars($taskInfo['limit']) . '"/>';
        return [
            'code' => $fieldCode,
            'label' => $this->labels['deleted'],
            'cshKey' => 'tablecleaner',
            'cshLabel' => $this->contextSensitiveHelpIds['limit'],
        ];
    }

    /**
     * Get optimize field
     *
     * @param array $taskInfo
     * @param $task
     * @param SchedulerModuleController $schedulerModule
     * @return array
     */
    protected function getOptimizeField(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    )
    {
        if ($taskInfo['optimize'] !== 'checked') {
            $taskInfo['optimize'] = '';
            if ($schedulerModule->CMD === 'edit' && $task->getOptimizeOption()) {
                $taskInfo['optimize'] = 'checked';
            }
        }

        $fieldCode = '<input type="checkbox" name="tx_scheduler[optimize]" id="' . $this->contextSensitiveHelpIds['dayLimit'] . '" value="checked" ' . (int)$taskInfo['optimize'] . '/>';
        return [
            'code' => $fieldCode,
            'label' => $this->labels['optimize'],
            'cshKey' => 'tablecleaner',
            'cshLabel' => $this->contextSensitiveHelpIds['optimize'],
        ];
    }

    /**
     * Get tables field
     *
     * @param array $taskInfo
     * @param $task
     * @param SchedulerModuleController $schedulerModule
     * @return array
     */
    protected function getTablesField(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    )
    {
        $fieldName = 'tx_scheduler[tables][]';
        $fieldOptions = $this->getTableOptions($taskInfo['tables']);
        $fieldHtml =
            '<select name="' . $fieldName . '" id="' . $this->contextSensitiveHelpIds['dayLimit'] . '" class="wide" size="10" multiple="multiple">' .
            $fieldOptions .
            '</select>';

        return [
            'code' => $fieldHtml,
            'label' => $this->labels['tables'],
            'cshKey' => 'tablecleaner',
            'cshLabel' => $this->contextSensitiveHelpIds['tables'],
        ];
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

        if (is_array($submittedData['tables'])) {
            $tables = \MichielRoos\Tablecleaner\Utility\Base::getTablesWithDeletedAndTstamp();
            foreach ($submittedData['tables'] as $table) {
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

        if ($submittedData['dayLimit'] <= 0) {
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

        if (array_key_exists('markAsDeleted', $submittedData)) {
            $submittedData['markAsDeleted'] = (int)$submittedData['markAsDeleted'];
        }

        return $isValid;
    }

    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches.
     *
     * @param   array $submittedData : array containing the data submitted by the user
     * @param   AbstractTask $task : reference to the current task object
     *
     * @return   void
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        /** @var $task Base */
        $task->setDayLimit((int)$submittedData['dayLimit']);
        $task->setLimit((int)$submittedData['limit']);
        if (array_key_exists('markAsDeleted', $submittedData)) {
            $task->setMarkAsDeleted($submittedData['markAsDeleted']);
        }
        $task->setOptimizeOption($submittedData['optimize'] === 'checked');
        $task->setTables($submittedData['hiddenTables']);
    }
}
