<?php
/**
 * Omniverse Table Class
 *
 * This is a light wrapper around an HTML table with some javascript for sorting
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.4 $
 * @package Omniverse
 */
namespace Omniverse\Widget;

class Table extends \Omniverse\Widget
{
  protected $aHead = null;
  protected $aFoot = null;
  protected $oCurrentRow = null;
  protected $sCurrentType = null;

  protected function init()
  {
    $sParamList = $this->getParam();
    $this->sPreScript .= "<table$sParamList>\n";
    $bAddBody = !empty($this->aHead) || !empty($this->aFoot);

    if ($this->aHead)
    {
      $this->sPreScript .= "<thead>\n";

      foreach ($this->aHead as $oRow)
      {
        $this->sPreScript .= $oRow;
      }

      $this->sPreScript .= "</thead>\n";
    }

    if ($bAddBody)
    {
      $this->sPreScript .= "<tbody>\n";
    }

    foreach ($this->aContent as $oRow)
    {
      $this->sPreScript .= $oRow;
    }

    if ($bAddBody)
    {
      $this->sPreScript .= "</tbody>\n";
    }

    if ($this->aFoot)
    {
      $this->sPreScript .= "<tfoot>\n";

      foreach ($this->aFoot as $oRow)
      {
        $this->sPreScript .= $oRow;
      }

      $this->sPreScript .= "</tfoot>\n";
    }

    $this->sPreScript .= "</table>\n";
    return true;
  }

  public function makeSortable()
  {
    \Omniverse\Widget::includeScript($this->sWebShareDir . "/sorttable.js");
    $this->addClass('sortable');
  }

  public function isSortable()
  {
   return preg_match("#\bsortable\b#", $this->getRawParam('class'));
  }

  public function startRow($sType=null)
  {
    $this->endRow();
    $this->sCurrentType = strtolower($sType);
    $this->oCurrentRow = \Omniverse\Tag::factory('TableRow');
    return $this->oCurrentRow;
  }

  public function startHeader()
  {
    return $this->startRow('head');
  }

  public function startFooter()
  {
    return $this->startRow('foot');
  }

  public function addCell($xData, $xSort=null)
  {
    if (!($this->oCurrentRow instanceof \Omniverse\Tag))
    {
      $this->startRow();
    }

    if (!($xData instanceof \Omniverse\Tag))
    {
      $sData = (string)$xData;
      $sCellType = $this->sCurrentType == 'head' && empty($this->aHead) ? 'TableHeader' : 'TableCell';
      $xData = \Omniverse\Tag::factory($sCellType);
      $xData->addContent($sData);
    }

    if ($xData instanceof \Omniverse\Tag)
    {
      if (!preg_match("#TableHeader|th|TableCell|td#i", $xData->getType()))
      {
        $oData = $xData;
        $sCellType = $this->sCurrentType == 'head' && empty($this->aHead) ? 'TableHeader' : 'TableCell';
        $xData = \Omniverse\Tag::factory($sCellType);
        $xData->addContent($oData);
      }

      if ($this->IsSortable())
      {
        if ($xSort === false)
        {
          $xData->setRawParam('class', 'sorttable_nosort');
        }
        elseif (!is_null($xSort))
        {
          $xData->setRawParam('sorttable_customkey', (string)$xSort);
        }

      }

      $this->oCurrentRow->addContent($xData);
      return $xData;
    }

    return false;
  }

  public function endRow()
  {
    if ($this->sCurrentType == 'head')
    {
      $this->aHead[] = $this->oCurrentRow;
    }
    elseif ($this->sCurrentType == 'foot')
    {
      $this->aFoot[] = $this->oCurrentRow;
    }
    elseif ($this->oCurrentRow instanceof \Omniverse\Tag\TableRow)
    {
      $this->addTag($this->oCurrentRow);
    }

    $this->oCurrentRow = null;
    $this->sCurrentType = null;
  }

  public function addRow($aRow)
  {
    foreach ($aRow as $sCell)
    {
      $this->addCell($sCell);
    }

    $this->endRow();
  }

  public static function generateSortHeader($sText)
  {
    return "<span class=\"sorttable_sort_anchor\">$sText</span>";
  }

  public function addHeader($aRow)
  {
    $this->startHeader();
    $this->addRow($aRow);
  }

  public function addFooter($aRow)
  {
    $this->startFooter();
    $this->addRow($aRow);
  }
}
