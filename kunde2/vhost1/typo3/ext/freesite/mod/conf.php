<?php
define("TYPO3_MOD_PATH", "ext/freesite/mod/");		// Relative path to the module from the typo3/ folder
$BACK_PATH="../../../";					// Relative path to the typo3/ folder

$MLANG["default"]["tabs_images"]["tab"] = "freesite.gif";
$MLANG["default"]["ll_ref"]="LLL:EXT:freesite/mod/locallang_mod.php";

$MCONF["script"]="index.php";
$MCONF["access"]="admin";
$MCONF["name"]="freesite";		// Name: [module]_[subModule]  or just [module] if no submodule!! Remember, no "_" in module names!!!
?>