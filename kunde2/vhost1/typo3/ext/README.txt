typo3/ext/

Globally available extensions for TYPO3
The idea of importing extensions to the global directory is that:
1) they are available for all sites using the same TYPO3 core source code (only possible with symlinked source on Unix/Linux)
2) they are distributed with the core of TYPO3 and thereby automatically updated with new source releases.

You can import your own extensions globally. Most people probably don't since this requires them to merge the globally available extensions from the core with whatever extensions they imported globally themselves.