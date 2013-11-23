<?php
/*
Name: Class.RecordNavigator.php
Type: Class
Purpose: Provide interface for creating next/previous and page # links
Usage:

$RN = new RecordNavigator(
	"SELECT COUNT(*) FROM yourtable",
	$passedOffset,
	20,
	"yourscript.php?catid=1"
);
$RN->createSequence();
$RN->createPrevNext("previous", "next");
echo($RN->getNavigator());

*/
class RecordNavigator
{
	var $queryCount;
	var $offset;
	var $limiter;
	var $seqStr;
	var $scriptPath;

	var $cObj = null; // for making typo3 links

	/* constructor */
	function RecordNavigator($queryCount, $offset, $limiter, $scriptpath)
	{ 
		$this->queryCount 	= $queryCount;
		$this->offset 		= $offset;
		$this->limiter		= $limiter;
		$this->scriptPath 	= $scriptpath;

		$this->cObj = new tslib_cObj();
	}
	
	/* create page # sequence */
	function createSequence()
	{
		$numPages = ceil($this->queryCount / $this->limiter);
		$nextOffset = 0;
		
		/* if there are more records than currently counted, generate sequence */
		if($this->queryCount > $this->limiter)
		{		
			for($i = 1; $i <= $numPages; $i++)
			{
				if($this->offset != $nextOffset)
				{
					$this->seqStr .= '<li>'.$this->cObj->getTypoLink(
						$i, 
						$GLOBALS['TSFE']->id, 
						array('offset' => $nextOffset), 
						''
					).'</li>';
				}
				else
				{
					$this->seqStr .= '<li class="current">'.$i.'</li>';
				}
					
				$nextOffset += $this->limiter;
			}
		}
	}
	
	/* create previous/next links */
	function createPrevNext($prevLabel, $nextLabel)
	{
		if((int) $this->offset != 0)
		{
			$prevOffset = $this->offset - $this->limiter;
			
			$this->seqStr = '<li class="prev">'.$this->cObj->getTypoLink(
				$prevLabel,
				$GLOBALS['TSFE']->id,
				array('offset' => $prevOffset),
				'').'</li>'.$this->seqStr;
		}
		if($this->queryCount > ($this->offset + $this->limiter))
		{
			$nextOffset = $this->offset + $this->limiter;
			$this->seqStr .= '<li class="next">'.$this->cObj->getTypoLink(
				$nextLabel,
				$GLOBALS['TSFE']->id,
				array('offset' => $nextOffset),
				'').'</li>';
		}
	}
	
	/* return full navigation string */
	function getNavigator()
	{
		return '<ul class="prevnext">'.$this->seqStr.'</ul>';
	}
}

if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tt_guest/pi/class.recordnavigator.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/tt_guest/pi/class.recordnavigator.php"]);
}
?>
