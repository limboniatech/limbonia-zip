<?php
namespace Omniverse;

/**
 * Omniverse input class
 *
 * This defines all the basic parts of Omniverse input
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Input implements \ArrayAccess, \Countable, \SeekableIterator
{
  /**
   * Inherit the Hash trait
   */
  use \Omniverse\Traits\Hash;

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
   * @return \Omniverse\Input
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
   * @throws \Omniverse\Exception\Object
   */
  protected function __construct($sType)
  {
    if (!isset(self::$aValidTypes[$sType]))
    {
      throw new \Omniverse\Exception\Object("Invalid input type found: $sType");
    }

    $hData = filter_input_array(self::$aValidTypes[$sType]);

    //to work around a PHP bug in getting server variables on some systems...
    //If PHP ever fixes that bug then this if statement can be removed!
    if ($sType == 'server' && count($hData) < 10)
    {
      $hData = $_SERVER;
    }

    $this->hData = is_null($hData) ? [] : \array_change_key_case($hData, CASE_LOWER);
  }
}