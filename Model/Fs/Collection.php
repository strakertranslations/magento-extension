<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Straker\EasyTranslationPlatform\Model\Fs;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Backup data collection
 */
class Collection extends \Magento\Backup\Model\Fs\Collection
{
    /**
     * Get backup-specific data from model for each row
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $row = parent::_generateRow($filename);
        foreach ($this->_backup->load(
            $row['basename'],
            $this->_varDirectory->getAbsolutePath($this->_path)
        )->getData() as $key => $value) {
            $row[$key] = $value;
        }
        $row['size'] = $this->_varDirectory->stat($this->_varDirectory->getRelativePath($filename))['size'];
        if (isset($row['display_name']) && $row['display_name'] == '') {
            $row['display_name'] = 'WebSetupWizard';
        }
//        $row['id'] = $row['time'] . '_' . $row['type'] . (isset($row['display_name']) ? $row['display_name'] : '');
        $row['id'] = $row['time'] . '_' . $row['type'];
        return $row;
    }
}
