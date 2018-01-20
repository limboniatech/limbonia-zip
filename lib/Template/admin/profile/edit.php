<?php
echo "<form name=\"Edit\" action=\"" . $module->generateUri('edit') . "\" method=\"post\">\n";
echo $module->getFormFields($editColumns, $currentItem->getAll());
echo "<div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><button type=\"submit\" name=\"Update\">Update</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type=\"button\" name=\"No\" onclick=\"parent.location='" . $module->generateUri($currentItem->id) . "'\">No</button></span></div>\n";
echo "</form>\n";