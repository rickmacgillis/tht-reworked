<?php

//////////////////////////////
// The Hosting Tool - Client Invoices (For Coupons)
// Client Area - Invoice Management
// Redesigned by Na'ven Enigma
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1) {
    die();
}

class page {
    public function content() { # Displays the page
        global $style, $db, $main, $invoice, $server;
        global $navens_coupons, $type, $sdk;
        global $navens_upgrade;

        if(is_numeric($main->getvar['view'])) {
            //Show the invoice

                        $invoice_info_top = $sdk->tdata("invoices", "", "", "WHERE `uid` = '{$_SESSION['cuser']}' AND id = '".$main->getvar['view']."'");
            $pack_data_top = $sdk->uidtopack();
            if(!$invoice_info_top['pid']){
                $db->query("UPDATE <PRE>invoices SET pid = '".$pack_data_top['user_packs']['pid']."' WHERE id = '".$invoice_info_top['id']."'");
                $invoice_info_top['pid'] = $pack_data_top['user_packs']['pid'];
            }

            if($_POST['submitaddcoupon']) {

                if(!$main->postvar['addcoupon']) {
                    $main->errors("Please enter a coupon code or click the checkout button.");
                }
                else {

                    $coupcode = $main->postvar['addcoupon'];
                    $user = $sdk->uname($_SESSION['cuser']);
                    $pack_data = $sdk->uidtopack();
                    if($invoice_info_top['pid'] != $pack_data['user_packs']['pid']){
                        $pack_data = $navens_upgrade->pidtobak($invoice_info_top['pid']);
                    }
                    $packid = $pack_data['packages']['id'];
                    $multi_coupons = $navens_coupons->coupconfig("multicoupons");

                    $coupon_info = $navens_coupons->coupon_data($coupcode);
                    $coupid = $coupon_info['id'];

                    $use_coupon = $navens_coupons->use_coupon($coupid, $packid, $main->getvar['view']);
                    if(!$use_coupon) {
                        if(!$multi_coupons) {
                            $main->errors("Coupon code entered was invalid or you're already using a coupon.");
                        }
                        else {
                            $main->errors("Coupon code entered was invalid.");
                        }
                    }
                    else {
                        $main->redirect("?page=invoices&view=".$main->getvar['view']);
                    }
                }
            }

            $invoice_info = $sdk->tdata("invoices", "", "", "WHERE `uid` = '{$_SESSION['cuser']}' AND id = '".$main->getvar['view']."'");

            if(empty($invoice_info)) {
                $main->redirect("?page=invoices");
                exit;
            }

            $userdata = $sdk->tdata("users", "id", $_SESSION['cuser']);
            $upackage = $sdk->tdata("user_packs", "userid", $_SESSION['cuser']);
            $package = $sdk->tdata("packages", "id", $invoice_info['pid']);
            $monthly = $type->additional($package['id']);
            $subtotal = $monthly['monthly'];


            if(is_numeric($main->getvar['remove'])) {
                $remove_id = $main->getvar['remove'];
                $remove = $navens_coupons->remove_coupon($remove_id, $package['id'], $invoice_info['id'], $_SESSION['cuser']);
                $main->redirect("?page=invoices&view=".$invoice_info['id']);
                exit;
            }

            $total_paid_real = $navens_coupons->totalpaid($main->getvar['view']);
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
                $main->redirect("?page=invoices&view=".$invoice_info['id']);
            }

            if($acct_balance > 0 && $invoice_info['is_paid'] == '1') {
                $db->query("UPDATE <PRE>invoices SET is_paid = '0' WHERE id = '".$invoice_info['id']."' LIMIT 1");
                $main->redirect("?page=invoices&view=".$invoice_info['id']);
            }

            if($_POST['checkout']) {
                if(!is_numeric($main->postvar['paythis'])) {
                    $main->errors("Please enter the amount you wish to pay today.");
                }
                else {
                    if($main->postvar['paythis'] > $acct_balance || $acct_balance <= 0) {
                        $main->errors("You can't pay more than you owe.  =)");
                    }
                    else {
                        $db->query("UPDATE <PRE>invoices SET pay_now = '".$main->postvar['paythis']."' WHERE id = '".$main->getvar['view']."'");
                        $main->redirect("?page=invoices&iid=".$main->getvar['view']);
                        exit;
                    }
                }
            }

            $created = strtotime($invoice_info['created']);
            $thirty_days = 30 * 24 * 60 * 60;
            $orig_due = $created + $thirty_days;

            if($main->convertdate("n/d/Y", $invoice_info['due']) != ($main->convertdate("n/d/Y", $created + $thirty_days))) {
                $due_text = " (Originally ".$main->convertdate("n/d/Y", $orig_due).")";
            }

            $due = $main->convertdate("n/d/Y", $invoice_info['due']);

            $invoice_info1['ID'] = $invoice_info['id'];
            $invoice_info1['DUE'] = $due.$due_text;
            $invoice_info1['PACKDUE'] = $due;
            $invoice_info1['CREATED'] = $main->convertdate("n/d/Y", $created);
            $invoice_info1['BASEAMOUNT'] = $sdk->money($subtotal);
            $invoice_info1['TOTALAMOUNT'] = $sdk->money($acct_balance);
            $invoice_info1['BALANCE'] = $sdk->money($acct_balance);
            $invoice_info1['PAYBALANCE'] = $acct_balance;
            $invoice_info1['CURRSYMBOL'] = $sdk->money($acct_balance, "", 1);
            $invoice_info1['TOTALPAID'] = $sdk->money($total_paid);
            $invoice_info1['COUPONTOTAL'] = $sdk->money($subtotal - $navens_coupons->get_discount("paid", $subtotal));

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

            $invoice_info1['STATUS'] = ($invoice_info["is_paid"] == 1 ? "<font color = '#779500'>Paid</font>" : "<font color = '#FF7800'>Unpaid</font>");
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

            $coupons_query = $db->query("SELECT * FROM <PRE>mod_navens_coupons_used WHERE user = '".$userdata['id']."' AND disabled = '0' ORDER BY `id` ASC");
            while($coupons_used_fetch = $db->fetch_array($coupons_query)) {
                $valid_coupon = $navens_coupons->check_expire($coupons_used_fetch['coupcode'], $userdata['id']);
                if($valid_coupon) {
                    $coupons['COUPONAMOUNT'] = $sdk->money($coupons_used_fetch['paiddisc']);
                    $coupons['COUPCODE'] = $coupons_used_fetch['coupcode'];
                    $coupons['REMOVE'] = $invoice_info['is_paid'] == 1 ? "" : ('(<a href = "?page=invoices&view='.$invoice_info['id'].'&remove='.$coupons_used_fetch['id'].'">Remove</a>)');
                    $invoice_info1['COUPONSLIST'] .= $navens_coupons->tpl("invoices/couponslist.tpl", $coupons);
                }
            }

            if(!$invoice_info1['COUPONSLIST']) {
                $invoice_info1['COUPONSLIST'] = "<tr><td></td><td align = 'center'>None</td></tr>";
            }
            } //Added by Na'ven's Upgrade


