<?php

// The Hosting Tool
// Client Area - Invoice Management
// By Jimmie Lin
// Date + UI improvements Julio Montoya <gugli100@gmail.com> Beeznest
// Released under the GNU-GPL

//Check if called by script
if(THT != 1) {
    die();
}

class page {
    public function content() { # Displays the page
        global $style, $db, $main, $invoice, $navens_coupons, $sdk, $type;
        global $navens_upgrade;

        if($sdk->isint(str_replace("P2H-", "", $main->getvar['view']))) {
            //Display the invoice


            if(substr_count($main->getvar['view'], "P2H-")) {
                $p2hid = str_replace("P2H-", "", $main->getvar['view']);
                $userid = $sdk->tdata("user_packs", "id", $p2hid);
                $userid = $userid['userid'];
                $userdata = $navens_coupons->admin_userdata($userid);
            }
            else {
                $invoiceid = $main->getvar['view'];
                $invoice_data_top = $sdk->tdata("invoices", "id", $invoiceid);
                $pid = $invoice_data_top['pid'];
                $userid = $invoice_data_top['uid'];
                $uidtopack = $navens_upgrade->uidtopack($userid, $pid);
                
                if(!$pid){
                    $db->query("UPDATE <PRE>invoices SET pid = '".$uidtopack['user_packs']['pid']."' WHERE id = '".$invoice_data_top['id']."'");
                }
                
                $userdata = $navens_coupons->admin_userdata($userid);
            }


            if($_POST['submitaddcoupon']) {

                if(!$main->postvar['addcoupon']) {
                    $main->errors("Please enter a coupon code.");
                }
                else {

                    $coupcode = $main->postvar['addcoupon'];
                    $user = $sdk->uname($userid);
                    $pack_data = $navens_upgrade->uidtopack($userid, $pid);
                    $packid = $pack_data['packages']['id'];
                    $multi_coupons = $navens_coupons->coupconfig("multicoupons");

                    if($p2hid) {
                        $monthly = $pack_data['additional']['monthly'];
                        $monthly = $navens_coupons->get_discount("p2hmonthly", $monthly, $userid);
                        $total_posted = $navens_coupons->totalposts($userid);
                        $amt_owed = max(0, ($monthly - $total_posted));
                    }
                    else {
                        $invoice_info = $sdk->tdata("invoices", "id", $invoiceid);
                        if($invoice_info['pid'] != $pack_data['user_packs']['pid']){
                            $pack_data = $navens_upgrade->pidtobak($invoice_info['pid'], $invoice_info["uid"]);
                        }
                        $total_paid = $navens_coupons->totalpaid($invoiceid);
                        $amt_owed = max(0, ($invoice_info['amount'] - $total_paid));
                    }

                    if($amt_owed == 0) {
                        $main->errors("The user's balance is already paid in full, so you can't add another coupon.");
                    }
                    else {

                        $coupon_info = $navens_coupons->coupon_data($coupcode);
                        $coupid = $coupon_info['id'];

                        $use_coupon = $navens_coupons->use_coupon($coupid, $packid, $invoiceid, $userid);
                        if(!$use_coupon) {
                            if(!$multi_coupons) {
                                $main->errors("Coupon code entered was invalid or user is already using a coupon.  You can give them a credit instead.");
                            }
                            else {
                                $main->errors("Coupon code entered was invalid or the user is already using this coupon.");
                            }
                        }
                        else {
                            $main->redirect("?page=invoices&view=".$main->getvar['view']);
                        }
                    }
                }
            }


            if($_POST['submitcredit']) {
                if(!is_numeric($main->postvar['credit'])) {
                    $main->errors("Please enter the amount to be credited or debited.");
                }
                else {
                    if($main->postvar['creditreason']) {
                        $creditreason = $main->postvar['creditreason'];
                        $creditreason = ' <a title="'.$creditreason.'" class="tooltip"><img src="<URL>themes/icons/information.png"></a>';
                        $creditreason = str_replace(",", "", $creditreason); //Can't have commas, no way no how!  ;)  lol  We need to be able to explode(",", $invoice_info['txn']);
                    }

                    if($p2hid) {
                        $credit_fee = $main->postvar['credit'];
                    }
                    else {
                        $credit_fee = $sdk->addzeros($main->postvar['credit']);
                    }

                    if($credit_fee != 0) {
                        if(substr_count($credit_fee, "-")) {
                            $creditfee_lable = "CHARGE";
                        }
                        else {
                            $creditfee_lable = "CREDIT";
                        }

                        $packinfo = $navens_upgrade->uidtopack($userid, $pid);
                        if($pid != $packinfo['user_packs']['pid'] && !$p2hid){
                            $packinfo = $navens_upgrade->pidtobak($pid, $userid);
                        }
                        $monthly = $packinfo['additional']['monthly'];
                        if($p2hid) {
                            $amt_owed = max(0, ($monthly - $navens_coupons->totalposts($userid)));
                        }
                        else {
                            $amt_owed = max(0, ($monthly - $navens_coupons->totalpaid($invoiceid)));
                        }

                        if($amt_owed == 0 && $creditfee_lable == "CREDIT") {
                            $main->errors("The user's balance is already paid in full, so you can't add a credit.");
                        }
                        else {

                            if($p2hid) {

                                $p2h_info = $sdk->tdata("mod_navens_coupons_p2h", "uid", $userid);
                                if($p2h_info['datepaid']) {
                                    $comma = ",";
                                }

                                $datepaid = $p2h_info['datepaid'].$comma.time();
                                $txn = $p2h_info['txn'].$comma.$creditfee_lable.$creditreason;
                                $amt_paid = $p2h_info['amt_paid'].$comma.$credit_fee;
                                $gateway = $p2h_info['gateway'].$comma."INTERNAL";

                                $db->query("UPDATE <PRE>mod_navens_coupons_p2h SET datepaid = '".$datepaid."', txn = '".$txn."', amt_paid = '".$amt_paid."', gateway = '".$gateway."' WHERE uid = '".$userid."' LIMIT 1");
                            }
                            else {
                                $invoice_info = $sdk->tdata("invoices", "id", $invoiceid);
                                if($invoice_info['pid'] != $packinfo['user_packs']['pid']){
                                    $pack_info = $navens_upgrade->pidtobak($invoice_info['pid'], $invoice_info["uid"]);
                                }
                                if($invoice_info['datepaid']) {
                                    $comma = ",";
                                }

                                $datepaid = $invoice_info['datepaid'].$comma.time();
                                $txn = $invoice_info['txn'].$comma.$creditfee_lable.$creditreason;
                                $amt_paid = $invoice_info['amt_paid'].$comma.$credit_fee;
                                $gateway = $invoice_info['gateway'].$comma."INTERNAL";

                                $db->query("UPDATE <PRE>invoices SET datepaid = '".$datepaid."', txn = '".$txn."', amt_paid = '".$amt_paid."', gateway = '".$gateway."' WHERE id = '".$invoiceid."' LIMIT 1");
                            }
                            $main->redirect("?page=invoices&view=".$main->getvar['view']);
                        }
                    }
                }
            }


            if($_POST['submitpayarrange']) {
                $invoice_info = $sdk->tdata("invoices", "id", $invoiceid);
                $duedate = $invoice_info['due'];
                $days_modify = $main->postvar['days'];
                $days_modify = $days_modify * 24 * 60 * 60;

                if($main->postvar['addsub'] == "add") {
                    $new_due_date = $duedate + $days_modify;
                }
                else {
                    $new_due_date = $duedate - $days_modify;
                }

                $db->query("UPDATE <PRE>invoices SET due = '".$new_due_date."' WHERE id = '".$invoiceid."' LIMIT 1");
                $main->redirect("?page=invoices&view=".$main->getvar['view']);
            }


            if($p2hid) {
                $p2h_info = $sdk->tdata("user_packs", "id", $p2hid);
            }
            else {
                $invoice_info = $sdk->tdata("invoices", "id", $invoiceid);
            }

            if(empty($invoice_info) && empty($p2h_info)) {
                $main->redirect("?page=invoices");
                exit;
            }

            if($main->getvar['deleteinv']) {
                if($main->postvar['yes']) {
                    if($p2hid) {
                        $db->query("DELETE FROM <PRE>mod_navens_coupons_p2h WHERE uid = '".$userid."' LIMIT 1");
                        $main->redirect("?page=invoices&view=".$main->getvar['view']);
                    }
                    else {
                        $db->query("DELETE FROM <PRE>invoices WHERE id = '".$invoiceid."' LIMIT 1");
                        $main->redirect("?page=invoices");
                    }
                }
                elseif($main->postvar['no']) {
                    $main->redirect("?page=invoices&view=".$main->getvar['view']);
                }
                else {
                    $array['HIDDEN'] = "<input type = 'hidden' name = 'confirm' value = 'confirm'>";
                    echo $style->replaceVar("tpl/warning.tpl", $array);
                    $warning_page = '1';
                }
            }

            if($userdata['removed'] == 1) {
                $upackage = $sdk->tdata("user_packs_bak", "userid", $userid);
            }
            else {
                $upackage = $sdk->tdata("user_packs", "userid", $userid);
            }

            if(!$p2hid){
                $package = $sdk->tdata("packages", "id", $invoice_info['pid']);
            }else{
                $package = $sdk->tdata("packages", "id", $upackage['pid']);
            }
            $monthly = $type->additional($package['id']);
            $subtotal = $monthly['monthly'];


            if(is_numeric($main->getvar['remove'])) {
                $remove_id = $main->getvar['remove'];
                if($p2hid) {
                    $navens_coupons->remove_p2h_coupon($remove_id, $userid);
                }
                else {
                    $navens_coupons->remove_coupon($remove_id, $package['id'], $invoice_info['id'], $userid);
                }
                $main->redirect("?page=invoices&view=".$main->getvar['view']);
                exit;
            }

            if($p2hid) {
                $due = date("m/t/Y");
                $created = date("m/1/Y");

                $p2h = $type->classes["p2h"];
                $monthly_with_disc = $navens_coupons->get_discount("p2hmonthly", $subtotal, $userid);
                $total_posts = $p2h->userposts($package['id'], $p2hid);
                $total_paid = $navens_coupons->totalposts($userid);

                if(empty($total_paid)) {
                    $total_paid = 0;
                }

                if(empty($total_posts)) {
                    $total_posts = 0;
                }

                $acct_balance = max(0, ($monthly_with_disc - $total_paid));

                $invoice_info1['BASEAMOUNT'] = $sdk->s($subtotal, " Post");
                $invoice_info1['TOTALAMOUNT'] = $sdk->s($acct_balance, " Post");
                $invoice_info1['BALANCE'] = $sdk->s($acct_balance, " Post");
                $invoice_info1['CREDIT'] = $acct_balance;
                $invoice_info1['CURRSYMBOL'] = "";
                $invoice_info1['POSTS'] = " Posts";
                $invoice_info1['TOTALPAID'] = $sdk->s($total_paid, " Post");
                $invoice_info1['COUPONTOTAL'] = $sdk->s(($subtotal - $monthly_with_disc), " Post");
                $invoice_info1['DELRESET'] = "Reset";

            }
            else {

                $created = strtotime($invoice_info['created']);
                $thirty_days = 30 * 24 * 60 * 60;
                $orig_due = $created + $thirty_days;

                if($main->getvar['resetpayarange']) {
                    $db->query("UPDATE <PRE>invoices SET due = '".$orig_due."' WHERE id = '".$invoiceid."' LIMIT 1");
                    $main->redirect("?page=invoices&view=".$invoiceid);
                }

                if($main->convertdate("n/d/Y", $invoice_info['due']) != ($main->convertdate("n/d/Y", $created + $thirty_days))) {
                    $due_text = " (Originally ".$main->convertdate("n/d/Y", $orig_due).") - <a href = '?page=invoices&view=".$invoiceid."&resetpayarange=1'>Reset</a>";
                }

                $due = $main->convertdate("n/d/Y", $invoice_info['due']);
                $created = $main->convertdate("n/d/Y", $created);
                
                $total_paid_real = $navens_coupons->totalpaid($invoiceid);
                if($total_paid_real < 0) {
                    $total_paid = "0.00";
                }else{
                    $total_paid = $total_paid_real;
                }
                
                
                $acct_balance = $invoice_info['amount'] - $total_paid_real;
                $acct_balance = $sdk->addzeros($acct_balance);

                if($acct_balance < 0) {
                    $acct_balance = "0.00";
                }

                if($acct_balance == 0 && $invoice_info['is_paid'] == '0') {
                    $db->query("UPDATE <PRE>invoices SET is_paid = '1' WHERE id = '".$invoice_info['id']."' LIMIT 1");
                    $main->redirect("?page=invoices&view=".$invoiceid);
                }

                if($acct_balance > 0 && $invoice_info['is_paid'] == '1') {
                    $db->query("UPDATE <PRE>invoices SET is_paid = '0' WHERE id = '".$invoice_info['id']."' LIMIT 1");
                    $main->redirect("?page=invoices&view=".$invoiceid);
                }
                $invoice_info1['BASEAMOUNT'] = $sdk->money($subtotal);
                $invoice_info1['TOTALAMOUNT'] = $sdk->money($acct_balance);
                $invoice_info1['BALANCE'] = $sdk->money($acct_balance);
                $invoice_info1['CREDIT'] = $acct_balance;
                $invoice_info1['CURRSYMBOL'] = $sdk->money($acct_balance, "", 1)." ";
                $invoice_info1['POSTS'] = "";
                $invoice_info1['TOTALPAID'] = $sdk->money($total_paid);
                $invoice_info1['COUPONTOTAL'] = $sdk->money($subtotal - $navens_coupons->get_discount("paid", $subtotal, $userid));
                $invoice_info1['DELRESET'] = "Delete";

            }

            $invoice_info1['ID'] = $main->getvar['view'];
            $invoice_info1['DUE'] = $due.$due_text;
            $invoice_info1['PACKDUE'] = $due;
            $invoice_info1['CREATED'] = $created;

            $invoice_info1['UNAME'] = $userdata['user'];
            $invoice_info1['FNAME'] = $userdata['firstname'];
            $invoice_info1['LNAME'] = $userdata['lastname'];
            $invoice_info1['ADDRESS'] = $userdata['address'];
            $invoice_info1['CITY'] = $userdata['city'];
            $invoice_info1['STATE'] = $userdata['state'];
            $invoice_info1['ZIP'] = $userdata['zip'];
            $invoice_info1['COUNTRY'] = strtoupper($userdata['country']);

            $invoice_info1['DOMAIN'] = $upackage['domain'];
            $invoice_info1['PACKAGE'] = $package['name'];

            $invoice_info1['STATUS'] = $acct_balance == 0 ? "<font color = '#779500'>Paid</font>" : "<font color = '#FF7800'>Unpaid</font>";
            if($invoice_info['locked'] && $invoice_info['hadcoupons']){
                $coupon_list   = explode(",", $invoice_info['hadcoupons']);
                $coupon_values = explode(",", $invoice_info['couponvals']);
                if($coupon_list){
                    for($i=0;$i<count($coupon_list);$i++){
                        $coupons['COUPONAMOUNT'] = $sdk->money($coupon_values[$i]);
                        $coupons['COUPCODE'] = $coupon_list[$i];
                        $coupons['REMOVE'] = "";
                        $invoice_info1['COUPONSLIST'] .= $navens_coupons->tpl("invoices/couponslist.tpl", $coupons);
                        $coup_total = $coup_total+$coupon_values[$i];
                    }
                    $invoice_info1['COUPONTOTAL'] = $sdk->money(min($subtotal, $coup_total));
                }
            }else{

            $coupons_query = $db->query("SELECT * FROM <PRE>mod_navens_coupons_used WHERE user = '".$userid."' AND disabled = '0' ORDER BY `id` ASC");
            while($coupons_used_fetch = $db->fetch_array($coupons_query)) {
                $valid_coupon = $navens_coupons->check_expire($coupons_used_fetch['coupcode'], $userid);
                if($valid_coupon) {

                    if($p2hid) {
                        $coupamt = $sdk->s($coupons_used_fetch['p2hmonthlydisc'], " Post");
                    }
                    else {
                        $coupamt = $sdk->money($coupons_used_fetch['paiddisc']);
                    }

                    $coupons['COUPONAMOUNT'] = $coupamt;
                    $coupons['COUPCODE'] = $coupons_used_fetch['coupcode'];
                    $coupons['REMOVE'] = $userdata['removed'] == 1 ? "" : ('(<a href = "?page=invoices&view='.$main->getvar['view'].'&remove='.$coupons_used_fetch['id'].'">Remove</a>)');
                    $invoice_info1['COUPONSLIST'] .= $navens_coupons->tpl("invoices/couponslist.tpl", $coupons);
                }
            }
            } //Added ny Na'ven's Upgrade System [2]

            if(!$invoice_info1['COUPONSLIST']) {
                $invoice_info1['COUPONSLIST'] = "<tr><td></td><td align = 'center'>None</td></tr>";
            }


            if($p2hid) {
                $p2h_payments = $sdk->tdata("mod_navens_coupons_p2h", "uid", $userid);
                $package_info = $sdk->uidtopack($userid);
                if(empty($p2h_payments)) {
                    $p2h_pay_array = array(
                        "uid" => $userid,
                        "amt_paid" => $total_posts,
                        "txn" => $package_info['uadditional']['fuser'],
                        "datepaid" => time(),
                        "gateway" => $package_info['additional']['forum']);

                    $sdk->insert("mod_navens_coupons_p2h", $p2h_pay_array);
                    $p2h_payments = $sdk->tdata("mod_navens_coupons_p2h", "uid", $userid);
                }

                $amt_paid = $p2h_payments['amt_paid'];
                $txn = $p2h_payments['txn'];
                $datepaid = $p2h_payments['datepaid'];
                $gateway = $p2h_payments['gateway'];
            }
            else {
                $amt_paid = $invoice_info['amt_paid'];
                $txn = $invoice_info['txn'];
                $datepaid = $invoice_info['datepaid'];
                $gateway = $invoice_info['gateway'];
            }

            $amt_paid = explode(",", $amt_paid);
            $txn = explode(",", $txn);
            $datepaid = explode(",", $datepaid);
            $gateway = explode(",", $gateway);

            $remnum = 1;
            for($i = 0; $i < count($amt_paid); $i++) {

                unset($remtxn);
                if($gateway[$i] == "INTERNAL" && !$invoice_info['locked'] && !$userdata['removed']) {
                    $remtxn = ' <a href = "?page=invoices&view='.$main->getvar['view'].'&remtxn='.$remnum.'">[Delete]</a>';
                }

                if($txn[$i] == $package_info['uadditional']['fuser']) {
                        if($amt_paid[$i] != $total_posts){
                                $reload = 1;
                        }
                    $amt_paid[$i] = $total_posts;
                    $datepaid[$i] = time();
                }

                $paid_this = $paid_this + $amt_paid[$i];
                if($p2hid) {
                    $txnlist['PAIDAMOUNT'] = $sdk->s(str_replace("-", "&#8722;", $amt_paid[$i]), " Post").$remtxn;
                }
                else {
                    $txnlist['PAIDAMOUNT'] = $sdk->money($amt_paid[$i]).$remtxn;
                }
                $txnlist['TXN'] = $txn[$i];
                $txnlist['PAIDDATE'] = $main->convertdate("n/d/Y", $datepaid[$i]);
                $txnlist['GATEWAY'] = $gateway[$i];
                $invoice_info1['TXNS'] .= $navens_coupons->tpl("invoices/txnlist.tpl", $txnlist);

                if($main->getvar['remtxn'] != $i + 1) {
                    $paidamts = $paidamts.",".$amt_paid[$i];
                    $paidtxn = $paidtxn.",".$txn[$i];
                    $paiddate = $paiddate.",".$datepaid[$i];
                    $paidgateway = $paidgateway.",".$gateway[$i];
                }
                $remnum++;
            }

            if($p2hid) {
                $paidamts = substr($paidamts, 1, strlen($paidamts));
                $paidtxn = substr($paidtxn, 1, strlen($paidtxn));
                $paiddate = substr($paiddate, 1, strlen($paiddate));
                $paidgateway = substr($paidgateway, 1, strlen($paidgateway));

                $p2h_pay_array = array(
                    "amt_paid" => $paidamts,
                    "txn" => $paidtxn,
                    "datepaid" => $paiddate,
                    "gateway" => $paidgateway);

                $sdk->update("mod_navens_coupons_p2h", $p2h_pay_array, "uid", $userid);

                if($main->getvar['remtxn'] || $reload) {
                    $main->redirect("?page=invoices&view=".$main->getvar['view']);
                }
            }
            else {
                if($main->getvar['remtxn']) {
                    $paidamts = substr($paidamts, 1, strlen($paidamts));
                    $paidtxn = substr($paidtxn, 1, strlen($paidtxn));
                    $paiddate = substr($paiddate, 1, strlen($paiddate));
                    $paidgateway = substr($paidgateway, 1, strlen($paidgateway));
                    $db->query("UPDATE <PRE>invoices SET amt_paid = '".$paidamts."', txn = '".$paidtxn."', datepaid = '".$paiddate."', gateway = '".$paidgateway."' WHERE id = '".$invoiceid."' LIMIT 1");
                    $main->redirect("?page=invoices&view=".$invoiceid);
                }
            }

            if($invoice_info['amt_paid'] || $p2hid) {
                $invoice_info1['TRANSACTIONS'] = $navens_coupons->tpl("invoices/invoicetransactions.tpl", $invoice_info1);
            }


            $addsub[] = array("Add", "add");
            $addsub[] = array("Subtract", "subtract");

            $days[] = array("1 Day", "1");
            for($num = 2; $num < 31; $num++) {
                $days[] = array($num." Days", $num);
            }

            $invoice_info1['ADDSUB'] = $sdk->dropDown("addsub", $addsub, "add", 0);
            $invoice_info1['DAYS'] = $sdk->dropDown("days", $days, 1, 0);

            if($userdata['removed'] == 1) {
                $invoice_info1['MODIFYFUNCS'] = '
                        <tr>
                         <td align="center" colspan = "2"><font color = "#FF0055"><strong>The owner of this invoice has been dismembered.  Er... I mean the member who owned this invoice has been removed.</strong></font></td>
                        </tr>';
            }elseif($invoice_info['locked']){
                $invoice_info1['MODIFYFUNCS'] = '
                        <tr>
                         <td align="center" colspan = "2"><font color = "#FF0055"><strong>The owner of this invoice has upgraded their account and this is an invoice from an old account.</strong></font></td>
                        </tr>';
            }
            
            else {
                if(!$invoice_info['locked']){
                if(!$p2hid) {
                    $invoice_info1['PAYARRANGE'] = $navens_coupons->tpl("invoices/payarrange.tpl", $invoice_info1);
                }
                else {
                    $invoice_info1['PAYARRANGE'] = "";
                }
                $invoice_info1['MODIFYFUNCS'] = $navens_coupons->tpl("invoices/adminopsmodify.tpl", $invoice_info1);
                }
            }

            $invoice_info1['TRANSACTIONS'] .= $navens_coupons->tpl("invoices/adminops.tpl", $invoice_info1);

            if(!$warning_page) {
                echo $navens_coupons->tpl("invoices/viewinvoice.tpl", $invoice_info1);
            }


        }
        else {
            //Display the invoice list


            //Status search
            $showstatus = "all";
            if($main->postvar['submitstatus']) {
                $showstatus = $main->postvar['status'];
            }
            //End ststus search


            //Type search
            $showtype = "all";
            if($main->postvar['submittype']) {
                $showtype = $main->postvar['invtype'];
            }
            //End type search


            $users[] = array("All", "all");
            $users[] = array("Orphans", "orphans");

            $users_query = $db->query("SELECT * FROM <PRE>users ORDER BY `user` ASC");
            while($users_data = $db->fetch_array($users_query)) {
                $users[] = array($users_data['user'], $users_data['id']);
            }

            //User search
            $users_default = "all";
            if($main->postvar['submitusers']) {
                $users_default = $main->postvar['users'];

                if($users_default != "all" && $users_default != "orphans") {
                    $show_user = " WHERE uid = '".$users_default."'";
                    $show_p2h_user = " AND userid = '".$users_default."'";
                    $username = $sdk->uname($users_default);
                    $for_user = " For ".$username;
                }
            }
            //End user search

            $num_invoices = 0;
            $num_paid = 0;
            $num_unpaid = 0;
            $total_unpaid = 0;
            if($showtype == "all" || $showtype == "p2h") {
                $p2h_query = $sdk->tdata("packages", "type", "p2h", "", 1);
                while($p2h_data = $db->fetch_array($p2h_query)) {
                    $user_packs_query = $sdk->tdata("user_packs", "", "", "WHERE pid = '".$p2h_data['id']."'".$show_p2h_user, 1);
                    while($user_packs = $db->fetch_array($user_packs_query)) {

                        unset($user_show);
                        unset($orphaned);
                        $user_show = $sdk->uname($user_packs["userid"]);
                        if(!$user_show) {
                            $user_show = '<font color = "FF0055">ORPHANED</font>';
                            $orphaned = 1;
                        }

                        if(($orphaned && $users_default == "orphans") || $users_default != "orphans") {
                            $pack_info = $sdk->uidtopack($user_packs['userid']);
                            $p2h = $type->classes["p2h"];
                            $monthly = $pack_info['additional']['monthly'];
                            $monthly_with_disc = $navens_coupons->get_discount("p2hmonthly", $monthly, $user_packs['userid']);
                            $userposts = $navens_coupons->totalposts($user_packs['userid']);

                            $invoices_data_array['ID'] = "P2H-".$user_packs['id'];
                            $invoices_data_array['USERFIELD'] = '<td width="100" align="center">'.$user_show.'</td>';
                            $invoices_data_array['DUE'] = $main->convertdate("n/d/Y", mktime(date("H"), date("i"), date("s"), date("n"), date("t"), date("Y")));
                            $invoices_data_array['CREATED'] = $main->convertdate("n/d/Y", mktime(date("H"), date("i"), date("s"), date("n"), 1, date("Y")));
                            $invoices_data_array['AMOUNT'] = $sdk->s($monthly, " Post");

                            if($showstatus == "unpaid" || $showstatus == "all") {
                                if($monthly_with_disc - $userposts > 0) {
                                    $pulled = 1;
                                    $invoices_data_array["PAID"] = "<font color = '#FF7800'>Unpaid</font>";
                                    $invoices_list_data['LIST'] .= $navens_coupons->tpl("invoices/invoice-list-item.tpl", $invoices_data_array);
                                }
                            }

                            if($showstatus == "paid" || $showstatus == "all" && !$pulled) {
                                if($monthly_with_disc - $userposts <= 0) {
                                    $invoices_data_array["PAID"] = "<font color = '#779500'>Paid</font>";
                                    $invoices_list_data['LIST'] .= $navens_coupons->tpl("invoices/invoice-list-item.tpl", $invoices_data_array);
                                }
                            }

                            if($monthly_with_disc - $userposts > 0) {
                                $total_unpaid = $total_unpaid + 1;
                            }

                            $pulled = 0;
                            $num_invoices = $num_invoices + 1;
                        }
                    }
                }
            }

            if($showtype == "all" || $showtype == "paid") {

                $invoices_query = $db->query("SELECT * FROM <PRE>invoices".$show_user." ORDER BY id DESC");

                while($invoices_data = $db->fetch_array($invoices_query)) {

                    unset($user_show);
                    unset($orphaned);
                    unset($invoice_locked);
                    $user_show = $sdk->uname($invoices_data["uid"]);
                    if(!$user_show) {
                        $user_show = '<font color = "FF0055">ORPHANED</font>';
                        $orphaned = 1;
                    }
                    
                    if($invoices_data['locked']){
                        $invoice_locked = ' <font color = "FF0055">LOCKED</font>';
                    }

                    if(($orphaned && $users_default == "orphans") || $users_default != "orphans") {
                        $pack_info = $navens_upgrade->uidtopack($invoices_data["uid"], $invoices_data['pid']);

                        if(!$invoices_data['pid']){
                            $db->query("UPDATE <PRE>invoices SET pid = '".$pack_info['user_packs']['pid']."' WHERE id = '".$invoices_data['id']."'");
                            $invoices_data['pid'] = $pack_info['user_packs']['pid'];
                        }

                        if($invoices_data['pid'] != $pack_info['user_packs']['pid']){
                            $pack_info = $navens_upgrade->pidtobak($invoices_data['pid'], $invoices_data["uid"]);
                        }
                        
                        $invoices_data_array['ID'] = $invoices_data['id'];
                        $invoices_data_array['USERFIELD'] = '<td width="100" align="center">'.$user_show.'</td>';
                        $invoices_data_array['DUE'] = $main->convertdate("n/d/Y", $invoices_data['due']);
                        $invoices_data_array['CREATED'] = $main->convertdate("n/d/Y", strtotime($invoices_data['created']));
                        $total_paid_real = $navens_coupons->totalpaid($invoices_data['id']);
                        if($total_paid_real < 0){
                            $pack_info['additional']['monthly'] = $pack_info['additional']['monthly']-$total_paid_real;
                        }
                        $invoices_data_array['AMOUNT'] = $sdk->money($pack_info['additional']['monthly']);

                        if($showstatus == "unpaid" || $showstatus == "all") {
                            if($invoices_data["is_paid"] == 0) {
                                $pulled = 1;
                                $invoices_data_array["PAID"] = "<font color = '#FF7800'>Unpaid</font>".$invoice_locked;
                                $invoices_list_data['LIST'] .= $navens_coupons->tpl("invoices/invoice-list-item.tpl", $invoices_data_array);
                            }
                        }

                        if($showstatus == "paid" || $showstatus == "all" && !$pulled) {
                            if($invoices_data["is_paid"] == 1) {
                                $invoices_data_array["PAID"] = "<font color = '#779500'>Paid</font>".$invoice_locked;
                                $invoices_list_data['LIST'] .= $navens_coupons->tpl("invoices/invoice-list-item.tpl", $invoices_data_array);
                            }
                        }

                        if($invoices_data["is_paid"] == 0) {
                            $total_unpaid = $total_unpaid + 1;
                        }

                        $pulled = 0;
                        $num_invoices = $num_invoices + 1;
                    }
                }
            }

            if(!$invoices_list_data['LIST']) {
                $invoices_list_data['LIST'] = "<tr>\n<td colspan = '7' align = 'center'>There are not currently any invoices to show.</td>\n</tr>";
            }

            $statusopts[] = array("All", "all");
            $statusopts[] = array("Unpaid", "unpaid");
            $statusopts[] = array("Paid", "paid");

            $typeopts[] = array("All", "all");
            $typeopts[] = array("P2H", "p2h");
            $typeopts[] = array("Paid", "paid");

            $invoices_list_data['USERS'] = $sdk->dropDown("users", $users, $users_default, 0);
            $invoices_list_data['TYPEOPTS'] = $sdk->dropDown("invtype", $typeopts, $showtype, 0);
            $invoices_list_data['STATUSOPTS'] = $sdk->dropDown("status", $statusopts, $showstatus, 0);

            $invoices_list_data['FORUSER'] = $for_user;
            $invoices_list_data['NUM'] = $num_invoices;
            $invoices_list_data['NUMPAID'] = $num_invoices - $total_unpaid;
            $invoices_list_data['NUMUNPAID'] = $total_unpaid;

            echo $navens_coupons->tpl("invoices/admin-page.tpl", $invoices_list_data);
        }
    }
}

?>
