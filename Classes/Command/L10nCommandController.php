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
     * Black hole
     *
     * @return void
     */
    protected function errorCommand() {
    }


}