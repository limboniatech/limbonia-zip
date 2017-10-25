<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

if ($controller->api->subAction == 'edit' && isset($release))
{
  echo "<h2>Edit Software Release</h2>
<form action=\"" . $module->generateUri($currentItem->id, $method, $release->id, 'edit') . "\" method=\"post\">
" . $module->getFormField('Version', $release->version, ['Type' => 'varchar']) . "
" . $module->getFormField('Note', $release->note, ['Type' => 'varchar']) . "
<div class=\"field\"><span class=\"blankLabel\"></span><span class=\"data\"><button type=\"submit\" name=\"Update\">Update</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method) . "\"><button name=\"No\">No</button></a></span></div>
</form>\n";
}
elseif ($controller->api->subAction == 'delete' && isset($release))
{
  echo "<h2>Delete Software Release</h2>
<form action=\"" . $module->generateUri($currentItem->id, $method, $release->id, 'delete') . "\" method=\"post\">
Are you sure you want to delete $currentItem->name's release number \"$release->version\"?
<button type=\"submit\" name=\"Yes\">Yes</button>&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method) . "\"><button name=\"No\">No</button></a></form>\n";
}
else
{
  echo "<form action=\"" . $module->generateUri($currentItem->id, $method, 'create') . "\" method=\"post\">
<div class=\"display\">
  <header>
    <span>Version</span>
    <span>Note</span>
  </header>\n";

  foreach ($currentItem->getReleaseList() as $release)
  {
  echo "  <div>
    <span>$release->version [<a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, $release->id, 'edit') . "\">Edit</a> | <a class=\"item\" href=\"" . $controller->generateUri('ticket', $release->ticketID) . "\">View Ticket</a> | <a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, $release->id, 'delete') . "\">Delete</a>]</span>
    <span>$release->note</span>
  </div>\n";
  }

  echo "  <footer>
    <span>
      <input type=\"text\" name=\"Version\" placeholder=\"Version Number (#.#.#)\">
      <input type=\"text\" name=\"Note\" placeholder=\"Version Note\">
      <button type=\"submit\">Create Release</button>
    </span>
  </footer>
</div>
</form>\n";
}