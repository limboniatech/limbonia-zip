<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Omniverse Password Check</title>
  <link rel="stylesheet" type="text/css" href="<?= $this->domain->uri . '/' . $this->getDir('share') ?>/login.css" />
<!--
  <script type="text/javascript" src="{{controller.domain.uri}}/{{controller.getDir('share')}}/modernizr-custom.js"></script>
  <script type="text/javascript">
  document.cookie = '{{controller.allowGridCookieName}}=' + (Modernizr.cssgrid ? '1' : '0');
  document.cookie = '{{controller.datePickerCookieName}}=' + (Modernizr.inputtypes.date ? '1' : '0');
  </script>
-->
</head>
<body onLoad="document.passCheck.email.focus();">
<form action="" method="post" name="passCheck">
<?= $sFailure ?>
    <div class="field"><span class="name">Email:</span><span class="value"><input type="text" name="email"></span></div>
    <div class="field"><span class="name">Password:</span><span class="value"><input type="password" name="password"></span></div>
    <div class="field"><span class="name"></span><span class="value"><input type="submit" name="submit" value="Authorization"></span></div>
  </form>
</body>
</html>