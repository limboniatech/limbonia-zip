//var NS4 = (navigator.appName == "Netscape" && parseInt(navigator.appVersion) < 5);

function Omnisys_AddOption(sSelectID, sTitle, sValue)
{
  var oSelect = document.getElementById(sSelectID);
  oSelect.options[oSelect.length] = new Option(sTitle, sValue);
}

function Omnisys_RemoveOption(sSelectID, iIndex)
{
  oSelect = document.getElementById(sSelectID);

  if (oSelect.length > 0)
  {
    oSelect.options[iIndex] = null;
  }
}

function Omnisys_MoveOptions(sFromID, sToID)
{
  oFrom = document.getElementById(sFromID);
  oTo = document.getElementById(sToID);
  var iFrom = oFrom.length;
  var aTitle = new Array();
  var aValue = new Array();
  var iCount = 0;

  // Find the selected Options in reverse order
  // and delete them from the 'from' Select.
  for(i = iFrom - 1; i >= 0; i--)
  {
    if (oFrom.options[i].selected)
    {
      aTitle[iCount] = oFrom.options[i].text;
      aValue[iCount] = oFrom.options[i].value;
      Omnisys_RemoveOption(sFromID, i);
      iCount++;
    }
  }

  // Add the selected text/values in reverse order.
  // This will add the Options to the 'to' Select
  // in the same order as they were in the 'from' Select.
  for(i = iCount - 1; i >= 0; i--)
  {
    Omnisys_AddOption(sToID, aTitle[i], aValue[i]);
  }

//  if(NS4) history.go(0);
}

function Omnisys_RemoveAll(sSelectID)
{
  oSelect = document.getElementById(sSelectID);
  var iFrom = oSelect.length;
  for(i = iFrom - 1; i >= 0; i--)
  {
    oSelect.options[i] = null;
  }
}

function Omnisys_SelectAll(sSelectID)
{
  oSelect = document.getElementById(sSelectID);
  for (i = 0; i < oSelect.length; i++)
  {
    oSelect.options[i].selected = true;
  }
}