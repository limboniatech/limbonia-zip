<?php
/**
 * Omniverse\Lib autoloader
 */
spl_autoload_register(function ($sClassName)
{
	if (preg_match("#^Omniverse\\\Lib\\\?(.+)#", $sClassName, $aMatch))
	{
		$sClassFile = __DIR__ . DIRECTORY_SEPARATOR . preg_replace("#[_\\\]#", DIRECTORY_SEPARATOR, strtolower($aMatch[1])) . '.php';

		if (is_file($sClassFile))
		{
			include_once $sClassFile;
		}
	}
});