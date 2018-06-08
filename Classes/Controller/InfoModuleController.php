<?php
namespace MichielRoos\Tablecleaner\Controller;

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

use MichielRoos\Tablecleaner\Domain\Model\PageRepository;
use MichielRoos\Tablecleaner\Utility\Base;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class InfoModuleController
 * @package MichielRoos\Tablecleaner
 */
class InfoModuleController extends ActionController
{

	/**
	 * @var PageRepository
	 */
	protected $pageRepository;

	/**
	 * inject Page repository
	 *
	 * @param PageRepository $pageRepository
	 * @return void
	 */
	public function injectPageRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}

	/**
	 * Index action
	 *
	 * @return void
	 */
	public function indexAction()
	{

		$uid = abs(GeneralUtility::_GP('id'));
		$values['startingPage'] = $this->pageRepository->findOneByUid($uid);

		// Initialize tree object:
		/** @var t3lib_browsetree $tree */
		$tree = GeneralUtility::makeInstance('t3lib_browsetree');
		// Also store tree prefix markup:
		$tree->expandFirst = true;
		$tree->addField('tx_tablecleaner_exclude', true);
		$tree->addField('tx_tablecleaner_exclude_branch', true);
		$tree->makeHTML = 2;
		$tree->table = 'pages';
		// Set starting page id of the tree (overrides webmounts):
		$tree->setTreeName('tablecleaner_' . $uid);
		$this->MOUNTS = $GLOBALS['WEBMOUNTS'];

		$tree->init();
		$treeData = $this->getTreeData($uid, $tree->subLevelID);
		$tree->setDataFromArray($treeData);

		$tree->getTree($uid);
		$tree->ext_IconMode = true;
		$tree->ext_showPageId = $GLOBALS['BE_USER']->getTSConfigVal('options.pageTree.showPageIdWithTitle');
		$tree->showDefaultTitleAttribute = true;
		/**
		 * Hmmm . . . need php_http module for this, can't count on that :-(
		 * parse_str($parsedUrl['query'], $urlQuery);
		 * unset($urlQuery['PM']);
		 * $parsedUrl = http_build_query($urlQuery);
		 * $tree->thisScript = http_build_url($parsedUrl);
		 */
		// Remove the PM parameter to avoid adding multiple of those to the url
		$tree->thisScript = preg_replace('/&PM=[^#$]*/', '', GeneralUtility::getIndpEnv('REQUEST_URI'));

		$tree->getBrowsableTree();

		$values['titleLength'] = intval($GLOBALS['BE_USER']->uc['titleLen']);
		$values['tree'] = $tree->tree;

		$this->view->assignMultiple($values);
	}

	/**
	 * Get tree data
	 *
	 * @param integer $uid
	 * @param string $subLevelId
	 * @return array
	 */
	protected function getTreeData($uid, $subLevelId)
	{

		// Filter the results by preference and access
		$clauseExludePidList = '';
		if ($pidList = $GLOBALS['BE_USER']->getTSConfigVal('options.hideRecords.pages')) {
			if ($pidList = $GLOBALS['TYPO3_DB']->cleanIntList($pidList)) {
				$clauseExludePidList = ' AND pages.uid NOT IN (' . $pidList . ')';
			}
		}
		$clause = ' AND ' . $GLOBALS['BE_USER']->getPagePermsClause(1) . ' ' . $clauseExludePidList;

		/**
		 * We want a page tree with all the excluded pages in there. This means
		 * all pages that have the exclude flag set and also all pages that have the
		 * excludeBranch flag set, including their children.
		 *
		 * 1). First fetch the page id's that have any exclusion options set
		 */
		$result = $GLOBALS['TYPO3_DB']->sql_query('
			SELECT GROUP_CONCAT(uid) AS uids
			FROM pages
			WHERE
				tx_tablecleaner_exclude = 1 AND
				deleted = 0 ' . $clause . ';
		');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		$excludePages = explode(',', $row['uids']);
		$GLOBALS['TYPO3_DB']->sql_free_result($result);

		$result = $GLOBALS['TYPO3_DB']->sql_query('
			SELECT GROUP_CONCAT(uid) AS uids
			FROM pages
			WHERE
				tx_tablecleaner_exclude_branch = 1 AND
				deleted = 0 ' . $clause . ';
		');
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
		$excludeBranchPages = explode(',', $row['uids']);
		$GLOBALS['TYPO3_DB']->sql_free_result($result);

		/**
		 * 2). Fetch the id's up to the 'current root' page.
		 * To build a complete page tree, we also need the parents of the
		 * excluded pages. So we merge the found pages and fetch the rootlines for
		 * all those pages.
		 */
		$allExcludedPages = array_merge($excludePages, $excludeBranchPages);
		$allExcludedPages = array_unique($allExcludedPages);

		$allUids = [];
		foreach ($allExcludedPages as $pageId) {
			// Don't fetch the rootline if the pageId is already in the list
			if (!in_array($pageId, $allUids)) {
				// Get the rootline up to the starting uid
				$rootLine = BackendUtility::BEgetRootLine($pageId, ' AND NOT uid = ' . $uid . $clause);
				foreach ($rootLine as $record) {
					$allUids[] = $record['uid'];
				}
			}
		}

		/**
		 * 3). Include self
		 */
		$allUids[] = $uid;

		/**
		 * 4). Fetch all the children of the pages that have exclude_branch set.
		 */
		foreach ($excludeBranchPages as $pageId) {
			$allUids = array_merge($allUids, Base::fetchChildPages($pageId));
		}
		$allUids = array_unique($allUids);

		$foundPages = $this->pageRepository->findByUids($allUids);
		$allPages = [];
		foreach ($foundPages as $page) {
			$allPages[$page['uid']] = $page;
		}

		$tree = $this->reassembleTree($allPages, $uid, $subLevelId);
		$rootElement[$uid] = $allPages[$uid];
		$rootElement[$uid][$subLevelId] = $tree;

		return $rootElement;
	}

	/**
	 * Assemble tree
	 *
	 * @param array $records
	 * @param integer $parentId
	 * @param string $subLevelId
	 *
	 * @return array
	 */
	protected function reassembleTree($records, $parentId, $subLevelId)
	{
		$branches = [];
		// Check if there are any children of the $parentId
		foreach ($records as $record) {
			if ($record['pid'] == $parentId) {
				$children = $this->reassembleTree($records, $record['uid'], $subLevelId);
				if ($children) {
					$branches[$record['uid']] = $record;
					$branches[$record['uid']][$subLevelId] = $children;
				} else {
					$branches[$record['uid']] = $record;
				}
				unset($records[$record['uid']]);
			}
		}
		return $branches;
	}

}
