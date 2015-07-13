<ERRORS>
<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
    plugins : 'code image media <WYSIWYG_PLUGS>',
    language : '<WYSIWYG_LANG>'
});
</script>
<form id="settings" name="settings" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="25%">Multiple Signups Per User:</td>
    <td>%MULTIPLE% <a title="Do you allow multiple signups for one IP?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>TLD's only:</td>
    <td>%TLDONLY% <a title="Allow ONLY top level domains? Leave as Disabled if unsure.  (No subdomains?)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Allow Signups:</td>
    <td>%GENERAL% <a title="Is the signup system offline?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign="top" colspan = "2">Signups Closed Message:<br><br>
    <textarea name="message" id="message">%MESSAGE%</textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Settings" class="button" /></td>
  </tr>
</table>
</form>
