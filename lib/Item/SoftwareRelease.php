<?php
namespace Omniverse\Item;

/**
 * Omniverse Software Release Item Class
 *
 * Item based wrapper around the SoftwareRelease table
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.1 $
 * @package Omniverse
 */
class SoftwareRelease extends \Omniverse\Item
{
  protected $aNoUpdate = ['TicketID'];

  public function setAll(array $hItem = [])
  {
    $hExtra = parent::setAll($hItem);

    if (isset($hExtra['Version']))
    {
      $this->__set('Version', $hExtra['Version']);
      unset($hExtra['Version']);
    }

    return $hExtra;
  }

  public function __get($sName)
  {
    if ($sName == 'Version')
    {
      return "$this->Major.$this->Minor.$this->Patch";
    }

    if ($sName == 'Released')
    {
      $oResult = $this->getDB()->prepare("SELECT Status FROM Ticket WHERE TicketID = :TicketID");
      $sStatus = $oResult->getOne([':TicketID' => $this->TicketID]);
      return $sStatus == 'closed';
    }

    return parent::__get($sName);
  }

  public function __set($sName, $xValue)
  {
    if ($sName == 'Version')
    {
      $aVersion = array_map('trim', explode('.', $xValue));
      $iMajor = isset($aVersion[0]) ? (integer)$aVersion[0] : 0;
      $iMinor = isset($aVersion[1]) ? (integer)$aVersion[1] : 0;
      $iPatch = isset($aVersion[2]) ? (integer)$aVersion[2] : 0;

      parent::__set('Major', $iMajor);
      parent::__set('Minor', $iMinor);
      parent::__set('Patch', $iPatch);
    }
    else
    {
      parent::__set($sName, $xValue);
    }
  }

  public function __isset($sName)
  {
    if ($sName == 'Version' || $sName == 'Released')
    {
      return true;
    }

    return parent::__isset($sName);
  }

  protected function create()
  {
    $hTicket =
    [
      'Status' => 'pending',
      'Priority' => 'normal',
      'Type' => 'system',
      'Subject' => "Software release: {$this->Software->Name}  {$this->Version}"
    ];

    $oTicket = parent::fromArray('Ticket', $hTicket, $this->getDB());
    $this->TicketID = $oTicket->save();

    try
    {
      return parent::create();
    }
    catch (\Exception $e)
    {
      //since the release wasn't create successfully remove it's ticket
      $oTicket->delete();
      throw $e;
    }
  }

  protected function update()
  {
    $xSuccess = parent::update();

    try
    {
      $this->Ticket->Subject = "Software release: {$this->Software->Name}  {$this->Version}";
      $this->Ticket->save();
    }
    catch (\Exception $e) {}

    return $xSuccess;
  }

  public function delete()
  {
    $oTicket = $this->ticket;
    $bSuccess = parent::delete();
    $oTicket->delete();
    return $bSuccess;
  }

  public function getTicketList($sType=null)
  {
    $sType = strtolower(trim($sType));
    $hCriteria =
    [
      'Type' => 'software',
      'SoftwareID' => $this->SoftwareID,
      'ReleaseID' => $this->ID
    ];

    if ($sType == 'complete')
    {
      $sSQL = "SELECT DISTINCT Ticket.* FROM Ticket WHERE Type = 'software' AND SoftwareID = $this->softwareId AND ReleaseID = $this->id AND (DevStatus = 'complete' OR Status = 'closed')";
      return parent::getList('Ticket', $sSQL, $this->getDB());
    }

    if ($sType == 'incomplete')
    {
      $hCriteria['DevStatus'] = "!=:complete";
      $hCriteria['Status'] = '!=:closed';
      $oTicket = \Omniverse\Item::factory('Ticket', $this->getDB());
      $hIncomplete = array();

      foreach ($oTicket->priorityList as $sPriority)
      {
        $hCriteria['Priority'] = $sPriority;
        $hIncomplete[$sPriority] = parent::search('Ticket', $hCriteria, null, $this->getDB());
      }

      return $hIncomplete;
    }

    return parent::search('Ticket', $hCriteria, null, $this->getDB());
  }
}