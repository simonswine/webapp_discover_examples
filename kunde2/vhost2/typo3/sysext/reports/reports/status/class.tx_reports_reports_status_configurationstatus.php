<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2010 Ingo Renner <ingo@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
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
***************************************************************/


/**
 * Performs some checks about the install tool protection status
 *
 * @author		Ingo Renner <ingo@typo3.org>
 * @package		TYPO3
 * @subpackage	reports
 *
 * $Id: class.tx_reports_reports_status_configurationstatus.php 7953 2010-06-17 14:07:33Z flyguide $
 */
class tx_reports_reports_status_ConfigurationStatus implements tx_reports_StatusProvider {

		// 10 MB
	protected $deprecationLogFileSizeWarningThreshold = 10485760;
		// 100 MB
	protected $deprecationLogFileSizeErrorThreshold   = 104857600;


	/**
	 * Determines the Install Tool's status, mainly concerning its protection.
	 *
	 * @return	array	List of statuses
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		$statuses = array(
			'emptyReferenceIndex' => $this->getReferenceIndexStatus(),
			'deprecationLog'      => $this->getDeprecationLogStatus()
		);

		if ($this->isMemcachedUsed()) {
			$statuses['memcachedConnection'] = $this->getMemcachedConnectionStatus();
		}

		return $statuses;
	}

	/**
	 * Checks if sys_refindex is empty.
	 *
	 * @return	tx_reports_reports_status_Status	An tx_reports_reports_status_Status object representing whether the reference index is empty or not
	 */
	protected function getReferenceIndexStatus() {
		$value    = $GLOBALS['LANG']->getLL('status_ok');
		$message  = '';
		$severity = tx_reports_reports_status_Status::OK;

		$count = $GLOBALS['TYPO3_DB']->exec_SELECTcountRows('*', 'sys_refindex');
		$registry = t3lib_div::makeInstance('t3lib_Registry');
		$lastRefIndexUpdate = $registry->get('core', 'sys_refindex_lastUpdate');

		if (!$count && $lastRefIndexUpdate) {
			$value    = $GLOBALS['LANG']->getLL('status_empty');
			$severity = tx_reports_reports_status_Status::WARNING;

			$url = 'sysext/lowlevel/dbint/index.php?&id=0&SET[function]=refindex';
			$message  = sprintf(
				$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.backend_reference_index'),
				'<a href="' . $url . '">',
				'</a>',
				t3lib_BeFunc::dateTime($lastRefIndexUpdate)
			);
		}
		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$GLOBALS['LANG']->getLL('status_referenceIndex'), $value, $message, $severity
		);
	}

	/**
	 * Checks whether memcached is configured, if that's the case we asume it's also used.
	 *
	 * @return	boolean	True if memcached is used, false otherwise.
	 */
	protected function isMemcachedUsed() {
		$memcachedUsed = FALSE;

		$memcachedServers = $this->getConfiguredMemcachedServers();
		if (count($memcachedServers)) {
			$memcachedUsed = TRUE;
		}

		return $memcachedUsed;
	}

	/**
	 * Gets the configured memcached server connections.
	 *
	 * @return	array 	An array of configured memcached server connections.
	 */
	protected function getConfiguredMemcachedServers() {
		$memcachedServers = array();

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'] as $table => $conf) {
				if (is_array($conf)) {
					foreach ($conf as $key => $value) {
						if (!is_array($value) && $value === 't3lib_cache_backend_MemcachedBackend') {
							$memcachedServers = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$table]['options']['servers'];
							break;
						}
					}
				}
			}
		}

		return $memcachedServers;
	}

	/**
	 * Checks whether TYPO3 can connect to the configured memcached servers.
	 *
	 * @return	tx_reports_reports_status_Status	An tx_reports_reports_status_Status object representing whether TYPO3 can connect to the configured memcached servers
	 */
	protected function getMemcachedConnectionStatus() {
		$value    = $GLOBALS['LANG']->getLL('status_ok');
		$message  = '';
		$severity = tx_reports_reports_status_Status::OK;

		$failedConnections    = array();
		$defaultMemcachedPort = ini_get('memcache.default_port');
		$memcachedServers     = $this->getConfiguredMemcachedServers();

		if (function_exists('memcache_connect') && is_array($memcachedServers)) {
			foreach ($memcachedServers as $testServer) {
				$configuredServer = $testServer;
				if (substr($testServer, 0, 7) == 'unix://') {
					$host = $testServer;
					$port = 0;
				} else {
					if (substr($testServer, 0, 6) === 'tcp://') {
						$testServer = substr($testServer, 6);
					}
					if (strstr($testServer, ':') !== FALSE) {
						list($host, $port) = explode(':', $testServer, 2);
					} else {
						$host = $testServer;
						$port = $defaultMemcachedPort;
					}
				}
				$memcachedConnection = @memcache_connect($host, $port);
				if ($memcachedConnection != null) {
					memcache_close($memcachedConnection);
				} else {
					$failedConnections[] = $configuredServer;
				}
			}
		}

		if (count($failedConnections)) {
			$value    = $GLOBALS['LANG']->getLL('status_connectionFailed');
			$severity = tx_reports_reports_status_Status::WARNING;

			$message = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.memcache_not_usable')
				. '<br /><br />'
				. '<ul><li>'
				. implode('</li><li>', $failedConnections)
				. '</li></ul>';
		}

		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$GLOBALS['LANG']->getLL('status_memcachedConfiguration'), $value, $message, $severity
		);
	}

	/**
	 * Provides status information on the deprecation log, whether it's enabled
	 * and if so whether certain limits in file size are reached.
	 *
	 * @return	tx_reports_reports_status_Status	The deprecation log status.
	 */
	protected function getDeprecationLogStatus() {
		$title    = $GLOBALS['LANG']->getLL('status_configuration_DeprecationLog');
		$value    = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:disabled');
		$message  = '';
		$severity = tx_reports_reports_status_Status::OK;

		if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog']) {
			$value    = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:enabled');
			$message  = '<p>' . $GLOBALS['LANG']->getLL('status_configuration_DeprecationLogEnabled') . '</p>';
			$severity = tx_reports_reports_status_Status::NOTICE;

			$logFile     = t3lib_div::getDeprecationLogFileName();
			$logFileSize = 0;

			if (file_exists($logFile)) {
				$logFileSize = filesize($logFile);

				$message .= '<p> ' . sprintf(
						$GLOBALS['LANG']->getLL('status_configuration_DeprecationLogFile'),
						$this->getDeprecationLogFileLink()
					) . '</p>';
			}

			if ($logFileSize > $this->deprecationLogFileSizeWarningThreshold) {
				$severity = tx_reports_reports_status_Status::WARNING;
			}

			if ($logFileSize > $this->deprecationLogFileSizeErrorThreshold) {
				$severity = tx_reports_reports_status_Status::ERROR;
			}

			if ($severity > tx_reports_reports_status_Status::OK) {
				$message .= '<p> ' . sprintf(
					$GLOBALS['LANG']->getLL('status_configuration_DeprecationLogSize'),
					t3lib_div::formatSize($logFileSize)
				) . '</p>';
			}
		}

		return t3lib_div::makeInstance('tx_reports_reports_status_Status',
			$title, $value, $message, $severity
		);
	}

	/**
	 * Creates a link to the deprecation log file with the absolute path as the
	 * link text.
	 *
	 * @return	string	Link to the deprecation log file
	 */
	protected function getDeprecationLogFileLink() {
		$logFile      = t3lib_div::getDeprecationLogFileName();
		$documentRoot = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT');

		$relativePath = substr($logFile, strlen($documentRoot));
		$link = '<a href="..' . $relativePath . '">' . $logFile . '</a>';

		return $link;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/reports/reports/status/class.tx_reports_reports_status_configurationstatus.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/reports/reports/status/class.tx_reports_reports_status_configurationstatus.php']);
}

?>
