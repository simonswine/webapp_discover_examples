<?php
// This is a test-script which may be included by the cObject PHP_SCRIPT in TypoScript
// All output must go to $content.
// This line will make sure that the page is NOT cached!! You may NOT set the value to false as some other code may have set it to true on purpose and this must be respected.
// $GLOBALS['TSFE']->set_no_cache();
# TYPO3 CVS ID: $Id: testscript.inc,v 1.2 2003/10/26 17:07:44 typo3 Exp $

if (!is_object($this)) die ('Error: No parent object present.');



$content='This is a testscript';

?>