<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addTypoScriptSetup('
  config.stat = 1
  config.stat_mysql = 1
');
?>