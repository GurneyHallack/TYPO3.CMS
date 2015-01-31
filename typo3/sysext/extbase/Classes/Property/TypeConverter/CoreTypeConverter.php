<?php
namespace TYPO3\CMS\Extbase\Property\TypeConverter;

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

use TYPO3\CMS\Core\Type\Exception\InvalidValueExceptionInterface;
use TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;

/**
 * Converter which transforms simple types to a core type
 * implementing \TYPO3\CMS\Core\Type\TypeInterface.
 *
 * @api
 */
class CoreTypeConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter {

	/**
	 * @var array<string>
	 */
	protected $sourceTypes = array('string', 'integer', 'float', 'boolean', 'array');

	/**
	 * @var string
	 */
	protected $targetType = 'TYPO3\\CMS\\Core\\Type\\TypeInterface';

	/**
	 * @var integer
	 */
	protected $priority = 1;

	/**
	 * @param mixed $source
	 * @param string $targetType
	 * @return boolean
	 */
	public function canConvertFrom($source, $targetType) {
		return TypeHandlingUtility::isCoreType($targetType);
	}

	/**
	 * Convert an object from $source to an Enumeration.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @return object the target type
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		try {
			return new $targetType($source);
		} catch (InvalidValueExceptionInterface $exception) {
			return new \TYPO3\CMS\Extbase\Error\Error($exception->getMessage(), 1381680012);
		}
	}
}
