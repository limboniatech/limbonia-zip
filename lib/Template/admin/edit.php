<?php
echo "<form name=\"Edit\" action=\"" . $module->generateUri($currentItem->id, 'edit') . "\" method=\"post\">\n";
echo $module->getFormFields($editColumns, $currentItem->getAll());
echo "<div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><button type=\"submit\" name=\"Update\">Update</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id) . "\"><button name=\"No\">No</button></span></div>\n";
echo "</form>\n";