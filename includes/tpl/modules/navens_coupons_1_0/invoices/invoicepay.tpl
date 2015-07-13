     <table class = "text" width = "100%" cellspacing="0" align="center" border = "1" bordercolor = '#888888' style="border-collapse: collapse" cellpadding="3">
      <tr bgcolor = "#EEEEEE">
       <td align="center" width="50%"></td>
       <td align="center"><strong>Invoice Total</strong></td>
      </tr>
      <tr>
       <td align="right" width="50%">Total: </td>
       <td align="center"><strong>%TOTALAMOUNT%</strong></td>
      </tr>
      <tr>
       <td align="center" colspan = "2">&nbsp;</td>
      </tr>
      <tr>
       <td align="right">Enter Coupon: </td>
       <td align="left"><input type = 'textbox' name = 'addcoupon' id = 'addcoupon'> <input type = 'submit' name = 'submitaddcoupon' id = 'submitaddcoupon' value = "Add"></td>
      </tr>
      <tr>
       <td align="right">Amount To Pay: </td>
       <td align="left">%CURRSYMBOL% <input type = 'textbox' name = 'paythis' id = 'paythis' value = '%PAYBALANCE%' size = "5"></td>
      </tr>
      <tr>
       <td align="center" colspan = "2"><br><br><input type = 'submit' name = 'checkout' id = 'checkout' value = "Checkout"></td>
      </tr>
      </table>
