<?php
namespace TYPO3\CMS\Core\Package;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A Package
 * Adapted from FLOW for TYPO3 CMS
 *
 * @api
 */
class Package extends \TYPO3\Flow\Package\Package implements PackageInterface {

	const PATTERN_MATCH_EXTENSIONKEY = '/^[0-9a-z_]+$/i';

	/**
	 * @var array
	 */
	protected $extensionManagerConfiguration = array();

	/**
	 * @var array
	 */
	protected $classAliases;

	/**
	 * @var bool
	 */
	protected $objectManagementEnabled = NULL;

	/**
	 * @var array
	 */
	protected $ignoredClassNames = array();

	/**
	 * Constructor
	 *
	 * @param \TYPO3\Flow\Package\PackageManager $packageManager the package manager which knows this package
	 * @param string $packageKey Key of this package
	 * @param string $packagePath Absolute path to the location of the package's composer manifest
	 * @param string $classesPath Path the classes of the package are in, relative to $packagePath. Optional, read from Composer manifest if not set.
	 * @param string $manifestPath Path the composer manifest of the package, relative to $packagePath. Optional, defaults to ''.
	 * @throws \TYPO3\Flow\Package\Exception\InvalidPackageKeyException if an invalid package key was passed
	 * @throws \TYPO3\Flow\Package\Exception\InvalidPackagePathException if an invalid package path was passed
	 * @throws \TYPO3\Flow\Package\Exception\InvalidPackageManifestException if no composer manifest file could be found
	 */
	public function __construct(\TYPO3\Flow\Package\PackageManager $packageManager, $packageKey, $packagePath, $classesPath = NULL, $manifestPath = '') {
		if (preg_match(self::PATTERN_MATCH_EXTENSIONKEY, $packageKey) !== 1 && preg_match(self::PATTERN_MATCH_PACKAGEKEY, $packageKey) !== 1) {
			throw new \TYPO3\Flow\Package\Exception\InvalidPackageKeyException('"' . $packageKey . '" is not a valid package key.', 1217959510);
		}
		if (!(@is_dir($packagePath) || (\TYPO3\Flow\Utility\Files::is_link($packagePath) && is_dir(\TYPO3\Flow\Utility\Files::getNormalizedPath($packagePath))))) {
			throw new \TYPO3\Flow\Package\Exception\InvalidPackagePathException(sprintf('Tried to instantiate a package object for package "%s" with a non-existing package path "%s". Either the package does not exist anymore, or the code creating this object contains an error.', $packageKey, $packagePath), 1166631889);
		}
		if (substr($packagePath, -1, 1) !== '/') {
			throw new \TYPO3\Flow\Package\Exception\InvalidPackagePathException(sprintf('The package path "%s" provided for package "%s" has no trailing forward slash.', $packagePath, $packageKey), 1166633720);
		}
		if (substr($classesPath, 1, 1) === '/') {
			throw new \TYPO3\Flow\Package\Exception\InvalidPackagePathException(sprintf('The package classes path provided for package "%s" has a leading forward slash.', $packageKey), 1334841320);
		}
		if (!@file_exists($packagePath . $manifestPath . 'ext_emconf.php')) {
			throw new \TYPO3\Flow\Package\Exception\InvalidPackageManifestException(sprintf('No ext_emconf file found for package "%s". Please create one at "%sext_emconf.php".', $packageKey, $manifestPath), 1360403545);
		}
		$this->packageManager = $packageManager;
		$this->manifestPath = $manifestPath;
		$this->packageKey = $packageKey;
		$this->packagePath = \TYPO3\Flow\Utility\Files::getNormalizedPath($packagePath);
		$this->classesPath = \TYPO3\Flow\Utility\Files::getNormalizedPath(\TYPO3\Flow\Utility\Files::concatenatePaths(array($this->packagePath, self::DIRECTORY_CLASSES)));
		try {
			$this->getComposerManifest();
		} catch (\TYPO3\Flow\Package\Exception\MissingPackageManifestException $exception) {
			$this->getExtensionEmconf($packageKey, $this->packagePath);
		}
		if ($this->objectManagementEnabled === NULL) {
			$this->objectManagementEnabled = FALSE;
		}
	}

	/**
	 * @return bool
	 */
	protected function getExtensionEmconf() {
		$_EXTKEY = $this->packageKey;
		$path = $this->packagePath . '/ext_emconf.php';
		$EM_CONF = NULL;
		if (@file_exists($path)) {
			include $path;
			if (is_array($EM_CONF[$_EXTKEY])) {
				$this->extensionManagerConfiguration = $EM_CONF[$_EXTKEY];
				$this->mapExtensionManagerConfigurationToComposerManifest();
			}
		}
		return FALSE;
	}

