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
 * @subpackage  sitefunctions
 *
 */
class ExcelWriterService implements \TYPO3\CMS\Core\SingletonInterface {


    /**
     * Create excel xml file
     * @param $contentArray
     * @param $page
     * @param $filename
     */
    public function writeExcelXMLFile($contentArray, $page, $filename)
    {
            require_once(ExtensionManagementUtility::extPath('sitefunctions', 'Resources/Public/Lib/ExcelWriterXML/ExcelWriterXML.php'));
            $xml = new \ExcelWriterXML;

            $sheet = $xml->addSheet('Contents');
            $sheet->columnWidth(1,'250');
            $sheet->columnWidth(2,'600');
            $sheet->columnWidth(3,'600');

            $headerStyle = $xml->addStyle('header');
            $headerStyle->fontBold();
            $format3 = $xml->addStyle('wraptext_top');
            $format3->alignWraptext();
            $format3->alignVertical('Top');

            $sheet->writeString(1,1,'Ident (do not change)',$headerStyle);
            $sheet->writeString(1,2,'Text',$headerStyle);
            $sheet->writeString(1,3,'Text FR',$headerStyle);

            $rowCount = 2;
            $colCount = 1;

            foreach($contentArray as $key => $value) {
                if(!empty($value)) {
                    $sheet->writeString($rowCount,$colCount,$key,$format3);
                    $sheet->writeString($rowCount,$colCount+1,$value,$format3);
                    $rowCount++;
                }
            }
            $xml->sendHeaders();
            $xml->writeData(ExtensionManagementUtility::extPath('sitefunctions', 'Resources/Public/Export/'.$filename));
    }

}
