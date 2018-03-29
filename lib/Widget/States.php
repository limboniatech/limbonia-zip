<?php
namespace Limbonia\Widget;

/**
 * Limbonia States Widget
 *
 * The methods needed to load cities and states
 *
 * @author Lonnie Blansett <lonnie@limbonia.tech>
 * @package Limbonia
 */
class States extends Select
{
  protected $hExtra = ['Cities' => 'City'];

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
    $hState = array_merge(['0' => 'Select a State'] , \Limbonia\Item\States::getStateList());
    $this->addArray($hState);
  }

  /**
   * Generate and return the cities located in the specified state
   *
   * @param string $sState
   * @param string $sWidget
   * @param string $sSelectedCity
   * @return string
   */
  public function ajax_getCitiesByState($sState, $sWidget, $sSelectedCity='')
  {
    $sCities = '';
    $sCities .= "var c=document.getElementById('$sWidget');\n";
    $sCities .= "for (i = c.length - 1 ; i > 0 ; i--) {c.options[i] = null;}\n";

    if ($sState != '0')
    {
      $oCity = $this->getController()->itemFactory('ZipCode')->getCitiesByState($sState);

      foreach ($oCity as $iKey => $hData)
      {
        $iScriptCount = $iKey + 1;
        $sCity = str_replace("'", "\'", ucwords(strtolower($hData['City'])));
        $sCities .= "c.options[$iScriptCount] = new Option('$sCity', '" . str_replace("'", "\'", $hData['City']) . "');\n";

        if ($sSelectedCity == $hData['City'])
        {
          $sCities .= "c.options[$iScriptCount].selected = true;\n";
        }
      }
    }

    return $sCities;
  }

  /**
   * Generate and return the zips located in the specified city
   *
   * @param string $sCity
   * @param string $sState
   * @param string $sWidget
   * @param string $sSelectedZip
   * @return string
   */
  public function ajax_getZipsByCity($sCity, $sState, $sWidget, $sSelectedZip)
  {
    $sZips = '';
    $sZips .= "var z=document.getElementById('$sWidget');\n";
    $sZips .= "for (i = z.length - 1 ; i > 0 ; i--) {z.options[i] = null;}\n";

    if ($sCity != '0')
    {
      $sCity = str_replace('%20', ' ', $sCity);
      $oZip = $this->getController()->itemFactory('ZipCode')->getZipsByCity($sCity, $sState);

      foreach ($oZip as $iKey => $hData)
      {
        $iScriptCount = $iKey + 1;
        $sZips .= "z.options[$iScriptCount] = new Option('{$hData['Zip']}', '{$hData['Zip']}');\n";

        if ($sSelectedZip == $hData['Zip'])
        {
          $sZips .= "z.options[$iScriptCount].selected = true;\n";
        }
      }
    }

    return $sZips;
  }
}