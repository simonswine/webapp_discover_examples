<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/**
 * Adding the admin panel to users by default and forcing the display of the edit-icons
 */
t3lib_extMgm::addUserTSConfig('

admPanel {
  enable.edit = 1
  module.edit.forceNoPopup = 1
  module.edit.forceDisplayFieldIcons = 1
  module.edit.forceDisplayIcons = 0
  hide = 1
}
options.shortcutFrame = 1

');

?>