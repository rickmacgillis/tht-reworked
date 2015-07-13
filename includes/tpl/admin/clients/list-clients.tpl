<ERRORS>
<div class="subborder">
 <form id="filter" name="filter" method="post">
  <select size="1" name="show">
   <option value="all">ALL</option>
   <option value="1">Active</option>
   <option value="4">Awaiting Payment</option>
   <option value="3">Awaiting Validation</option>
   <option value="3">Unconfirmed Email Address</option>
   <option value="2">Suspended</option>
   <option value="9">Cancelled</option>
  </select>
  <input type="submit" name="filter" id="filter" value="Filter Accounts" class="button" />
 </form>
 <table width="100%" cellspacing="2" cellpadding="2" border="1" style="border-collapse: collapse" bordercolor="#000000">
  <tr bgcolor="#EEEEEE">
   <td width="100" align="center" style="border-collapse: collapse" bordercolor="#000000">Date Registered</td>
   <td width="100" align="center" style="border-collapse: collapse" bordercolor="#000000">Username</td>
   <td align="center" style="border-collapse: collapse" bordercolor="#000000">E-mail</td>
  </tr>
  %CLIENTS%
 </table>
</div>
<center>
%PAGING%
</center>
