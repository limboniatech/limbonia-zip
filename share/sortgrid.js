//the object's name must be identical to sTarget
//because it's part of the "magic"...
function Omniverse_Widget_SortGrid(sTarget)
{
  //Private object data
  var aHeader = Array();
  var iHeader = 0;
  var aData = Array();
  var iData = 0;
  var sTarget = sTarget;
  var oTarget = null;
  var iWidth = '100%';
  var iColumnToSort = 0;
  var bSortDescending = true;
  var iCellNumber = 0;
  var bRowOpen = false;
  var bDrawn = false;
  var self = this;

  this.setWidth = function(iNewWidth)
  {
    iWidth = iNewWidth;
    redraw();
  }

  this.sortColumn = function(iColumn)
  {
    iColumnToSort = iColumn;
    redraw();
  }

  this.addColumn = function(sColumnName, bSortable, sPrefix, sPostfix)
  {
    var oHeader = new Object;
    oHeader.sName = sColumnName;
    oHeader.bSortable = (arguments.length > 1) ? bSortable : true;
    oHeader.sPrefix = (arguments.length > 2) ? sPrefix : '';
    oHeader.sPostfix = (arguments.length > 3) ? sPostfix : '';

    aHeader.push(oHeader);
    iHeader = aHeader.length;
  }

  this.startRow = function()
  {
    if (bRowOpen)
    {
      this.endRow();
    }

    iData = aData.length;
    var aTemp = Array();
    aData.push(aTemp);
    iCellNumber = 0;
    bRowOpen = true;
  }

  this.addCell = function (sortValue, sHTML)
  {
    if (!bRowOpen)
    {
      return false;
    }

    if (iCellNumber >= iHeader)
    {
      this.endRow();
      return false;
    }

    oCell = new Object;
    oCell.Row = iData;
    oCell.Column = iCellNumber;
    oCell.SortValue = sortValue;

    //if html isn't passed in we'll default it to sortValue...
    oCell.HTML = (arguments.length > 1) ? sHTML : sortValue;

    var sRegExp = new RegExp('type *?= *?("|\')?(.*?)\\1', "i");
    if (oCell.HTML.match(sRegExp))
    {
      oCell.Type = RegExp.$2.toUpperCase();
      var sRegExp = new RegExp('>', 'i');
      oCell.HTML = oCell.HTML.replace(sRegExp, ' onBlur="' + sTarget +'.update(' + iData + ', ' + iCellNumber + ', this.name);">');
    }

    var sRegExp = new RegExp('<select', "i");
    if (oCell.HTML.match(sRegExp))
    {
      oCell.Type = 'SELECT';
      oCell.HTML = oCell.HTML.replace(sRegExp, '<select onBlur="' + sTarget +'.update(' + iData + ', ' + iCellNumber + ', this.name);" ');
    }

    aData[iData].push(oCell);
    iCellNumber = aData[iData].length;
  }

  this.endRow = function()
  {
    if (!bRowOpen)
    {
      return false;
    }

    //if there are unfilled cells left in this row, fill them up with empty data...
    while (iCellNumber < iHeader)
    {
      this.addCell('', '', '', '');
    }

    iData = aData.length;
    bRowOpen = false;
  }

  this.draw = function()
  {
    //if any arguments are passed in then the first one *must* be iColumnToSort
    if (arguments.length > 0)
    {
      if (iColumnToSort != arguments[0])
      {
        iColumnToSort = arguments[0];

        //when sort columns change there is an apparent bug that makes the sorting fail unless it is done twice
        //to make the column sort descending we set bSortDescending = false for the for the "extra" sort
        bSortDescending = false;
        sort();
      }
    }

    if (iHeader == 0)
    {
      alert('No headers have been set, so the grid can not be drawn!');
      return false;
    }

    if (iData == 0)
    {
      alert('No data has been set, so the grid can not be drawn!');
      return false;
    }

    if (oTarget == null)
    {
      document.write('\n<div name="' + sTarget + '" id="' + sTarget + '" class="sortGridContainor"></div>');
      oTarget = document.getElementById(sTarget);
    }

    var sHTML = '';
    var iCount = 0;
    var jCount = 0;
    var iCellCount = 0;
    var aTemp = new Array();

    if (bDrawn)
    {
      sort();
    }

    aTemp.push('\n<table width="' + iWidth + '"  class="OmnisysSortGrid">');
    aTemp.push('<thead>');
    aTemp.push('  <tr class="OmnisysSortGridHeader">');

    var sText;
    var sCSS;
    for (iCount = 0; iCount < iHeader; iCount++)
    {
      sText = aHeader[iCount].sPrefix;
      sCSS = iCount == iColumnToSort ? 'Selected' : '';
      sText += aHeader[iCount].bSortable ? '<a href="javascript:void(0);" onClick="' + sTarget + '.draw(' + iCount + ');" class="OmnisysSortGridHeader">' + aHeader[iCount].sName + '</a>' : aHeader[iCount].sName;
      sText += aHeader[iCount].sPostfix;
      aTemp.push('    <th class="OmnisysSortGridHeader' + sCSS + '" nowrap>' + sText + '</th>');
    }
    aTemp.push('  </tr>');
    aTemp.push('</thead>');

    aTemp.push('<tbody>');
    var bCSS = false;
    for (iCount = 0; iCount < iData; iCount++)
    {
      bCSS = !bCSS;
      sCSS = bCSS ? '' : 'Alt';
      iCellCount = aData[iCount].length;
      aTemp.push('  <tr class="OmnisysSortGrid' + sCSS + '">');
      for (jCount = 0; jCount < iCellCount; jCount++)
      {
        sCSS = jCount == iColumnToSort ? 'Selected' : '';
        aTemp.push('    <td class="OmnisysSortGrid' + sCSS + '">' + aData[iCount][jCount].HTML + '</td>');
      }
      aTemp.push('  </tr>');
    }

    aTemp.push('</tbody>');
    aTemp.push('</table>\n');

    sHTML = aTemp.join('\n');
    oTarget.innerHTML = sHTML;
    bDrawn = true;
  }

  this.update = function(iRow, iColumn, sElementName)
  {
    for (iCount = 0; iCount < iData; iCount++)
    {
      if (aData[iCount][iColumn].Row == iRow)
      {
        switch (aData[iCount][iColumn].Type)
        {
          case 'CHECKBOX':
            aData[iCount][iColumn].SortValue = (document.getElementById(sElementName).checked == true) ? '1' : '0';
            aData[iCount][iColumn].HTML = aData[iCount][iColumn].SortValue == 1 ? replace('>', ' checked>', aData[iCount][iColumn].HTML) : replace('checked>', '>', aData[iCount][iColumn].HTML);
            break;

          case 'SELECT':
            //for selects to work correctly, the options value *must* be set
            //*and* equal to the visual text... i.e. <option value="test">test</option>
            var iSelected = document.getElementById(sElementName).selectedIndex;
            aData[iCount][iColumn].SortValue = document.getElementById(sElementName).options[iSelected].value;
            aData[iCount][iColumn].HTML = replace(' selected', '', aData[iCount][iColumn].HTML);
            aData[iCount][iColumn].HTML = replace('value="' + aData[iCount][iColumn].SortValue + '"','value="' + aData[iCount][iColumn].SortValue + '" selected', aData[iCount][iColumn].HTML);
            break;

          case 'INPUT':
            aData[iCount][iColumn].SortValue = document.getElementById(sElementName).value;
            aData[iCount][iColumn].HTML = replace('value *?= *?("|\').*?("|\')','value="' + aData[iCount][iColumn].SortValue + '"', aData[iCount][iColumn].HTML);
            break;
        }
      }
    }
    sort();
  }

  //private methods

  function redraw()
  {
    if (bDrawn)
    {
      self.draw();
    }
  }

  function sort()
  {
    var aTemp = new Array();
    var aNew = new Array();
    var iFound = 0;
    var iCount = 0;

    for (iCount = 0; iCount < iData; iCount++)
    {
      aTemp[iFound] = new Array();
      aTemp[iFound][0] = aData[iCount][iColumnToSort].Row;
      aTemp[iFound][1] = aData[iCount][iColumnToSort].SortValue.toUpperCase();
      iFound++;
    }

    bSortDescending ? aTemp.sort(sortColumnArray) : aTemp.reverse(sortColumnArray);
    bSortDescending = !bSortDescending;

    iFound = 0;
    for (iCount = 0; iCount < iData; iCount++)
    {
      sRow = aTemp[iFound][0];
      aNew[iFound] = getSortedRow(sRow);
      iFound++;
    }
    aData = aNew;
  }

  function replace(sSearchValue, sReplaceValue, sOriginalValue)
  {
    var sRegExp = eval("/" + sSearchValue + "/g");
    return sOriginalValue.replace(sRegExp, sReplaceValue);
  }

  function sortColumnArray(aA, aB)
  {
    if (aA[1] > aB[1])
    {
      return 1;
    }
    else if (aA[1] < aB[1])
    {
      return -1;
    }
    return 0;
  }

  function getSortedRow(sRow)
  {
    var oRow;
    var iCount = 0;

    for (iCount = 0; iCount < iData; iCount++)
    {
      oRow = aData[iCount];
      if (oRow[iColumnToSort].Row == sRow)
      {
        return oRow;
      }
    }
  }

}//end of Omnisys_Widget_SortGrid javascript object