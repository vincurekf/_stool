<?php
$dir = new DirectoryIterator(__DIR__);
foreach ($dir as $fileinfo) {
	if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
		if ($fileinfo->getFilename() != '_autoload.php' && $fileinfo->getFilename() != 'index.php') {
			//
			require_once __DIR__ . '/' . $fileinfo->getFilename();
		}
	}
}
