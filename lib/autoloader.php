<?php
/**
 * Omniverse autoloader
 */

require 'Controller.php';

spl_autoload_register(function ($sClassName)
{
	$sClassType = preg_match("#^Omniverse\\\?(.+)#", $sClassName, $aMatch) ? $aMatch[1] : $sClassName;
  $sClassPath = preg_replace("#[_\\\]#", DIRECTORY_SEPARATOR, $sClassType);

  foreach (\Omniverse\Controller::getLibs() as $sLibDir)
  {
  	$sClassFile = $sLibDir . DIRECTORY_SEPARATOR . $sClassPath . '.php';

    if (is_file($sClassFile))
    {
      require $sClassFile;
      break;
    }
  }
});

//Twig autoloader
require_once '../private_html/lib/Twig/Autoloader.php';
