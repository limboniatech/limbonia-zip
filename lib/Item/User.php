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
  const PASSWORD_DEFAULT_LENGTH = 16;
  const PASSWORD_ENCRYPTION_COST = 10;
  const PASSWORD_ENCRYPTION_ALGO = PASSWORD_BCRYPT;
  protected $hResource = null;
  protected $bAdmin = false;

  public static function getByEmail($sEmail, Database $oDatabase = null)
  {
    \Omniverse\Email::validate($sEmail, false);
    $oUserList = parent::search('User', ['Email' => $sEmail], null, $oDatabase);
    return count($oUserList) == 0 ? false : $oUserList[0];
  }

  public static function validatePassword($sPassword)
  {
    if (empty($sPassword))
    {
      throw new \Exception('Empty password.');
    }
  }

  public static function login($sEmail, $sPassword)
  {
    $sPass = trim($sPassword);
    self::validatePassword($sPassword);

    if (!$oUser = self::getByEmail($sEmail))
    {
      throw new \Exception("Invalid user/password");
    }

    if (!$oUser->Active)
    {
      throw new \Exception("Invalid user/password");
    }

    if (!password_verify($sPassword, $oUser->Password))
    {
      throw new \Exception("Invalid user/password");
    }

    return $oUser;
  }

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

  public function resetPassword()
  {
    $sPassword = self::generatePassword();
    $this->password = $sPassword;
    $this->save();
    return $sPassword;
  }

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

  public function isAdmin()
  {
    if (is_null($this->hResource) && !$this->bAdmin)
    {
      $this->generateResourceList();
    }

    return $this->bAdmin;
  }

  public function hasResource($sResource, $sComponent=null)
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

  public function getResourceKeys()
  {
    $oResult = $this->getDB()->prepare("SELECT KeyID, Level FROM User_Key WHERE UserID = :UserID");
    return $oResult->fetchAssoc([':UserID' => $this->ID]);
  }

  public function getResourceList()
  {
    return parent::search('ResourceKey', null, 'Name', $this->getDB());
  }

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

  public function __get($sName)
  {
    if (strtolower($sName) == 'name')
    {
      return trim("$this->firstName $this->lastName");
    }

    return parent::__get($sName);
  }

  public function __isset($sName)
  {
    if (strtolower($sName) == 'name')
    {
      return true;
    }

    return parent::__isset($sName);
  }
}