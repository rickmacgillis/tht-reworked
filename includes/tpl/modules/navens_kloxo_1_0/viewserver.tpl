<script type="text/javascript">
function serverchange(value) {
        $.get("<AJAX>?function=editserverhash&server=%ID%&type="+value, function(data) {
                $("#passtext").slideUp(500);
                $("#passbox").slideUp(500, function() {
                        var result = data.split(";:;");
                        if(result[0] == "1") {
                                $("#passbox").html('<input name="hash" type="text" id="hash" value="'+result[1]+'" />');
                                $("#passtext").html('Password:');
                        }
                        else {
                                $("#passbox").html('<textarea name="hash" id="hash" cols="45" rows="5">'+result[1]+'</textarea>');
                                $("#passtext").html('Access Hash:');
                        }
                        $("#passtext").slideDown(500);
                        $("#passbox").slideDown(500);
                });
        });
        $.get("<AJAX>?function=editservernstmp&server=%ID%&type="+value, function(data) {
                $("#nstext").slideUp(500);
                $("#nsbox").slideUp(500, function() {
                        var result = data.split(";:;");
                        if(result[0] == "1") {
                                $("#nsbox").html('<input name="nstmp" type="text" id="nstmp" value="'+result[1]+'" />');
                                $("#nstext").html('DNS Template:');

                                $("#nstext").slideDown(500);
                                $("#nsbox").slideDown(500);
                        }
                });
        });
        $.get("<AJAX>?function=editserverwelcome&server=%ID%&type="+value, function(data) {
                $("#welcometext").slideUp(500);
                $("#welcomebox").slideUp(500, function() {
                        var result = data.split(";:;");
                        if(result[0] == "1") {
                                $("#welcomebox").html('<select name="welcome" id="welcome">%WELCOMEOPTS%</select>');
                                $("#welcometext").html('Backend Welcome Email:');

                                $("#welcometext").slideDown(500);
                                $("#welcomebox").slideDown(500);
                        }
                });
        });
}
$(window).load(function () {
        serverchange(document.getElementById('type').value);
});
</script>
<ERRORS>
<form id="addserver" name="addserver" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="175">Name:</td>
    <td>
      <input name="name" type="text" id="name" value="%NAME%" />
      <a title="The server's user-friendly name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>Host:</td>
    <td>
      <input name="host" type="text" id="host" value="%HOST%" />
      <a title="The Server's Hostname. Must be a FQDN!" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>Server IP:</td>
    <td><input type="text" name="ip" id="ip" value="%SERVERIP%" /><a title="The IP for the server the accounts will be created on." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Reseller Port:</td>
    <td><input type="text" name="whmport" id="whmport" value="%RESELLERPORT%" /><a title="The port for WHM/DA for reseller accounts  (Ex. 2086 would be used for example.com:2086)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>CP Port:</td>
    <td><input type="text" name="port" id="port" value="%PORT%" /><a title="The port for cPanel  (Ex. 2082 would be used for example.com:2082)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign = "top">Nameservers<br>(One per line):</td>
    <td><textarea name = "nameservers" id = "nameservers" cols = "30" rows = "5">%NAMESERVERS%</textarea><a title="The nameservers for this server.<br>(Ex. ns1.example.com)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Username:</td>
    <td><input type="text" name="user" id="user" value="%USER%" />
    <a title="The username to access the server." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td valign="top"><span id="passtext"></span></td>
    <td><span id="passbox"></span></td>
  </tr>
  <tr>
    <td valign="top"><span id="nstext"></span></td>
    <td><span id="nsbox"></span></td>
  </tr>
  <tr>
    <td valign="top"><span id="welcometext"></span></td>
    <td><span id="welcomebox"></span></td>
  </tr>
  <tr>
    <td valign="top">Type:</td>
    <td><select name="type" id="type" onchange="serverchange(this.value)">%TYPE%</select> <a title="The control panel that this server is running." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Server" /></td>
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
