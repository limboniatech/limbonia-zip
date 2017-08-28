<?php
namespace Omniverse\Item;

/**
 * Omniverse State Item Class
 *
 * Item based wrapper around the States table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class States extends \Omniverse\Item
{
  /**
   * List of states by zip code
   *
   * @var array
   */
  protected static $hState = [];

  /**
   * Return the list of states 
   *
   * @return array
   */
  public static function getStateList()
  {
    if (empty(self::$hState))
    {
      $oStates = parent::search('States', ['Actual' => 1], 'State');

      if (count($oStates) > 0)
      {
        foreach ($oStates as $hTemp)
        {
          self::$hState[$hTemp['PostalCode']] = $hTemp['State'];
        }
      }
    }

    return self::$hState;
  }
}