<?php
# TYPO3 CVS ID: $Id: testscript_EXT.php,v 1.4 2004/03/22 15:46:24 typo3 Exp $

if (!is_object($TSFE)) die ('Error: No parent object present.');




/**
 * Printing current time dynamically
 *
 * @param	string		Content (ignore)
 * @param	array		TypoScript configuration passed
 * @return	string		Current time wrapped in <font> tags with red color
 */
function user_printTimeExt($content,$conf)	{
	return '<font color="red">Dynamic time: '.date('H:i:s').'</font><br />';
}

?>

This is output from an external script!
<br />
<br />

You can get the content of the record, that included this script in $REC:<br />
<br />


<?php debug($REC); ?>

<br />
<br />
The configuration for the script is in the array, $CONF:<br />
<br />




<?php
debug($CONF);

?>

<br />
<br />
These are global variables!

<br />
<br />
Good luck....


BTW: The time:
<?php if ($CONF['showTime'])	{echo user_printTimeExt('','');} ?>
<br />
<br />

<?php

$content = '(This is returned in the variable, $content, which is another option...';

?>
<hr />