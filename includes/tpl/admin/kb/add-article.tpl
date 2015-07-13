<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
    plugins : 'code image media <WYSIWYG_PLUGS>',
    language : '<WYSIWYG_LANG>'
});
</script>
<ERRORS>
<form action="" method="post" name="addArticle">
    <div class="subborder" id="addbox">
        <div class="sub">
          <table width="100%" border="0" cellspacing="2" cellpadding="0">
            <tr>
                <td colspan="2"><strong>Add Article</strong></td>
            </tr>
            <tr>
                <td width="20%">Category:</td>
                <td>%DROPDOWN%</td>
            </tr>
            <tr>
                <td width="20%">Name:</td>
                <td><input name="name" type="text" id="name" size="40" /></td>
            </tr>
            <tr>
                <td colspan = "2" valign="top">Description:<br><br>
                <textarea name="description" id="description" cols="" rows=""></textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input name="add" id="add" type="submit" value="Add Article" class="button" /></td>
            </tr>
          </table>
        </div>
    </div>
</form>
