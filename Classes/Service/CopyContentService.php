<?php
namespace NXS\Sitefunctions\Service;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Ricardo Marschall <marschall@nexus-netsoft.com>
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */


/**
 * @author      Ricardo Marschall <marschall@nexus-netsoft.com>
 * @subpackage  tx_nxscore
 *
 * This package includes all function for Langdetect integration
 */
class CopyContentService implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * @var $fluxParentMapping
     */
    protected $fluxParentMapping;

    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * ContentService
     *
     * @var \NXS\Sitefunctions\Service\ContentService
     * @inject
     */
    protected $contentService;

    /**
     * @param $targetPid
     */
    public function init($targetPid)
    {
        $this->configuration = array_shift($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_sitefunctions_domain_model_copycfg', 'pid='.$targetPid,'','','1'));
    }

    /**
     * @param $sourcePage
     * @param $targetPage
     */
    public function copyContent($sourcePage, $targetPage)
    {
        $this->processT3Contentelems($sourcePage, $targetPage);
        $this->updateFluxParent($targetPage);
    }


    /**
     * @param $sourcePage
     * @param $targetPage
     * @return void
     */
    protected function processT3Contentelems($sourcePage, $targetPage)
    {
        $tables[] = 'tt_content';

        if(!empty($this->configuration)) {
            $tables = explode(',', $this->configuration['tablelist']);
        }

        foreach($tables as $table) {
            $contentArray = $this->contentService->getContentRecordsFromPage($sourcePage['uid'],$table);
            foreach ($contentArray as $content) {
                $newContent = $content;
                unset($newContent['uid']);
                $newContent['pid'] = $targetPage['uid'];
                $this->fluxParentMapping[$content['uid']] = $this->contentService->insertContentEntry($newContent,$table);
            }
        }
    }

    /**
     * Update tx_flux_parent
     *
     * @param $targetPage
     */
    protected function updateFluxParent($targetPage)
    {
        $contentArray = $this->contentService->getContentRecordsFromPage($targetPage['uid']);
        foreach($contentArray as $content) {
            if(array_key_exists($content['tx_flux_parent'], $this->fluxParentMapping)) {
                $newContent = array();
                $newContent['tx_flux_parent'] = $this->fluxParentMapping[$content['tx_flux_parent']];
                $this->contentService->updateContentEntry($content['uid'], $newContent);
            }
        }
    }

}
