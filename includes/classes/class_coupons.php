<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Coupons Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPLv3
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();
    
}

class coupons{

    //This validates the form to add and edit coupons.
	//If we're in edit mode, we specify the ID, not the word "edit".
    public function validate_admin_form($add_edit){
        global $dbh, $postvar, $getvar, $instance;
        
        $no_check_fields = array("monthsgoodfor", "expiredate", "limitedcoupons", "unlimitedcoupons", "neverexpire", "username", "paiddisc", "p2hinitdisc", "p2hmonthlydisc", "coupid");
        check::empty_fields($no_check_fields);
        if(!main::errors()){

            $postvar = $postvar;
            
            $coupon_code_check = self::coupon_data($postvar['coupcode']);
            if(!empty($coupon_code_check) && $add_edit == 'add'){

                $error = "The coupon code already exists in the database.<br>";
                
            }

            if(is_numeric($postvar['coupcode'])){

                $error = "The coupon code cannot be fully numeric.<br>";
                
            }

            if($postvar['unlimitedcoupons']){

                $postvar['limitedcoupons'] = "";
                
            }else{

                if(!main::isint($postvar['limitedcoupons'])){

                    $error = "Please enter the number of times the coupon may be used or check unlimited.<br>";
                    
                }elseif($postvar['limitedcoupons'] <= 0){

                    $error = "Please enter a number greater than 0 for the number of times the coupon may be used or check unlimited.<br>";
                    
                }

            }

            if($postvar['goodfor'] == 'months' && (!$postvar['monthsgoodfor'] || !main::isint($postvar['monthsgoodfor']))){

                $error .= "Please specify the number of months the coupon is good for when applied.  The months must not be specified as a decimal.<br>";
                
            }else{

                if($postvar['goodfor'] != 'months'){

                    unset($postvar['monthsgoodfor']);
                    
                }

            }

            if(!$postvar['expiredate'] && !$postvar['neverexpire']){

                $error .= "Please enter an expiration date or check the box for never expire.<br>";
                
            }

            if(!$postvar['packages'] && !$postvar['allpacks']){

                $error .= "Please choose packages for this coupon or check the box for all packages.<br>";
                
            }

            if($postvar['expiredate'] && !$postvar['neverexpire']){

                $date_blowed_up = explode("/", $postvar['expiredate']);
                if(count($date_blowed_up) != 3 || !main::isint($date_blowed_up[0]) || !main::isint($date_blowed_up[1]) || !main::isint($date_blowed_up[2]) || strlen($date_blowed_up[0]) != 2 || strlen($date_blowed_up[1]) != 2 || strlen($date_blowed_up[2]) != 4 || $date_blowed_up[0] > 12 || $date_blowed_up[1] > 31){

                    $error .= "Please enter a valid expiration date in the format MM/DD/YYYY or check the box to have the coupon never expire.<br>";
                    
                }

            }

            if($postvar['userselect'] == 'newuser' && !$postvar['username']){

                $error .= "Please enter the new user's username who should be allowed to use this coupon.<br>";
                
            }

            if(!$postvar['paiddisc'] && !$postvar['p2hinitdisc'] && !$postvar['p2hmonthlydisc']){

                $error .= "You must enter the paid discount amount or you must enter the P2H initial discount and/or the P2H monthly discount.<br>";
                
            }else{

                if(!$postvar['paiddisc']){

                    $postvar['paiddisc'] = "0";
                    $postvar['paidtype'] = "0";
                    
                }

                if(!$postvar['p2hinitdisc']){

                    $postvar['p2hinitdisc'] = "0";
                    $postvar['p2hinittype'] = "0";
                    
                }

                if(!$postvar['p2hmonthlydisc']){

                    $postvar['p2hmonthlydisc'] = "0";
                    $postvar['p2hmonthlytype'] = "0";
                    
                }

                $postvar['paiddisc'] = str_replace(array(" ", ","), array("", "."), $postvar['paiddisc']);
                if(!is_numeric($postvar['paiddisc'])){

                    $error .= "The paid discount amount must be given as a number for paid packages.  (Ex. 1.99)<br>";
                    
                }

                if(!main::isint($postvar['p2hinitdisc']) || !main::isint($postvar['p2hmonthlydisc'])){

                    $error .= "The P2H post discounts must be given as a whole number for p2h packages.<br>";
                    
                }

            }

            if(!$error){

                if($postvar['neverexpire']){

                    $postvar['expiredate'] = "99/99/9999";
                    
                }

                if($postvar['userselect'] == 'newuser'){

                    $postvar['userselect'] = $postvar['username'];
                    
                }

                if($postvar['allpacks']){

                    $postvar['packages'] = "all";
                    $packages_query      = $dbh->select("packages", array("type", "!=", "free"), 0, 0, 1);
                    while($packages_data = $dbh->fetch_array($packages_query)){

                        $packtype .= $packages_data['type'].",";
                        
                    }

                }else{

                    foreach($postvar['packages'] as $key => $val){

                        $packs .= $val.",";
                        $packtype .= type::packagetype($val).",";
                        
                    }

                    $postvar['packages'] = substr($packs, 0, -1);
                    
                }

                if(substr_count($packtype, "paid") && $postvar['paiddisc'] == "0"){

                    $error .= "You selected at least one paid package.  Please enter a discount amount greater than 0 for the paid discount field.<br>";
                    
                }

                if(substr_count($packtype, "p2h") && $postvar['p2hinitdisc'] == "0" && $postvar['p2hmonthlydisc'] == "0"){

                    $error .= "You selected at least one p2h package.  Please enter a discount amount greater than 0 for at least one of the post discount fields.<br>";
                    
                }

                if(substr_count($packtype, "paid") && !substr_count($packtype, "p2h")){

                    $postvar['p2hinitdisc']    = "0";
                    $postvar['p2hmonthlydisc'] = "0";
                    $postvar['p2hinittype']    = "0";
                    $postvar['p2hmonthlytype'] = "0";
                    
                }

                if(!substr_count($packtype, "paid") && substr_count($packtype, "p2h")){

                    $postvar['paiddisc'] = "0";
                    $postvar['paidtype'] = "0";
                    
                }

                if(!$error){

                    if($add_edit == "add"){

                        $coupons_insert = array(
                            "paidtype"       => $postvar['paidtype'],
                            "p2hinittype"    => $postvar['p2hinittype'],
                            "p2hmonthlytype" => $postvar['p2hmonthlytype'],
                            "limited"        => $postvar['limitedcoupons'],
                            "coupname"       => $postvar['name'],
                            "shortdesc"      => $postvar['shortdesc'],
                            "coupcode"       => $postvar['coupcode'],
                            "area"           => $postvar['area'],
                            "goodfor"        => $postvar['goodfor'],
                            "monthsgoodfor"  => $postvar['monthsgoodfor'],
                            "expiredate"     => $postvar['expiredate'],
                            "user"           => $postvar['userselect'],
                            "packages"       => $postvar['packages'],
                            "paiddisc"       => $postvar['paiddisc'],
                            "p2hinitdisc"    => $postvar['p2hinitdisc'],
                            "p2hmonthlydisc" => $postvar['p2hmonthlydisc']
                        );
                        
                        $dbh->insert("coupons", $coupons_insert);
                        $error = "The coupon has been added successfully!";
                        
                    }else{

                        $coupons_update = array(
                            "paidtype"       => $postvar['paidtype'],
                            "p2hinittype"    => $postvar['p2hinittype'],
                            "p2hmonthlytype" => $postvar['p2hmonthlytype'],
                            "limited"        => $postvar['limitedcoupons'],
                            "coupname"       => $postvar['name'],
                            "shortdesc"      => $postvar['shortdesc'],
                            "coupcode"       => $postvar['coupcode'],
                            "area"           => $postvar['area'],
                            "goodfor"        => $postvar['goodfor'],
                            "monthsgoodfor"  => $postvar['monthsgoodfor'],
                            "expiredate"     => $postvar['expiredate'],
                            "user"           => $postvar['userselect'],
                            "packages"       => $postvar['packages'],
                            "paiddisc"       => $postvar['paiddisc'],
                            "p2hinitdisc"    => $postvar['p2hinitdisc'],
                            "p2hmonthlydisc" => $postvar['p2hmonthlydisc']
                        );
                        
                        $dbh->update("coupons", $coupons_update, array("id", "=", $add_edit), "1");
                        $error = "The coupon has been edited successfully!";
                        
                    }

                }

            }

        }

        return $error;
        
    }

