<ERRORS>
<form id="form1" name="form1" method="post" action="">
<table width="100%" border="0" cellspacing="3" cellpadding="0">
  <tr>
    <td width="33%">Days Unpaid Until Suspension:</td>
    <td width="13%"><input name="susdays" type="text" id="susdays" size="5" value="%SUSDAYS%" /></td>
    <td>&nbsp;<a title="The amount of days that when a invoice has been left unpaid, it suspends the client." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Days Suspended Until Termination:</td>
    <td><input name="termdays" type="text" id="termdays" size="5" value="%TERDAYS%" />&nbsp;</td>
    <td>&nbsp;<a title="How many days of suspension it takes to terminate." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Currency:</td>
    <td>%CURRENCY%</td>
    <td>&nbsp;<a title="The currency the user has to pay the invoice in." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Paypal Email:</td>
    <td><input name="paypalemail" type="text" id="paypalemail" size="20" value="%PAYPALEMAIL%" /></td>
    <td>&nbsp;<a title="The email you want paypal working with." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Paypal Mode:</td>
    <td>%PAYPALMODE%</td>
    <td>&nbsp;<a title="The mode for paypal.  To test your system, use the sandbox mode.  You'll need a PayPal developer account for that." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Paypal Sandbox Seller Email:</td>
    <td><input name="paypalsandemail" type="text" id="paypalsandemail" size="20" value="%PAYPALSANDEMAIL%" /></td>
    <td>&nbsp;<a title="The paypal sandbox email address you recieved from https://developer.paypal.com" class="tooltip"><img src="<URL>themes/icons/information.png" /></a> &nbsp;&nbsp;&nbsp;&nbsp;<a href = "https://developer.paypal.com" target = "_blank">Get a free PayPal developer account</a></td>
  </tr>
  <tr>
    <td colspan="3" align="center"><input type="submit" name="submit" id="submit" value="Save Settings" /></td>
    </tr>
</table>
</form>
