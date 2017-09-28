<?php
namespace Omniverse\Traits;

/**
 * Omniverse Factory Trait
 *
 * This trait allows an inheriting class to add a factory method
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
trait DriverList
{
  protected static function generateDriverList()
  {
    if (!isset($_SESSION['DriverList']))
    {
      $_SESSION['DriverList'] = [];
    }

    if (!isset($_SESSION['DriverList'][__CLASS__]))
    {
      $_SESSION['DriverList'][__CLASS__] = [];
      $sClassDir = preg_replace("#\\\#", '/', preg_replace("#Omniverse\\\\#", '', __CLASS__));

      foreach (\Omniverse\Controller::getLibs() as $sLib)
      {
        foreach (glob("$sLib/$sClassDir/*.php") as $sClassFile)
        {
          $sClasseName = basename($sClassFile, ".php");
          $_SESSION['DriverList'][__CLASS__][strtolower($sClasseName)] = $sClasseName;
        }
      }
    }
  }

  public static function driverList()
  {
    self::generateDriverList();
    return $_SESSION['DriverList'][__CLASS__];
  }

  public static function driver(string $sName)
  {
    self::generateDriverList();
    return $_SESSION['DriverList'][__CLASS__][strtolower($sName)] ?? $sName;
  }
}
