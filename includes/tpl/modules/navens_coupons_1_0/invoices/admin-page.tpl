<ERRORS>
<form method = "POST">
<p>From here you can see all invoices in your THT installation and some simple statistics.</p>
<div class="subborder">
 <div class="sub">
  <strong>Invoice Statistics</strong>
  <div class='break'></div>
  <strong>Number of Invoices%FORUSER%:</strong> %NUM%<br />
  <strong>Invoices Paid%FORUSER%:</strong> %NUMPAID%<br />
  <strong>Unpaid Invoices%FORUSER%:</strong> %NUMUNPAID%
 </div>
</div>
<br>
<div class="subborder">
 <div class="sub">
  <table class = "text" width="100%" cellspacing="0" cellpadding="3" border="0" style="border-collapse: collapse">
   <tr>
    <td width="200">Show only invoices from user:</td>
    <td width="75"><select name = "users" id = "users">
         %USERS%
        </select></td>
    <td><input type = "submit" value = "Search" name = "submitusers" id = "submitusers"></td>
   </tr>
   <tr>
    <td width="200">Show only invoices of type:</td>
    <td width="75"><select name = "invtype" id = "invtype">
         %TYPEOPTS%
        </select></td>
    <td><input type = "submit" value = "Search" name = "submittype" id = "submittype"></td>
   </tr>
   <tr>
    <td width="200">Show only invoices of status:</td>
    <td width="75"><select name = "status" id = "status">
         %STATUSOPTS%
        </select></td>
    <td><input type = "submit" value = "Search" name = "submitstatus" id = "submitstatus"></td>
   </tr>
  </table>
 </div>
</div>
<br>
<div class="subborder">
 <div class="sub">
  <table class = "text" width="100%" cellspacing="0" cellpadding="3" border="0" style="border-collapse: collapse">
   <tr bgcolor="#EEEEEE">
    <td width="100" align="center">Invoice #</td>
    <td width="100" align="center">Invoice Date</td>
    <td width="100" align="center">Due Date</td>
    <td width="100" align="center">Total Due</td>
    <td width="100" align="center">Status</td>
    <td width="100" align="center">User</td>
    <td width="100" align="center">View Invoice</td>
   </tr>
   %LIST%
  </table>
 </div>
</div>
</form>
