function showLimboniawindow(sURL, iTop, iLeft, iWidth, iHeight, bToolbar, bMenubar, bLocation, bStatus, bScrollbars, bResizable)
{
  sURL = (arguments.length > 0) ? sURL : '_blank';
  iTop = (arguments.length > 1) ? iTop : 100;
  iLeft = (arguments.length > 2) ? iLeft : 100;
  iWidth = (arguments.length > 3) ? iWidth : 400;
  iHeight = (arguments.length > 4) ? iHeight : 300;
  bToolbar = (arguments.length > 5) ? bToolbar : 0;
  bMenubar = (arguments.length > 6) ? bMenubar : 0;
  bLocation = (arguments.length > 7) ? bLocation : 0;
  bStatus = (arguments.length > 8) ? bStatus : 0;
  bScrollbars = (arguments.length > 9) ?  bScrollbars: 0;
  bResizable = (arguments.length > 10) ? bResizable : 0;

  var Limboniawindow = window.open(sURL, 'Limboniawindow', 'top='+iTop+',left='+iLeft+',width='+iWidth+',height='+iHeight+',toolbar='+bToolbar+',menubar='+bMenubar+',location='+bLocation+',status='+bStatus+',scrollbars='+bScrollbars+',resizable='+bResizable);
  Limboniawindow.opener = self;
  if (window.focus)
  {
    Limboniawindow.focus();
  }

  return Limboniawindow;
}