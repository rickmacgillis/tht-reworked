<ERRORS>
<form method = "POST">
<div class="subborder">
 <div class="sub">
  <table class = "text" width = "100%" cellspacing="0" align="center" border = "1" bordercolor = '#888888' style="border-collapse: collapse" cellpadding="3">
   <tr bgcolor = "#EEEEEE">
    <td align="center" width="50%"><strong>Coupon</strong></td>
    <td align="center"><strong>Amount</strong></td>
   </tr>
   %COUPONSLIST%
   <tr>
    <td align="right">Total Savings: </td>
    <td align="center"><strong>%COUPONTOTAL%</strong></td>
   </tr>
  </table><br><br>
  %TRANSACTIONS%
  <table class = "text" width = "100%" cellspacing="0" align="center" border = "1" bordercolor = '#888888' style="border-collapse: collapse" cellpadding="3">
   <tr bgcolor = "#EEEEEE">
    <td align="center" width="50%"></td>
    <td align="center"><strong>Posts Required</strong></td>
   </tr>
   <tr>
    <td align="right" width="50%">Sub Total: </td>
    <td align="center"><strong>%BASEAMOUNT%</strong></td>
   </tr>
   <tr>
    <td align="right" width="50%">Savings: </td>
    <td align="center"><strong><font color = "#779500">&#8722;%COUPONTOTAL% &nbsp;</font></strong></td>
   </tr>
   <tr>
    <td align="right" width="50%">You Posted: </td>
    <td align="center"><strong><font color = "%POSTEDCOLOUR%">&nbsp;%USERPOSTED% &nbsp;</font></strong></td>
   </tr>
   <tr>
    <td align="right" width="50%">Total: </td>
    <td align="center"><strong>%TOTALAMOUNT%</strong></td>
   </tr>
   <tr>
    <td align="center" colspan = "2">&nbsp;</td>
   </tr>
   <tr>
    <td align="right" width="50%">Status: </td>
    <td align="center">&nbsp;<strong>%PAIDSTATUS%</strong></td>
   </tr>
   %ADDCOUPONS%
   </table>
 </div>
</div>
</form>
