<?php
$sBorderColor = "#4682B4";
$sHeaderBackground = "#4682B4";
$sHeaderColor = "white";
$sWeekBackground = "#87CEFA";
$sWeekColor = "white";
$sInMonth_DayColor = "black";
$sOutMonth_DayColor = "gray";
$sWeekendBackground = "#DBEAF5";
$sCurrentDayBackground = "#FFB6C1";
$sDefaultDayBackground = "white";
$aWeekDays = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];
$iStartDay = 0;
$sDateFormat = "m/d/Y";
$sFontFace = "tahoma, verdana, arial";
$sPrevButton = "/share/limbonia/cal_prev.gif";
$sNextButton = "/share/limbonia/cal_next.gif";
$iWindowHeight = 190;
$sRawConfig = filter_input(INPUT_GET, 'Config');

if (empty($sRawConfig))
{
  die('<html>
  <head>
    <title>Calendar</title>
  </head>
  <body onload="self.alert(\'No configuration data, CLOSING!\')">
  </body>
</html>');
}

$sConfig = gzinflate(stripslashes($sRawConfig));

$hConfig = unserialize($sConfig);
extract($hConfig);

$Config = rawurlencode(gzdeflate(serialize($hConfig)));

$sDate = filter_input(INPUT_GET, 'Date');
$Start = strtotime((empty($sDate) ? 'now' : $sDate));

$hStartDate = getdate($Start);
$iFirstDay = $hStartDate["wday"];
$sMonthName = $hStartDate["month"];
$iMonthNum = $hStartDate["mon"];
$iDayNum = $hStartDate["mday"];
$iYearNum = $hStartDate["year"];

$iDayCount = date("t", mktime(0, 0, 0, $iMonthNum, $iDayNum, $iYearNum));

$hPreDate = getdate(mktime(12, 0, 0, $iMonthNum - 1, $iDayNum, $iYearNum));
$iPreDay = $hPreDate["mday"];
$iPreMonth = $hPreDate["mon"];
$iPreYear = $hPreDate["year"];

$hNextDate = getdate(mktime(12, 0, 0, $iMonthNum + 1, $iDayNum, $iYearNum));
$iNextDay = $hNextDate["mday"];
$iNextMonth = $hNextDate["mon"];
$iNextYear = $hNextDate["year"];

$iCellHeight = ($iWindowHeight - 10) / 8;
$sCellHeight = "height=\"$iCellHeight\"";

echo "<html>
<head>
  <title>Calendar</title>
  <script type=\"text/javascript\">
  function setDate(num)
  {
    window.opener.document.getElementById('$Target').value=num; window.close()
  }
  </script>
</head>
<body onLoad=\"self.focus()\" bgcolor=\"white\" topmargin=\"0\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\">
<table cellspacing=\"0\" border=\"0\" width=\"100%\">
<tr><td bgcolor=\"$sBorderColor\">
<table cellspacing=\"1\" cellpadding=\"3\" border=\"0\" width=\"100%\">
<tr>
  <td $sCellHeight bgcolor=\"$sHeaderBackground\"><a href=\"?Config=".$Config."&Date=$iPreMonth/$iPreDay/$iPreYear\"><img src=\"$sPrevButton\" border=\"0\"></a></td>
  <td $sCellHeight bgcolor=\"$sHeaderBackground\" align=\"center\" colspan=\"5\"><font color=\"$sHeaderColor\" face=\"$sFontFace\" size=\"2\"> $sMonthName $iYearNum</font></td>
  <td $sCellHeight bgcolor=\"$sHeaderBackground\" align=\"right\"><a href=\"?Config=".$Config."&Date=$iNextMonth/$iNextDay/$iNextYear\"><img src=\"$sNextButton\" border=\"0\"></a></td>
</tr>
<tr>\n";

for ($i = 0; $i < 7; $i++)
{
  $sDayName = $aWeekDays[($i + $iStartDay) % 7];
  echo "<td $sCellHeight bgcolor=\"$sWeekBackground\" align=\"center\"><font color=\"$sWeekColor\" face=\"$sFontFace\" size=\"2\">$sDayName</font></td>\n";
}

echo "</tr>\n";

$iCalendarDay = $iDayNum - $iFirstDay + $iStartDay;

while ($iCalendarDay > 1)
{
  $iCalendarDay -= 7;
}

for ($j = 0; $j < 6; $j++)
{
  echo "<tr>\n";

  for ($i = 0; $i < 7; $i++)
  {
    $iCurrentDayOfWeek = ($i + $iStartDay) % 7;
    $iCurrentTimeStamp = $Start - $iDayNum * 86400 + $iCalendarDay * 86400;
    $sCurrentDate = date($sDateFormat, $iCurrentTimeStamp);
    $iCurrentDayNum = date("d", $iCurrentTimeStamp);

    if ($iCalendarDay > 0 && $iCalendarDay <= $iDayCount)
    {
      if ($iCalendarDay == $iDayNum)
      {
        $sBackgroundColor = $sCurrentDayBackground;
        $sFontColor = $sInMonth_DayColor;
      }
      elseif ($iCurrentDayOfWeek == 0 || $iCurrentDayOfWeek == 6)
      {
        $sBackgroundColor = $sWeekendBackground;
        $sFontColor = $sInMonth_DayColor;
      }
      else
      {
        $sBackgroundColor = $sDefaultDayBackground;
        $sFontColor = $sInMonth_DayColor;
      }
    }
    else
    {
      if ($iCurrentDayOfWeek == 0 || $iCurrentDayOfWeek == 6)
      {
        $sBackgroundColor = $sWeekendBackground;
        $sFontColor = $sOutMonth_DayColor;
      }
      else
      {
        $sBackgroundColor = $sDefaultDayBackground;
        $sFontColor = $sOutMonth_DayColor;
      }
    }

    echo "<td $sCellHeight bgcolor=\"$sBackgroundColor\" align=\"center\">";
    echo "<a href=\"javascript:setDate('$sCurrentDate');\"><font color=\"$sFontColor\" face=\"$sFontFace\" size=\"2\">$iCurrentDayNum</font></a></td>\n";
    $iCalendarDay++;
  }

  if ($iCalendarDay > $iDayCount)
  {
    break;
  }

  echo "</tr>\n";
}

echo "</table>
</td>
</tr>
</table>
</body>
</html>";