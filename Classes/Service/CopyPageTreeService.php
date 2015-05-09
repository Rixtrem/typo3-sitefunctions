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
class CopyPageTreeService implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * queryGenerator
     *
     * @var \TYPO3\CMS\Core\Database\QueryGenerator
     * @inject
     */
    protected $queryGenerator;

    /**
     * copyContentService
     *
     * @var \NXS\Sitefunctions\Service\CopyContentService
     * @inject
     */
    protected $copyContentService;

    /**
     * pageRepository
     *
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     * @inject
     */
    protected $pageRepository;

    /**
     * @var string
     */
    protected $fields = "*";

    /**
     * @var string
     */
    protected $sortField = "sorting";

    /**
     * @var bool $ignoreContent If set to true, no content will be copied
     */
    protected $ignoreContent;


    /**
     * initiate copy process
     *
     * @param $sourcePid
     * @param $targetPid
     * @param $ignoreContent
     */
    public function copyPageTree($sourcePid,$targetPid, $ignoreContent = false)
    {
        $this->ignoreContent = $ignoreContent;

        if(!$ignoreContent) {
            $this->copyContentService->init($targetPid);
        }
        $pages = $this->pageRepository->getMenu($sourcePid, $this->fields, $this->sortField);
        $this->copyPagesRecursive($pages, $targetPid);
    }

    /**
     * Walk recursive through page tree and do the copy
     * @param $pages
     * @param $parentPage
     * @param $targetPid
     */
    protected function copyPagesRecursive($pages, $targetPid)
    {
        foreach($pages as $page)
        {
            $newPage = $page;
            $newPage['pid'] = $targetPid;
            unset($newPage['uid']);
            $lastInserted = $this->insertPageEntry($newPage);

            if(!$this->ignoreContent) {
                $this->copyContentService->copyContent($page, $this->pageRepository->getPage($lastInserted));
            }
            $subPages = $this->pageRepository->getMenu($page['uid'], $this->fields, $this->sortField);
            if(count($subPages) > 0) {
                $this->copyPagesRecursive($subPages, $lastInserted);
            }
        }
    }

    /**
     * @param $page
     * @return int last insert id
     */
    protected function insertPageEntry($page)
    {
        $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $page);
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
    }

}

?>