    public function admin_userdata($userid){
        global $dbh, $postvar, $getvar, $instance;
        
        $user_info            = $dbh->select("users", array("id", "=", $userid));
        $user_info['removed'] = 0;
        
        if(!$user_info['user']){

            $user_info            = $dbh->select("users_bak", array("uid", "=", $userid));
            $user_info['removed'] = 1;
            
        }

        return $user_info;
        
    }

    //Grabs all the data for a coupon
    public function coupon_data($coupcode = "", $coupid = ""){
        global $dbh, $postvar, $getvar, $instance;
        
        if($coupcode){

            $coup_de_data = $dbh->select("coupons", array("coupcode", "=", $coupcode));
            
        }

        if($coupid){

            $coup_de_data = $dbh->select("coupons", array("id", "=", $coupid));
            
        }

        return $coup_de_data;
        
    }

    //Grabs all the data for coupon(s) the user is using
    public function user_coupon_data($userid, $multi_coupons = 0, $coupcode = "", $coupid = "", $notdisabled = 1){
        global $dbh, $postvar, $getvar, $instance;
        
        if($coupcode){

            $where[] = array("coupcode", "=", $coupcode, "AND");
            
        }

        if($coupid){

            $where[] = array("id", "=", $coupid, "AND");
            
        }

        if($notdisabled){

            $where[] = array("disabled", "=", "0", "AND");
            
        }

        $where[]     = array("user", "=", $userid);
        $coup_de_out = $dbh->select("coupons_used", $where, array("timeapplied", "DESC"), 0, 1);
        
        if(!$multi_coupons){

            $coup_de_out = $dbh->fetch_array($coup_de_out);
            
        }

        return $coup_de_out; //lol I love my naming conventions.  =)
        
    }

