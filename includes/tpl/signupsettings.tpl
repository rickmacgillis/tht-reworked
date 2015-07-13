<ERRORS>
<script type="text/javascript" src="<URL>includes/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
        tinyMCE.init({
        mode : "textareas",
        skin : "o2k7",
        theme : "simple"
        });
</script>
<form id="settings" name="settings" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="25%">Multiple Signups Per IP:</td>
    <td>
      %MULTIPLE%    <a title="Do you allow multiple signups for one IP?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>TLD's only:</td>
    <td>
      %TLDONLY%    <a title="Allow ONLY top level domains? Leave as Disabled if unsure.  (No subdomains?)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>Allow Signups:</td>
    <td>
      %GENERAL%    <a title="Is the signup system offline?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>Use Akismet:</td>
    <td>
      %USEAKISMET%    <a title="Do you want your signup form checked with Akismet?  (First name, last name, and email will be checked.)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td>Akismet API Key:</td>
    <td>
      <input type = "text" name = "akismetkey" id = "akismetkey" value = "%AKISMETKEY%"><a title="If you want to use Akismet, enter your API key from http://akismet.com (Its free for most users.)" class="tooltip"><img src="<URL>themes/icons/information.png" /></a> <a href = "http://akismet.com" target = "_blank">Get an API key from Akismet.com</a>
    </td>
  </tr>
  <tr>
    <td valign="top">Signups Closed Message:</td>
    <td><textarea name="message" id="message" cols="" rows="">%MESSAGE%</textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="add" id="add" value="Edit Settings" /></td>
  </tr>
</table>
</form>
