<script type="text/javascript">
function changePrefix(value)
{
        var prefix = document.getElementById('prefix');
        
        if (value == 'phpbb') { prefix.value = 'phpbb_'; }
        if (value == 'phpbb2') { prefix.value = 'phpbb_'; }
        if (value == 'mybb') { prefix.value = 'mybb_'; }
        if (value == 'ipb') { prefix.value = 'ipb_'; }
        if (value == 'ipb3') { prefix.value = 'ipb_'; }
        if (value == 'vb') { prefix.value = 'vb_'; }
        if (value == 'smf') { prefix.value = 'smf_'; }
        if (value == 'aef') { prefix.value = 'aef_'; }
        if (value == 'drupal') { prefix.value = ''; }
}
</script>
<ERRORS>
<form id="add" name="add" method="post" action="">
  <table width="100%" border="0" cellspacing="3" cellpadding="0">
    <tr>
     <td width="30%">Hostname:</td>
     <td><input name="hostname" type="text" id="hostname" value="localhost" /></td>
    </tr>
    <tr>
     <td>Forum Name:</td>
     <td><input name="forumname" type="text" id="forumname" maxlength="28"/></td>
    </tr>
    <tr>
     <td width="30%">mySQL Username:</td>
     <td><input name="username" type="text" id="username" /></td>
    </tr>
    <tr>
     <td>mySQL Password:</td>
     <td><input name="password" type="password" id="password" /></td>
    </tr>
    <tr>
     <td>mySQL Database:</td>
     <td><input name="database" type="text" id="database" /></td>
    </tr>
    <tr>
     <td width="30%">Forum:</td>
     <td>
      <select name="forum" id="forum" onchange="changePrefix(this.value)">
       <option value="phpbb" selected="selected">phpBB 3</option>
       <option value="phpbb2">phpBB 2</option>
       <option value="mybb">myBB</option>
       <option value="ipb">Invision Power Board 2</option>
       <option value="ipb3">Invision Power Board 3</option>
       <option value="vb">vBulletin</option>
       <option value="smf">SMF</option>
       <option value="aef">AEF</option>
       <option value="drupal">Drupal</option>
      </select>
     </td>
    </tr>
    <tr>
     <td>Forum Prefix:</td>
     <td><input name="prefix" type="text" id="prefix" value="phpbb_" /></td>
    </tr>
    <tr>
     <td>Forum URL:</td>
     <td><input name="url" type="text" id="url" value="" /></td>
    </tr>
    <tr>
     <td colspan="2" align="center"><input type="submit" name="type" id="type" value="Add Forum" class="button" /></td>
    </tr>
  </table>
</form>
