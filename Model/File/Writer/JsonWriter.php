<?php
/**
 * Copyright © 2016 Rouven Alexander Rieker
 * See LICENSE.md bundled with this module for license details.
 */
namespace Semaio\ConfigImportExport\Model\File\Writer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class JsonWriter
 *
 * @package Semaio\ConfigImportExport\Model\File\Writer
 */
class JsonWriter extends AbstractWriter
{
    /**
     * @param string $filename
     * @param array  $data
     * @param string $directory
     */
    protected function _write($filename, array $data, $dir = null)
    {
        // Prepare data
        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);

        // Write data to file
        if (!is_null($dir)) {
            $output = sprintf('%s/%s', $dir, $filename);
            file_put_contents($output, $content);
        } else {
            $tmpDirectory = $this->getFilesystem()
                ->getDirectoryWrite(DirectoryList::VAR_DIR);

            $tmpDirectory->writeFile($filename, $content);
            $output = $tmpDirectory->getAbsolutePath($filename);
        }

        $this->getOutput()->writeln(sprintf(
            '<info>Wrote: %s settings to file %s</info>',
            count($data),
            $output
        ));
    }

    /**
     * @return string
     */
    public function getFileExtension()
    {
        return 'json';
    }
}
