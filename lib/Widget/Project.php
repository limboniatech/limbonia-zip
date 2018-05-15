<?php
namespace Limbonia\Widget;

/**
 * Limbonia Project Widget
 *
 * The methods needed to load project names and releases
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class Project extends \Limbonia\Widget\Select
{
  /**
   * Constructor
   *
   * It increments the widget counter and generates a unique (but human readable) name.
   *
   * @param string $sName (optional)
   * @param \Limbonia\Controller $oController (optional)
   * @throws Limbonia\Exception\Object
   */
  public function __construct($sName = null, \Limbonia\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->sType = 'select';
    $this->addOption('Select a Project', '0');
    $oProjectList = \Limbonia\Item\Project::getProjectList($this->getController());

    foreach ($oProjectList as $oProject)
    {
      $this->addOption($oProject->name, $oProject->id);
    }
  }

  /**
   * Generate and return the release list associated with the specified project ID
   *
   * @param integer $iProject
   * @param string $sWidgetId
   * @param integer $iSelectedRelease
   * @return string
   */
  public function ajax_getReleasesByProject($iProject, $sWidgetId, $iSelectedRelease='')
  {
    $sVersions = '';
    $sVersions .= "var c=document.getElementById('$sWidgetId');";
    $sVersions .= "for (i = c.length - 1 ; i > 0 ; i--) {c.options[i] = null;}";

    if ($iProject != '0' && !empty($iProject))
    {
      $oProject = $this->getController()->itemFromId('project', $iProject);
      $oReleaseList = $oProject->getReleaseList('active');

      foreach ($oReleaseList as $iKey => $oRelease)
      {
        $iScriptCount = $iKey + 1;
        $sVersions .= "c.options[$iScriptCount] = new Option('" . $oRelease->version . "', '" . $oRelease->id . "');";

        if ($iSelectedRelease == $oRelease->id)
        {
          $sVersions .= "c.options[$iScriptCount].selected = true;";
        }
      }
    }

    return $sVersions;
  }

  /**
   * Generate and return the category list associated with the specified project ID
   *
   * @param integer $iProject
   * @param string $sWidget
   * @param integer $iSelectedCategory
   * @return string
   */
  public function ajax_getCategorysByProject($iProject, $sWidget, $iSelectedCategory='')
  {
    $sCategories = '';
    $sCategories .= "var c=document.getElementById('$sWidget');";
    $sCategories .= "for (i = c.length - 1 ; i > 0 ; i--) {c.options[i] = null;}";

    if ($iProject != '0' && !empty($iProject))
    {
      $oProject = $this->getController()->itemFromId('project', $iProject);
      $oElementList = $oProject->getCategoryList();

      foreach ($oElementList as $iKey => $oElement)
      {
        $iScriptCount = $iKey + 1;
        $sCategories .= "c.options[$iScriptCount] = new Option('" . $oElement->name . "', '" . $oElement->id . "');";

        if ($iSelectedCategory == $oElement->id)
        {
          $sCategories .= "c.options[$iScriptCount].selected = true;";
        }
      }
    }

    return $sCategories;
  }
}