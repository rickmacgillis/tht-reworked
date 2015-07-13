<ERRORS>
<form method = "POST">
<div class="subborder">
 <div class="sub">
  <table class = "text" width="100%" cellspacing="3" cellpadding="3" border="0" style="border-collapse: collapse">
   <tr>
    <td align="left" width = "50%">
    <strong>Invoiced To:</strong><br>
    %FNAME% %LNAME% (%UNAME%)<br>
    %ADDRESS%<br>
    %CITY%, %STATE% %ZIP% %COUNTRY%<br><br>
    </td>
    <td align="left">
     <strong>Invoice #%ID%</strong><br>
     Invoice Date: %CREATED%<br>
     Due Date: %DUE%<br>
     Status: %STATUS%<br><br>
    </td>
   </tr>
   <tr>
    <td align="left" colspan = "2">
     <table class = "text" width = "100%" cellspacing="0" align="center" border = "1" bordercolor = '#888888' style="border-collapse: collapse" cellpadding="3">
      <tr bgcolor = "#EEEEEE">
       <td align="center" width="50%"><strong>Description</strong></td>
       <td align="center"><strong>Amount</strong></td>
      </tr>
      <tr>
       <td>%PACKAGE% - %DOMAIN% (%CREATED% - %PACKDUE%)</td>
       <td align="center">%BASEAMOUNT%</td>
      </tr>
      <tr>
       <td align="right">Sub Total: </td>
       <td align="center"><strong>%BASEAMOUNT%</strong></td>
      </tr>
     </table><br><br>
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
    </td>
   </tr>
  </table>
 </div>
</div>
</form>
