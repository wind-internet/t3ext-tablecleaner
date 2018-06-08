<?php
defined('TYPO3_MODE') || die('¯\_(ツ)_/¯');

if (TYPO3_MODE === 'BE') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', [
		'tx_tablecleaner_exclude' => [
			'exclude' => true,
			'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang_db.xml:pages.tx_tablecleaner_exclude',
			'config' => [
				'type' => 'check',
				'default' => 0,
				'items' => [
					['LLL:EXT:lang/locallang_core.xlf:labels.enabled', 1]
				]
			]
		],
		'tx_tablecleaner_exclude_branch' => [
			'exclude' => true,
			'label' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang_db.xml:pages.tx_tablecleaner_exclude_branch',
			'config' => [
				'type' => 'check',
				'default' => 0,
				'items' => [
					['LLL:EXT:lang/locallang_core.xlf:labels.enabled', 1]
				]
			]
		]
	]);

	if (isset($GLOBALS['TCA']['pages']['palettes']['visibility'])) {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'visibility',
			'tx_tablecleaner_exclude', 'after:nav_hide');
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'visibility',
			'tx_tablecleaner_exclude_branch',
			'after:tx_tablecleaner_exclude');
	} else {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_tablecleaner_exclude', '',
			'after:nav_hide');
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_tablecleaner_exclude_branch',
			'', 'after:tx_tablecleaner_exclude');
	}

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tablecleaner',
		'EXT:tablecleaner/Resources/Private/Language/ContextSensitiveHelp.xml');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('pages',
		'EXT:tablecleaner/Resources/Private/Language/ContextSensitiveHelpPages.xml');

	/**
	 * Register the Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
	// Extension name
		'MichielRoos.tablecleaner',
		// Place in section
		'web',
		// Module name
		'InfoModule',
		// Position
		'after:info',
		// An array holding the controller-action-combinations that are accessible
		// The first controller and its first action will be the default
		[
			'InfoModule' => 'index'
		],
		[
			'access' => 'user,group',
			'icon' => 'EXT:tablecleaner/ext_icon.gif',
			'labels' => 'LLL:EXT:tablecleaner/Resources/Private/Language/locallang.xlf',
		]
	);
}