	/**
	 *
	 */
	protected function mapExtensionManagerConfigurationToComposerManifest() {
		if (is_array($this->extensionManagerConfiguration)) {
			$extensionManagerConfiguration = $this->extensionManagerConfiguration;
			$composerManifest = $this->composerManifest = new \stdClass();
			$composerManifest->name = $this->getPackageKey();
			$composerManifest->type = 'typo3cms-extension';
			$composerManifest->description = $extensionManagerConfiguration['title'];
			$composerManifest->version = $extensionManagerConfiguration['version'];
			if (isset($extensionManagerConfiguration['constraints']['depends']) && is_array($extensionManagerConfiguration['constraints']['depends'])) {
				$composerManifest->require = new \stdClass();
				foreach ($extensionManagerConfiguration['constraints']['depends'] as $requiredPackageKey => $requiredPackageVersion) {
					if (!empty($requiredPackageKey)) {
						$composerManifest->require->$requiredPackageKey = $requiredPackageVersion;
					} else {
						// TODO: throw meaningful exception or fail silently?
					}
				}
			}
			if (isset($extensionManagerConfiguration['constraints']['conflicts']) && is_array($extensionManagerConfiguration['constraints']['conflicts'])) {
				$composerManifest->conflict = new \stdClass();
				foreach ($extensionManagerConfiguration['constraints']['conflicts'] as $conflictingPackageKey => $conflictingPackageVersion) {
					if (!empty($conflictingPackageKey)) {
						$composerManifest->conflict->$conflictingPackageKey = $conflictingPackageVersion;
					} else {
						// TODO: throw meaningful exception or fail silently?
					}
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function getPackageReplacementKeys() {
		return $this->getComposerManifest('replace') ?: array();
	}

	/**
	 * Returns the PHP namespace of classes in this package.
	 *
	 * @return string
	 * @api
	 */
	public function getNamespace() {
		if(!$this->namespace) {
			$manifest = $this->getComposerManifest();
			if (isset($manifest->autoload->{'psr-0'})) {
				$namespaces = $manifest->autoload->{'psr-0'};
				if (count($namespaces) === 1) {
					$this->namespace = key($namespaces);
				} else {
					throw new \TYPO3\Flow\Package\Exception\InvalidPackageStateException(sprintf('The Composer manifest of package "%s" contains multiple namespace definitions in its autoload section but Flow does only support one namespace per package.', $this->packageKey), 1348053245);
				}
			} else {
				$packageKey = $this->getPackageKey();
				if (strpos($packageKey, '.') === FALSE) {
					// Old school with unknown vendor name
					$this->namespace =  '*\\' . \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($packageKey);
				} else {
					$this->namespace = str_replace('.', '\\', $packageKey);
				}
			}
		}
		return $this->namespace;
	}

	/**
	 * @return array
	 */
	public function getClassFiles() {
		if (!is_array($this->classFiles)) {
			$this->classFiles = $this->filterClassFiles($this->buildArrayOfClassFiles($this->classesPath . '/', $this->namespace . '\\'));
		}
		return $this->classFiles;
	}

	/**
	 * @param array $classFiles
	 * @return array
	 */
	protected function filterClassFiles(array $classFiles) {
		$classesNotMatchingClassRule = array_filter(array_keys($classFiles), function($className) {
			return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\\\x7f-\xff]*$/', $className) !== 1;
		});
		foreach ($classesNotMatchingClassRule as $forbiddenClassName) {
			unset($classFiles[$forbiddenClassName]);
		}
		foreach ($this->ignoredClassNames as $ignoredClassName) {
			if (isset($classFiles[$ignoredClassName])) {
				unset($classFiles[$ignoredClassName]);
			}
		}
		return $classFiles;
	}

	/**
	 * @return array
	 */
	public function getClassFilesFromAutoloadRegistry() {
		$autoloadRegistryPath = $this->packagePath . 'ext_autoload.php';
		if (@file_exists($autoloadRegistryPath)) {
			return require $autoloadRegistryPath;
		}
		return array();
	}

	/**
	 *
	 */
	public function getClassAliases() {
		if (!is_array($this->classAliases)) {
			try {
				$extensionClassAliasMapPathAndFilename = \TYPO3\Flow\Utility\Files::concatenatePaths(array(
					$this->getPackagePath(),
					'Migrations/Code/ClassAliasMap.php'
				));
				if (@file_exists($extensionClassAliasMapPathAndFilename)) {
					$this->classAliases = require $extensionClassAliasMapPathAndFilename;
				}
			} catch (\BadFunctionCallException $e) {
			}
			if (!is_array($this->classAliases)) {
				$this->classAliases = array();
			}
		}
		return $this->classAliases;
	}
}