    public function use_coupon($coupid, $package, $invoiceid = "", $userid = "", $area = "invoices"){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$userid){

            $userid = $_SESSION['cuser'];
            
        }

        $user          = main::uname($userid);
        $multi_coupons = $dbh->config("multicoupons");
        
        $coupon_info      = self::coupon_data("", $coupid);
        $coupon_vailidate = self::validate_coupon($coupon_info['coupcode'], $area, $user, $package);
        
        if($coupon_vailidate){

            unset($where);
            $where[]          = array("user", "=", $userid, "AND");
            $where[]          = array("disabled", "=", 0);
            $user_has_coupons = $dbh->select("coupons_used", $where);
            
            if($multi_coupons || empty($user_has_coupons)){

                unset($where);
                $where[]     = array("user", "=", $userid, "AND");
                $where[]     = array("coupcode", "=", $$coupon_info['coupcode']);
                $user_coupon = $dbh->select("coupons_used", $where);
                $pack_info   = main::uidtopack($userid);
                $monthly     = $pack_info['additional']['monthly'];
                
                if($user_coupon['disabled'] == '2'){

                    //This prevents the user from removing the coupon before its expired and then adding it back in so they can reset its expiration date.
                    $datedisabled = $user_coupon['datedisabled'];
                    $timeapplied  = $user_coupon['timeapplied'];
                    $difference   = $datedisabled - $timeapplied;
                    $time         = time();
                    $timeapplied  = $time - $difference;
                    
                    $update = array(
                        "disabled"    => "0",
                        "timeapplied" => $timeapplied
                    );
                    
                    $where[] = array("user", "=", $userid, "AND");
                    $where[] = array("coupcode", "=", $coupon_info['coupcode']);
                    $dbh->update("coupons_used", $update, $where);
                    main::thtlog("Coupon Re-Enabled", "Coupon renabled (".$coupon_info['coupcode'].")", $userid);
                    
                }else{

                    $coupon_info['paiddisc']       = self::percent_to_value("paid", $coupon_info['paidtype'], $coupon_info['paiddisc'], $monthly);
                    $coupon_info['p2hmonthlydisc'] = self::percent_to_value("p2h", $coupon_info['p2hmonthlytype'], $coupon_info['p2hmonthlydisc'], $monthly);
                    $coupons_used_insert = array(
                        "user"           => $userid,
                        "coupcode"       => $coupon_info['coupcode'],
                        "timeapplied"    => time(),
                        "packages"       => $package,
                        "goodfor"        => $coupon_info['goodfor'],
                        "monthsgoodfor"  => $coupon_info['monthsgoodfor'],
                        "paiddisc"       => $coupon_info['paiddisc'],
                        "p2hmonthlydisc" => $coupon_info['p2hmonthlydisc']
                    );
                    
                    $dbh->insert("coupons_used", $coupons_used_insert);
                    main::thtlog("Coupon Used", "Coupon used (".$coupon_info['coupcode'].")", $userid);
                    
                }

                if($invoiceid){

                    $total = self::get_discount("paid", $monthly, $userid);
                    if($total){

                        $paid = "0";
                        
                    }else{

                        $paid = "1";
                        
                    }

                    $update  = array(
                        "amount"  => $total,
                        "is_paid" => $paid
                    );
                    $where[] = array("id", "=", $invoiceid);
                    $dbh->update("invoices", $update, $where);
                    
                }

                return true;
                
            }else{

                return false;
                
            }

        }else{

            return false;
            
        }

    }

    public function remove_p2h_coupon($coupid, $user = ""){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$user){

            $user = $_SESSION['cuser'];
            
        }

        $coupons_used_update = array(
            "disabled"     => "2",
            "datedisabled" => time()
        );
        
        $where[] = array("id", "=", $coupid, "AND");
        $where[] = array("user", "=", $user);
        $dbh->update("coupons_used", $coupons_used_update, $where, "1");
        
        $used_coupon_info = self::user_coupon_data($user, 0, "", $coupid, 0);
        if($used_coupon_info['disabled'] == "2"){

            main::thtlog("Coupon Removed", "Coupon removed (".$used_coupon_info['coupcode'].")", $user);
            
        }

    }

    public function remove_coupon($coupid, $package, $invoiceid, $userid = ""){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$userid){

            $userid = $_SESSION['cuser'];
            
        }

        $used_coupon_info = self::user_coupon_data($userid, 0, "", $coupid);
        $invoice_info     = $dbh->select("invoices", array("id", "=", $invoiceid));
        $total            = $invoice_info['amount'];
        $pack_info        = main::uidtopack($userid);
        $monthly          = $pack_info['additional']['monthly'];
        
        //Disabled 2 means the coupon didn't expire, but it was removed.  If the user re-ads it, we need to put them back on the coupon for the duration
        //left on the coupon.  This prevents users from removing a coupon before it expired and re-adding it for unlimited uses of that discount.
        
        $coupons_used_update = array(
            "disabled"     => "2",
            "datedisabled" => time()
        );
        
        unset($where);
        $where[] = array("id", "=", $coupid, "AND");
        $where[] = array("user", "=", $userid);
        $dbh->update("coupons_used", $coupons_used_update, $where, "1");
        
        $invoice_total = self::get_discount("paid", $monthly, $userid);
        
        if($invoice_total > $monthly){

            $invoice_total = $monthly;
            
        }else{

            $invoice_total = $invoice_total;
            
        }

        if($invoice_total > 0){

            $invoices_update = array(
                "is_paid" => "0",
                "amount"  => $invoice_total
            );
            
        }else{

            $invoices_update = array(
                "amount" => $invoice_total
            );
            
        }

        unset($where);
        $where[] = array("id", "=", $invoiceid, "AND");
        $where[] = array("uid", "=", $userid);
        $dbh->update("invoices", $invoices_update, $where, "1");
        
        //As this simply removes the coupon if the user ID and other info match the records, we need to make sure that somone disn't
        //just type in the url of someone else's coupon.  This way if it failed because they didn't realy remove the coupon, we
        //don't log it.
        $used_coupon_info = self::user_coupon_data($userid, 0, "", $coupid, 0);
        if($used_coupon_info['disabled'] == "2"){

            main::thtlog("Coupon Removed", "Coupon removed (".$used_coupon_info['coupcode'].")", $userid);
            
        }

        return true;
        
    }

    public function get_used_coupon_discount($coupid, $userid, $package){
        global $dbh, $postvar, $getvar, $instance;
        
        $coupon_info      = self::user_coupon_data($userid, 0, "", $coupid);
        $paiddisc         = $coupon_info['paiddisc'];
        $used_coupcode    = $coupon_info['coupcode'];
        $orig_coupon_info = self::coupon_data($used_coupcode);
        $paidtype         = $orig_coupon_info['paidtype'];
        $monthly          = type::additional($package);
        $monthly          = $monthly['monthly'];
        $paiddisc         = self::percent_to_value("paid", $paidtype, $paiddisc, $monthly);
        return $paiddisc;
        
    }

    public function get_discount($discount_type, $original_price, $userid = ""){
        global $dbh, $postvar, $getvar, $instance;
        
        if(empty($userid)){

            $userid = $_SESSION['cuser'];
            
        }

        if(!is_numeric($userid)){

            $userid = main::userid($userid);
            
        }

        $multi_coupons = $dbh->config("multicoupons");
        $coupons_query = self::user_coupon_data($userid, $multi_coupons);
        
        switch($discount_type){

            case "paid":
                if($multi_coupons){

                    $newprice = $original_price;
                    while($coupons_used_fetch = $dbh->fetch_array($coupons_query)){

                        $valid_coupon = self::check_expire($coupons_used_fetch['coupcode'], $userid);
                        if($valid_coupon){

                            $newprice = $newprice - $coupons_used_fetch['paiddisc'];
                            
                        }

                    }

                    return max(0, $newprice);
                    
                }else{

                    $valid_coupon = self::check_expire($coupons_query['coupcode'], $userid);
                    if($valid_coupon){

                        return max(0, $original_price - $coupons_query['paiddisc']);
                        
                    }else{

                        return $original_price;
                        
                    }

                }

                break;
            
            case "p2hmonthly": 
                if($multi_coupons){

                    $newprice = $original_price;
                    while($coupons_used_fetch = $dbh->fetch_array($coupons_query)){

                        $valid_coupon = self::check_expire($coupons_used_fetch['coupcode'], $userid);
                        if($valid_coupon){

                            $newprice = $newprice - $coupons_used_fetch['p2hmonthlydisc'];
                            
                        }

                    }

                    return max(0, $newprice);
                    
                }else{

                    $valid_coupon = self::check_expire($coupons_query['coupcode'], $userid);
                    if($valid_coupon){

                        return max(0, $original_price - $coupons_query['p2hmonthlydisc']);
                        
                    }else{

                        return $original_price;
                        
                    }

                }

                break;
                
        }

    }

    public function check_expire($coupcode, $userid = ''){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$userid){

            $userid = $_SESSION['cuser'];
            
        }

        $coupon_info          = self::user_coupon_data($userid, 0, $coupcode);
        $coupon_applied       = $coupon_info['timeapplied'];
        $coupon_goodfor       = $coupon_info['goodfor'];
        $coupon_monthsgoodfor = $coupon_info['monthsgoodfor'];
        $coupon_id            = $coupon_info['id'];
        $today                = time();
        
        switch($coupon_goodfor){

            case "life":
                return true;
                break;
            
            case "current":
                
                $coupon_expires = $coupon_applied + 2592000;
                if($coupon_expires > $today){

                    return true;
                    
                }else{

                    $dbh->update("coupons_used", array("disabled" => "1"), array("id", "=", $coupon_id), "1");
                    return false;
                    
                }

                break;
            
            case "months":
                
                $coupon_expires = $coupon_applied + ($coupon_monthsgoodfor * 2592000);
                if($coupon_expires > $today){

                    return true;
                    
                }else{

                    $dbh->update("coupons_used", array("disabled" => "1"), array("id", "=", $coupon_id), "1");
                    return false;
                    
                }

                break;
                
        }

    }

    public function percent_to_value($typename, $typeval, $discount, $original){

        if($typename == "paid"){

            $round = 2;
            
        }else{

            $round = 0;
            
        }

        if($typeval == '1'){

            if($discount < 100){

                $discount = ($discount / 100) * $original;
                $discount = round($discount, $round);
                
            }else{

                $discount = $original;
                
            }

        }

        return $discount;
        
    }

    public function totalpaid($invoiceid){
        global $dbh, $postvar, $getvar, $instance;
		
        $invoice_info = $dbh->select("invoices", array("id", "=", $invoiceid));
        $amt_paid     = $invoice_info['amt_paid'];
        
        $amt_paid = explode(",", $amt_paid);
        
        for($i = 0; $i < count($amt_paid); $i++){

            $paid_this = $paid_this + $amt_paid[$i];
            
        }

        return $paid_this;
        
    }

    public function totalposts($userid, $packid=0){
        global $dbh, $postvar, $getvar, $instance;
		
        $p2h_info = $dbh->select("coupons_p2h", array("uid", "=", $userid));
        $amt_paid = $p2h_info['amt_paid'];
        $txn      = $p2h_info['txn'];
        $datepaid = $p2h_info['datepaid'];
        $gateway  = $p2h_info['gateway'];

        $package_info = main::uidtopack($userid);
        $p2h = $instance->packtypes["p2h"];
		
		if(!$packid){
		
			$packid = $package_info['packages']['id'];
		
		}

        $total_posts = $p2h->userposts($packid, $package_info['user_data']['id']);
        
        $amt_paid = explode(",", $amt_paid);
        $txn      = explode(",", $txn);
        $datepaid = explode(",", $datepaid);
        
        for($i = 0; $i < count($amt_paid); $i++){

            if($txn[$i] == $package_info['uadditional']['fuser']){

                $found_posts  = 1;
                $amt_paid[$i] = $total_posts;
                
            }

            $posted = $posted + $amt_paid[$i];
            
        }

        if(!$found_posts){

            $posted = $posted + $total_posts;
            
        }

        return $posted;
        
    }

    public function validate_coupon($coupcode, $areaused, $uname, $package){
        global $dbh, $postvar, $getvar, $instance;
        
        $userid      = main::userid($uname);
        $coupon_info = self::coupon_data($coupcode);
        
        if(empty($coupon_info)){

            return false;
            
        }

        if($coupon_info['expiredate'] != "99/99/9999"){

            $today         = time();
            $coupon_expiry = explode("/", $coupon_info['expiredate']);
            $expiry_time   = mktime(date("H"), date("i"), date("s"), ltrim($coupon_expiry[0]), ltrim($coupon_expiry[1]), $coupon_expiry[2]);
            if($today >= $expiry_time){

                return false;
                
            }

        }

        if($coupon_info['area'] != "both" && $coupon_info['area'] != $areaused){

            return false;
            
        }

        if($coupon_info['user'] != "all" && $coupon_info['user'] != $uname){

            return false;
            
        }

        if($coupon_info['packages'] != "all"){

            $available_packs = explode(",", $coupon_info['packages']);
            if(!in_array($package, $available_packs)){

                return false;
                
            }

        }

        if($coupon_info['limited']){

            $coupons_used_query = $dbh->select("coupons_used", array("coupcode", "=", $coupcode), 0, 0, 1);
            $coupons_used_rows  = $dbh->num_rows($coupons_used_query);
            if($coupons_used_rows >= $coupon_info['limited']){

                return false;
                
            }

        }

        $coupon_used = self::user_coupon_data($userid, 0, $coupcode);
        if(!empty($coupon_used) && $coupon_used['disabled'] != '2'){

            return false;
            
        }

        //All checks passed.
        //
        //Brok
        //    en
        //        Eng
        //           lish
        //                   lol
        
        $package_type    = type::packagetype($package);
        $package_info    = type::additional($package);
        $package_monthly = $package_info['monthly'];
        $package_p2hinit = $package_info['signup'];
        
        $paidtype       = $coupon_info['paidtype'];
        $p2hinittype    = $coupon_info['p2hinittype'];
        $p2hmonthlytype = $coupon_info['p2hmonthlytype'];
        
        $coupon_info['paiddisc']       = self::percent_to_value("paid", $paidtype, $coupon_info['paiddisc'], $package_monthly);
        $coupon_info['p2hinitdisc']    = self::percent_to_value("p2h", $p2hinittype, $coupon_info['p2hinitdisc'], $package_p2hinit);
        $coupon_info['p2hmonthlydisc'] = self::percent_to_value("p2h", $p2hmonthlytype, $coupon_info['p2hmonthlydisc'], $package_monthly);
        
        if($package_type == "paid"){

            if($coupon_info['paiddisc'] >= $package_monthly){

                $coupon_text = "Free ";
                
            }else{

                $they_pay = $package_monthly - $coupon_info['paiddisc'];
                
                $currency = main::money($they_pay);
                
                $coupon_text   = "Only pay ".$currency." ";
                $pay_per_month = "/month";
                
            }

        }else{

            $init_required    = $package_p2hinit - $coupon_info['p2hinitdisc'];
            $monthly_required = $package_monthly - $coupon_info['p2hmonthlydisc'];
            if($init_required > 1){

                $s = "s";
                
            }

            if($monthly_required > 1){

                $s2 = "s";
                
            }

            if($coupon_info['p2hinitdisc'] >= $package_p2hinit){

                $coupon_p2hdisc = "0 Posts";
                
            }else{

                $coupon_p2hdisc = $init_required." Post".$s." required";
                
            }

            if($coupon_info['p2hmonthlydisc'] >= $package_monthly){

                $coupon_p2hmonth = "0 Posts";
                
            }else{

                $coupon_p2hmonth = $monthly_required." Post".$s2." required";
                
            }

            if($coupon_info['p2hinitdisc'] > 0 && $coupon_info['p2hmonthlydisc'] > 0){

                $coupon_text = $coupon_p2hdisc." to sign up and ".$coupon_p2hmonth." for the month";
                
            }else{

                if($coupon_info['p2hinitdisc'] > 0){

                    $coupon_text = $coupon_p2hdisc." to sign up";
                    $no_goodfor  = "1"; //YOU!  lol  Good for nothing?  lol
                    
                }else{

                    $coupon_text = $coupon_p2hmonth." for the month";
                    
                }

            }

        }

        if($coupon_info['goodfor'] == "life"){

            $coupon_text .= $pay_per_month." for the lifetime of the account.";
            
        }

        if($coupon_info['goodfor'] == "current"){

            $coupon_text .= " for the current month.";
            
        }

        if($coupon_info['goodfor'] == "months"){

            if($coupon_info['monthsgoodfor'] > 1){

                $s3 = "s";
                
            }

            if($no_goodfor != '1'){

                $coupon_text .= $pay_per_month." for the next ".$coupon_info['monthsgoodfor']." month".$s3.".";
                
            }else{

                $coupon_text .= "."; //<- It's a period.  lol
                
            }

        }

        $coupon_text = "<font color = '#779500'>Good for: ".$coupon_text."</font>";
        
        return $coupon_text;
        
    }

}

?>