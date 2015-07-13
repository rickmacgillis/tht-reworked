<script type="text/javascript">

var wrong = '<img src="<URL>themes/icons/cross.png">';
var right = '<img src="<URL>themes/icons/accept.png">';
var loading = '<img src="<URL>themes/icons/ajax-loader.gif">';

function couponcheck() {
        $("#couponcheck").html(loading);
        window.setTimeout(function() {
                $.get("<AJAX>?function=couponcheck&coupon="+document.getElementById("coupon").value+"&username="+document.getElementById("username").value+"&package=%PACKID%&location=orders", function(data) {
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
<form method="POST" name="order" id="order">
<div>
    %ERRORS%
    <div class="table" id="2">
        <div class="cat">Step Two - Terms of Service</div>
        <div class="text">
            <table border="0" cellspacing="2" cellpadding="0" align="center" style="width: 100%;">
              <tr>
                <td colspan="2">
                    <div class="subborder">
                        <div class="sub" id="description">
                            %TOS%
                        </div>
                    </div>
                </td>
              </tr>
              <tr>
                <td width="330"><input name="agree" id="agree" type="checkbox" value="1" %AGREE%/> Do you agree to the <NAME> Terms of Service?</td>
                <td><a title="The Terms of Service is the set of rules you abide by. These must be agreed to." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
            </table>
        </div>
    </div>
    <br><br>
        <div class="table" id="3">
        <div class="cat">Step Three - Client Account</div>
        <div class="text">
            <table border="0" cellspacing="2" cellpadding="0" align="center" style="width: 100%;">
              <tr>
                <td width = "200">Username:</td>
                <td width = "250"><input type="text" name="username" id="username" size = "47" value = "%USERNAME%" /></td>
                <td align="left" width = "16" colspan = "2"><a title="The username is your unique identity to your account. This is both your client account and control panel username. Please keep it under 8 characters." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Password:</td>
                <td><input type="password" name="password" id="password" size = "47" value = "%PASSWORD%" /></td>
                <td align="left" valign="middle" colspan = "2"><a title="Your password is your own personal key that allows only you to log you into your account." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Confirm Password:</td>
                <td colspan = "3"><input type="password" name="confirmp" id="confirmp" size = "47" value = "%CONFPASS%" /></td>
              </tr>
              <tr>
                <td>Email:</td>
                <td><input type="text" name="email" id="email" size = "47" value = "%EMAIL%" /></td>
                <td align="left" colspan = "2"><a title="Your email is your own address where all <NAME> emails will be sent to. Make sure this is valid." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>First Name:</td>
                <td><input type="text" name="firstname" id="firstname" size = "47" value = "%FIRSTNAME%" /></td>
                <td align="left" colspan = "2"><a title="Your first name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Last Name:</td>
                <td><input type="text" name="lastname" id="lastname" size = "47" value = "%LASTNAME%" /></td>
                <td align="left" colspan = "2"><a title="Your last name." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Address:</td>
                <td><input type="text" name="address" id="address" size = "47" value = "%ADDRESS%" /></td>
                <td align="left" colspan = "2"><a title="Your personal address." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>City:</td>
                <td><input type="text" name="city" id="city" size = "47" value = "%CITY%" /></td>
                <td align="left" colspan = "2"><a title="Your city. Letters only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>State:</td>
                <td><input type="text" name="state" id="state" size = "47" value = "%STATE%" /></td>
                <td align="left" colspan = "2"><a title="Your state. Letters only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Zip Code:</td>
                <td><input type="text" name="zip" id="zip" size = "47" value = "%ZIP%" /></td>
                <td align="left" colspan = "2"><a title="Your zip/postal code. Numbers only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Country:</td>
                <td>%COUNTRIES%</td>
                <td align="left" colspan = "2"><a title="Your country." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Phone Number:</td>
                <td><input type="text" name="phone" id="phone" size = "47" value = "%PHONE%" /></td>
                <td align="left" colspan = "2"><a title="Your personal phone number. Numbers and dashes only." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Time Zone:</td>
                <td>%TZADJUST%</td>
                <td align="left" colspan = "2"><a title="Select your time zone to show the dates and times on this site in your timezone." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td><img src="<URL>includes/captcha_image.php"></td>
                <td><input type="text" name="human" id="human" size = "47" /></td>
                <td align="left" colspan = "2"><a title="Answer the question to prove you are not a bot." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
              </tr>
              <tr>
                <td>Coupon:</td>
                <td><input type="text" name="coupon" id="coupon" size = "47" value = "%COUPON%" onchange="couponcheck()" /></td>
                <td align="left" width = "20"><a title="If you have a coupon code, please enter it here." class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                <td align="left" id = "couponcheck">&nbsp;</td>
              </tr>
            </table>
        </div>
    </div>
    <br><br>
    <div class="table" id="4">
        <div class="cat">Step Four - Hosting Account</div>
        <div class="text">
             <table width="100%" border="0" cellspacing="2" cellpadding="0">
                %DOMORSUB%
                %TYPESPECIFIC%
            </table>
        </div>
    </div>
    <br><br>
    <table width="100%" border="0" cellspacing="2" cellpadding="0" id="steps">
      <tr>
        <td align="right"><input type="submit" name="submitfinish" id="finish" value="Finish" class="button" /></td>
      </tr>
    </table>
</div>
</form>
