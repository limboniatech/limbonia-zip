<?php
namespace Limbonia;

/**
 * Limbonia Tag base class
 *
 * This defines all the basic parts of a basic HTML tag
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Tag implements \Limbonia\Interfaces\Tag
{
  use \Limbonia\Traits\Tag;
  use \Limbonia\Traits\DriverList;

  /**
   * factory method that creates an instance of a specific type of widget.
   * It must be called statically.
   *
   * @param string $sType - The type of widget to instantiate
   * @return "mixed" - The object requested on success, otherwise false.
   */
  public static function factory($sType)
  {
    $sTypeClass = self::driverClass($sType);

    if (\class_exists($sTypeClass, true))
    {
      return new $sTypeClass();
  }

    return new Tag($sType);
  }

  /**
   * Constructor
   *
   * It generates the type for further use later...
   */
  public function __construct($sType=null)
  {
    if (!isset($this->sType))
    {
      $this->sType = $sType;
      $this->getType();
    }
  }
}