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
    <td width="20%">Support Area:</td>
    <td>%ENABLED% <a title="Is the Support area online?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign="top" colspan = "2">Support Area Closed Message:<br><br>
    <textarea name="smessage" id="smessage">%MESSAGE%</textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Settings" class="button" /></td>
  </tr>
</table>
</form>
