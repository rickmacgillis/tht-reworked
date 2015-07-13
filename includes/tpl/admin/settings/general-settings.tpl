<ERRORS>
<form id="settings" name="settings" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width = "55%">Site Name:</td>
    <td><input size = "40" name="name" type="text" id="name" value="%NAME%" /> <a title="Your THT Website's Name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>URL:</td>
    <td><input size = "40" name="url" type="text" id="host" value="%URL%" /> <a title="Your THT Website's URL. (Recommended: http://%RECURL%/) It must Include the trailing slash." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Default Page:</td>
    <td>%DEFAULT_PAGE% <a title="The Default page shown when accessing the root directory.  Set this to other and enter the directory name below if you wish to use a different directory." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>If Other:</td>
    <td><input size = "40" name="otherdefault" type="text" id="otherdefault" value="%OTHERDEFAULT%" /> <a title="The Default page shown when accessing the root directory.  (Ex. MyDirectory - Do not add a /)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>IANA TLD update frequency in days:</td>
    <td><input size = "40" name="tld_update_days" type="text" id="tld_update_days" value="%TLD_UPDATE_DAYS%" /> <a title="IANA (Internet Assigned Numbers Authority) maintains a list of valid TLDs.  (Ex. .co, .com, etc.)  Set this to how often you want your system to pull the list for updating it.  This helps with domain validation." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Queue an IANA update on the next domain check?</td>
    <td>%QUEUE_IANA% <a title="If you're having trouble with domains being validated, you can force the system to update the allowed extentions.  If IANA's list cannot be reached, then the system will skip the check.  THT Reworked supports double TLDs as well.  (Ex. .co.uk)" class="tooltip" /><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Settings" class="button" /></td>
  </tr>
</table>
</form>
