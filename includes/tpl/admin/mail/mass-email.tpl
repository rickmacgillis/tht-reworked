<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
    plugins : 'code image media <WYSIWYG_PLUGS>',
    language : '<WYSIWYG_LANG>'
});
</script>
<ERRORS>
<div id="ajaxemail">
<form action="" method="post" id="emailme" name="emailme">
  <table width="100%" border="0" cellspacing="3" cellpadding="0">
    <tr>
        <td width="30%">Subject:</td>
        <td><input name="msgsubject" id="msgsubject" type="text" size="30" /></td>
    </tr>
    <tr>
        <td valign="top" colspan = "2">Content:<br><br>
        <textarea name="msgcontent" id="msgcontent" cols="45" rows="5"></textarea></td>
    </tr>
    <tr>
        <td colspan="2" align="center"><input type="submit" name="goform" id="goform" value="Send Email" /></td>
      </tr>
  </table>
 </form>
</div>
