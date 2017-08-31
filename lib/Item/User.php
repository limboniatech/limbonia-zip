<?php
namespace Omniverse\Item;

/**
 * Omniverse User Item Class
 *
 * Item based wrapper around the User table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class User extends \Omniverse\Item
{
  /**
   * The default password length
   */
  const PASSWORD_DEFAULT_LENGTH = 16;

  /**
   * The "cost" for the encryption function to  use
   */
  const PASSWORD_ENCRYPTION_COST = 10;

  /**
   * The name of the algorithm used to encrypt the password
   */
  const PASSWORD_ENCRYPTION_ALGO = PASSWORD_BCRYPT;

  /**
   * List of resources that this user has access to
   *
   * @var array
   */
  protected $hResource = null;

  /**
   * Is this user an admin?
   *
   * @var boolean
   */
  protected $bAdmin = false;

  /**
   * Generate and return a user object from the specified email
   *
   * @param string $sEmail
   * @param \Omniverse\Database $oDatabase (optional)
   * @return \Omniverse\Item\User User object on success or false on failure
   */
  public static function getByEmail($sEmail, \Omniverse\Database $oDatabase = null)
  {
    \Omniverse\Email::validate($sEmail, false);
    $oUserList = parent::search('User', ['Email' => $sEmail], null, $oDatabase);
    return count($oUserList) == 0 ? false : $oUserList[0];
  }

  /**
   * Make sure the specified password follows all the current guidelines
   *
   * @todo Create method for adding / controlling the password guidelines with config and scripting options
   *
   * @param string $sPassword
   * @throws \Exception
   */
  public static function validatePassword($sPassword)
  {
    if (empty($sPassword))
    {
      throw new \Exception('Empty password.');
    }
  }

  /**
   * Generate and return a valid user from the specified email and password
   *
   * @param string $sEmail
   * @param string $sPassword
   * @return \Omniverse\Item\User
   * @throws \Exception
   */
  public static function login($sEmail, $sPassword)
  {
    self::validatePassword($sPassword);
    $oUser = self::getByEmail($sEmail);

    if ($oUser == false)
    {
      throw new \Exception("Invalid user/password");
    }

    if (!$oUser->active)
    {
      throw new \Exception("Invalid user/password");
    }

    if (!password_verify($sPassword, $oUser->password))
    {
      throw new \Exception("Invalid user/password");
    }

    return $oUser;
  }

  /**
   * Generate and return and password of the specified length
   *
   * @param integer $iLength (optional)
   * @return string
   */
  public static function generatePassword($iLength = self::PASSWORD_DEFAULT_LENGTH)
  {
    $iLength = empty($iLength) ? self::PASSWORD_DEFAULT_LENGTH : (integer)$iLength;
    $sLetters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $sPassword = '';

    for ($i = 0; $i < $iLength; $i++)
    {
      $sPassword .= $sLetters[(rand(1, strlen($sLetters) - 1))];
    }

    return $sPassword;
  }

  /**
   * Reset this user's password to something random and return that password
   *
   * @return string
   */
  public function resetPassword()
  {
    $sPassword = self::generatePassword();
    $this->password = $sPassword;
    $this->save();
    return $sPassword;
  }

  /**
   * Generate and return the list of resources that this user has access to
   */
  protected function generateResourceList()
  {
    $oResult = $this->getDB()->prepare("SELECT COUNT(1) FROM User_Key uk NATURAL JOIN ResourceKey rk WHERE rk.Name='Admin' AND uk.Level = 1000 AND uk.UserID = :UserID");
    $oResult->execute([':UserID' => $this->hData['UserID']]);
    $iAdminCount = $oResult->fetchOne();
    $this->bAdmin = $iAdminCount > 0;

    if ($this->bAdmin)
    {
      $this->hResource = null;
    }
    else
    {
      $oResult = $this->getDB()->prepare("SELECT rl.Resource, rl.Component, rk.Name, uk.Level FROM ResourceLock rl, User_Key uk, ResourceKey rk WHERE rk.KeyID=uk.KeyID AND (rl.KeyID=uk.KeyID OR rk.Name='Admin') AND rl.MinKey <= uk.Level AND uk.UserID = :UserID");
      $bSuccess = $oResult->execute([':UserID' => $this->hData['UserID']]);
      $this->hResource = [];

      if ($bSuccess && count($oResult) > 0)
      {
        foreach ($oResult as $hResource)
        {
          $this->hResource[$hResource['Resource']][] = $hResource['Component'];
        }
      }
    }
  }

  /**
   * Is this user an admin?
   *
   * @return boolean
   */
  public function isAdmin()
  {
    if (is_null($this->hResource) && !$this->bAdmin)
    {
      $this->generateResourceList();
    }

    return $this->bAdmin;
  }

  /**
   * Does this user have the specified resource?
   *
   * @param string $sResource
   * @param string $sComponent (optional)
   * @return boolean
   */
  public function hasResource($sResource, $sComponent = null)
  {
    if ($this->isAdmin())
    {
      return true;
    }

    if (empty($sComponent))
    {
      return isset($this->hResource[$sResource]);
    }

    return isset($this->hResource[$sResource]) && in_array($sComponent, $this->hResource[$sResource]);
  }

  /**
   * Return the list of resource keys and their levels that this user has
   *
   * @return array
   */
  public function getResourceKeys()
  {
    $oResult = $this->getDB()->query("SELECT KeyID, Level FROM User_Key WHERE UserID = $this->id");
    return $oResult->fetchAssoc();
  }

  /**
   * Return the list of resource key objects
   *
   * @return \Omniverse\ItemList
   */
  public function getResourceList()
  {
    return parent::search('ResourceKey', null, 'Name', $this->getDB());
  }

  /**
   * Set the specified list of resource keys for this user
   *
   * @param array $hResource
   */
  public function setResourceKeys($hResource)
  {
    $this->getDB()->exec('DELETE FROM User_Key WHERE UserID = ' . $this->id);

    if (count($hResource) > 0)
    {
      $oResult = $this->getDB()->prepare("INSERT INTO User_Key VALUES ($this->id, :Key, :Level)");

      foreach ($hResource as $iKey => $iLevel)
      {
        $oResult->execute([':Key' => $iKey, ':Level' => $iLevel]);
      }
    }
  }

  /**
   * Format the specified value to valid input using type data from the specified column
   *
   * @param string $sColumn
   * @param mixed $xValue
   * @return mixed
   */
  protected function formatInput($sName, $xValue)
  {
    if ($sName == 'Password' && $this->isCreated())
    {
      return password_hash($xValue, self::PASSWORD_ENCRYPTION_ALGO, ['cost' => self::PASSWORD_ENCRYPTION_COST]);
    }

    if ($sName == 'Email' && $this->isCreated())
    {
      $xValue = trim($xValue);
      //if it validates successfully then let the normal value be returned...
      \Omniverse\Email::validate($xValue);
    }

    return parent::formatInput($sName, $xValue);
  }

  /**
   * Get the specified data
   *
   * @param string $sName
   * @return mixed
   */
  public function __get($sName)
  {
    if (strtolower($sName) == 'name')
    {
      return trim("$this->firstName $this->lastName");
    }

    return parent::__get($sName);
  }

  /**
   * Determine if the specified value is set (exists) or not...
   *
   * @param string $sName
   * @return boolean
   */
  public function __isset($sName)
  {
    if (strtolower($sName) == 'name')
    {
      return true;
    }

    return parent::__isset($sName);
  }

  public function getTickets()
  {
    return parent::search('Ticket', ['OwnerID' => $this->id, 'Status' => '!=:closed'], ['Priority', 'DueDate DESC'], $this->getDB());
  }
}