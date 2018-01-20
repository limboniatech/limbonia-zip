<?php
echo "    <form name=\"Edit\" action=\"" . $module->generateUri($currentItem->id, $method) . "\" method=\"post\">\n";
echo $module->getFormField('Status', $currentItem->status, $editColumns['Status'], false);
echo $module->getFormField('Priority', $currentItem->priority, $editColumns['Priority'], false);
echo $module->getFormField('OwnerID', $currentItem->ownerID, $editColumns['OwnerID'], false);
echo $module->getFormField('Subject', $currentItem->subject, $editColumns['Subject'], false);
echo $module->getFormField('CategoryID', $currentItem->categoryID, $editColumns['CategoryID'], false);
echo $module->getFormField('StartDate', $currentItem->startDate, $editColumns['StartDate'], false);
echo $module->getFormField('DueDate', $currentItem->dueDate, $editColumns['DueDate'], false);

if ($currentItem->type == 'software')
{
  echo $module->getFormField('SoftwareID', $currentItem->softwareID, $editColumns['SoftwareID'], false);
  echo $module->getFormField('ReleaseID', $currentItem->releaseID, $editColumns['ReleaseID'], false);
  echo $module->getFormField('ElementID', $currentItem->elementID, $editColumns['ElementID'], false);
  echo $module->getFormField('Severity', $currentItem->severity, $editColumns['Severity'], false);
  echo $module->getFormField('Projection', $currentItem->projection, $editColumns['Projection'], false);
  echo $module->getFormField('DevStatus', $currentItem->devStatus, $editColumns['DevStatus'], false);
  echo $module->getFormField('QualityStatus', $currentItem->qualityStatus, $editColumns['QualityStatus'], false);
  echo $module->getFormField('Description', $currentItem->description, $editColumns['Description'], false);
  echo $module->getFormField('StepsToReproduce', $currentItem->stepsToReproduce, $editColumns['StepsToReproduce'], false);
}

echo $module->getFormField('UpdateText', '', [], false);
echo $module->getFormField('UpdateType', '', [], false);
echo $module->getFormField('TimeWorked', '', [], false);
echo "    <div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><input type=\"submit\" name=\"Update\" value=\"Update\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" name=\"No\" value=\"No\" onclick=\"parent.location='" . $module->generateUri($currentItem->id) . "'\"></span></div>\n";
echo "    </form>\n";