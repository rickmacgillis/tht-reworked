<?php

//////////////////////////////
// The Hosting Tool - Na'ven's Coupons
// Coupons Class
// By Na'ven Enigma
// Released under the GNU-GPLv3
//////////////////////////////

//Check if called by script
if(THT != 1) {
    die();
}

//Create the class
class navens_coupons {

    //This validates the form to add and edit coupons.
    public function validate_admin_form($add_edit) { //If we're in edit mode, we specify the ID, not the word "edit".
        global $main, $db, $type, $sdk;
        foreach($main->postvar as $key => $value) {
            if($value == "" && !$n && $key != "monthsgoodfor" && $key != "expiredate" && $key != "limitedcoupons" && $key != "unlimitedcoupons" && $key != "neverexpire" && $key != "username" && $key != "paiddisc" && $key != "p2hinitdisc" && $key != "p2hmonthlydisc" && $key != "coupid") {
                $error = "Please fill in all the required fields!";
                $n++;
            }
        }
        if(!$n) {
            $var = $main->postvar;

            $coupon_code_check = $this->coupon_data($var['coupcode']);
            if(!empty($coupon_code_check) && $add_edit == 'add') {
                $error = "The coupon code already exists in the database.<br>";
            }

            if(is_numeric($var['coupcode'])) {
                $error = "The coupon code cannot be fully numeric.<br>";
            }

            if($var['unlimitedcoupons']) {
                $var['limitedcoupons'] = "";
            }
            else {
                if(!$sdk->isint($var['limitedcoupons'])) {
                    $error = "Please enter the number of times the coupon may be used or check unlimited.<br>";
                }
                elseif($var['limitedcoupons'] <= 0) {
                    $error = "Please enter a number greater than 0 for the number of times the coupon may be used or check unlimited.<br>";
                }
            }

            if($var['goodfor'] == 'months' && (!$var['monthsgoodfor'] || !$sdk->isint($var['monthsgoodfor']))) {
                $error .= "Please specify the number of months the coupon is good for when applied.  The months must not be specified as a decimal.<br>";
            }
            else {
                if($var['goodfor'] != 'months') {
                    unset($var['monthsgoodfor']);
                }
            }

            if(!$var['expiredate'] && !$var['neverexpire']) {
                $error .= "Please enter an expiration date or check the box for never expire.<br>";
            }

            if(!$var['packages'] && !$var['allpacks']) {
                $error .= "Please choose packages for this coupon or check the box for all packages.<br>";
            }

            if($var['expiredate'] && !$var['neverexpire']) {
                $date_blowed_up = explode("/", $var['expiredate']);
                if(count($date_blowed_up) != 3 || !$sdk->isint($date_blowed_up[0]) || !$sdk->isint($date_blowed_up[1]) || !$sdk->isint($date_blowed_up[2]) || strlen($date_blowed_up[0]) != 2 || strlen($date_blowed_up[1]) != 2 || strlen($date_blowed_up[2]) != 4 || $date_blowed_up[0] > 12 || $date_blowed_up[1] > 31) {
                    $error .= "Please enter a valid expiration date in the format MM/DD/YYYY or check the box to have the coupon never expire.<br>";
                }
            }

            if($var['userselect'] == 'newuser' && !$var['username']) {
                $error .= "Please enter the new user's username who should be allowed to use this coupon.<br>";
            }

            if(!$var['paiddisc'] && !$var['p2hinitdisc'] && !$var['p2hmonthlydisc']) {
                $error .= "You must enter the paid discount amount or you must enter the P2H initial discount and/or the P2H monthly discount.<br>";
            }
            else {
                if(!$var['paiddisc']) {
                    $var['paiddisc'] = "0";
                    $var['paidtype'] = "0";
                }

                if(!$var['p2hinitdisc']) {
                    $var['p2hinitdisc'] = "0";
                    $var['p2hinittype'] = "0";
                }

                if(!$var['p2hmonthlydisc']) {
                    $var['p2hmonthlydisc'] = "0";
                    $var['p2hmonthlytype'] = "0";
                }


                if(!$sdk->isint($var['paiddisc'])) {
                    $error .= "The paid discount amount must be given as a number for paid packages.  (Ex. 1.99)<br>";
                }

                if(!$sdk->isint($var['p2hinitdisc']) || !$sdk->isint($var['p2hmonthlydisc'])) {
                    $error .= "The P2H post discounts must be given as a whole number for p2h packages.<br>";
                }
            }

            if(!$error) {
                if($var['neverexpire']) {
                    $var['expiredate'] = "99/99/9999";
                }

                if($var['userselect'] == 'newuser') {
                    $var['userselect'] = $var['username'];
                }

                if($var['allpacks']) {
                    $var['packages'] = "all";
                    $packages_query = $db->query("SELECT * FROM <PRE>packages WHERE type != 'free'");
                    while($packages_data = $db->fetch_array($packages_query)) {
                        $packtype .= $packages_data['type'].",";
                    }
                }
                else {
                    foreach($var['packages'] as $key => $val) {
                        $packs .= $val.",";
                        $packtype .= $type->determineType($val).",";
                    }
                    $var['packages'] = substr($packs, 0, -1);
                }

                if(substr_count($packtype, "paid") && $var['paiddisc'] == "0") {
                    $error .= "You selected at least one paid package.  Please enter a discount amount greater than 0 for the paid discount field.<br>";
                }

                if(substr_count($packtype, "p2h") && $var['p2hinitdisc'] == "0" && $var['p2hmonthlydisc'] == "0") {
                    $error .= "You selected at least one p2h package.  Please enter a discount amount greater than 0 for at least one of the post discount fields.<br>";
                }

                if(substr_count($packtype, "paid") && !substr_count($packtype, "p2h")) {
                    $var['p2hinitdisc'] = "0";
                    $var['p2hmonthlydisc'] = "0";
                    $var['p2hinittype'] = "0";
                    $var['p2hmonthlytype'] = "0";
                }

                if(!substr_count($packtype, "paid") && substr_count($packtype, "p2h")) {
                    $var['paiddisc'] = "0";
                    $var['paidtype'] = "0";
                }

                if(!$error) {
                    if($add_edit == "add") {
                        $db->query("INSERT INTO `<PRE>mod_navens_coupons` SET paidtype = '".$var['paidtype']."', p2hinittype = '".$var['p2hinittype']."', p2hmonthlytype = '".$var['p2hmonthlytype']."', limited = '".$var['limitedcoupons']."', coupname = '".$var['name']."', shortdesc = '".$var['shortdesc']."', coupcode = '".$var['coupcode']."', area = '".$var['area']."', goodfor = '".$var['goodfor']."', monthsgoodfor = '".$var['monthsgoodfor']."', expiredate = '".$var['expiredate']."', user = '".$var['userselect']."', packages = '".$var['packages']."', paiddisc = '".$var['paiddisc']."', p2hinitdisc = '".$var['p2hinitdisc']."', p2hmonthlydisc = '".$var['p2hmonthlydisc']."'");
                        $error = "The coupon has been added successfully!";
                    }
                    else {
                        $db->query("UPDATE `<PRE>mod_navens_coupons` SET paidtype = '".$var['paidtype']."', p2hinittype = '".$var['p2hinittype']."', p2hmonthlytype = '".$var['p2hmonthlytype']."', limited = '".$var['limitedcoupons']."', coupname = '".$var['name']."', shortdesc = '".$var['shortdesc']."', coupcode = '".$var['coupcode']."', area = '".$var['area']."', goodfor = '".$var['goodfor']."', monthsgoodfor = '".$var['monthsgoodfor']."', expiredate = '".$var['expiredate']."', user = '".$var['userselect']."', packages = '".$var['packages']."', paiddisc = '".$var['paiddisc']."', p2hinitdisc = '".$var['p2hinitdisc']."', p2hmonthlydisc = '".$var['p2hmonthlydisc']."' WHERE id = '".$add_edit."' LIMIT 1");
                        $error = "The coupon has been edited successfully!";
                    }
                }
            }
        }

        return $error;
    }


