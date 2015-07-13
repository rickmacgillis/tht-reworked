<script type="text/javascript">
tinyMCE.init({
    mode : "textareas",
	plugins : '<WYSIWYG_PLUGS>',
	language : '<WYSIWYG_LANG>'
});
</script>
<ERRORS>
<form id="addticket" name="addticket" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="20%">Title:</td>
    <td width="10%">
      <input name="title" size = "66" type="text" id="title" /></td>
    <td width="70%"><a title="The name of the ticket. This should briefly describe your problem." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign="top">Urgency:</td>
    <td colspan = "2"><label>
      <select name="urgency" id="urgency">
        <option>Very High</option>
        <option>High</option>
        <option selected="selected">Medium</option>
        <option>Low</option>
      </select>
    </label><a title="The urgency of your ticket. Is it very important and needs solving fast?" class="tooltip"><img src="<URL>themes/icons/information.png" alt="Info" /></a></td>
  </tr>
  <tr>
    <td colspan = "3" valign="top">Content:<br><br>
	<textarea name="content" id="msgcontent"></textarea></td>
  </tr>
  <tr>
    <td align="center" colspan="3"><input type="submit" name="add" id="add" value="Add Ticket" class="button" /></td>
  </tr>
</table>
</form>
