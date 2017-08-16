<?php
$BorderColor = "#4682B4";
$HeaderBackground = "#4682B4";
$HeaderColor = "white";
$WeekBackground = "#87CEFA";
$WeekColor = "white";
$InMonth_DayColor = "black";
$OutMonth_DayColor = "gray";
$WeekendBackground = "#DBEAF5";
$CurrentDayBackground = "#FFB6C1";
$DefaultDayBackground = "white";
$WeekDays = array("Su", "Mo", "Tu", "We", "Th", "Fr", "Sa");
$StartDay = 0;
$DateFormat = "m/d/Y";
$FontFace = "tahoma, verdana, arial";
$PrevButton = "/share/omnisys/cal_prev.gif";
$NextButton = "/share/omnisys/cal_next.gif";
$WindowHeight = 190;

if (empty($_GET['Config']))
{
  $sFail = "<html>\n";
  $sFail .= "<head>\n";
  $sFail .= "  <title>Calendar</title>\n";
  $sFail .= "</head>\n";
  $sFail .= "<body onload=\"self.alert('No configuration data, CLOSING!')\">\n";
  $sFail .= "</body>\n";
  $sFail .= "</html>\n";
  die($sFail);
}

$sConfig = gzinflate(stripslashes($_GET['Config']));

$hConfig = unserialize($sConfig);
extract($hConfig);

$Config = rawurlencode(gzdeflate(serialize($hConfig)));

$Start = strtotime((empty($_GET['Date']) ? 'now' : $_GET['Date']));

$StartDate = getdate($Start);
$FirstDay = $StartDate["wday"];
$MonthName = $StartDate["month"];
$MonthNum = $StartDate["mon"];
$DayNum = $StartDate["mday"];
$YearNum = $StartDate["year"];

$DayCount = date("t", mktime(0, 0, 0, $MonthNum, $DayNum, $YearNum));

$PreDate = getdate(mktime(12, 0, 0, $MonthNum - 1, $DayNum, $YearNum));
$PreDay = $PreDate["mday"];
$PreMonth = $PreDate["mon"];
$PreYear = $PreDate["year"];

$NextDate = getdate(mktime(12, 0, 0, $MonthNum + 1, $DayNum, $YearNum));
$NextDay = $NextDate["mday"];
$NextMonth = $NextDate["mon"];
$NextYear = $NextDate["year"];

$CellHeight = ($WindowHeight - 10) / 8;
$CellHeight = "height=\"$CellHeight\"";

print "<html>\n";
print "<head>\n";
print "  <title>Calendar</title>\n";
print "  <script type=\"text/javascript\">\n";
print "  function setDate(num)\n";
print "  {\n";
print "    window.opener.document.getElementById('$Target').value=num; window.close()\n";
print "  }\n";
print "  </script>\n";
print "</head>\n";
print "<body onLoad=\"self.focus()\" bgcolor=\"white\" topmargin=\"0\" leftmargin=\"0\" marginheight=\"0\" marginwidth=\"0\">\n";
print "<table cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
print "<tr><td bgcolor=\"$BorderColor\">\n";
print "<table cellspacing=\"1\" cellpadding=\"3\" border=\"0\" width=\"100%\">\n";
print "<tr>\n";
print "  <td $CellHeight bgcolor=\"$HeaderBackground\"><a href=\"?Config=".$Config."&Date=$PreMonth/$PreDay/$PreYear\"><img src=\"$PrevButton\" border=\"0\"></a></td>\n";
print "  <td $CellHeight bgcolor=\"$HeaderBackground\" align=\"center\" colspan=\"5\"><font color=\"$HeaderColor\" face=\"$FontFace\" size=\"2\"> $MonthName $YearNum</font></td>\n";
print "  <td $CellHeight bgcolor=\"$HeaderBackground\" align=\"right\"><a href=\"?Config=".$Config."&Date=$NextMonth/$NextDay/$NextYear\"><img src=\"$NextButton\" border=\"0\"></a></td>\n";
print "</tr>\n";
print "<tr>\n";

for ($i = 0; $i < 7; $i++)
{
  $DayName = $WeekDays[($i + $StartDay) % 7];
  print "<td $CellHeight bgcolor=\"$WeekBackground\" align=\"center\"><font color=\"$WeekColor\" face=\"$FontFace\" size=\"2\">$DayName</font></td>\n";
}
print "</tr>\n";

$CalendarDay = $DayNum - $FirstDay + $StartDay;
while ($CalendarDay > 1)
{
  $CalendarDay -= 7;
}
for ($j = 0; $j < 6; $j++)
{
  print "<tr>\n";
  for ($i = 0; $i < 7; $i++)
  {
    $CurrentDayOfWeek = ($i + $StartDay) % 7;
    $CurrentTimeStamp = $Start - $DayNum * 86400 + $CalendarDay * 86400;
    $CurrentDate = date($DateFormat, $CurrentTimeStamp);
    $CurrentDayNum = date("d", $CurrentTimeStamp);
    if ($CalendarDay > 0 && $CalendarDay <= $DayCount)
    {
      if ($CalendarDay == $DayNum)
      {
        $BackgroundColor = $CurrentDayBackground;
        $FontColor = $InMonth_DayColor;
      }
      elseif ($CurrentDayOfWeek == 0 || $CurrentDayOfWeek == 6)
      {
        $BackgroundColor = $WeekendBackground;
        $FontColor = $InMonth_DayColor;
      }
      else
      {
        $BackgroundColor = $DefaultDayBackground;
        $FontColor = $InMonth_DayColor;
      }
    }
    else
    {
      if ($CurrentDayOfWeek == 0 || $CurrentDayOfWeek == 6)
      {
        $BackgroundColor = $WeekendBackground;
        $FontColor = $OutMonth_DayColor;
      }
      else
      {
        $BackgroundColor = $DefaultDayBackground;
        $FontColor = $OutMonth_DayColor;
      }
    }
    print "<td $CellHeight bgcolor=\"$BackgroundColor\" align=\"center\">";
    print "<a href=\"javascript:setDate('$CurrentDate');\"><font color=\"$FontColor\" face=\"$FontFace\" size=\"2\">$CurrentDayNum</font></a></td>\n";
    $CalendarDay++;
  }
  if ($CalendarDay > $DayCount)
  {
    break;
  }
  print "</tr>\n";
}

print "</table>\n";
print "</td>\n";
print "</tr>\n";
print "</table>\n";
print "</body>\n";
print "</html>\n";
?>
