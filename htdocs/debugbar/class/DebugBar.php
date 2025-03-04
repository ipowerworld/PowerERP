<?php

dol_include_once('/debugbar/class/autoloader.php');

use \DebugBar\DebugBar;
use \DebugBar\DataCollector\PhpInfoCollector;

dol_include_once('/debugbar/class/DataCollector/DolMessagesCollector.php');
dol_include_once('/debugbar/class/DataCollector/DolRequestDataCollector.php');
dol_include_once('/debugbar/class/DataCollector/DolConfigCollector.php');
dol_include_once('/debugbar/class/DataCollector/DolTimeDataCollector.php');
dol_include_once('/debugbar/class/DataCollector/DolMemoryCollector.php');
dol_include_once('/debugbar/class/DataCollector/DolPhpCollector.php');
dol_include_once('/debugbar/class/DataCollector/DolExceptionsCollector.php');
dol_include_once('/debugbar/class/DataCollector/DolQueryCollector.php');
dol_include_once('/debugbar/class/DataCollector/PowerERPCollector.php');
dol_include_once('/debugbar/class/DataCollector/DolLogsCollector.php');

/**
 * PowerERPDebugBar class
 *
 * @see http://phpdebugbar.com/docs/base-collectors.html#base-collectors
 */

class PowerERPDebugBar extends DebugBar
{
	/**
	 * Constructor
	 *
	 */
	public function __construct()
	{
		global $conf;

		//$this->addCollector(new PhpInfoCollector());
		//$this->addCollector(new DolMessagesCollector());
		$this->addCollector(new DolRequestDataCollector());
		//$this->addCollector(new DolConfigCollector());      // Disabled for security purpose
		$this->addCollector(new DolTimeDataCollector());
		$this->addCollector(new PhpCollector());
		$this->addCollector(new DolMemoryCollector());
		//$this->addCollector(new DolExceptionsCollector());
		$this->addCollector(new DolQueryCollector());
		$this->addCollector(new PowerERPCollector());
		if (isModEnabled('syslog')) {
			$this->addCollector(new DolLogsCollector());
		}
	}

	/**
	 * Returns a JavascriptRenderer for this instance
	 *
	 * @return string      String content
	 */
	public function getRenderer()
	{
		$renderer = parent::getJavascriptRenderer(DOL_URL_ROOT.'/includes/maximebf/debugbar/src/DebugBar/Resources');
		$renderer->disableVendor('jquery');			// We already have jquery loaded globally by the main.inc.php
		$renderer->disableVendor('fontawesome');	// We already have fontawesome loaded globally by the main.inc.php
		$renderer->disableVendor('highlightjs');	// We don't need this
		$renderer->setEnableJqueryNoConflict(false);	// We don't need no conflict
		return $renderer;
	}
}
