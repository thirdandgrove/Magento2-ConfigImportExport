<?php
/**
 * Copyright © 2016 Rouven Alexander Rieker
 * See LICENSE.md bundled with this module for license details.
 */
namespace Semaio\ConfigImportExport\Model\File\Writer;

use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Filesystem;

/**
 * Class AbstractWriter
 *
 * @package Semaio\ConfigImportExport\Model\File\Writer
 */
abstract class AbstractWriter implements WriterInterface
{
    /**
     * @var string
     */
    private $baseFilename = null;

    /**
     * @var string
     */
    private $baseDirectory = null;

    /**
     * @var bool
     */
    private $isHierarchical = false;

    /**
     * @var bool
     */
    private $isFilePerNameSpace = false;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * AbstractWriter constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param array $data
     */
    public function write(array $data = [])
    {
        if (true === $this->getIsHierarchical()) {
            $preparedData = $this->_prepareDataHierarchical($data);
        } else {
            $preparedData = $this->_prepareDataFlat($data);
        }

        if ($this->getIsFilePerNameSpace()) {
            $namespacedData = [];
            foreach ($preparedData as $key => $item) {
                $tmpPath = explode('/', $key);
                if (!isset($namespacedData[$tmpPath[0]])) {
                    $namespacedData[$tmpPath[0]] = [];
                }
                $namespacedData[$tmpPath[0]][$key] = $item;
            }

            foreach ($namespacedData as $namespace => $configData) {
                $this->_write(
                    $this->getFilename($namespace),
                    $configData,
                    $this->getBaseDirectory()
                );
            }
        } else {
            $this->_write(
                $this->getFilename(),
                $preparedData,
                $this->getBaseDirectory()
            );
        }
    }

    /**
     * @param string $filename
     * @param array  $data
     * @param string $directory
     */
    abstract protected function _write($filename, array $data, $dir = null);

    /**
     * @param array $exportData
     * @return array
     */
    protected function _prepareDataHierarchical(array $exportData)
    {
        $return = array();
        foreach ($exportData as $row) {
            $pathDetails = explode('/', $row['path']);
            if (!isset($return[$pathDetails[0]])) {
                $return[$pathDetails[0]] = array();
            }
            if (!isset($return[$pathDetails[0]][$pathDetails[1]])) {
                $return[$pathDetails[0]][$pathDetails[1]] = array();
            }
            if (!isset($return[$pathDetails[0]][$pathDetails[1]][$pathDetails[2]])) {
                $return[$pathDetails[0]][$pathDetails[1]][$pathDetails[2]] = array();
            }
            if (!isset($return[$pathDetails[0]][$pathDetails[1]][$pathDetails[2]][$row['scope']])) {
                $return[$pathDetails[0]][$pathDetails[1]][$pathDetails[2]][$row['scope']] = array();
            }
            $return[$pathDetails[0]][$pathDetails[1]][$pathDetails[2]][$row['scope']][$row['scope_id']] = $row['value'];
        }

        return $return;
    }

    /**
     * @param array $exportData
     * @return array
     */
    protected function _prepareDataFlat(array $exportData)
    {
        $return = array();
        foreach ($exportData as $row) {
            if (!isset($return[$row['path']])) {
                $return[$row['path']] = array();
            }
            if (!isset($return[$row['path']][$row['scope']])) {
                $return[$row['path']][$row['scope']] = array();
            }
            $return[$row['path']][$row['scope']]['' . $row['scope_id']] = $row['value'];
        }

        return $return;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param string $baseDirectory
     */
    public function setBaseDirectory($baseDirectory)
    {
        $this->baseDirectory = $baseDirectory;
    }

    /**
     * @return string
     */
    public function getBaseDirectory()
    {
        return $this->baseDirectory;
    }

    /**
     * @param string $baseFilename
     */
    public function setBaseFilename($baseFilename)
    {
        $this->baseFilename = $baseFilename;
    }

    /**
     * @return string
     */
    public function getBaseFilename()
    {
        return $this->baseFilename;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param bool $isHierarchical
     * @return $this
     */
    public function setIsHierarchical($isHierarchical)
    {
        $this->isHierarchical = $isHierarchical;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHierarchical()
    {
        return $this->isHierarchical;
    }

    /**
     * @param bool $isFilePerNameSpace
     * @return $this
     */
    public function setIsFilePerNameSpace($isFilePerNameSpace)
    {
        $this->isFilePerNameSpace = $isFilePerNameSpace;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFilePerNameSpace()
    {
        return $this->isFilePerNameSpace;
    }

    /**
     * @param null|string $namespace
     * @return string
     */
    public function getFilename($namespace = null)
    {
        $filename = [];

        // Check if the a base filename was given
        if ($baseFilename = $this->getBaseFilename()) {
            $filename[] = $baseFilename;
        } else {
            $filename[] = date('Ymd_His');
            $filename[] = 'config';
        }

        // Add namespace to filename
        if (null !== $namespace) {
            $filename[] = $namespace;
        }

        return implode('_', $filename) . '.' . $this->getFileExtension();
    }
}
