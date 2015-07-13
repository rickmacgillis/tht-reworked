<script type="text/javascript">

var wrong = '<img src="<URL>themes/icons/cross.png">';
var right = '<img src="<URL>themes/icons/accept.png">';
var loading = '<img src="<URL>themes/icons/ajax-loader.gif">';

function couponcheck() {
        $("#couponcheck").html(loading);
        window.setTimeout(function() {
                $.get("<AJAX>?function=couponcheck&coupon="+document.getElementById("addcoupon").value+"&username=%USER%"+"&package=%PACKID%&location=invoices", function(data) {
                        if(data == "1") {
                                $("#couponcheck").html(right);
                        }else if(data == "0") {
                                $("#couponcheck").html(wrong);
                        }else{
                                $("#couponcheck").html(data);
                        }
                });
        },500);
}

</script>
     <table class = "text" width = "100%" cellspacing="0" align="center" border = "1" bordercolor = '#888888' style="border-collapse: collapse" cellpadding="3">
      <tr bgcolor = "#EEEEEE">
       <td align="center" width="50%"></td>
       <td align="center"><strong>Invoice Total</strong></td>
      </tr>
      <tr>
       <td align="right" width="50%">Total: </td>
       <td align="center"><strong>%TOTALAMT%</strong></td>
      </tr>
      <tr>
       <td align="center" colspan = "2" id = "couponcheck">&nbsp;</td>
      </tr>
      <tr>
       <td align="right">Enter Coupon: </td>
       <td align="left"><input type = 'textbox' name = 'addcoupon' id = 'addcoupon' onchange="couponcheck()"> <input type = 'submit' name = 'submitaddcoupon' id = 'submitaddcoupon' value = "Add"></td>
      </tr>
      <tr>
       <td align="right">Amount To Pay: </td>
       <td align="left">%CURRSYMBOL% <input type = 'textbox' name = 'paythis' id = 'paythis' value = '%PAYBALANCE%' size = "5"></td>
      </tr>
      <tr>
       <td align="center" colspan = "2"><br><br><input type = 'submit' name = 'checkout' id = 'checkout' value = "Checkout"></td>
      </tr>
      </table>
