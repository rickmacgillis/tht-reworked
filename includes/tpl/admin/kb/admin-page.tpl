<script type="text/javascript">
function addme() {
        $("#addbox").slideToggle(500);        
}
</script>
<script type="text/javascript">
function editme(id) {
        $.get("<AJAX>?function=%AJAX%&id="+id, function(data) {
                        var result = data.split("{}[]{}");
                        if(document.getElementById("editbox").style.display == "none") {
                                document.getElementById("editname").value = result[0];
                                tinyMCE.get("editdescription").execCommand('mceSetContent',false, result[1] );
                                if(result[2]){
                                document.getElementById("catidedit").value = result[2];
                                }
                                $("#editbox").slideDown(500);        
                        }
                        else {
                                $("#editbox").slideUp(500, function(data) {
                                        document.getElementById("editname").value = result[0];
                                        tinyMCE.get("editdescription").execCommand('mceSetContent',false, result[1] );
                                        if(result[2]){
                                        document.getElementById("catidedit").value = result[2];
                                        }
                                        $("#editbox").slideDown(500);
                                });
                        }
                        document.getElementById("id").value = id;
      });
}
</script>
<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
	plugins : 'code image media <WYSIWYG_PLUGS>',
	language : '<WYSIWYG_LANG>'
});
</script>
<ERRORS>
<div class="subborder">
        <div class="sub">
             <table width="100%" border="0" cellspacing="2" cellpadding="0">
              <tr>
                <td width="1%"><img src="<ICONDIR>add.png"></td>
                <td><a href="Javascript:addme()">Add %NAME%</a></td>
              </tr>
            </table>
        </div>
</div>
<form action="" method="post" name="add%NAME%">
    <div class="subborder" id="addbox" style="display:none;">
        <div class="sub">
          <table width="100%" border="0" cellspacing="2" cellpadding="0">
            <tr>
                <td colspan="2"><strong>Add %NAME%</strong></td>
            </tr>
            %CATID%
            <tr>
                <td width="20%">%SUB%:</td>
                <td><input name="name" type="text" id="name" size="40" /></td>
            </tr>
            <tr>
                <td colspan = "2" valign="top">%SUB2%:<br><br>
				<textarea name="description" id="description" cols="" rows=""></textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input name="add" id="add" type="submit" value="Add %NAME%" class="button" /></td>
            </tr>
          </table>
        </div>
    </div>
</form>
<form action="" method="post" name="edit%NAME%">
    <div class="subborder" id="editbox" style="display:none;">
        <div class="sub">
          <table width="100%" border="0" cellspacing="2" cellpadding="0">
            <tr>
                <td colspan="2"><strong>Edit %NAME%</strong></td>
            </tr>
            %CATIDEDIT%
            <tr>
                <td width="20%">%SUB%:</td>
                <td><input name="editname" type="text" id="editname" size="40" /></td>
            </tr>
            <tr>
                <td colspan = "2" valign="top">%SUB2%:<br><br>
				<textarea name="editdescription" id="editdescription" cols="" rows=""></textarea></td>
            </tr>
            <tr>
                <td colspan="2" align="center"><input name="id" id="id" type="hidden" /><input name="edit" id="edit" type="submit" value="Edit %NAME%" class="button" /></td>
            </tr>
          </table>
        </div>
    </div>
</form>
%BOXES%
