<?php
namespace MichielRoos\Tablecleaner\Utility;

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
 * Base utility methods
 *
 * @package TYPO3
 * @subpackage tablecleaner
 * @version $Id:$
 * @license http://opensource.org/licenses/gpl-license.php
 *    GNU Public License, version 2
 */
class Base
{

    /**
     * Get tables with deleted and tstamp fields
     *
     * @return array $tables  The tables
     */
    public static function getTablesWithDeletedAndTstamp()
    {
        $tables = [];
        $resource = $GLOBALS['TYPO3_DB']->sql_query(
            "SELECT TABLE_NAME
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_NAME
			IN (
				SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE COLUMN_NAME = 'deleted'
				AND TABLE_SCHEMA =  '" . TYPO3_db . "'
			)
			AND COLUMN_NAME = 'tstamp'
			AND TABLE_SCHEMA =  '" . TYPO3_db . "'"
        );

        if ($resource instanceof \mysqli_result) {
            while (($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource))) {
                $tables[] = $result['TABLE_NAME'];
            };
        }
        return $tables;
    }

    /**
     * Get all tables with hidden and tstamp fields
     *
     * @return array $tables  The tables
     */
    public static function getTablesWithHiddenAndTstamp()
    {
        $tables = [];
        $resource = $GLOBALS['TYPO3_DB']->sql_query(
            "SELECT TABLE_NAME
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_NAME
			IN (
				SELECT TABLE_NAME
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE COLUMN_NAME = 'hidden'
				AND TABLE_SCHEMA =  '" . TYPO3_db . "'
			)
			AND COLUMN_NAME = 'tstamp'
			AND TABLE_SCHEMA =  '" . TYPO3_db . "'"
        );
        if ($resource instanceof \mysqli_result) {
            while (($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource))) {
                $tables[] = $result['TABLE_NAME'];
            };
        }
        return $tables;
    }

    /**
     * Get all tables with a parent id
     *
     * @return array $tables  The tables
     */
    public static function getTablesWithPid()
    {
        $tables = [];
        $resource = $GLOBALS['TYPO3_DB']->sql_query(
            "SELECT TABLE_NAME
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_NAME
			AND COLUMN_NAME = 'pid'
			AND TABLE_SCHEMA =  '" . TYPO3_db . "'"
        );
        if ($resource instanceof \mysqli_result) {
            while (($result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource))) {
                $tables[] = $result['TABLE_NAME'];
            };
        }
        return $tables;
    }

    /**
     * Fetch child pages
     *
     * @param integer $pageId
     *
     * @return array $pageIds
     */
    public static function fetchChildPages($pageId)
    {
        $pageIds = [];
        if (!$pageId) {
            return $pageIds;
        }
        $res = $GLOBALS['TYPO3_DB']->sql_query('SELECT uid FROM pages WHERE pid = ' . $pageId);
        $pageIds[] = $pageId;
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            $pageIds[] = $row['uid'];
            $pageIds = array_merge($pageIds, self::fetchChildPages($row['uid']));
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $pageIds;
    }

    /**
     * Fetch pages that have 'tx_tablecleaner_exclude' or
     * 'tx_tablecleaner_exclude_branch'set. If 'tx_tablecleaner_exclude_branch'
     * is set, also recursively fetch the children of that page.
     *
     * @return array $pageIds
     */
    public static function fetchExcludedPages()
    {
        $pageIds = [];

        // First fetch the pages that have 'tx_tablecleaner_exclude' set
        $res = $GLOBALS['TYPO3_DB']->sql_query('
			SELECT
				uid
			FROM
				pages
			WHERE
				tx_tablecleaner_exclude = 1;
			');
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            $pageIds[] = $row['uid'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        // Then recursively fetch the pages that have
        // 'tx_tablecleaner_exclude_branch' set
        $res = $GLOBALS['TYPO3_DB']->sql_query('
			SELECT
				uid
			FROM
				pages
			WHERE
				tx_tablecleaner_exclude_branch = 1;
			');
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            $pageIds = array_merge($pageIds, self::fetchChildPages($row['uid']));
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        $pageIds = array_unique($pageIds);

        return $pageIds;
    }

}
