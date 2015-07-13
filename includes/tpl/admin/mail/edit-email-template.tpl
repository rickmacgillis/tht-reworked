<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
    plugins : 'code image media <WYSIWYG_PLUGS>',
    language : '<WYSIWYG_LANG>'
});
</script>
<ERRORS>
<form action="" method="post" name="edit" id="edit">
 <table width="100%" border="0" cellspacing="2" cellpadding="0" class = "text">
  <tr>
    <td colspan="2" valign="top">
     <b>Description: </b><br>
     %DESCRIPTION%</td>
  </tr>
  <tr>
    <td colspan="2" height = "10"></td>
  </tr>
  <tr>
    <td valign="top">Subject:</td>
    <td><input type="text" name="subject" id="subject" value = "%SUBJECT%" /> <a title="The subject of the email." class="tooltip"><img src="<ICONDIR>information.png" /></a></td>
  </tr>
  <tr>
    <td colspan="2"><textarea name="emailcontent" id="emailcontent" cols="" rows="">%TEMPLATE%</textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input name="edittpl" id="edit" type="submit" value="Edit Template" class="button" /></td>
  </tr>
 </table>
</form>
