<?php
namespace Omniverse\Item;

/**
 * Omniverse Customer Item Class
 *
 * Item based wrapper around the Customer table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class Customer extends \Omniverse\Item
{
  /**
   * Return the entire list of
   *
   * @return \Omniverse\ItemList
   */
  public function getContactList()
  {
    return parent::getList('user', "SELECT * FROM User U, Customer_User CU WHERE U.UserID = CU.UserID AND U.Type = 'contact' AND CU.CustomerID = ?", [$this->id], $this->getDB());
  }
}