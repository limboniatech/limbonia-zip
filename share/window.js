/**
 * Open a browser window based on the passed parameters
 *
 * @param {Array} hConfig
 * @returns {Window}
 */
function showLimboniaWindow(hConfig)
{
  /**
   * Return a string equivilant to the specified boolean variable
   *
   * @param {Bool} bVar
   * @returns {String}
   */
  var processBoolean = function(bVar)
  {
    return bVar ? '1' : '0';
  };
  var sURL = (hConfig.url) ? hConfig.url : '_blank';
  var iTop = (hConfig.top) ? hConfig.top : 100;
  var iLeft = (hConfig.left) ? hConfig.left : 100;
  var iWidth = (hConfig.width) ? hConfig.width : 400;
  var iHeight = (hConfig.height) ? hConfig.height : 300;
  var sToolbar = (hConfig.toolbar) ? processBoolean(hConfig.toolbar) : '0';
  var sMenubar = (hConfig.menubar) ? processBoolean(hConfig.menubar) : '0';
  var sLocation = (hConfig.location) ? processBoolean(hConfig.location) : '0';
  var sStatus = (hConfig.status) ? processBoolean(hConfig.status) : '0';
  var sScrollbars = (hConfig.scrollbars) ? processBoolean(hConfig.scrollbars) : '0';
  var sResizable = (hConfig.resizable) ? processBoolean(hConfig.resizable) : '0';

  var LimboniaWindow = window.open(sURL, 'Limboniawindow', 'top='+iTop+',left='+iLeft+',width='+iWidth+',height='+iHeight+',toolbar='+sToolbar+',menubar='+sMenubar+',location='+sLocation+',status='+sStatus+',scrollbars='+sScrollbars+',resizable='+sResizable);
  LimboniaWindow.opener = self;

  if (window.focus)
  {
    LimboniaWindow.focus();
  }

  return LimboniaWindow;
}