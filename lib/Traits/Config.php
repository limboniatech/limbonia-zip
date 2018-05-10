<?php
namespace Limbonia\Traits;

/**
 * Limbonia Config Trait
 *
 * This trait allows an inheriting class to read, access and save configuration
 * data to and from an INI file
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
trait Config
{
  /**
   * The Configuration array for this object
   *
   * @var array
   */
  protected $hConfig = [];

  /**
   * Magic method used to determine if the specified property is set
   *
   * @param string $sName
   * @return boolean
   */
  public function __isset($sName)
  {
    return array_key_exists(strtolower($sName), $this->hConfig);
  }

  /**
   * Magic method used to remove the specified property
   *
   * @param string $sName
   */
  public function __unset($sName)
  {
    $sLowerName = strtolower($sName);

    if ($this->__isset($sLowerName))
    {
      unset($this->hConfig[$sLowerName]);
    }
  }

  /**
   * Magic method used to set the specified property to the specified value
   *
   * @param string $sName
   * @param mixed $xValue
   */
  public function __set($sName, $xValue)
  {
    $this->hConfig[strtolower($sName)] = $xValue;
  }

  /**
   * Magic method used to generate and return the specified property
   *
   * @param string $sName
   * @return mixed
   */
  public function __get($sName)
  {
    return $this->hConfig[strtolower($sName)];
  }

  /**
   * Read the specified INI file(s) and store the data fro later use.
   *
   * @param array|string $xIni Either an array of INI files or a single INI file
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
   * Set a whole section of the internal config data with the specified array
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
   * Does the specified value exist?
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
   * Return the specified value
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