            $amt_paid = $invoice_info['amt_paid'];
            $txn = $invoice_info['txn'];
            $datepaid = $invoice_info['datepaid'];
            $gateway = $invoice_info['gateway'];

            $amt_paid = explode(",", $amt_paid);
            $txn = explode(",", $txn);
            $datepaid = explode(",", $datepaid);
            $gateway = explode(",", $gateway);
            $invoice_info1['TRANSACTIONS'] = "";

            for($i = 0; $i < count($amt_paid); $i++) {

                $paid_this = $paid_this + $amt_paid[$i];
                $txnlist['PAIDAMOUNT'] = $sdk->money($amt_paid[$i]);
                $txnlist['TXN'] = $txn[$i];
                $txnlist['PAIDDATE'] = $main->convertdate("n/d/Y", $datepaid[$i]);
                $txnlist['GATEWAY'] = $gateway[$i];
                $invoice_info1['TXNS'] .= $navens_coupons->tpl("invoices/txnlist.tpl", $txnlist);
            }

            if($invoice_info["is_paid"]) {
                if(!$invoice_info['amt_paid']){
                    $invoice_info1['TXNS'] = "<tr><td colspan = '4' align = 'center'><b>--- None ---</b></td></tr>";
                }
                $invoice_info1['TRANSACTIONS'] = $navens_coupons->tpl("invoices/invoicetransactions.tpl", $invoice_info1);
            }
            else {
                if($invoice_info['amt_paid']) {
                    $invoice_info1['TRANSACTIONS'] = $navens_coupons->tpl("invoices/invoicetransactions.tpl", $invoice_info1);
                }
                $invoice_info1['TRANSACTIONS'] .= $navens_coupons->tpl("/invoices/invoicepay.tpl", $invoice_info1);
            }
            echo $navens_coupons->tpl("invoices/viewinvoice.tpl", $invoice_info1);

        }
        else {
            //Show the list of invoices
            $pack_info = $sdk->uidtopack();
            
//REMOVED CODE FROM MODULE: navens_upgrade_system [0]  DO NOT REMOVE


            $query = $db->query("SELECT * FROM `<PRE>invoices` WHERE `uid` = '{$_SESSION['cuser']}' ORDER BY `id` DESC");

            $array2['LIST'] = "";
            while($array = $db->fetch_array($query)) {
                if(!$array['pid']){
                    $db->query("UPDATE <PRE>invoices SET pid = '".$pack_info['user_packs']['pid']."' WHERE id = '".$array['id']."'");
                    $array['pid'] = $pack_info['user_packs']['pid'];
                }

                if($array['pid'] != $pack_info['user_packs']['pid']){
                    $pack_info = $navens_upgrade->pidtobak($array['pid']);
                }
                $monthly = $pack_info['additional']['monthly'];
                $array1['ID'] = $array['id'];
                $array1['USERFIELD'] = "";
                $array1['DUE'] = $main->convertdate("n/d/Y", $array['due']);
                $array1['CREATED'] = $main->convertdate("n/d/Y", strtotime($array['created']));
                $array1["PAID"] = ($array["is_paid"] == 1 ? "<font color = '#779500'>Paid</font>" : "<font color = '#FF7800'>Unpaid</font>");
                $total_paid_real = $navens_coupons->totalpaid($array['id']);
                if($total_paid_real < 0){
                    $monthly = $monthly-$total_paid_real;
                }
                $array1['AMOUNT'] = $sdk->money($monthly);
                $array2['LIST'] .= $navens_coupons->tpl("invoices/invoice-list-item.tpl", $array1);
            }
            $array2['NUM'] = mysql_num_rows($query);

            if($array2['NUM'] == 0) {
                $array2['LIST'] = "<tr>\n<td colspan = '6' align = 'center'>You currently do not have any invoices.</td>\n</tr>";
            }

            echo $navens_coupons->tpl("invoices/client-page.tpl", $array2);
        }
    }
}

?>
