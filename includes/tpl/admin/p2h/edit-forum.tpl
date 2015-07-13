<ERRORS>
<form id="add" name="add" method="post" action="">
 <table width="100%" border="0" cellspacing="3" cellpadding="0">
  <tr>
   <td colspan = "2"><strong>Editing Forum:</strong> %NAME%</td>
  </tr>
  <tr>
   <td width="30%">Name:</td>
   <td><input name="forumname" type="text" id="forumname" value="%NAME%" /></td>
  </tr>
  <tr>
   <td width="30%">Hostname:</td>
   <td><input name="hostname" type="text" id="hostname" value="%HOST%" /></td>
  </tr>
  <tr>
   <td>mySQL Username:</td>
   <td><input name="username" type="text" id="username" /></td>
  </tr>
  <tr>
   <td>mySQL Password:<br />(Blank for no change)</td>
   <td><input name="password" type="password" id="password" /></td>
  </tr>
  <tr>
   <td>mySQL Database:</td>
   <td><input name="database" type="text" id="database" /></td>
  </tr>
  <tr>
   <td>Forum Prefix:</td>
   <td><input name="prefix" type="text" id="prefix" /></td>
  </tr>
  <tr>
   <td>Forum URL:</td>
   <td><input name="url" type="text" id="url" value="%URL%" /></td>
  </tr>
  <tr>
   <td colspan="2" align="center"><input type="submit" name="type" id="type" value="Edit Forum" class="button" /></td>
  </tr>
 </table>
</form>
