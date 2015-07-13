<div class="subborder" id="ticket-%ID%">
    <div class="sub">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <tr>
                <td><a href="?page=tickets&sub=view&do=%ID%"><strong>%TITLE%</strong></a>&nbsp;<span class = "priority_%URGENCY_CLASS%">%URGENCYTEXT%</span><br />Last Updated: %UPDATE%</td>
                <td width="30" align="right"><img title = "%STATUSMSG%" alt="Ticket Status" src="<ICONDIR>%STATUS%.png"></td>
                <td class="rightbreak">&nbsp;</td>
                <td align="center" width="5%">
                 <a href="?page=tickets&sub=view&do=%ID%" class="tooltip" title="View the ticket '%TITLE%'."><img alt="View Ticket" src="<ICONDIR>eye.png"></a>
                 <a href="javascript:void(0);" class="ticket-delete" id="ticket-delete-%ID%" title="Delete the ticket '%TITLE%'."><img alt="Delete Ticket" src="<ICONDIR>delete.png"></a>
                </td>
            </tr>
        </table>
    </div>
</div>
