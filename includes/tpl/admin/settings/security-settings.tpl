<ERRORS>
<form id="edit" name="edit" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width = "55%"><strong>Show THT version in copyright?</strong></td>
    <td>%SHOW_VERSION_ID%
     <a title="Do you want to show the THT Version you're running in the Copyright?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
  <td><strong>Show footer information?</strong></td>
    <td>%SHOW_PAGE_GENTIME%
     <a title="Show simple debug information? IP Address, Page Generation time." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
    </tr>
  <tr>
    <td><strong>Show footer server information?</strong></td>
    <td>%SHOW_FOOTER%
     <a title="Show system information in the footer. It's reccomended that you turn this off on production servers." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td><strong>Show THT's errors?</strong></td>
    <td>%SHOW_ERRORS%
     <a title="Anyone who views your server can see these errors.  It's a good idea to shut these off on production servers." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td><strong>Send an email on cron output?</strong></td>
    <td>%EMAIL_ON_CRON%
    <a title="Should an email be sent to every staff member whenever the cron.php file gives any kind of output?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td><strong>Email to recieve output of cron:</strong></td>
    <td><input type = "text" name = "email_for_cron" value = "%EMAIL_FOR_CRON%">
    <a title="If you elected to have an email with the cron output sent to someone, what email address should recieve it?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td><strong>Session timeout in minutes (0 to disable)</strong></td>
    <td><input type = "text" name = "session_timeout" value = "%SESSION_TIMEOUT%">
     <a title="Anyone who views your server can see these errors.  It's a good idea to shut these off on production servers." class="tooltip"><img src="<URL>themes/icons/information.png" /></a>
    </td>
  </tr>
  <tr>
    <td align="center" colspan = "3" height = "10"></td>
  </tr>
  <tr>
    <td align="center" colspan = "3"><input type="submit" name="add" id="add" value="Edit Security Settings" class="button" /></td>
  </tr>
</table>
</form>
