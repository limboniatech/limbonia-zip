<?php
namespace Limbonia\Traits;

/**
 * Limbonia DriverList Trait
 *
 * This trait allows an inheriting class to add a driver list to help with the
 * implementation of a factory method in the receiving class.
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
trait DriverList
{
  /**
   * The subclass of any inheriting class
   *
   * @var string
   */
  protected $sType = null;

  /**
   * Return the full class type of this class
   *
   * @return string
   */
  public static function classType()
  {
    return preg_replace("#Limbonia\\\\#", '', __CLASS__);
  }

  /**
   * Generate and cache the driver list for the current object type
   */
  public static function generateDriverList()
  {
    if (!isset($_SESSION['DriverList']))
    {
      $_SESSION['DriverList'] = [];
    }

    if (!isset($_SESSION['DriverList'][__CLASS__]))
    {
      $_SESSION['DriverList'][__CLASS__] = [];
      $sClassDir = preg_replace("#\\\#", '/', self::classType());

      foreach (\Limbonia\Controller::getLibs() as $sLib)
      {
        foreach (glob("$sLib/$sClassDir/*.php") as $sClassFile)
        {
          $sDriverName = basename($sClassFile, ".php");

          if (isset($_SESSION['DriverList'][__CLASS__][strtolower($sDriverName)]))
          {
            continue;
          }

          include_once $sClassFile;

          $sClassName = __CLASS__ . "\\" . $sDriverName;

          if (!class_exists($sClassName, false))
          {
            continue;
          }

          if (!is_subclass_of($sClassName, __CLASS__, true))
          {
            continue;
          }

          $_SESSION['DriverList'][__CLASS__][strtolower($sDriverName)] = $sDriverName;
        }
      }
    }
  }

  /**
   * Generate and return an object of the specified type with specified parameters
   *
   * @param string $sType - the type of object to create
   * @param array $aParam - array of parameters to initialize the
   * @return self
   * @throws \Limbonia\Exception
   */
  public static function driverFactory(string $sType, ...$aParam)
  {
    $sTypeClass = self::driverClass($sType);

    if (!class_exists($sTypeClass, true))
    {
      throw new \Limbonia\Exception("Driver for " . self::classType() . " ($sType) not found!");
    }

    return new $sTypeClass(...$aParam);
  }

  /**
   * Generate and return the class name for the specified type returning an empty string if non is found
   *
   * @param string $sType
   * @return string
   */
  public static function driverClass($sType): string
  {
    $sDriver = self::driver($sType);
    return empty($sDriver) ? '' : __CLASS__ . "\\" . $sDriver;
  }

  /**
   * Return the driver list for the current object type
   *
   * @return array
   */
  public static function driverList(): array
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
  public static function driver(string $sName): string
  {
    self::generateDriverList();
    return $_SESSION['DriverList'][__CLASS__][strtolower($sName)] ?? '';
  }

  /**
   * Get the subclass type for this object
   *
   * @return string
   */
  public function getType()
  {
    if (is_null($this->sType))
    {
      $this->sType = str_replace(__CLASS__ . "\\", '', get_class($this));
    }

    return $this->sType;
  }
}
