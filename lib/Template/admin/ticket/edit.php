<?php
echo "    <form name=\"Edit\" action=\"" . $module->generateUri($currentItem->id, $method) . "\" method=\"post\">\n";
echo $module->getFormField('Status', $currentItem->status, $fields['Status']);
echo $module->getFormField('Priority', $currentItem->priority, $fields['Priority']);
echo $module->getFormField('OwnerID', $currentItem->ownerID, $fields['OwnerID']);
echo $module->getFormField('Subject', $currentItem->subject, $fields['Subject']);
echo $module->getFormField('CategoryID', $currentItem->categoryID, $fields['CategoryID']);
echo $module->getFormField('StartDate', $currentItem->startDate, $fields['StartDate']);
echo $module->getFormField('DueDate', $currentItem->dueDate, $fields['DueDate']);

if ($currentItem->type != 'software')
{
  echo $module->getFormField('Description', $currentItem->description, $fields['Description']);
}

if ($currentItem->type == 'software')
{
  echo $module->getFormField('SoftwareID', $currentItem->softwareID, $fields['SoftwareID']);
  echo $module->getFormField('ReleaseID', $currentItem->releaseID, $fields['ReleaseID']);
  echo $module->getFormField('ElementID', $currentItem->elementID, $fields['ElementID']);
  echo $module->getFormField('Severity', $currentItem->severity, $fields['Severity']);
  echo $module->getFormField('Projection', $currentItem->projection, $fields['Projection']);
  echo $module->getFormField('DevStatus', $currentItem->devStatus, $fields['DevStatus']);
  echo $module->getFormField('QualityStatus', $currentItem->qualityStatus, $fields['QualityStatus']);
  echo $module->getFormField('Description', $currentItem->description, $fields['Description']);
  echo $module->getFormField('StepsToReproduce', $currentItem->stepsToReproduce, $fields['StepsToReproduce']);
}

echo $module->getFormField('UpdateText', '', []);
echo $module->getFormField('UpdateType', '', []);
echo $module->getFormField('TimeWorked', '', []);
echo "    <div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><input type=\"submit\" name=\"Update\" value=\"Update\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" name=\"No\" value=\"No\" onclick=\"parent.location='" . $module->generateUri($currentItem->id) . "'\"></span></div>\n";
echo "    </form>\n";