    public function admin_userdata($userid) {
        global $sdk;

        $user_info = $sdk->tdata("users", "id", $userid);
        $user_info['removed'] = 0;

        if(!$user_info['user']) {
            $user_info = $sdk->tdata("users_bak", "uid", $userid);
            $user_info['removed'] = 1;
        }

        return $user_info;
    }


    //Grabs all the data for a coupon
    public function coupon_data($coupcode = "", $coupid = "") {
        global $sdk;

        if($coupcode) {
            $coup_de_data = $sdk->tdata("mod_navens_coupons", "coupcode", $coupcode);
        }

        if($coupid) {
            $coup_de_data = $sdk->tdata("mod_navens_coupons", "id", $coupid);
        }

        return $coup_de_data;
    }


    //Grabs all the data for coupon(s) the user is using
    public function user_coupon_data($userid, $multi_coupons = 0, $coupcode = "", $coupid = "", $notdisabled = 1) {
        global $db;

        if($coupcode) {
            $coupcode_search = " AND coupcode = '".$coupcode."'";
        }

        if($coupid) {
            $coupcode_search_id = " AND id = '".$coupid."'";
        }

        if($notdisabled) {
            $not_disabled_q = " AND disabled = '0'";
        }

        $coup_de_out = $db->query("SELECT * FROM <PRE>mod_navens_coupons_used WHERE user = '".$userid."'".$coupcode_search.$coupcode_search_id.$not_disabled_q." ORDER BY `timeapplied` DESC");

        if(!$multi_coupons) {
            $coup_de_out = $db->fetch_array($coup_de_out);
        }

        return $coup_de_out; //lol I love my naming conventions.  =)
    }


