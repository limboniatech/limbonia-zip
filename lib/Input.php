<?php
namespace Limbonia;

/**
 * Limbonia input class
 *
 * This defines all the basic parts of Limbonia input
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Input implements \ArrayAccess, \Countable, \SeekableIterator
{
  /**
   * Inherit the Hash trait
   */
  use \Limbonia\Traits\Hash;

  /**
   * List of singleton classes
   *
   * @var array
   */
  static protected $hSingletons = [];

  /**
   * List of valid input types
   *
   * @var array
   */
  static protected $aValidTypes =
  [
    'cookie' => INPUT_COOKIE,
    'env' => INPUT_ENV,
    'get' => INPUT_GET,
    'post' => INPUT_POST,
    'server' => INPUT_SERVER
  ];

  /**
   * Only allow the creation of only one input class of each type
   *
   * @param string $sType
   * @return \Limbonia\Input
   */
  public static function singleton($sType)
  {
    $sLowerType = \strtolower($sType);

    if (!isset(self::$hSingletons[$sLowerType]))
    {
      self::$hSingletons[$sLowerType] = new self($sLowerType);
    }

    return self::$hSingletons[$sLowerType];
  }

  /**
   * The constructor
   *
   * @param string $sType
   * @throws \Limbonia\Exception
   */
  protected function __construct($sType)
  {
    if (!isset(self::$aValidTypes[$sType]))
    {
      throw new \Limbonia\Exception("Invalid input type found: $sType");
    }

    //to work around a PHP bug in getting server variables on some systems...
    $hData = $sType === 'server' ? $_SERVER : filter_input_array(self::$aValidTypes[$sType]);
    $this->hData = is_null($hData) ? [] : \array_change_key_case($hData, CASE_LOWER);
  }
}