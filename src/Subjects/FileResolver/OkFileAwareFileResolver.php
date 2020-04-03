<?php

/**
 * TechDivision\Import\Subjects\FileResolver\OkFileAwareFileResolver
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

namespace TechDivision\Import\Subjects\FileResolver;

use TechDivision\Import\Exceptions\LineNotFoundException;
use TechDivision\Import\Exceptions\MissingOkFileException;
use TechDivision\Import\ApplicationInterface;
use TechDivision\Import\Services\RegistryProcessorInterface;
use TechDivision\Import\Loaders\FilteredLoaderInterface;

/**
 * Plugin that processes the subjects.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import
 * @link      http://www.techdivision.com
 */
class OkFileAwareFileResolver extends AbstractFileResolver implements OkFileAwareFileResolverInterface
{

    /**
     * The appliation instance.
     *
     * @var \TechDivision\Import\ApplicationInterface
     */
    private $application;

    /**
     * Initializes the file resolver with the application and the registry instance.
     *
     * @param \TechDivision\Import\ApplicationInterface                $application       The application instance
     * @param \TechDivision\Import\Services\RegistryProcessorInterface $registryProcessor The registry instance
     * @param \TechDivision\Import\Loaders\FilteredLoaderInterface     $filesystemLoader  The filesystem loader instance
     */
    public function __construct(
        ApplicationInterface $application,
        RegistryProcessorInterface $registryProcessor,
        FilteredLoaderInterface $filesystemLoader
    ) {

        // set the application instance
        $this->application = $application;

        // pass the processor and filesystem loader instance to the parent constructor
        parent::__construct($registryProcessor, $filesystemLoader);
    }

    /**
     * Return's the application instance.
     *
     * @return \TechDivision\Import\ApplicationInterface The application instance
     */
    protected function getApplication() : ApplicationInterface
    {
        return $this->application;
    }

    /**
     * Remove's the passed line from the file with the passed name.
     *
     * @param string $line     The line to be removed
     * @param string $filename The name of the file the line has to be removed
     *
     * @return void
     * @throws \Exception Is thrown, if the file doesn't exists, the line is not found or can not be removed
     */
    protected function removeLineFromFile(string $line, string $filename) : void
    {
        $this->getApplication()->removeLineFromFile($line, $filename);
    }

    /**
     * Query whether or not the basename, without suffix, of the passed filenames are equal.
     *
     * @param string $filename1 The first filename to compare
     * @param string $filename2 The second filename to compare
     *
     * @return boolean TRUE if the passed files basename are equal, else FALSE
     * @todo Refactorig required, because of duplicate method
     * @see \TechDivision\Import\Loaders\Filters\OkFileFilter::isEqualFilename()
     */
    protected function isEqualFilename(string $filename1, string $filename2) : bool
    {
        return $this->stripSuffix($filename1, $this->getSuffix()) === $this->stripSuffix($filename2, $this->getOkFileSuffix());
    }

    /**
     * Strips the passed suffix, including the (.), from the filename and returns it.
     *
     * @param string $filename The filename to return the suffix from
     * @param string $suffix   The suffix to return
     *
     * @return string The filname without the suffix
     * @todo Refactorig required, because of duplicate method
     * @see \TechDivision\Import\Loaders\Filters\OkFileFilter::stripSuffix()
     */
    protected function stripSuffix(string $filename, string $suffix) : string
    {
        return basename($filename, sprintf('.%s', $suffix));
    }

    /**
     * Prepares and returns an OK filename from the passed parts.
     *
     * @param array $parts The parts to concatenate the OK filename from
     *
     * @return string The OK filename
     * @todo Refactorig required, because of duplicate method
     * @see \TechDivision\Import\Loaders\Filters\OkFileFilter::prepareOkFilename()
     */
    protected function prepareOkFilename(array $parts) : string
    {
        return sprintf('%s/%s.%s', $this->getSourceDir(), implode($this->getElementSeparator(), $parts), $this->getOkFileSuffix());
    }

    /**
     * Returns the elements the filenames consists of.
     *
     * @return array The array with the filename elements
     * @todo Refactorig required, because of duplicate method
     * @see \TechDivision\Import\Loaders\Filters\OkFileFilter::getPatternElements()
     */
    protected function getPatternElements()
    {
        return $this->getFileResolverConfiguration()->getPatternElements();
    }

    /**
     * Returns the elements the filenames consists of, converted to lowercase.
     *
     * @return array The array with the filename elements
     * @todo Refactorig required, because of duplicate method
     * @see \TechDivision\Import\Loaders\Filters\OkFileFilter::getPatternKeys()
     */
    protected function getPatternKeys()
    {

        // load the pattern keys from the configuration
        $patternKeys = $this->getPatternElements();

        // make sure that they are all lowercase
        array_walk($patternKeys, function (&$value) {
            $value = strtolower($value);
        });

        // return the pattern keys
        return $patternKeys;
    }

