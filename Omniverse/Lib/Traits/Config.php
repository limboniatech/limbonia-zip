<?php
namespace Omniverse\Lib\Traits;

/**
 * Omniverse Programming Config Trait
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.3 $
 * @package Omniverse\Lib
 */
trait Config
{
  /**
   * The Configuration array for this object
   *
   * @var array
   */
  protected $hConfig = [];

  public function __isset($sName)
  {
    return array_key_exists($sName, $this->hConfig);
  }

  public function __unset($sName)
  {
    if ($this->__isset($sName))
    {
      unset($this->hConfig[$sName]);
    }
  }

  public function __set($sName, $xValue)
  {
    $this->hConfig[$sName] = $xValue;
  }

  public function __get($sName)
  {
    return $this->hConfig[$sName];
  }

  /**
   * Read the specified ini file(s) and store the data fro later use.
   *
   * @param array|string $xIni Either an array of ini files or a single ini file
   */
  protected function readIni($xIni)
  {
    $aIni = is_array($xIni) ? $xIni : explode(',', $xIni);

    foreach ($aIni as $sIni)
    {
      $sIni = trim($sIni);

      if (is_readable($sIni))
      {
        $this->hConfig = array_merge($this->hConfig, parse_ini_file($sIni, true));
      }
    }
  }

  /**
   * Directly store the specified config data
   *
   * @param array $hConfig
   */
  protected function setConfig(array $hConfig = [])
  {
    $this->hConfig = $hConfig;
  }

  /**
   * Set a whole section of the internal config data with the specifie array
   *
   * @param string $sSection
   * @param array $hSection
   */
  protected function setSection($sSection, array $hSection = [])
  {
    $this->hConfig[$sSection] = $hSection;
  }

  /**
   * Set the specified value into the specified location
   *
   * @param string $sSection
   * @param string $sName
   * @param string $sValue
   */
  protected function setValue($sSection, $sName, $sValue)
  {
    $this->hConfig[$sSection][$sName] = $Value;
  }

  /**
   * Does the specified section exist?
   *
   * @param string $sSection
   * @return boolean
   */
  public function hasSection($sSection)
  {
    return isset($this->hConfig[$sSection]) && is_array($this->hConfig[$sSection]);
  }

  /**
   * Return the specified section
   *
   * @param string $sSection
   * @return array
   */
  public function getSection($sSection)
  {
    return $this->hasSection($sSection) ? $this->hConfig[$sSection] : [];
  }

  /**
   * Does the speified value exist?
   *
   * @param string $sName
   * @param string $sSection
   * @return boolean
   */
  public function hasValue($sName, $sSection = null)
  {
    return (empty($sSection) && isset($this->hConfig[$sName])) || isset($this->hConfig[$sSection][$sName]);
  }

  /**
   * Get ans return the specified value
   *
   * @param string $sName
   * @param string $sSection
   * @return mixed
   */
  public function getValue($sName, $sSection = null)
  {
    if (empty($sSection) && isset($this->hConfig[$sName]))
    {
      return $this->hConfig[$sName];
    }

    if (isset($this->hConfig[$sSection][$sName]))
    {
      return $this->hConfig[$sSection][$sName];
    }

    return null;
  }
}
