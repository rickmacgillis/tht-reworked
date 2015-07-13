<script type="text/javascript">
function check() {
        if(document.getElementById("understand").checked == true) {
                $("#passwordpart").slideDown(500);
        }
        else {
                $("#passwordpart").slideUp(500);
                $("#finish").slideUp(500);
        }
}
</script>
<ERRORS>
<form id="delete" name="delete" method="post">
<div class="subborder">
    <div class="sub">
      <span class="errors">Notice:</span> This WILL cancel your client account and your hosting account. This means all your files, once this step has completed can't be retrieved.<br /><br>
      <input name="understand" type="checkbox" id="understand" value="1" onchange="check()" /><strong> I understand this notice above</strong>
       <a title="Tick here <b>only if you <i>really</i> want to cancel your account!</b>" class="tooltip"><img src="<ICONDIR>information.png" /></a>
    </div>
</div>
<div id="passwordpart" style="display:none">
    <div class="subborder">
        <div class="sub">
            <table width="100%" border="0" cellspacing="2" cellpadding="0">
              <tr>
                <td width="25%">Your Password:</td>
                <td width="10%"><input name="password" id="password" type="password" />
                </td>
                <td width="65%" align="left"><input name="delete" id="delete" type="submit" value="Delete Account"/>  <a title="Click here to cancel your account." class="tooltip"><img src="<ICONDIR>information.png" /></a></td>
              </tr>
            </table>
        </div>
    </div>
</div>
</form>
