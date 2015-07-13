    <div>
        <div id="1">
          <form method="POST" name="order" id="order">
            <div class="table">
                <div class="cat">Step One - Choose Type/Package</div>
                <div class="text">
                    <table width="100%" border="0" cellspacing="2" cellpadding="0">
                        <tr>
                            <td width="20%">Domain/Subdomain:</td>
                            <td><select name="domsub" id="domsub">
                                <option value="dom" selected="selected">Domain</option>
                                %CANHASSUBDOMAIN%
                                </select></td>
                            <td width="70%"><a title="Choose the type of hosting:<br /><strong>Domain:</strong> example.com<br /><strong>Subdomain:</strong> example.subdomain.com" class="tooltip"><img src="<URL>themes/icons/information.png" /></a></td>
                        </tr>
                    </table>
                </div>
            </div>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                %PACKAGES%
            </table>
          </form>
        </div>
    </div>
