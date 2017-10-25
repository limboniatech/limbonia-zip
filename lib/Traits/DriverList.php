<?php
namespace Omniverse\Traits;

/**
 * Omniverse DriverList Trait
 *
 * This trait allows an inheriting class to add a driver list to help with the
 * implementation of a factory method in the receiving class.
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
trait DriverList
{
  /**
   * Generate and cache the driver list for the current object type
   */
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

          if (!isset($_SESSION['DriverList'][__CLASS__][strtolower($sClasseName)]))
          {
            $_SESSION['DriverList'][__CLASS__][strtolower($sClasseName)] = $sClasseName;
          }
        }
      }
    }
  }

  /**
   * Return the driver list for the current object type
   *
   * @return array
   */
  public static function driverList()
  {
    self::generateDriverList();
    return $_SESSION['DriverList'][__CLASS__];
  }

  /**
   *  Return the driver name for the specified name, if there is one
   *
   * @param string $sName
   * @return string
   */
  public static function driver(string $sName)
  {
    self::generateDriverList();
    return $_SESSION['DriverList'][__CLASS__][strtolower($sName)] ?? '';
  }
}
