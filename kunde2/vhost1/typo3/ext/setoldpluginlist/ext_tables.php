<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');

$TCA['tt_content']['columns']['list_type']['config']['items'] = Array (	
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.0', '3'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.1', '4'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.2', '2'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.3', '5'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.4', '9'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.5', '0'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.6', '6'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.7', '7'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.8', '1'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.9', '8'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.10', '10'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.11', '11'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.12', '--div--'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.13', '20'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.14', '21'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.15', '--div--'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.16', '100'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.17', '101'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.18', '102'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:list_type.I.19', '103')
				);

$TCA['tt_content']['columns']['splash_layout']['config']['items'] = array_merge($TCA['tt_content']['columns']['splash_layout']['config']['items'], array(
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:splash_layout.I.0', '--div--'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:splash_layout.I.1', '30'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:splash_layout.I.2', '31'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:splash_layout.I.3', '32'),
					Array('LLL:EXT:setoldpluginlist/locallang_tca.php:splash_layout.I.4', '33')
				));

					
?>