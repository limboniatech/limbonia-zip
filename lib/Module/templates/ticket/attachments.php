<?php
if (isset($success))
{
  echo "<div class=\"methodSuccess\">$success</div>\n";
}

if (isset($failure))
{
  echo "<div class=\"methodFailure\">$failure</div>\n";
}

foreach ($currentItem->attachmentList as $key => $attachment)
{
  echo "<div><a class=\"item\" href=\"{$attachment['link']}\" onClick=\"window.open('{$attachment['link']},'attachment$key'); return false;\">{$attachment['name']}</a> (uploaded {$attachment['time']}) <a class=\"item\" href=\"" . $module->generateUri($currentItem->id, $method, $attachment['id'], 'delete') . "\">delete</a></div>\n";
}

echo "<form action=\"" . $module->generateUri($currentItem->id, $method, 'add') . "\" method=\"post\" enctype=\"multipart/form-data\">Select a new file to attach: <input type=\"file\" name=\"Attachment\"><input type=\"submit\" value=\"Upload\"></form>\n";