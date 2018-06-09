<?php
defined('TYPO3_MODE') || die('¯\_(ツ)_/¯');

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\MichielRoos\Tablecleaner\Task\Deleted::class] = [
    'extension' => 'tablecleaner',
    'title' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.deleted.title',
    'description' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.deleted.description',
    'additionalFields' => \MichielRoos\Tablecleaner\Task\DeletedAdditionalFieldProvider::class
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\MichielRoos\Tablecleaner\Task\Hidden::class] = [
    'extension' => 'tablecleaner',
    'title' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.hidden.title',
    'description' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.hidden.description',
    'additionalFields' => \MichielRoos\Tablecleaner\Task\HiddenAdditionalFieldProvider::class
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\MichielRoos\Tablecleaner\Task\Expired::class] = [
    'extension' => 'tablecleaner',
    'title' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.expired.title',
    'description' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf:tasks.expired.description',
    'additionalFields' => \MichielRoos\Tablecleaner\Task\ExpiredAdditionalFieldProvider::class
];
