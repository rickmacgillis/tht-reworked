<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
	plugins : 'code image media <WYSIWYG_PLUGS>',
	language : '<WYSIWYG_LANG>'
});
</script>
<form action="" method="post" name="email" id="email">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td valign="top">Subject:</td>
    <td><input type="text" name="subject" id="subject" size = "65" />
    <a title="The subject of the email." class="tooltip"><img src="<ICONDIR>information.png" /></a></td>
  </tr>
  <tr>
    <td colspan = "2" valign="top" id="description">Message:<br>
	<textarea name="content" id="msgcontent" cols="" rows=""></textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input name="edit" id="edit" type="submit" value="Send Email" class="button" /></td>
  </tr>
</table>
</form>
