<?php
/**
 * Omniverse Table Class
 *
 * This is a light wrapper around an HTML table with some JavaScript for sorting
 *
 * @author Lonnie Blansett <lonnie@omniverserpg.com>
 * @version $Revision: 1.4 $
 * @package Omniverse
 */
namespace Omniverse\Widget;

class Table extends \Omniverse\Widget
{
  /**
   * A list of rows to put into the table head
   *
   * @var array
   */
  protected $aHead = null;

  /**
   * A list of rows to put into the table foot
   *
   * @var array
   */
  protected $aFoot = null;

  /**
   * The current row object
   *
   * @var \Omniverse\Tag
   */
  protected $oCurrentRow = null;

  /**
   * The type of the current row... either  head, foot or null
  *
   * @var string
   */
  protected $sCurrentType = null;

  /**
   * Generate and return the HTML needed to create the sort header
   *
   * @param string $sText
   * @return string
   */
  public static function generateSortHeader($sText)
  {
    return "<span class=\"sorttable_sort_anchor\">$sText</span>";
  }

  /**
   * Stub create method that will be overridden by a child class.
   *
   * @return boolean
   */
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

  /**
   * Make the current table sortable
   */
  public function makeSortable()
  {
    \Omniverse\Widget::includeScript($this->sWebShareDir . "/sorttable.js");
    $this->addClass('sortable');
    $this->addClass('sortGrid');
    $this->writeJavascript('sorttable.init();');
  }

  /**
   * Is this table already sortable?
   *
   * @return boolean
   */
  public function isSortable()
  {
   return preg_match("#\bsortable\b#", $this->getRawParam('class'));
  }

  /**
   * Start a new row of the specified type for this table and return the row object
   *
   * @param string $sType
   * @return \Omniverse\Tag
   */
  public function startRow($sType = null)
  {
    $this->endRow();
    $this->sCurrentType = strtolower($sType);
    $this->oCurrentRow = \Omniverse\Tag::factory('TableRow');
    return $this->oCurrentRow;
  }

  /**
   * Start a row of type header
   *
   * @return \Omniverse\Tag
   */
  public function startHeader()
  {
    return $this->startRow('head');
  }

  /**
   * Start a row of type footer
   *
   * @return \Omniverse\Tag
   */
  public function startFooter()
  {
    return $this->startRow('foot');
  }

  /**
   * Add a new cell to the current row
   *
   * @param mixed $xData
   * @param boolean|string $xSort (optional) - Pass false to make the column unsortable or a string to add a custom search key.  Applies only to a sortable table.
   * @return boolean
   */
  public function addCell($xData, $xSort = null)
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

      if ($this->isSortable())
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

  /**
   * End the current row
   */
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

  /**
   * Add a whole row of regular data, all at once
   *
   * @param array $aRow
   */
  public function addRow($aRow)
  {
    foreach ($aRow as $sCell)
    {
      $this->addCell($sCell);
    }

    $this->endRow();
  }

  /**
   * Add a whole row of regular data to the table head, all at once
   *
   * @param array $aRow
   */
  public function addHeader($aRow)
  {
    $this->startHeader();
    $this->addRow($aRow);
  }

  /**
   * Add a whole row of regular data to the table foot, all at once
   *
   * @param array $aRow
   */
  public function addFooter($aRow)
  {
    $this->startFooter();
    $this->addRow($aRow);
  }
}
