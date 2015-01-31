<?php
namespace TYPO3\CMS\Fluid\Core\Widget;

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
 * Represents a widget request.
 */
class WidgetRequest extends \TYPO3\CMS\Extbase\Mvc\Web\Request {

	/**
	 * @var \TYPO3\CMS\Fluid\Core\Widget\WidgetContext
	 */
	protected $widgetContext;

	/**
	 * @return \TYPO3\CMS\Fluid\Core\Widget\WidgetContext
	 */
	public function getWidgetContext() {
		return $this->widgetContext;
	}

	/**
	 * @param \TYPO3\CMS\Fluid\Core\Widget\WidgetContext $widgetContext
	 * @return void
	 */
	public function setWidgetContext(\TYPO3\CMS\Fluid\Core\Widget\WidgetContext $widgetContext) {
		$this->widgetContext = $widgetContext;
		$this->setControllerObjectName($widgetContext->getControllerObjectName());
	}

	/**
	 * Returns the unique URI namespace for this widget in the format pluginNamespace[widgetIdentifier]
	 *
	 * @return string
	 */
	public function getArgumentPrefix() {
		return $this->widgetContext->getParentPluginNamespace() . '[' . $this->widgetContext->getWidgetIdentifier() . ']';
	}
}
