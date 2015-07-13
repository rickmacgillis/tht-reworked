<ERRORS>
<form id="restoreform" name="restoreform" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td>This will restore your account from the backup selected below.<br><br></td>
  </tr>
  <tr>
   <td width = "55%">Restore most recent <select name = 'restoretype'>
    <option value = "daily">daily</option>
    <option value = "weekly">weekly</option>
    <option value = "monthly">monthly</option>
   </select> backup.  <strong>(This CANNOT be undone!)</strong> <a title="Select the type of backup you wish to restore." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td><br><strong>Also restore the following:</strong></td>
  </tr>
  <tr>
    <td><input type = "checkbox" name = "mail" id = "mail" value = "1"> <img src = "../themes/icons/email.png" border = "0"> Email filters and forwarders</td>
  </tr>
  <tr>
    <td><input type = "checkbox" name = "mysql" id = "mysql" value = "1"> <img src = "../themes/icons/database.png" border = "0"> MySQL Databases</td>
  </tr>
  <tr>
    <td><input type = "checkbox" name = "subs" id = "subs" value = "1"> <img src = "../themes/icons/server_add.png" border = "0"> Subdomain Entries</td>
  </tr>
  <tr>
    <td align="center" height = "10"></td>
  </tr>
  <tr>
    <td align="center"><input type="submit" name="restorebackup" id="restorebackup" value="Restore Entire Account" /></td>
  </tr>
</table>
</form>
