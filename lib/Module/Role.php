<?php
namespace Limbonia\Module;

/**
 * Limbonia Role Module class
 *
 * Admin module for handling groups
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Role extends \Limbonia\Module
{
  use \Limbonia\Traits\ItemModule
  {
    \Limbonia\Traits\ItemModule::processApiGetItem as originalprocessApiGetItem;
  }

  /**
   * List of sub-menu options
   *
   * @var array
   */
  protected $hSubMenuItems =
  [
    'view' => 'View',
    'edit' => 'Edit',
    'resources' => 'Resources'
  ];

  /**
   * List of actions that are allowed to run
   *
   * @var array
   */
  protected $aAllowedActions = ['search', 'create', 'editdialog', 'editcolumn', 'edit', 'list', 'view', 'resources'];

    /**
   * Generate and return the default item data, filtered by API controls
   *
   * @return array
   * @throws \Exception
   */
  protected function processApiGetItem()
  {
    switch ($this->oApi->action)
    {
      case 'resources':
        $hResourceList = [];
        $hKeys = $this->oItem->getResourceKeys();

        foreach ($this->oItem->getResourceList() as $oResource)
        {
          $hResourceList[$oResource->id] = $oResource->getAll();
          $hResourceList[$oResource->id]['Level'] = $hKeys[$oResource->id];
        }

        return $hResourceList;
    }

    return $this->originalProcessApiGetItem();
  }

    /**
   * Process the posted resource data and display the result
   */
  protected function prepareTemplatePostResources()
  {
    try
    {
      $hData = $this->editGetData();

      if (!isset($hData['ResourceKey']))
      {
        throw new \Exception("Resource key list not found");
      }

      $this->oItem->setResourceKeys($hData['ResourceKey']);
      $this->oController->templateData('success', "This user's resource update has been successful.");
    }
    catch (\Exception $e)
    {
      $this->oController->templateData('failure', "This user's resource update has failed. <!--" . $e->getMessage() . '-->');
    }

    if (isset($_SESSION['EditData']))
    {
      unset($_SESSION['EditData']);
    }

    $this->oController->server['request_method'] = 'GET';
    $this->sCurrentAction = 'view';
  }
}