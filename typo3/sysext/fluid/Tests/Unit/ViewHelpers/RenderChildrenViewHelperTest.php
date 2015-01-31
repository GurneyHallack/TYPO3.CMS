<?php
namespace TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers;

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
 * Testcase for RenderChildren ViewHelper
 */
class RenderChildrenViewHelperTest extends \TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase {

	/**
	 * @var \TYPO3\CMS\Fluid\ViewHelpers\RenderChildrenViewHelper
	 */
	protected $viewHelper;

	/**

	 */
	public function setUp() {
		$this->controllerContext = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ControllerContext', array(), array(), '', FALSE);
		$this->viewHelper = $this->getAccessibleMock('TYPO3\\CMS\\Fluid\\ViewHelpers\\RenderChildrenViewHelper', array('renderChildren'));
		$this->viewHelper->_set('controllerContext', $this->controllerContext);
	}

	/**
	 * @test
	 */
	public function renderCallsEvaluateOnTheRootNodeAndRegistersTheArguments() {
		$this->request = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\Widget\\WidgetRequest');
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->viewHelper->initializeArguments();
		$templateVariableContainer = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TemplateVariableContainer');
		$templateVariableContainer->expects($this->at(0))->method('add')->with('k1', 'v1');
		$templateVariableContainer->expects($this->at(1))->method('add')->with('k2', 'v2');
		$templateVariableContainer->expects($this->at(2))->method('remove')->with('k1');
		$templateVariableContainer->expects($this->at(3))->method('remove')->with('k2');
		$renderingContext = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\Rendering\\RenderingContextInterface');
		$renderingContext->expects($this->any())->method('getTemplateVariableContainer')->will($this->returnValue($templateVariableContainer));
		$rootNode = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode');
		$widgetContext = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\Widget\\WidgetContext');
		$this->request->expects($this->any())->method('getWidgetContext')->will($this->returnValue($widgetContext));
		$widgetContext->expects($this->any())->method('getViewHelperChildNodeRenderingContext')->will($this->returnValue($renderingContext));
		$widgetContext->expects($this->any())->method('getViewHelperChildNodes')->will($this->returnValue($rootNode));
		$rootNode->expects($this->any())->method('evaluate')->with($renderingContext)->will($this->returnValue('Rendered Results'));
		$output = $this->viewHelper->render(array('k1' => 'v1', 'k2' => 'v2'));
		$this->assertEquals('Rendered Results', $output);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\CMS\Fluid\Core\Widget\Exception\WidgetRequestNotFoundException
	 */
	public function renderThrowsExceptionIfTheRequestIsNotAWidgetRequest() {
		$this->request = $this->getMock('Tx_Fluid_MVC_Request');
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->viewHelper->initializeArguments();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @expectedException \TYPO3\CMS\Fluid\Core\Widget\Exception\RenderingContextNotFoundException
	 */
	public function renderThrowsExceptionIfTheChildNodeRenderingContextIsNotThere() {
		$this->request = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\Widget\\WidgetRequest');
		$this->controllerContext->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
		$this->viewHelper->initializeArguments();
		$widgetContext = $this->getMock('TYPO3\\CMS\\Fluid\\Core\\Widget\\WidgetContext');
		$this->request->expects($this->any())->method('getWidgetContext')->will($this->returnValue($widgetContext));
		$widgetContext->expects($this->any())->method('getViewHelperChildNodeRenderingContext')->will($this->returnValue(NULL));
		$widgetContext->expects($this->any())->method('getViewHelperChildNodes')->will($this->returnValue(NULL));
		$this->viewHelper->render();
	}
}
