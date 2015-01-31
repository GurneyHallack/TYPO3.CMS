<?php
namespace TYPO3\CMS\Extbase\Tests\Unit\Property\TypeConverter;

/*
 * This file is part of the TYPO3 CMS project.
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

use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter;

/**
 * Test case
 */
class ObjectConverterTest extends UnitTestCase {

	/**
	 * @var ObjectConverter
	 */
	protected $converter;

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockReflectionService;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockObjectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\Container\Container|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mockContainer;

	/**
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 */
	public function setUp() {
		$this->mockReflectionService = $this->getMock('TYPO3\CMS\Extbase\Reflection\ReflectionService');
		$this->mockObjectManager = $this->getMock('TYPO3\CMS\Extbase\Object\ObjectManagerInterface');
		$this->mockContainer = $this->getMock('\TYPO3\CMS\Extbase\Object\Container\Container');

		$this->converter = new ObjectConverter();
		$this->inject($this->converter, 'reflectionService', $this->mockReflectionService);
		$this->inject($this->converter, 'objectManager', $this->mockObjectManager);
		$this->inject($this->converter, 'objectContainer', $this->mockContainer);
	}

	/**
	 * @test
	 */
	public function checkMetadata() {
		$this->assertEquals(array('array'), $this->converter->getSupportedSourceTypes(), 'Source types do not match');
		$this->assertEquals('object', $this->converter->getSupportedTargetType(), 'Target type does not match');
		$this->assertEquals(0, $this->converter->getPriority(), 'Priority does not match');
	}

	/**
	 * @return array
	 */
	public function dataProviderForCanConvert() {
		return array(
			// Is entity => cannot convert
			array('TYPO3\\CMS\\Extbase\\Tests\\Fixture\\Entity', FALSE),
			// Is valueobject => cannot convert
			array('TYPO3\\CMS\\Extbase\\Tests\\Fixture\\ValueObject', FALSE),
			// Is no entity and no value object => can convert
			array('stdClass', TRUE)
		);
	}

	/**
	 * @test
	 * @dataProvider dataProviderForCanConvert
	 * @param $className
	 * @param $expected
	 */
	public function canConvertFromReturnsTrueIfClassIsTaggedWithEntityOrValueObject($className, $expected) {
		$this->assertEquals($expected, $this->converter->canConvertFrom('myInputData', $className));
	}

	/**
	 * @test
	 */
	public function getTypeOfChildPropertyShouldUseReflectionServiceToDetermineType() {
		$this->mockReflectionService->expects($this->any())->method('hasMethod')->with('TheTargetType', 'setThePropertyName')->will($this->returnValue(FALSE));
		$this->mockReflectionService->expects($this->any())->method('getMethodParameters')->with('TheTargetType', '__construct')->will($this->returnValue(array(
			'thePropertyName' => array(
				'type' => 'TheTypeOfSubObject',
				'elementType' => NULL
			)
		)));
		$this->mockContainer->expects($this->any())->method('getImplementationClassName')->will($this->returnValue('TheTargetType'));

		$configuration = new PropertyMappingConfiguration();
		$configuration->setTypeConverterOptions('TYPO3\CMS\Extbase\Property\TypeConverter\ObjectConverter', array());
		$this->assertEquals('TheTypeOfSubObject', $this->converter->getTypeOfChildProperty('TheTargetType', 'thePropertyName', $configuration));
	}

}
