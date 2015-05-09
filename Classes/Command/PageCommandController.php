<?php
namespace NXS\Sitefunctions\Command;

class PageCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

    /**
     * copyPageTreeService
     *
     * @var \NXS\Sitefunctions\Service\CopyPageTreeService
     * @inject
     */
    protected $copyPageTreeService;
	
	/**
	 * Install extensions
     *
     * @param string $sourcePid Source Pid
     * @param string $targetPid Target Pid
	 * @return void
     */
	public function copyTreeCommand($sourcePid = null, $targetPid = null)
    {
        if (NULL === $sourcePid || NULL === $targetPid) {
            $this->response->setContent('Both, source and target pid, must be specified' . LF);
            $this->response->send();
            $this->response->setExitCode(128);
            $this->forward('error');
        }

        if (!intval($sourcePid) || !intval($targetPid)) {
            $this->response->setContent('Both, source and target pid, must be integer' . LF);
            $this->response->send();
            $this->response->setExitCode(128);
            $this->forward('error');
        }
        $this->copyPageTreeService->copyPageTree($sourcePid,$targetPid);
    }

    /**
     * Black hole
     *
     * @return void
     */
    protected function errorCommand() {
    }


}