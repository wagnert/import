<?php

/**
 * TechDivision\Import\ConfigurationInterface
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import;

use TechDivision\Import\Configuration\CsvConfigurationInterface;
use TechDivision\Import\Configuration\ParamsConfigurationInterface;
use TechDivision\Import\Configuration\DatabaseConfigurationInterface;

/**
 * The interface for the import configuration.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */
interface ConfigurationInterface extends CsvConfigurationInterface, ParamsConfigurationInterface
{

    /**
     * Return's the application's unique DI identifier.
     *
     * @return string The application's unique DI identifier
     */
    public function getId();

    /**
     * Return's the systemm name to be used.
     *
     * @return string The system name to be used
     */
    public function getSystemName();

    /**
     * Return's the entity type code to be used.
     *
     * @return string The entity type code to be used
     */
    public function getEntityTypeCode();

    /**
     * Return's the operation name that has to be used.
     *
     * @return string The operation name that has to be used
     */
    public function getOperationName();

    /**
     * Return's the TRUE if the import artefacts have to be archived.
     *
     * @return boolean TRUE if the import artefacts have to be archived
     */
    public function haveArchiveArtefacts();

    /**
     * The directory where the archives will be stored.
     *
     * @return string The archive directory
     */
    public function getArchiveDir();

    /**
     * Return's the database configuration with the passed ID.
     *
     * @param string $id The ID of the database connection to return
     *
     * @return \TechDivision\Import\Configuration\DatabaseConfigurationInterface The database configuration
     * @throws \Exception Is thrown, if no database configuration is available
     */
    public function getDatabaseById($id);

    /**
     * Return's the databases for the given type.
     *
     * @param string $type The database type to return the configurations for
     *
     * @return \Doctrine\Common\Collections\Collection The collection with the database configurations
     */
    public function getDatabasesByType($type);

    /**
     * Return's the database configuration.
     *
     * @return \TechDivision\Import\Configuration\DatabaseConfigurationInterface The database configuration
     * @throws \Exception Is thrown, if no database configuration is available
     */
    public function getDatabase();

    /**
     * Return's the ArrayCollection with the configured operations.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection The ArrayCollection with the operations
     */
    public function getOperations();

    /**
     * Return's the operation, initialize from the actual operation name.
     *
     * @return \TechDivision\Import\Configuration\OperationConfigurationInterface The operation instance
     */
    public function getOperation();

    /**
     * Return's the array with the plugins of the operation to use.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection The ArrayCollection with the plugins
     * @throws \Exception Is thrown, if no plugins are available for the actual operation
     */
    public function getPlugins();

    /**
     * Return's the Magento installation directory.
     *
     * @return string The Magento installation directory
     */
    public function getInstallationDir();

    /**
     * Return's the source directory that has to be watched for new files.
     *
     * @return string The source directory
     */
    public function getSourceDir();

    /**
     * Return's the target directory with the files that has been imported.
     *
     * @return string The target dir
     */
    public function getTargetDir();

    /**
     * Return's the Magento edition, EE or CE.
     *
     * @return string The Magento edition
     */
    public function getMagentoEdition();

    /**
     * Return's the Magento version, e. g. 2.1.0.
     *
     * @return string The Magento version
     */
    public function getMagentoVersion();

    /**
     * Return's the multiple field delimiter character to use, default value is comma (,).
     *
     * @return string The multiple field delimiter character
     */
    public function getMultipleFieldDelimiter();

    /**
     * Return's the multiple value delimiter character to use, default value is comma (|).
     *
     * @return string The multiple value delimiter character
     */
    public function getMultipleValueDelimiter();

    /**
     * Queries whether or not strict mode is enabled or not, default is TRUE.
     *
     * @return boolean TRUE if strict mode is enabled, else FALSE
     */
    public function isStrictMode();

    /**
     * Queries whether or not debug mode is enabled or not, default is TRUE.
     *
     * @return boolean TRUE if debug mode is enabled, else FALSE
     */
    public function isDebugMode();

    /**
     * Return's the log level to use.
     *
     * @return string The log level to use
     */
    public function getLogLevel();

    /**
     * Remove's all configured database configuration.
     *
     * @return void
     */
    public function clearDatabases();

    /**
     * Return's the number database configurations.
     *
     * @return integer The number of database configurations
     */
    public function countDatabases();

    /**
     * Add's the passed database configuration.
     *
     * @param \TechDivision\Import\Configuration\DatabaseConfigurationInterface $database The database configuration
     *
     * @return void
     */
    public function addDatabase(DatabaseConfigurationInterface $database);

    /**
     * Return's the explicit DB ID to use.
     *
     * @return string The explicit DB ID to use
     */
    public function getUseDbId();

    /**
     * Return's the PID filename to use.
     *
     * @return string The PID filename to use
     */
    public function getPidFilename();

    /**
     * Return's a collection with the path to additional vendor directories.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection The paths to additional vendor directories
     */
    public function getAdditionalVendorDirs();

    /**
     * Return's an array with the path of the Magento Edition specific extension libraries.
     *
     * @return array The paths of the Magento Edition specific extension libraries
     */
    public function getExtensionLibraries();

    /**
     * The array with the subject's custom header mappings.
     *
     * @return array The custom header mappings
     */
    public function getHeaderMappings();

    /**
     * The array with the subject's custom image types.
     *
     * @return array The custom image types
     */
    public function getImageTypes();

    /**
     * Return's the array with the configured listeners.
     *
     * @return array The array with the listeners
     */
    public function getListeners();

    /**
     * Whether or not the import should be wrapped within a single transation.
     *
     * @return boolean TRUE if the import should be wrapped in a single transation, else FALSE
     */
    public function isSingleTransaction();

    /**
     * Whether or not the cache functionality should be enabled.
     *
     * @return boolean TRUE if the cache has to be enabled, else FALSE
     */
    public function isCacheEnabled();

    /**
     * Return's the serial from the commandline.
     *
     * @return string The serial
     */
    public function getSerial();

    /**
     * Return's the configuration for the caches.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection The cache configurations
     */
    public function getCaches();

    /**
     * Return's the cache configuration for the passed type.
     *
     * @param string $type The cache type to return the configuation for
     *
     * @return \TechDivision\Import\Configuration\CacheConfigurationInterface The cache configuration
     */
    public function getCacheByType($type);

    /**
     * Return's the alias configuration.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection The alias configuration
     */
    public function getAliases();
}
