<ERRORS>
<form id="packageserver" name="packageserver" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="10%">Server:</td>
    <td width="10%">%SERVER%</td>
    <td valign = "top" align = "left"><a title="The Server which the package is located at." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td>Type:</td>
    <td>
     <select name="type" id="type">
      <option value="free" %SELECTED1%>Free</option>
      %P2HOPTION%
      <option value="paid" %SELECTED3%>Paid</option>
     </select>
    </td>
    <td valign = "top"><a title="The type of your package. You can choose between <em>free</em>, <em>post2host</em> and <em>paid</em>." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td colspan = "3" align="center"><input type="submit" name="packserver" id="packserver" value="Select" class="button" /></td>
  </tr>
</table>
</form>
