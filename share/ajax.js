/*
function $()
{
  var aElements = [];

  for (var i = 0; i < arguments.length; i++)
  {
    var sElement = arguments[i];

    if (typeof sElement === 'string')
    {
      if (document.getElementById)
      {
        sElement = document.getElementById(sElement);
      }
      else if (document.all)
      {
        sElement = document.all[sElement];
      }
    }

    aElements.push(sElement);
  }

  return (arguments.length === 1 && aElements.length > 0) ? aElements[0] : aElements;
}
*/

function Omniverse_GetRequest()
{
  //compliant browsers
  if (window.XMLHttpRequest)
  {
    return new XMLHttpRequest();
  }
  //Internet Explorer
  else if (window.ActiveXObject)
  {
    try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {}
    try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {}
  }

  alert("Unfortunatelly you browser doesn't support XMLHttpRequest!");
  return false;
}

function Omniverse_HttpRequest(sClass, sFunction, aArg, bReportStatus, bDebug, sBaseUri)
{
  if (arguments.length < 4) { bReportStatus = false; }
  if (arguments.length < 5) { bDebug = false; }
  if (arguments.length < 6) { sBaseUri = '/ajax/'; }

  var oRequest = Omniverse_GetRequest();
  if (!oRequest)
  {
    return false;
  }

  oRequest.onreadystatechange = function()
  {
    switch (oRequest.readyState)
    {
      case 1:
        if (bReportStatus) { window.status = 'Sending request...'; }
        break;

      case 2:
        if (bReportStatus) { window.status = 'Waiting for reply...'; }
        break;

      case 3:
        if (bReportStatus) { window.status = 'Receiving reply...'; }
        break;

      case 4:
        if (bReportStatus) { window.status = 'Processing reply...'; }
        switch (oRequest.status)
        {
          case 200:
            if (bReportStatus) {window.status = ''; }
            if (bDebug)
            {
              var errorWin = window.open('', 'ERROR');
              errorWin.document.body.innerHTML = oRequest.responseText;
            }
            else
            {
              eval(oRequest.responseText);
              //if we ever change to XML we will put the parsing here...
            }
            break;

          case 404:
            if (bReportStatus) { window.status = 'Request failed: URL not found!'; }
            break;

          case 500:
            var errorWin = window.open('', 'ERROR');
            errorWin.document.body.innerHTML = oRequest.responseText;
            break;

          default:
            alert('Request Failed: (Code ' + oRequest.status + ') ' + oRequest.statusText);
        }
    }
  }
  oRequest.open('post', sBaseUri + '/' + sClass + '/' + encodeURIComponent(sFunction));
  oRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  var sArgs = null;

  if (aArg.length > 0)
  {
    sArgs = '';

    for (i = 0; i < aArg.length; i++)
    {
      value = encodeURIComponent(aArg[i]);
      sConnect = (i === 0) ? '' : '&';
      sArgs += sConnect + i + '=' + value;
    }
  }

  oRequest.send(sArgs);
}