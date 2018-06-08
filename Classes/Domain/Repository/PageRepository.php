<?php
namespace MichielRoos\Tablecleaner\Domain\Repository;

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

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class PageRepository
 * @package MichielRoos\Tablecleaner\Domain\Repository
 */
class PageRepository extends Repository
{

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'sorting' => QueryInterface::ORDER_ASCENDING
	);

	/**
	 * Initialize repository
	 *
	 * @return void
	 */
	public function initializeObject()
	{
		$defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
		$defaultQuerySettings->setIgnoreEnableFields(true);
		$defaultQuerySettings->setRespectStoragePage(false);
		$this->setDefaultQuerySettings($defaultQuerySettings);
	}

	/**
	 * Find by list of uids
	 *
	 * @param array $ids
	 *
	 * @return array
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
	 */
	public function findByUids($ids)
	{
		$query = $this->createQuery();

		return $query->matching(
			$query->logicalAnd(
				$query->in('uid', $ids),
				$query->equals('deleted', 0)
			)
		)
			->execute();
	}
}