    /**
     * Return's an array with the names of the expected OK files for the actual subject.
     *
     * @return array The array with the expected OK filenames
     * @todo Refactorig required, because of duplicate method
     * @see \TechDivision\Import\Loaders\Filters\OkFileFilter::getOkFilenames()
     */
    protected function getOkFilenames() : array
    {

        // initialize the array for the available okFilenames
        $okFilenames = array();

        // prepare the OK filenames based on the found CSV file information
        for ($i = 1; $i <= sizeof($patternKeys = $this->getPatternKeys()); $i++) {
            // intialize the array for the parts of the names (prefix, filename + counter)
            $parts = array();
            // load the parts from the matches
            for ($z = 0; $z < $i; $z++) {
                // append the part
                $parts[] = $this->getMatch($patternKeys[$z]);
            }

            // query whether or not, the OK file exists, if yes append it
            if (file_exists($okFilename = $this->prepareOkFilename($parts))) {
                $okFilenames[] = $okFilename;
            }
        }

        // prepare and return the pattern for the OK file
        return $okFilenames;
    }

    /**
     * Query whether or not, the passed CSV filename is in the OK file. If the filename was found,
     * the OK file will be cleaned-up.
     *
     * @param string $filename The filename to be cleaned-up
     *
     * @return void
     * @throws \Exception Is thrown, if the passed filename is NOT in the OK file or the OK can not be cleaned-up
     */
    public function cleanUpOkFile(string $filename) : void
    {

        // query whether or not the subject needs an OK file, if yes remove the filename from the file
        if ($this->getSubjectConfiguration()->isOkFileNeeded() === false) {
            return;
        }

        try {
            // try to load the expected OK filenames
            if (sizeof($okFilenames = $this->getOkFilenames()) === 0) {
                throw new MissingOkFileException(sprintf('Can\'t find a OK filename for file %s', $filename));
            }

            // iterate over the found OK filenames (should usually be only one, but could be more)
            foreach ($okFilenames as $okFilename) {
                // clear the filecache
                \clearstatcache();
                // if the OK filename matches the CSV filename AND the OK file is empty
                if ($this->isEqualFilename($filename, $okFilename) && filesize($okFilename) === 0) {
                    unlink($okFilename);
                    return;
                }

                // else, remove the CSV filename from the OK file
                $this->removeLineFromFile(basename($filename), $fh = fopen($okFilename, 'r+'));
                fclose($fh);

                // if the OK file is empty, delete the file
                if (filesize($okFilename) === 0) {
                    unlink($okFilename);
                }

                // return immediately
                return;
            }

            // throw an exception if either no OK file has been found,
            // or the CSV file is not in one of the OK files
            throw new \Exception(
                sprintf(
                    'Can\'t found filename %s in one of the expected OK files: %s',
                    $filename,
                    implode(', ', $okFilenames)
                )
            );
        } catch (LineNotFoundException $lne) {
            // wrap and re-throw the exception
            throw new \Exception(
                sprintf(
                    'Can\'t remove filename %s from OK file: %s',
                    $filename,
                    $okFilename
                ),
                null,
                $lne
            );
        }
    }

    /**
     * Loads the files from the source directory and return's them sorted.
     *
     * @param string $serial The unique identifier of the actual import process
     *
     * @return array The array with the files matching the subjects suffix
     * @throws \Exception Is thrown, when the source directory is NOT available
     * @throws \TechDivision\Import\Exceptions\MissingOkFileException Is thrown, if files to be processed are available but the mandatory OK file is missing
     */
    public function loadFiles(string $serial) : array
    {

        // initialize the array with the files that has to be handled
        $filesToHandle = parent::loadFiles($serial);

        // load the size of the files before the filters have been applied
        $sizeBeforeFiltersHaveBeenApplied = $this->getFilesystemLoader()->getSizeBeforeFiltersHaveBeenApplied();

        // stop processing, if files ARE available, an OK file IS mandatory, but
        // NO file will be processed (because of a missing/not matching OK file)
        if ($this->getSubjectConfiguration()->isOkFileNeeded() && $sizeBeforeFiltersHaveBeenApplied > 0 && sizeof($filesToHandle) === 0) {
            throw new MissingOkFileException(
                sprintf(
                    'Stop processing, because can\'t find the mandatory OK file to process at least one of %d files',
                    $sizeBeforeFiltersHaveBeenApplied
                )
            );
        }

        // clean-up the .OK file for the found files
        foreach ($filesToHandle as $fileToHandle) {
            $this->cleanUpOkFile($fileToHandle);
        }

        // return the array with the files that has to be handled
        return $filesToHandle;
    }
}
