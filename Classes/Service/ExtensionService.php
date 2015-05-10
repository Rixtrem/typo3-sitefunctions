<?php
namespace NXS\Sitefunctions\Service;

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('nxs_langedit')."Classes/Service/LangfilespecialdirService.php";


/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Michael Mißbach <missbach@nexus-netsoft.com>
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
 * @author      Michael Mißbach <missbach@nexus-netsoft.com>
 * @subpackage  tx_nxslangedit
 *
 */
class ExtensionService implements \TYPO3\CMS\Core\SingletonInterface 
{
	/**
	 * Liste aller Extensions
	 * @var array
	 */
	protected $aExtensions;
	
	/**
	 * Liste aller möglichen Sprachschlüsseldateipfade relativ zum Extension-Pfad
	 * @var array
	 */
	protected $aRelPossibleLangPathes = array("Resources/Private/Language/");
	
	/**
	 * Konstruktor
	 * @return void*/
	public function __construct()
	{
		$this->aExtensions = $GLOBALS['TYPO3_LOADED_EXT'];
										
		foreach($this->aExtensions as $sKey=>$sEntry)
		{
			$this->aExtensions[$sKey]['title'] = $this->getExtensionTitle($sKey);
			if(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($sKey))
			{
				$this->aExtensions[$sKey]['isLoaded']="true";							
			}
			else 
			{
				$this->aExtensions[$sKey]['isLoaded']="false";
			}
		}		
	}
	
	/**
	 * Liefert Titel der Extension zurück
	 * @param string $sKey
	 * @return string*/
	public function GetExtensionTitle($sKey)
	{
		$sPath = $this->getAbsPath($sKey);
		
		if (file_exists($sPath."ext_emconf.php"))
		{
			include $sPath."ext_emconf.php";						
			return $EM_CONF['']['title'];
		}
		return $sKey;
	}

	/**
	 * Liefert absoluten Pfad zurück
	 * @param string $sKey
	 * @return string*/
	private function getAbsPath($sKey)
	{
		return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($sKey);
	}
	
	/**
	 * Liefert Auflistung der Extensions zurück
	 * @return array*/
	public function GetExtensions()
	{
		return $this->aExtensions;
	}
	
	/**
	 * Prüft ob Sprachschlüsseldateien vorhanden sind
	 * @param string $sKey
	 * @return bool*/
	public function ExistsLangfiles($sKey)
	{
		$bFound = false;
		$sPath = $this->getAbsPath($sKey);
		
		foreach($this->aRelPossibleLangPathes as $sPathSingle)
		{
			if (file_exists($sPath.$sPathSingle."locallang.xlf"))
			{
		    	$bFound = true;
		    	break;
			}
		}	
		return $bFound;		
	}
	
	/**
	 * Liefert Pfad zu Sprachschlüsseldateien zurück
	 * @param string $sKey
	 * @return string*/
	public function GetLangfiles($sKey)
	{
		$sPath = $this->getAbsPath($sKey);
		
		$aFiles = array();
		
		foreach($this->aRelPossibleLangPathes as $sPathSingle)
		{
			$aDirFiles = $this->readDirectory($sPath, $sPathSingle);
									
			$aFiles = array_merge($aFiles, $this->selectFiles($aDirFiles));		
		}
		
		return $aFiles;		
	}
	
	/**
	 * Selektiert erforderliche Dateien
	 * @param array $aDirFiles
	 * @return array*/
	private function selectFiles($aDirFiles)
	{
		$aFiles = array();
		$iCounter = 0;
		
		foreach($aDirFiles as $aDirFilesSingle)
		{
			if ($this->endsWith($aDirFilesSingle['file'],"locallang.xlf"))
			{
				$sFileTmp = str_replace("locallang.xlf","",$aDirFilesSingle['file']);
				$sLang = str_replace(".","",$sFileTmp);				
				$aFiles[$iCounter]['lang'] = $sLang == "" ? "default" : $sLang;
				
				$aFiles[$iCounter]['file'] = $aDirFilesSingle['path'].$aDirFilesSingle['file'];
				$aFiles[$iCounter]['relfile'] = "/".$aDirFilesSingle['relpath'].$aDirFilesSingle['file'];
				$iCounter++;
			}
		}
		return $aFiles;
	}
	
	/**
	 * Liest Verzeichnis ein und liefert nur Dateien zurück
	 * @param string $sPath
	 * @param string $sRelPath
	 * @return array*/
	private function readDirectory($sPath,$sRelPath)
	{
		$aFiles = array();
		if ( is_dir (\NXS\NxsLangedit\Service\LangfilespecialdirService::processFolder($sPath.$sRelPath )))
		{
			if ( $oHandle = opendir(\NXS\NxsLangedit\Service\LangfilespecialdirService::processFolder($sPath.$sRelPath) ) )
		    {
		    	$iCounter = 0;
		        while (($sFile = readdir($oHandle)) !== false)
		        {
		        	if(filetype(\NXS\NxsLangedit\Service\LangfilespecialdirService::processFolder($sPath.$sRelPath.$sFile)) == "file")
		        	{
		        		$aFiles[$iCounter]['file'] = $sFile;
		        		$aFiles[$iCounter]['path'] = $sPath.$sRelPath;
		        		$aFiles[$iCounter]['relpath'] = $sRelPath;
		        		
		        		$iCounter++;
		        	}		            
		        }
		        closedir($oHandle);
		    }
		}
		return $aFiles;
	}
	
	/**
     * Prüft ob String mit einer bestimmten Zeichenfolge endet
     * @param string $sCheck der zu überprüfende String
     * @param string $sEndStr der Suchstring
     * @return bool
     * @author Michael Mißbach <missbach@nexus-netsoft.com>
     */
    protected function endsWith($sCheck, $sEndStr) 
    {
        if (!is_string($sCheck) || !is_string($sEndStr) || strlen($sCheck)<strlen($sEndStr)) 
        {
            return false;
        }

        return (substr($sCheck, strlen($sCheck)-strlen($sEndStr), strlen($sEndStr)) === $sEndStr);
    }

}