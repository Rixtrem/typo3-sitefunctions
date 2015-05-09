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
class ContentService implements \TYPO3\CMS\Core\SingletonInterface {


    /**
     * @param $content
     * @param $table
     * @return int last insert id
     */
    public function insertContentEntry($content, $table = "tt_content")
    {
        $GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $content);
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
    }

    /**
     * @param $contentUid
     * @param $content
     * @return int last insert id
     */
    public function updateContentEntry($contentUid, $content)
    {
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', "uid=".$contentUid , $content);
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
    }

    /**
     * @param integer $pageUid
     * @param string $table
     * @return array[]
     */
    public function getContentRecordsFromPage($pageUid, $table = "tt_content") {
        $conditions = "pid=".$pageUid;
        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, $conditions, 'uid');
        return $rows;
    }

}
