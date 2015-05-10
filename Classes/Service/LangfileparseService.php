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
class LangfileparseService implements \TYPO3\CMS\Core\SingletonInterface 
{
	/**
	 * DOMDocument
	 * @var array
	 */
	protected $aDocument;
	
	/**
	 * Filename
	 * @var array
	 */
	protected $aFile;
	
	/**
	 * Filename
	 * @var array
	 */
	protected $aRelFile;

	/**
	 * Filename
	 * @var array
	 */
	protected $aExtension;
	
	/**
	 * Dirty-Flag für File
	 * @var array
	 */
	protected $aDirty;

	/**
	 * Informationenen über Dateien
	 * @var string
	 */
	protected $sMessage;

	/**
	 * Setzt die gespeicherten Werte zurück
	 * @return void*/
	public function Reset()
	{
		$this->aDocument	 = array();
		$this->aFile	 	 = array();
		$this->aRelFile	 	 = array();
		$this->aDirty		 = array();
		$this->aExtension	 = array();
	}
	
	/**
	 * Setzt die zu parsende File
	 * @param string $sExt
	 * @param string $sFile
	 * @param string $sRelFile
	 * @param string $sLang
	 * @return void*/
	public function Load($sExt, $sFile, $sRelFile, $sLang)
	{
		if(!$this->aDocument[$sLang])
		{
			$this->aDocument[$sLang] 	 = new \DOMDocument('1.0');
			$this->aFile[$sLang]		 = $sFile;
			$this->aRelFile[$sLang]		 = $sRelFile;
			$this->aExtension[$sLang]	 = $sExt;
			$this->aDirty[$sLang]	 	 = false;
	
	        $this->read($sLang);
		}
	}
	
	/**
	 * Liefert in Abhängigkeit der gewählten Sprache source oder target zurück
	 * @param $sLang
	 * @return string*/
	private function sourceTargetSelector($sLang)
	{
		return $sLang == "default" ? "source" : "target";
	}
	
	/**
	 * Liest Datei ein
	 * @param string $sLang
	 * @return*/
	private function read($sLang)
	{
		$this->aDocument[$sLang]->load(\NXS\NxsLangedit\Service\LangfilespecialdirService::processFolder($this->aFile[$sLang]));
	}
	
	/**
	 * Speichert jede File ab, die verändert wurde
	 * @return void*/
	public function SaveAll()
	{
           
		foreach($this->aDocument as $sKey=>$oDocument)
		{
			if($this->aDirty[$sKey])
			{
				$oDocument->save(\NXS\NxsLangedit\Service\LangfilespecialdirService::processFolder($this->aFile[$sKey]));
			}
		}
	}
	
	/**
	 * Liefert Schlüssel zurück
	 * @param string $sLang
	 * @param string $sLanguageKey
	 * @return string*/
	public function GetValue($sLang,$sLanguageKey)
	{
		//todo: implement it!
		foreach($this->aDocument as $sKey=>$oDocument)
		{
			if ($sKey == $sLang )
			{			
				$oKeysList 	= &$oDocument->getElementsByTagName('trans-unit');
				$iLength 	= $oDocument->getElementsByTagName('trans-unit')->length;
				
				for($iCounter = 0; $iCounter < $iLength; $iCounter++)
				{		
					$sLangKey = $oKeysList->item($iCounter)->getAttribute('id');
					
					if($sLangKey == $sLanguageKey)
					{						
						$oChildNodes = $oKeysList->item($iCounter)->childNodes;
						return $oChildNodes->item(1)->nodeValue;
					}						
				}		
			}
		}	
	}
	
	/**
	 * Setzt Schlüssel in Xlf
	 * @param string $sLang
	 * @param string $sLanguageKey
	 * @param string $sValue
	 * @return void*/
	public function SetKey($sLang, $sLanguageKey, $sValue)
	{
		$this->aDirty[$sLang] = true;
		
		foreach($this->aDocument as $sKey=>$oDocument)
		{
			if ($sKey == $sLang )
			{			
				$oKeysList 	= &$oDocument->getElementsByTagName('trans-unit');
				$iLength 	= $oDocument->getElementsByTagName('trans-unit')->length;
				
				for($iCounter = 0; $iCounter < $iLength; $iCounter++)
				{				
					$sLangKey = $oKeysList->item($iCounter)->getAttribute('id');
					
					if($sLangKey == $sLanguageKey)
					{
						$this->writeValue($oKeysList, $iCounter, $sLang, $sValue);
						return;
					}						
				}		
			}
		}		
	}
	
