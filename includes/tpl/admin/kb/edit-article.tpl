<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
    plugins : 'code image media <WYSIWYG_PLUGS>',
    language : '<WYSIWYG_LANG>'
});
</script>
<ERRORS>
<form action="" method="post" name="editArticle">
    <div class="subborder" id="editbox">
        <div class="sub">
          <table width="100%" border="0" cellspacing="2" cellpadding="0">
            <tr>
                <td colspan="2"><strong>Edit Article</strong></td>
            </tr>
            <tr>
                <td width="20%">Category:</td>
                <td>%DROPDOWN%</td>
            </tr>
            <tr>
                <td width="20%">Name:</td>
                <td><input name="editname" type="text" id="editname" size="40" value = "%NAME%" /></td>
            </tr>
            <tr>
                <td colspan = "2" valign="top">Description:<br><br>
                <textarea name="editdescription" id="editdescription" cols="" rows="">%DESCRIPTION%</textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input name="edit" id="edit" type="submit" value="Edit Article" class="button" /></td>
            </tr>
          </table>
        </div>
    </div>
</form>