    public function use_coupon($coupid, $package, $invoiceid = "", $userid = "", $area = "invoices") {
        global $db, $sdk;

        if(!$userid) {
            $userid = $_SESSION['cuser'];
        }

        $user = $sdk->uname($userid);
        $multi_coupons = $this->coupconfig("multicoupons");

        $coupon_info = $this->coupon_data("", $coupid);
        $coupon_vailidate = $this->validate_coupon($coupon_info['coupcode'], $area, $user, $package);

        if($coupon_vailidate) {
            $user_has_coupons = $sdk->tdata("mod_navens_coupons_used", "", "", "WHERE user = '".$userid."' AND disabled = '0'");

            if($multi_coupons || empty($user_has_coupons)) {
                $user_coupon = $sdk->tdata("mod_navens_coupons_used", "", "", "WHERE user = '".$userid."' AND coupcode = '".$coupon_info['coupcode']."'");
                $pack_info = $sdk->uidtopack($userid);
                $monthly = $pack_info['additional']['monthly'];

                if($user_coupon['disabled'] == '2') {
                    //This prevents the user from removing the coupon before its expired and then adding it back in so they can reset its expiration date.
                    $datedisabled = $user_coupon['datedisabled'];
                    $timeapplied = $user_coupon['timeapplied'];
                    $difference = $datedisabled - $timeapplied;
                    $time = time();
                    $timeapplied = $time - $difference;

                    $array = array("disabled" => "0", "timeapplied" => $timeapplied);


                    $sdk->update("mod_navens_coupons_used", $array, "", "", "WHERE user = '".$userid."' AND coupcode = '".$coupon_info['coupcode']."'");
                    $sdk->thtlog("Coupon renabled (".$coupon_info['coupcode'].")", $userid);
                }
                else {

                    $coupon_info['paiddisc'] = $this->percent_to_value("paid", $coupon_info['paidtype'], $coupon_info['paiddisc'], $monthly);
                    $coupon_info['p2hmonthlydisc'] = $this->percent_to_value("p2h", $coupon_info['p2hmonthlytype'], $coupon_info['p2hmonthlydisc'], $monthly);
                    $array = array(
                        "user" => $userid,
                        "coupcode" => $coupon_info['coupcode'],
                        "timeapplied" => time(),
                        "packages" => $package,
                        "goodfor" => $coupon_info['goodfor'],
                        "monthsgoodfor" => $coupon_info['monthsgoodfor'],
                        "paiddisc" => $coupon_info['paiddisc'],
                        "p2hmonthlydisc" => $coupon_info['p2hmonthlydisc']);


                    $sdk->insert("mod_navens_coupons_used", $array);
                    $sdk->thtlog("Coupon used (".$coupon_info['coupcode'].")", $userid);
                }

                if($invoiceid) {
                    $total = $this->get_discount("paid", $monthly, $userid);
                    if($total) {
                        $paid = "0";
                    }
                    else {
                        $paid = "1";
                    }
                    $sdk->update("invoices", array("amount" => $total, "is_paid" => $paid), "id", $invoiceid);
                }
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }


    public function remove_p2h_coupon($coupid, $user = "") {
        global $db, $sdk;

        if(!$user) {
            $user = $_SESSION['cuser'];
        }

        $time = time();
        $db->query("UPDATE <PRE>mod_navens_coupons_used SET disabled = '2', datedisabled = '".$time."' WHERE id = '".$coupid."' AND user = '".$user."' LIMIT 1");

        $used_coupon_info = $this->user_coupon_data($user, 0, "", $coupid, 0);
        if($used_coupon_info['disabled'] == "2") {
            $sdk->thtlog("Coupon removed (".$used_coupon_info['coupcode'].")", $user);
        }

    }


    public function remove_coupon($coupid, $package, $invoiceid, $userid = "") {
        global $db, $sdk;

        if(!$userid) {
            $userid = $_SESSION['cuser'];
        }

        $used_coupon_info = $this->user_coupon_data($userid, 0, "", $coupid);
        $invoice_info = $sdk->tdata("invoices", "id", $invoiceid);
        $total = $invoice_info['amount'];
        $pack_info = $sdk->uidtopack($userid);
        $monthly = $pack_info['additional']['monthly'];
        $time = time();

        //Disabled 2 means the coupon didn't expire, but it was removed.  If the user re-ads it, we need to put them back on the coupon for the duration
        //left on the coupon.  This prevents users from removing a coupon before it expired and re-adding it for unlimited uses of that discount.
        $db->query("UPDATE <PRE>mod_navens_coupons_used SET disabled = '2', datedisabled = '".$time."' WHERE id = '".$coupid."' AND user = '".$userid."' LIMIT 1");

        $invoice_total = $this->get_discount("paid", $monthly, $userid);

        if($invoice_total > $monthly) {
            $invoice_total = $monthly;
        }
        else {
            $invoice_total = $invoice_total;
        }

        if($invoice_total > 0) {
            $setpaid = ", is_paid = '0'";
        }

        $db->query("UPDATE <PRE>invoices SET amount = '".$invoice_total."'".$setpaid." WHERE id = '".$invoiceid."' AND uid = '".$userid."' LIMIT 1");

        //As this simply removes the coupon if the user ID and other info match the records, we need to make sure that somone disn't
        //just type in the url of someone else's coupon.  This way if it failed because they didn't realy remove the coupon, we
        //don't log it.
        $used_coupon_info = $this->user_coupon_data($userid, 0, "", $coupid, 0);
        if($used_coupon_info['disabled'] == "2") {
            $sdk->thtlog("Coupon removed (".$used_coupon_info['coupcode'].")", $userid);
        }
        return true;
    }


    public function get_used_coupon_discount($coupid, $userid, $package) {
        global $db, $type;

        $coupon_info = $this->user_coupon_data($userid, 0, "", $coupid);
        $paiddisc = $coupon_info['paiddisc'];
        $used_coupcode = $coupon_info['coupcode'];
        $orig_coupon_info = $this->coupon_data($used_coupcode);
        $paidtype = $orig_coupon_info['paidtype'];
        $monthly = $type->additional($package);
        $monthly = $monthly['monthly'];
        $paiddisc = $this->percent_to_value("paid", $paidtype, $paiddisc, $monthly);
        return $paiddisc;
    }


    public function get_discount($discount_type, $original_price, $userid = "") {
        global $db, $sdk;

        if(empty($userid)) {
            $userid = $_SESSION['cuser'];
        }

        if(!is_numeric($userid)) {
            $userid = $sdk->userid($userid);
        }

        $multi_coupons = $this->coupconfig("multicoupons");
        $coupons_query = $this->user_coupon_data($userid, $multi_coupons);

        switch($discount_type) {

            case "paid":
                if($multi_coupons) {

                    $newprice = $original_price;
                    while($coupons_used_fetch = $db->fetch_array($coupons_query)) {
                        $valid_coupon = $this->check_expire($coupons_used_fetch['coupcode'], $userid);
                        if($valid_coupon) {
                            $newprice = $newprice - $coupons_used_fetch['paiddisc'];
                        }
                    }

                    return max(0, $newprice);
                }
                else {
                    $valid_coupon = $this->check_expire($coupons_query['coupcode'], $userid);
                    if($valid_coupon) {
                        return max(0, $original_price - $coupons_query['paiddisc']);
                    }
                    else {
                        return $original_price;
                    }
                }
                break;

            case "p2hmonthly":
                if($multi_coupons) {

                    $newprice = $original_price;
                    while($coupons_used_fetch = $db->fetch_array($coupons_query)) {
                        $valid_coupon = $this->check_expire($coupons_used_fetch['coupcode'], $userid);
                        if($valid_coupon) {
                            $newprice = $newprice - $coupons_used_fetch['p2hmonthlydisc'];
                        }
                    }

                    return max(0, $newprice);
                }
                else {
                    $valid_coupon = $this->check_expire($coupons_query['coupcode'], $userid);
                    if($valid_coupon) {
                        return max(0, $original_price - $coupons_query['p2hmonthlydisc']);
                    }
                    else {
                        return $original_price;
                    }
                }
                break;

        }

    }


    public function check_expire($coupcode, $userid = '') {
        global $db;

        if(!$userid) {
            $userid = $_SESSION['cuser'];
        }

        $coupon_info = $this->user_coupon_data($userid, 0, $coupcode);
        $coupon_applied = $coupon_info['timeapplied'];
        $coupon_goodfor = $coupon_info['goodfor'];
        $coupon_monthsgoodfor = $coupon_info['monthsgoodfor'];
        $coupon_id = $coupon_info['id'];
        $today = time();

        switch($coupon_goodfor) {

            case "life":
                return true;
                break;

            case "current":

                $coupon_expires = $coupon_applied + 2592000;
                if($coupon_expires > $today) {
                    return true;
                }
                else {
                    $db->query("UPDATE <PRE>mod_navens_coupons_used SET disabled = '1' WHERE id = '".$coupon_id."' LIMIT 1");
                    return false;
                }

                break;

            case "months":

                $coupon_expires = $coupon_applied + ($coupon_monthsgoodfor * 2592000);
                if($coupon_expires > $today) {
                    return true;
                }
                else {
                    $db->query("UPDATE <PRE>mod_navens_coupons_used SET disabled = '1' WHERE id = '".$coupon_id."' LIMIT 1");
                    return false;
                }

                break;

        }
    }


    public function coupconfig($configname) {
        global $sdk;
        $coup_de_data = $sdk->tdata("mod_navens_coupons_config", "configname", $configname);
        return $coup_de_data['configvalue'];
    }


    public function percent_to_value($typename, $typeval, $discount, $original) {

        if($typename == "paid") {
            $round = 2;
        }
        else {
            $round = 0;
        }

        if($typeval == '1') {
            if($discount < 100) {
                $discount = ($discount / 100) * $original;
                $discount = round($discount, $round);
            }
            else {
                $discount = $original;
            }
        }

        return $discount;

    }


    public function totalpaid($invoiceid) {
        global $sdk;
        $invoice_info = $sdk->tdata("invoices", "id", $invoiceid);
        $amt_paid = $invoice_info['amt_paid'];

        $amt_paid = explode(",", $amt_paid);

        for($i = 0; $i < count($amt_paid); $i++) {
            $paid_this = $paid_this + $amt_paid[$i];
        }

        return $paid_this;

    }


    public function totalposts($userid) {
        global $sdk, $type;
        $p2h_info = $sdk->tdata("mod_navens_coupons_p2h", "uid", $userid);
        $amt_paid = $p2h_info['amt_paid'];
        $txn = $p2h_info['txn'];
        $datepaid = $p2h_info['datepaid'];
        $gateway = $p2h_info['gateway'];

        $package_info = $sdk->uidtopack($userid);
        if(!class_exists("p2h")){
            $p2h = $type->createtype("p2h");
        }else{
            $p2h = new p2h();
        }
        $total_posts =  $p2h->userposts($package_info['packages']['id'], $package_info['user_packs']['id']);

        $amt_paid = explode(",", $amt_paid);
        $txn      = explode(",", $txn);
        $datepaid = explode(",", $datepaid);


        for($i = 0; $i < count($amt_paid); $i++) {
                if($txn[$i] == $package_info['uadditional']['fuser']) {
                    $found_posts = 1;
                    $amt_paid[$i] = $total_posts;
                }
                
            $posted = $posted + $amt_paid[$i];
        }
        
        if(!$found_posts){
            $posted = $posted+$total_posts;
        }

        return $posted;

    }


    public function validate_coupon($coupcode, $areaused, $uname, $package) {
        global $type, $db, $sdk;

        $userid = $sdk->userid($uname);
        $coupon_info = $this->coupon_data($coupcode);

        if(empty($coupon_info)) {
            return false;
        }

        if($coupon_info['expiredate'] != "99/99/9999") {
            $today = time();
            $coupon_expiry = explode("/", $coupon_info['expiredate']);
            $expiry_time = mktime(date("H"), date("i"), date("s"), ltrim($coupon_expiry[0]), ltrim($coupon_expiry[1]), $coupon_expiry[2]);
            if($today >= $expiry_time) {
                return false;
            }
        }

        if($coupon_info['area'] != "both" && $coupon_info['area'] != $areaused) {
            return false;
        }

        if($coupon_info['user'] != "all" && $coupon_info['user'] != $uname) {
            return false;
        }

        if($coupon_info['packages'] != "all") {
            $available_packs = explode(",", $coupon_info['packages']);
            if(!in_array($package, $available_packs)) {
                return false;
            }
        }

        if($coupon_info['limited']) {
            $coupons_used_q = $db->query("SELECT * FROM <PRE>mod_navens_coupons_used WHERE coupcode = '".$coupcode."'");
            $num_coupons_used = $db->num_rows($coupons_used_q);
            if($num_coupons_used >= $coupon_info['limited']) {
                return false;
            }
        }

        $coupon_used = $this->user_coupon_data($userid, 0, $coupcode);
        if(!empty($coupon_used) && $coupon_used['disabled'] != '2') {
            return false;
        }

        //All checks passed.
        //
        //Brok
        //    en
        //        Eng
        //           lish
        //                   lol

        $package_type = $type->determineType($package); //Free packages are checked for on ajax.php, so we skip them. lol
        $package_info = $type->additional($package);
        $package_monthly = $package_info['monthly'];
        $package_p2hinit = $package_info['signup'];

        $paidtype = $coupon_info['paidtype'];
        $p2hinittype = $coupon_info['p2hinittype'];
        $p2hmonthlytype = $coupon_info['p2hmonthlytype'];

        $coupon_info['paiddisc'] = $this->percent_to_value("paid", $paidtype, $coupon_info['paiddisc'], $package_monthly);
        $coupon_info['p2hinitdisc'] = $this->percent_to_value("p2h", $p2hinittype, $coupon_info['p2hinitdisc'], $package_p2hinit);
        $coupon_info['p2hmonthlydisc'] = $this->percent_to_value("p2h", $p2hmonthlytype, $coupon_info['p2hmonthlydisc'], $package_monthly);

        if($package_type == "paid") {
            if($coupon_info['paiddisc'] >= $package_monthly) {
                $coupon_text = "Free ";
            }
            else {
                $they_pay = $package_monthly - $coupon_info['paiddisc'];

                $currency = $sdk->money($they_pay);

                $coupon_text = "Only pay ".$currency." ";
                $pay_per_month = "/month";
            }
        }
        else {
            $init_required = $package_p2hinit - $coupon_info['p2hinitdisc'];
            $monthly_required = $package_monthly - $coupon_info['p2hmonthlydisc'];
            if($init_required > 1) {
                $s = "s";
            }
            if($monthly_required > 1) {
                $s2 = "s";
            }

            if($coupon_info['p2hinitdisc'] >= $package_p2hinit) {
                $coupon_p2hdisc = "0 Posts";
            }
            else {
                $coupon_p2hdisc = $init_required." Post".$s." required";
            }

            if($coupon_info['p2hmonthlydisc'] >= $package_monthly) {
                $coupon_p2hmonth = "0 Posts";
            }
            else {
                $coupon_p2hmonth = $monthly_required." Post".$s2." required";
            }

            if($coupon_info['p2hinitdisc'] > 0 && $coupon_info['p2hmonthlydisc'] > 0) {
                $coupon_text = $coupon_p2hdisc." to sign up and ".$coupon_p2hmonth." for the month";
            }
            else {
                if($coupon_info['p2hinitdisc'] > 0) {
                    $coupon_text = $coupon_p2hdisc." to sign up";
                    $no_goodfor = "1"; //YOU!  lol  Good for nothing?  lol
                }
                else {
                    $coupon_text = $coupon_p2hmonth." for the month";
                }
            }
        }

        if($coupon_info['goodfor'] == "life") {
            $coupon_text .= $pay_per_month." for the lifetime of the account.";
        }

        if($coupon_info['goodfor'] == "current") {
            $coupon_text .= " for the current month.";
        }

        if($coupon_info['goodfor'] == "months") {
            if($coupon_info['monthsgoodfor'] > 1) {
                $s3 = "s";
            }
            if($no_goodfor != '1') {
                $coupon_text .= $pay_per_month." for the next ".$coupon_info['monthsgoodfor']." month".$s3.".";
            }
            else {
                $coupon_text .= "."; //<- It's a period.  lol
            }
        }

        $coupon_text = "<font color = '#779500'>Good for: ".$coupon_text."</font>";

        return $coupon_text;
    }


    public function tpl($tpl_file, $array = 0) {
        global $sdk;
        return $sdk->tpl("navens_coupons_1_0", $tpl_file, $array);
    }


}

?>
