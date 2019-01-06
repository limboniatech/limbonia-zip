<?php
/**
 * Limbonia autoloader
 */

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
require 'Controller.php';

spl_autoload_register(function ($sClassName)
{
  $sClassType = preg_match("#^Limbonia\\\?(.+)#", $sClassName, $aMatch) ? $aMatch[1] : $sClassName;
  $sClassPath = preg_replace("#[_\\\]#", DIRECTORY_SEPARATOR, $sClassType);

  foreach (\Limbonia\Controller::getLibs() as $sLibDir)
  {
    $sClassFile = $sLibDir . DIRECTORY_SEPARATOR . $sClassPath . '.php';

    if (is_file($sClassFile))
    {
      require $sClassFile;
      break;
    }
  }
});
