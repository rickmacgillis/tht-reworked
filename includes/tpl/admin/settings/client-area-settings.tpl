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
    <td width="20%">Cancel Account:</td>
    <td>%DELACC% <a title="Do you allow your clients to cancel their own account?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Client Area:</td>
    <td>%ENABLED% <a title="Is the Client area online?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign="top" colspan = "2">Client Area Announcements:<br><br>
    <textarea name="alerts" id="alerts" cols="" rows="">%ALERTS%</textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Settings" class="button" /></td>
  </tr>
</table>
</form>
