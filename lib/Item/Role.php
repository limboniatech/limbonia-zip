<?php
namespace Limbonia\Item;

/**
 * Limbonia Role Item Class
 *
 * Item based wrapper around the Role table
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Role extends \Limbonia\Item
{
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
   * Generate and return the list of resources that this user has access to
   */
  protected function generateResourceList()
  {
    if (!is_null($this->hResource) || $this->bAdmin)
    {
      return true;
    }

    $oResult = $this->getDatabase()->prepare("SELECT COUNT(1) FROM Role_Key gk NATURAL JOIN ResourceKey rk WHERE rk.Name='Admin' AND gk.Level = 1000 AND gk.RoleID = :RoleID");
    $oResult->execute([':RoleID' => $this->hData['RoleID']]);
    $iAdminCount = $oResult->fetchColumn();
    $this->bAdmin = $iAdminCount > 0;

    if ($this->bAdmin)
    {
      $this->hResource = null;
    }
    else
    {
      $oResult = $this->getDatabase()->prepare("SELECT rl.Resource, rl.Component, rk.Name, gk.Level FROM ResourceLock rl, Role_Key gk, ResourceKey rk WHERE rk.KeyID = gk.KeyID AND (rl.KeyID = gk.KeyID OR rk.Name = 'Admin') AND rl.MinKey <= gk.Level AND gk.RoleID = :RoleID");
      $bSuccess = $oResult->execute([':RoleID' => $this->hData['RoleID']]);
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
    $this->generateResourceList();
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
    $this->generateResourceList();

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
    $oResult = $this->getDatabase()->query("SELECT KeyID, Level FROM Role_Key WHERE RoleID = $this->id");
    return $oResult->fetchAssoc();
  }

  /**
   * Return the list of resource key objects
   *
   * @return \Limbonia\ItemList
   */
  public function getResourceList()
  {
    return parent::search('ResourceKey', null, 'Name', $this->getDatabase());
  }

  /**
   * Set the specified list of resource keys for this user
   *
   * @param array $hResource
   */
  public function setResourceKeys($hResource)
  {
    $this->getDatabase()->exec('DELETE FROM Role_Key WHERE RoleID = ' . $this->id);

    if (count($hResource) > 0)
    {
      $oResult = $this->getDatabase()->prepare("INSERT INTO Role_Key VALUES ($this->id, :Key, :Level)");

      foreach ($hResource as $iKey => $iLevel)
      {
        $oResult->execute([':Key' => $iKey, ':Level' => (integer)$iLevel]);
      }
    }
  }
}