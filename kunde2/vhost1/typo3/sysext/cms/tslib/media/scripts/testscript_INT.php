<?php
# TYPO3 CVS ID: $Id: testscript_INT.php,v 1.2 2003/10/26 17:07:44 typo3 Exp $

if (!is_object($this)) die ('Error: No parent object present.');




$content.='
This is output from an internal script!
<br />
Works like ordinary include-scripts.
<br />
';

debug($this->data);

?>