<ERRORS>
<form id="settings" name="settings" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="15%">Site Name:</td>
    <td>
      <input size = "40" name="name" type="text" id="name" value="%NAME%" />
      <a title="Your THT Website's Name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>URL:</td>
    <td>
      <input size = "40" name="url" type="text" id="host" value="%URL%" />
      <a title="Your THT Website's URL. (Recommended: http://%RECURL%/) It must Include the trailing slash." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>Default Page:</td>
    <td>%DROPDOWN%    <a title="The Default page shown when accessing the root directory.  Set this to other and enter the directory name below if you wish to use a different directory." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>If Other:</td>
    <td><input size = "40" name="otherdefault" type="text" id="otherdefault" value="%OTHERDEFAULT%" />    <a title="The Default page shown when accessing the root directory.  (Ex. MyDirectory - Do not add a /)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Settings" /></td>
  </tr>
</table>
</form>