	/**
	 * Schreibt Value in das XLF
	 * @param object $oKeysList
	 * @param integer $iCounter
	 * @param string $sLang
	 * @param string $sValue
	 * @return*/
	private function writeValue(&$oKeysList,$iCounter, $sLang, $sValue)
	{
		$oChildNodes = &$oKeysList->item($iCounter)->childNodes;
		
		if($this->tagsExists($sValue))
		{
			$oChild = &$oChildNodes->item(1);
			$oChild->nodeValue = "";
			$oChild->appendChild($this->cDataWrap($sLang, $sValue));
		}
		else
		{
			$oChildNodes->item(1)->nodeValue = $sValue;
		}
	}
	
	/**
	 * CDATA Wrap
	 * @param string $sLang
	 * @param string $sValue
	 * @return*/
	private function cDataWrap($sLang, $sValue)
	{
		return $this->aDocument[$sLang]->createCDATASection($sValue);
	}
	
	/**
	 * Liefert alle Schlüssel jeder Sprache zurück
	 * @return array*/
	public function getAllKeys()
	{
		$aKeys = array();
				
		foreach($this->aDocument as $sKey=>$oDocument)
		{
			$oKeysList 	= $oDocument->getElementsByTagName('trans-unit');
			$iLength 	= $oDocument->getElementsByTagName('trans-unit')->length;

			$aKeys[$sKey]['file']	    = $this->aFile[$sKey];
			$aKeys[$sKey]['extfile'] 	= "EXT:".$this->aExtension[$sKey].$this->aRelFile[$sKey];
			$aKeys[$sKey]['extension']  = $this->aExtension[$sKey];			
			
			for($iCounter = 0; $iCounter < $iLength; $iCounter++)
			{				
				$sLangKey 	= $oKeysList->item($iCounter)->getAttribute('id');
				$sLangValue = $oKeysList->item($iCounter)->nodeValue;
				
				$aKeys[$sKey]['keys'][$sLangKey] = $sLangValue;				
			}			
		}		
		return $aKeys;
	}
	
	/**
	 * Überprüft Sprachschlüsseldateien
	 * @return array*/
	public function getInformations()
	{
		$sMessage = "";
		
		foreach($this->aDocument as $sKey=>$oDocument)
		{			
			$oKeysList 	= $oDocument->getElementsByTagName('trans-unit');
			$iLength 	= $oDocument->getElementsByTagName('trans-unit')->length;

			$this->addMessage("Sprache: ".$sKey);
			$this->addMessage("Anzahl Schlüssel: ".$iLength);
			
			$aKeys = array();
			
			for($iCounter = 0; $iCounter < $iLength; $iCounter++)
			{				
				$sLangKey 	= $oKeysList->item($iCounter)->getAttribute('id');
				
				if(array_key_exists($sLangKey, $aKeys))
				{
					$this->addMessage("Doppelter Schlüssel: ".$sLangKey, true);
				}
								
				$aKeys[$sLangKey] = "exists";				
			}			
		}		
		return $this->sMessage;			
	}

	/**
	 * Fügt eine Zeile den Informationen hinzu
	 * @param string $sText
	 * @param bool $bRed
	 * @return void*/
	private function addMessage($sText, $bRed=false)
	{
		if ($bRed)
		{
			$this->sMessage .= "<p class='important'>".$sText."</p>";		
		}
		else
		{
			$this->sMessage .= $sText."<br/>";			
		}
	}
		
	/**
	 * Überprüft ob Tags im String vorhanden sind
	 * @param string $sContent
	 * @return bool*/
	private function tagsExists($sContent)
	{
		return $sContent !== strip_tags($sContent);
	}
}