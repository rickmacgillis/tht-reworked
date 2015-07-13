<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
    plugins : 'code image media <WYSIWYG_PLUGS>',
    language : '<WYSIWYG_LANG>'
});
</script>
<ERRORS>
<form id="editpackage" name="editpackage" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="35%">Name:</td>
    <td width="35%"><input name="name" type="text" id="name" value="%NAME%" /></td>
    <td valign = "top"><a title="The package name" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Backend:</td>
    <td><input name="backend" type="text" id="backend" value="%BACKEND%" /></td>
    <td valign = "top"><a title="The package name as shown in the control panel.<br><br>For ZPanel, this needs to be the ID (number) of the package.  In ZPanel, edit the package and look at the URL while in edit mode.  The last number on the URL is the package ID.  (Ex. other=PACKAGEID)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td colspan = "3">Description:<br><br>
    <textarea name="description" id="description">%DESCRIPTION%</textarea><br></td>
  </tr>  
  <tr>
    <td>Admin Validation:</td>
    <td><input name="admin" type="checkbox" id="admin" value="1" %ADMIN_CHECKED% /></td>
    <td valign = "top"><a title="Does this package require admin validation?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
    <tr>
    <td>Reseller:</td>
    <td><input name="reseller" type="checkbox" id="reseller" value="1" %RESELLER_CHECKED% /></td>
    <td valign = "top"><a title="Is this package a reseller?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
    <tr>
    <td>Hidden:</td>
    <td><input name="hidden" type="checkbox" id="hidden" value="1" %HIDDEN_CHECKED% /></td>
    <td valign = "top"><a title="Is this package hidden on the order form? (Direct orders allowed.)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
    <tr>
    <td>Disabled:</td>
    <td><input name="disabled" type="checkbox" id="disabled" value="1" %DISABLED_CHECKED% /></td>
    <td valign = "top"><a title="Are new orders disabled for this package?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  </tr>
  <tr>
    <td>Direct Link:</td>
    <td><input name="direct" type="text" size="60" id="direct" value="%URL%order/index.php?id=%ID%" readonly /></td>
    <td valign = "top"><a title="A link to order the package directly without showing other packages. (Allows ordering hidden packages.)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  %PACKAGES_FIELDS%
  %TYPE_FORM%
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td align="center"><input type="hidden" name="server" id="server" value="%SERVER%" /><input type="hidden" name="type" id="type" value="%TYPE%" /><input type="submit" name="edit" id="edit" value="Edit Package" class="button" /></td>
  </tr>
</table>
</form>
