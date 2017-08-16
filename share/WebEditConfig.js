FCKConfig.ToolbarSets['Default'] = [
      ['Source','DocProps','-','Save','NewPage','Preview','-','Templates'],
      ['Print','SpellCheck'],
      ['Undo','Redo','-','Find','Replace','RemoveFormat'],
      ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],['TextColor','BGColor'],
      ['OrderedList','UnorderedList','-','Outdent','Indent'],
      ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
      ['Link','Unlink','Anchor'],
      ['Image','Table','Rule','Smiley','SpecialChar','UniversalKey'],
      ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
      ['Style','FontFormat','FontName','FontSize']
];

FCKConfig.ToolbarSets['Text'] = [
      ['Source','Save','NewPage','Preview'],['Print','SpellCheck'],
      ['Undo','Redo','-','Find','Replace','RemoveFormat'],
      ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],['TextColor','BGColor'],
      ['OrderedList','UnorderedList','-','Outdent','Indent'],
      ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
      ['Link','Unlink','Anchor'],
      ['SpecialChar','UniversalKey'],
      ['Style','FontFormat','FontName','FontSize']
];

FCKConfig.ToolbarSets['Basic'] = [['Bold','Italic','-','OrderedList','UnorderedList','-','Link','Unlink']];

FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/php/connector.php';

FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php';

FCKConfig.LinkUploadURL = FCKConfig.BasePath + 'filemanager/upload/php/upload.php';

FCKConfig.ImageUploadURL = FCKConfig.BasePath + 'filemanager/upload/php/upload.php?Type=Image';