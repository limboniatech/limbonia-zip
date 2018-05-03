<?php
echo "<form name=\"Edit\" action=\"" . $module->generateUri($currentItem->id, $method) . "\" method=\"post\">\n";

$hResourceKeys = $currentItem->getResourceKeys();

foreach ($currentItem->getResourceList() as $key)
{
  $sValue = isset($hResourceKeys[$key->id]) ? $hResourceKeys[$key->id] : '';
  echo "<div class=\"field\"><span class=\"label\">$key->name</span><span class=\"data\"><input name=\"" . $module->getType() . "[ResourceKey][$key->id]\" value=\"$sValue\"></span></div>\n";
}

echo "<div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><button type=\"submit\" name=\"Update\">Update</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id) . "\"><button name=\"No\">No</button></a></span></div>\n";
echo "</form>";
