<?php
namespace Omniverse\Widget;

class States extends Select
{
  protected $hExtra = array('Cities' => 'City');

  public function __construct($sName = null, \Omniverse\Controller $oController = null)
  {
    parent::__construct($sName, $oController);
    $this->sType = 'select';
    $hState = array_merge(array('0' => 'Select a State') , \Omniverse\Item\States::getStateList());
    $this->addArray($hState);
  }

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