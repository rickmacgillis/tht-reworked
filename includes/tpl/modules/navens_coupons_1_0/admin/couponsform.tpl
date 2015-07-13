<ERRORS>
<form id="add" name="add" method="post" action="">
<table class = "text" width="100%" border="0" cellspacing="0" cellpadding="0" style = "border-collapse:collapse;">
  <tr>
    <td width = "200">Coupon Name:</td>
    <td><input type="text" name="name" id="name" size = "40" value = "%COUPNAME%"></td>
    <td colspan = "2"></td>
    <td><a title="The coupon's name to be shown in the admin area" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr bgcolor = "#EEEEEE">
    <td valign = "top">Short Description:</td>
    <td colspan = "3"><textarea name = "shortdesc" id = "shortdesc" cols = "55" rows = "5">%SHORTDESC%</textarea></td>
    <td valign = "top"><a title="The description of the coupon for both the admin area and the order page" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td>Coupon Code:</td>
    <td><input type="text" name="coupcode" id="coupcode" size = "40" value = "%COUPCODE%"></td>
    <td colspan = "2"></td>
    <td><a title="The code you want people to type to use this coupon." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr bgcolor = "#EEEEEE">
    <td>Limited Number of Coupons:</td>
    <td><input type="text" name="limitedcoupons" id="limitedcoupons" size = "10" value = "%LIMITEDCOUPONS%"></td>
    <td colspan = "2">Or Unlimited: <input type="checkbox" name="unlimitedcoupons" id="unlimitedcoupons" value = "1" %UNLIMITEDCOUPONS%></td>
    <td><a title="If you want to limit how many times this coupon can be used in total, then you can enter that here.  This is the total number of times anyone can enter the coupon, not how many times an individual user may use the coupon." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td>Expiry Date For Entry:</td>
    <td><input type="text" name="expiredate" id="expiredate" size = "10" value = "%EXPIREDATE%"> <font size = '1'>(Format: MM/DD/YYYY)</font></td>
    <td colspan = "2">Or Never: <input type="checkbox" name="neverexpire" id="neverexpire" value = "1" %NOEXPIRE%></td>
    <td><a title="When is the last date that someone can enter the ticket?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td bgcolor = "#EEEEEE">Availability:</td>
    <td><select name = "area" id = "area">
         %AREA%
        </select></td>
    <td colspan = "2"></td>
    <td><a title="Where do you want this coupon to be able to be used?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td>Good For:</td>
    <td><select name = "goodfor" id = "goodfor">
         %GOODFOR%
        </select></td>
    <td>How Many Months?:</td>
    <td><input type="text" name="monthsgoodfor" id="monthsgoodfor" size = "5" value = "%GOODFORMONTHS%"></td>
    <td><a title="When the coupon is applied, how long is it good for?  If you select 'Set Number Of Months' then you need to specify that number below.  For the purposes of this program, 1 month = 30 days and that's added to the current day its applied." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td bgcolor = "#EEEEEE">Exclusive User:</td>
    <td><select name = "userselect" id = "userselect">
         %USERNAMES%
        </select></td>
    <td>Other Username:</td>
    <td><input type="text" name="username" id="username" size = "10" value = "%USERNAME%"></td>
    <td><a title="If you want this coupon to be used only by one user, then you can enter their username here.  If the user is a new user, you can enter their username manually." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td valign = "top">Which Packages:</td>
    <td valign = "top"><select name = "packages[]" id = "packages" multiple="multiple" size="5"  style="width: 225px">
         %PACKAGES%
        </select></td>
    <td valign = "top" colspan = "2">Or All: <input type="checkbox" name="allpacks" id="allpacks" value = "1" %ALLPACKS%></td>
    <td valign = "top"><a title="What packages is this coupon valid for?" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td bgcolor = "#EEEEEE">Paid Discount Amount:</td>
    <td><input type="text" name="paiddisc" id="paiddisc" size = "5" value = "%PAID%"> <select name = "paidtype" id = "paidtype">
         %PAIDTYPE%
        </select></td>
    <td colspan = "2"></td>
    <td><a title="If you want this coupon available to all packages or a paid package, please enter the discount amount." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td>P2H Discount Amount Initial:</td>
    <td><input type="text" name="p2hinitdisc" id="p2hinitdisc" size = "5" value = "%INITPOSTS%"> <select name = "p2hinittype" id = "p2hinittype">
         %P2HINITTYPE%
        </select> Posts</td>
    <td colspan = "2"></td>
    <td><a title="If you want this coupon available to all packages or a p2h package, please enter the discount amount.  If the coupon is not available on the order page, this can be ignored." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "5"></td>
  </tr>
  <tr>
    <td bgcolor = "#EEEEEE">P2H Discount Amount Monthly:</td>
    <td><input type="text" name="p2hmonthlydisc" id="p2hmonthlydisc" size = "5" value = "%MONTHLYPOSTS%"> <select name = "p2hmonthlytype" id = "p2hmonthlytype">
         %P2HMONTHLYTYPE%
        </select> Posts Per Month</td>
    <td colspan = "2"></td>
    <td><a title="If you want this coupon available to all packages or a p2h package, please enter the discount amount.  If the package is only on the order page, this can be ignored if you don't want them to have a discount for the month." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
  </tr>
  <tr>
    <td align="center" colspan = "5" height = "10"></td>
  </tr>
  <tr>
    <td align="center" colspan = "3"><input type="hidden" name="coupid" id="coupid" value="%ID%" /><input type="submit" name="%ADDEDIT%" id="%ADDEDIT%" value="%ADDEDIT% Coupon" /></td>
  </tr>
</table>
</form>
