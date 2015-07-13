<ERRORS>
<form id="editserver" name="editserver" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="35%">Name:</td>
    <td width="35%"><input name="name" type="text" id="name" value="%NAME%" /></td>
    <td valign = "top"><a title="The Server Name, shown in the AdminCP." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Host:</td>
    <td><input name="host" type="text" id="host" value="%HOST%" /></td>
    <td valign = "top"><a title="The Server's Hostname. Must be a FQDN!" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Server IP:</td>
    <td><input type="text" name="ip" id="ip" value="%SERVERIP%" /></td>
    <td valign = "top"><a title="The IP for the server the accounts will be created on." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Reseller Port:</td>
    <td><input type="text" name="resellerport" id="resellerport" value="%RESELLERPORT%" /></td>
    <td valign = "top"><a title="The port that the reseller users use to connect to the control panel on. 123.123.123.123:PORT!" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>CP Port:</td>
    <td><input type="text" name="port" id="port" value="%PORT%" /></td>
    <td valign = "top"><a title="The port for cPanel  (Ex. 2082 would be used for example.com:2082)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign = "top">Nameservers (One per line):</td>
    <td><textarea name = "nameservers" id = "nameservers" cols = "40" rows = "10">%NAMESERVERS%</textarea></td>
    <td valign = "top"><a title="The nameservers for this server.<br>(Ex. ns1.example.com)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  %SERVER_FIELDS%
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Server" class="button" /></td>
  </tr>
</table>
</form>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
<tr>
<td align="center"><strong>HTTP</strong></td>
<td align="center"><strong>FTP</strong></td>
<td align="center"><strong>MySQL</strong></td>
<td align="center"><strong>POP3</strong></td>
<td align="center"><strong>SSH</strong></td>
</tr>
<tr>
<td align="center"> <img src="../includes/status.php?link=%HOST%:80"></td>
<td align="center"> <img src="../includes/status.php?link=%HOST%:21"></td>
<td align="center"> <img src="../includes/status.php?link=%HOST%:3306"></td>
<td align="center"> <img src="../includes/status.php?link=%HOST%:110"></td>
<td align="center"> <img src="../includes/status.php?link=%HOST%:22"></td>
</tr>
</table>
