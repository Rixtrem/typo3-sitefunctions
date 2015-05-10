<?php
namespace NXS\Sitefunctions\Command;

class L10nCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

    /**
     * ExportL10nService
     *
     * @var \NXS\Sitefunctions\Service\ExportL10nService
     * @inject
     */
    protected $exportL10nService;

    /**
     * ExtensionService
     *
     * @var \NXS\Sitefunctions\Service\ExtensionService
     * @inject
     */
    protected $extensionService;

    /**
     * ExcelWriteService
     *
     * @var \NXS\Sitefunctions\Service\ExcelWriterService
     * @inject
     */
    protected $excelWriterService;



    /**
     * LangfileparseService
     *
     * @var \NXS\Sitefunctions\Service\LangfileparseService
     * @inject
     */
    protected $langfileparseService;

	/**
     *
     * @param string $sourcePid Source Pid
	 * @return void
     */
	public function exportCommand($sourcePid = null)
    {
        if (NULL === $sourcePid) {
            $this->response->setContent('Source pid, must be specified' . LF);
            $this->response->send();
            $this->response->setExitCode(128);
            $this->forward('error');
        }

        $this->exportL10nService->exportPageTree($sourcePid);
    }

	/**
     *
     * @param string $sourcePid Source Pid
	 * @return void
     */
	public function exportLangKeysCommand($sourcePid = null)
    {
        if (NULL === $sourcePid) {
            $this->response->setContent('Source pid, must be specified' . LF);
            $this->response->send();
            $this->response->setExitCode(128);
            $this->forward('error');
        }

        $contentArray = array();
        $extensionArray = array('nxs_contract', 'nxs_contacts');

        foreach($extensionArray as $extensionName) {
            $langfiles = $this->extensionService->GetLangfiles($extensionName);
            $this->langfileparseService->Reset();
            foreach($langfiles as $langfile) {
                $this->langfileparseService->Load($extensionName, $langfile['file'],$langfile['relfile'],'default');
                $keys = $this->langfileparseService->getAllKeys();
                var_dump($keys['default']['keys']);

                foreach($keys['default']['keys'] as $key => $value) {
                    $contentArray[$extensionName.'::'.$key] = trim($value);
                }

                $filename = "langkey_".$extensionName.".xml";
                $this->excelWriterService->writeExcelXMLFile($contentArray,$extensionName, $filename);
                //$contentArray =
            }
        }
    }

    /**
     * Black hole
     *
     * @return void
     */
    protected function errorCommand() {
    }


}