<ERRORS>
<form id="opts" name="opts" method="post" action="">
<table class = "text" width="100%" border="0" cellspacing="0" cellpadding="0" style = "border-collapse:collapse;">
  <tr>
    <td width = "150">Use Multiple Coupons:</td>
    <td><select name = "multicoup" id = "multicoup">
         %MULTICOUP%
        </select> <a title="Can users use multiple coupons?  If not, then the most recently added coupon will be used.  Customers can only use each coupon code once if multiple coupons are allowed, even if they expire.  Also, no matter if this is enabled or not, they cannot discount the price/posts to less than 0." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td width = "150">P2H Grace Period:</td>
    <td><input type = "textbox" name = "graceperiod" id = "graceperiod" value = "%GRACEPERIOD%"> <a title="This is the grace period that P2H users have after they sign up in order to not be effected by the P2H cron.  After this amount of time, the user is required to have posted the monthly required amount or they'll be warned and later suspended." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "10"></td>
  </tr>
  <tr>
    <td align="center" colspan = "3"><input type="submit" name="update" id="update" value="Update" /></td>
  </tr>
</table>
</form>
