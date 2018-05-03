<?php
namespace Limbonia\Module;

/**
 * Limbonia Ticket Category Module class
 *
 * Admin module for handling ticket categories
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class TicketCategory extends \Limbonia\Module
{
  use \Limbonia\Traits\ItemModule
  {
    \Limbonia\Traits\ItemModule::getColumns as originalGetColumns;
  }

  /**
   * List of column names in the order required
   *
   * @return array
   */
  protected function columnOrder()
  {
    return
    [
      'Name',
      'ParentID',
      'AssignmentMethod',
      'UserID',
      'RoleID',
      'KeyID',
      'Level'
    ];
  }

  protected function getAdminHeader()
  {
    $sHeader = parent::getAdminHeader();
    $sHeader .= "\n<style>
#TicketCategoryUserIDField,
#TicketCategoryRoleIDField,
#TicketCategoryKeyIDField,
#TicketCategoryLevelField
{
  display: none;
}
</style>\n
<script type=\"text/javascript\">
/**
 * Toggle the associated data when the Asignment Method is changed
 *
 * @param {String} sOption
 * @returns {Boolean}
 */
function toggleMethod(sOption)
{
  userDiv = document.getElementById('TicketCategoryUserIDField');
  roleDiv = document.getElementById('TicketCategoryRoleIDField');
  keyDiv = document.getElementById('TicketCategoryKeyIDField');
  levelDiv = document.getElementById('TicketCategoryLevelField');

  if (sOption === 'unassigned')
  {
    userDiv.style.display = roleDiv.style.display = keyDiv.style.display = levelDiv.style.display = 'none';
  }

  if (sOption === 'least tickets by resource' || sOption === 'round robin by resource')
  {
    userDiv.style.display = roleDiv.style.display = 'none';
    keyDiv.style.display = levelDiv.style.display = 'block';
  }

  if (sOption === 'least tickets by role' || sOption === 'round robin by role')
  {
    userDiv.style.display = keyDiv.style.display = levelDiv.style.display = 'none';
    roleDiv.style.display = 'block';
  }

  if (sOption === 'direct')
  {
    userDiv.style.display = 'block';
    roleDiv.style.display = keyDiv.style.display = levelDiv.style.display = 'none';
  }
}

$('#TicketCategoryAssignmentMethod').change(function()
{
  toggleMethod($(this).val());
});

toggleMethod($('#TicketCategoryAssignmentMethod').val());
</script>\n";

    return $sHeader;
  }

    /**
   * Generate and return a list of columns based on the specified type
   *
   * @param string $sType (optional)
   * @return array
   */
  public function getColumns($sType = null)
  {
    $sLowerType = strtolower($sType);

    if ($sLowerType == 'view')
    {
      switch ($this->oItem->assignmentMethod)
      {
        case 'direct':
          $this->aIgnore['view'] =
          [
            'RoleID',
            'KeyID',
            'Level'
          ];
          break;

        case 'unassigned':
          $this->aIgnore['view'] =
          [
            'UserID',
            'RoleID',
            'KeyID',
            'Level'
          ];
          break;

        case 'round robin by role':
        case 'least tickets by role':
          $this->aIgnore['view'] =
          [
            'UserID',
            'KeyID',
            'Level'
          ];
          break;

        case 'round robin by resource':
        case 'least tickets by resource':
          $this->aIgnore['view'] =
          [
            'KeyID',
            'Level'
          ];
          break;
      }
    }

    return $this->originalGetColumns($sType);
  }

  /**
   * Generate and return the HTML for the specified form field based on the specified information
   *
   * @param string $sName
   * @param string $sValue
   * @param array $hData
   * @return string
   */
  public function getFormField($sName, $sValue = null, $hData = [])
  {
    if ($sName == 'ParentID')
    {
      $oSelect = $this->oController->widgetFactory('Select', "$this->sType[ParentID]");
      $oSelect->addOption('None', '');
      $oCategories = \Limbonia\Item::search('TicketCategory', ['CategoryID' => '!=:' . $this->oItem->id], 'Name');

      foreach ($oCategories as $oCat)
      {
        if ($sValue == $oCat->id)
        {
          $oSelect->setSelected($oCat->id);
        }

        $oSelect->addOption($oCat->name, $oCat->id);
      }

      return self::widgetField($oSelect, 'Parent');
    }

    return parent::getFormField($sName, $sValue, $hData);
  }

  /**
   * Generate and return the HTML for the specified form field based on the specified information
   *
   * @param string $sName
   * @param string $sValue
   * @param array $hData
   * @return string
   */
  public function getField($sName, $sValue = null, $hData = [])
  {
    if ($sName == 'ParentID')
    {
      if (empty($sValue))
      {
        return '';
      }

      $oCategory = $this->oController->itemFromId('ticketcategory', $sValue);
      return $this->field($oCategory->name, 'Parent', $this->sType . $sName);
    }

    return parent::getField($sName, $sValue, $hData);
  }
}