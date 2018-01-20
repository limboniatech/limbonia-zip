<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

if ($currentItem->parentID > 0)
{
  echo "<div>PARENT:<br><a class=\"item\" href=\"" . $module->generateUri($currentItem->parentID) . "\">{$currentItem->parent->subject}</a> [updated {$currentItem->parent->lastUpdate}, priority: {$currentItem->parent->priority}, status: {$currentItem->parent->status}] <a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, 'removeparent') . "\">delete</a></div>\n";
}
else
{
  echo "<form action=\"" . $module->generateUri($currentItem->id, $method, 'setparent') . "\" method=\"post\">Set Parent Ticket: <input type=\"text\" name=\"SetParent\"><input type=\"submit\" value=\"Set Parent\"></form>\n";
}

echo "<br>\n";

$oChildren = $currentItem->getChildren();

if (count($oChildren) > 0)
{
  echo "CHILDREN:<br>\n";

  foreach ($oChildren as $child)
  {
    echo "<div><a class=\"item\" href=\"" . $module->generateUri($child->id) . "\">$child->subject</a> [updated $child->lastUpdate, priority: $child->priority, status: $child->status] <a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, $child->id, 'removechild') . "\">delete</a></div>\n";
  }
}

echo "<form action=\"{{module.generateUri(currentItem.id, method, 'addchild')}}\" method=\"post\">Add Child Ticket: <input type=\"text\" name=\"AddChild\"><input type=\"submit\" value=\"Add Child\"></form>\n";