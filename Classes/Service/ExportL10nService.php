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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * @author      Ricardo Marschall <marschall@nexus-netsoft.com>
 * @subpackage  tx_nxscore
 *
 * This package includes all function for Langdetect integration
 */
class ExportL10nService implements \TYPO3\CMS\Core\SingletonInterface {


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
     * ContentService
     *
     * @var \NXS\Sitefunctions\Service\ContentService
     * @inject
     */
    protected $contentService;

    /**
     * ExcelWriteService
     *
     * @var \NXS\Sitefunctions\Service\ExcelWriterService
     * @inject
     */
    protected $excelWriterService;

    /**
     * FlexFormService
     *
     * @var \TYPO3\CMS\Extbase\Service\FlexFormService
     * @inject
     */
    protected $flexFormService;

    protected $finalContentArray = array();


    /**
     * initiate copy process
     *
     * @param $sourcePid
     */
    public function exportPageTree($sourcePid)
    {

        $pages = $this->pageRepository->getMenu($sourcePid, $this->fields, $this->sortField);
        $this->writePageContentRecursive($pages);
    }

    /**
     * Walk recursive through page tree and do the copy
     * @param $pages
     */
    protected function writePageContentRecursive($pages)
    {

        $fieldArray = array(
            'page'          => array(
                'title', 'keywords', 'description', 'abstract'
            ),
            'tt_content'    => array(
                'header', 'bodytext'
            )
        );

        foreach($pages as $page)
        {
            $this->finalContentArray = array();

            foreach($fieldArray['page'] as $pageField) {
                if(!empty($page[$pageField])) {
                    $this->finalContentArray[$this->getPageIdent($page, $pageField)] = $page[$pageField];
                }
            }

            $contentArray = $this->contentService->getContentRecordsFromPage($page['uid']);
            foreach($contentArray as $content) {

                foreach($fieldArray['tt_content'] as $contentField) {
                    if(!empty($content[$contentField])) {
                        $this->finalContentArray[$this->getContentIdent($content, $contentField)] = $content[$contentField];
                    }

                    if(!empty($content['pi_flexform'])) {
                        $flexFormArray = $this->flexFormService->convertFlexFormContentToArray($content['pi_flexform']);
                        $this->handleFlexForm($flexFormArray, $content);
                    }
                }

            }

            $filename = "page_".$page['uid']."_".preg_replace('/[^\w]+/','_',$page['title']).".xml";
            $this->excelWriterService->writeExcelXMLFile($this->finalContentArray,$page, $filename);
            $subPages = $this->pageRepository->getMenu($page['uid'], $this->fields, $this->sortField);
            if(count($subPages) > 0) {
                $this->writePageContentRecursive($subPages);
            }
        }
    }

    /**
     * @param $flexFormArray
     * @param $content
     */
    protected function handleFlexForm($flexFormArray, $content, $flexFormKeyPrefix = "")
    {
        foreach($flexFormArray as $flexFormKey => $flexFormField) {

            if(is_array($flexFormField)) {
                $flexFormKeyPrefix = $flexFormKey."_";
                $this->handleFlexForm($flexFormField, $content, $flexFormKeyPrefix);
            }

            if(!empty($flexFormField)) {
                $this->finalContentArray[$this->getContentIdent($content, $flexFormKeyPrefix.$flexFormKey, true)] = $flexFormArray[$flexFormKey];
            }
        }
    }

    protected function getPageIdent($page,$fieldname)
    {
        return "page_".$page['uid']."_".$fieldname;
    }

    protected function getContentIdent($content,$fieldname, $isFlexForm = false)
    {
        $flexform = ($isFlexForm) ? "_flexform" : "";
        return "tt_content_".$content['uid'].$flexform."_".$fieldname;
    }

}
