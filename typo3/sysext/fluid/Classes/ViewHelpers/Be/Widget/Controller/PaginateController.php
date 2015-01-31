<?php
namespace TYPO3\CMS\Fluid\ViewHelpers\Be\Widget\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is backported from the TYPO3 Flow package "TYPO3.Fluid".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class PaginateController
 *
 */
class PaginateController extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $configuration = array('itemsPerPage' => 10, 'insertAbove' => FALSE, 'insertBelow' => TRUE, 'recordsLabel' => '');

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	protected $objects;

	/**
	 * @var integer
	 */
	protected $currentPage = 1;

	/**
	 * @var integer
	 */
	protected $numberOfPages = 1;

	/**
	 * @var integer
	 */
	protected $offset = 0;

	/**
	 * @var integer
	 */
	protected $itemsPerPage = 0;

	/**
	 * @var integer
	 */
	protected $numberOfObjects = 0;

	/**
	 * @return void
	 */
	public function initializeAction() {
		$this->objects = $this->widgetConfiguration['objects'];
		\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($this->configuration, $this->widgetConfiguration['configuration'], FALSE);
		$this->numberOfObjects = count($this->objects);
		$this->numberOfPages = ceil($this->numberOfObjects / (int)$this->configuration['itemsPerPage']);
	}

	/**
	 * @param integer $currentPage
	 * @return void
	 */
	public function indexAction($currentPage = 1) {
		// set current page
		$this->currentPage = (int)$currentPage;
		if ($this->currentPage < 1) {
			$this->currentPage = 1;
		}
		if ($this->currentPage > $this->numberOfPages) {
			// set $modifiedObjects to NULL if the page does not exist
			$modifiedObjects = NULL;
		} else {
			// modify query
			$this->itemsPerPage = (int)$this->configuration['itemsPerPage'];
			$query = $this->objects->getQuery();
			$query->setLimit($this->itemsPerPage);
			$this->offset = $this->itemsPerPage * ($this->currentPage - 1);
			if ($this->currentPage > 1) {
				$query->setOffset($this->offset);
			}
			$modifiedObjects = $query->execute();
		}
		$this->view->assign('contentArguments', array(
			$this->widgetConfiguration['as'] => $modifiedObjects
		));
		$this->view->assign('configuration', $this->configuration);
		$this->view->assign('pagination', $this->buildPagination());
	}

	/**
	 * Returns an array with the keys "current", "numberOfPages", "nextPage", "previousPage", "startRecord", "endRecord"
	 *
	 * @return array
	 */
	protected function buildPagination() {
		$endRecord = $this->offset + $this->itemsPerPage;
		if ($endRecord > $this->numberOfObjects) {
			$endRecord = $this->numberOfObjects;
		}
		$pagination = array(
			'current' => $this->currentPage,
			'numberOfPages' => $this->numberOfPages,
			'hasLessPages' => $this->currentPage > 1,
			'hasMorePages' => $this->currentPage < $this->numberOfPages,
			'startRecord' => $this->offset + 1,
			'endRecord' => $endRecord
		);
		if ($this->currentPage < $this->numberOfPages) {
			$pagination['nextPage'] = $this->currentPage + 1;
		}
		if ($this->currentPage > 1) {
			$pagination['previousPage'] = $this->currentPage - 1;
		}
		return $pagination;
	}
}
