<?php

//////////////////////////////
// The Hosting Tool - Na'ven's Upgrade System
// Upgrade Class
// By Na'ven Enigma
// Released under the GNU-GPLv3
//////////////////////////////

//Check if called by script
if(THT != 1) {
    die();
}

//Create the class
class navens_upgrade {

    public function tpl($tpl_file, $array = 0) {
        global $sdk;
        return $sdk->tpl("navens_upgrade_system_1_0", $tpl_file, $array);
    }
    
    
    
    
    public function pidtobak($pid, $userid = "") {
        global $db, $type, $sdk;

        if(!$userid) {
            $userid = $_SESSION['cuser'];
        }

        $userpackage = $sdk->tdata("user_packs_bak", "", "", "WHERE userid = '".$userid."' AND pid = '".$pid."'");

        $packageinfo = $sdk->tdata("packages", "id", $userpackage['pid']);
        $additional = $type->additional($userpackage['pid']);
        $uadditional = $sdk->userAdditional($userpackage['id'], "_bak");

        $package_data['user_packs'] = $userpackage;
        $package_data['packages'] = $packageinfo;
        $package_data['additional'] = $additional;
        $package_data['uadditional'] = $uadditional;

        return $package_data;
    }
    
    
    public function uidtopack($userid = "", $pid = "") {
        global $db, $type, $sdk;

        if(!$userid) {
            $userid = $_SESSION['cuser'];
        }

        $userpackage = $sdk->tdata("user_packs", "userid", $userid);
        if(empty($userpackage)) {
            $package_data['removed'] = 1;
            if(!$pid){
                $backup = "_bak";
                $userpackage = $sdk->tdata("user_packs_bak", "userid", $userid);
            }else{
                $package_data = array_merge($package_data, $this->pidtobak($pid, $userid));
                return $package_data;
            }
        }
        else {
            $package_data['removed'] = 0;
        }

        $packageinfo = $sdk->tdata("packages", "id", $userpackage['pid']);
        $additional = $type->additional($userpackage['pid']);
        $uadditional = $sdk->userAdditional($userpackage['id'], $backup);

        $package_data['user_packs'] = $userpackage;
        $package_data['packages'] = $packageinfo;
        $package_data['additional'] = $additional;
        $package_data['uadditional'] = $uadditional;

        return $package_data;
    }
    
    
    //Codes:
    //
    //Responses that aren't numerical
    //
    //now        - They can upgrade/downgrade now now.
    //next       - They can upgrade/downgrade next month.
    //inelegible - They are inelegible for the p2h plan.
    //owe        - They owe money, so they can't change the plan.
    //check      - We need to have the user enter their forum credentials so we can see if they can change to a p2h plan.
    //check next - Same as check and next put together.
    //check now  - Same as check and now put together.
    //
    //If it isn't a code listed above, then its the amount to prorate.
    public function prorate($pid, $coupcode = "", $userid = "", $admin = ""){
    global $db;
    global $sdk;
    global $type;
    global $navens_coupons;

    if(!$userid){
        $userid = $_SESSION['cuser'];
    }
    
    $upackinfo = $sdk->uidtopack($userid);
    $umonthly  = $upackinfo['additional']['monthly'];
    $usignup   = $upackinfo['additional']['signup'];
    $utype     = $upackinfo['packages']['type'];
    
    $pack_data = $sdk->tdata("packages", "id", $pid);
    $packinfo  = $type->additional($pid);
    $monthly   = $packinfo['monthly'];
    $signup    = $packinfo['signup'];
    $pack_type = $pack_data['type'];

    //For P2H
    $umonthly_discount = max(0, $navens_coupons->get_discount("p2hmonthly", $umonthly, $userid));

    if($coupcode){
        $user = $sdk->uname($userid);
        $response = $navens_coupons->validate_coupon($coupcode, "orders", $user, $pid);
        if($response){
            $coup_data = $navens_coupons->coupon_data($coupcode);
            $signup = max(0, $signup-$coup_data['p2hinitdisc']);
        }
    }

    switch($utype){

        case "free":

            switch($pack_type){

                case "free":
                    //No need to prorate it.
                    return "now";
                    
                break;

                case "paid":
                    //No need to prorate it.
                    return "now";

                break;

                case "p2h":
                
                    if($upackinfo['uadditional']['fuser']){
                    $total_posts = $navens_coupons->totalposts($userid);

                    if($total_posts < $signup){
                        $inelegible = 1;
                    }

                    }else{
                        $check = 1;
                    }

                    if((!$inelegible && !$check) || ($admin && !$check)){
                        //They haven't paid yet, so they can get the account changed now.
                        return "now";

                    }elseif($inelegible && !$admin){
                        //They don't have the required number of posts made to be allowed to switch to this plan.
                        return "inelegible";

                    }elseif($check){
                        //We need to check if they have enough posts to be allowed to change the plan.  This means the user has to enter their credentials for the forum since we don't have it yet.
                        return "check";

                    }
                    
                break;

            }
        break;

        case "paid":

        $invoice_query = $db->query("SELECT * FROM <PRE>invoices WHERE uid = '".$userid."' AND locked = '0'");
        while($invoice_data = $db->fetch_array($invoice_query)){
        $paid = $navens_coupons->totalpaid($invoice_data['id']);

        if($paid >= $invoice_data['amount'] && $paid > 0){
        $amt_owed = $monthly - $paid;
        $prorate = 1;
        }
        
        if($paid < $invoice_data['amount']){
        //They change to a different paid plan only.  The new plan will be added to the newest invoice anyway.
        $owes_money = 1;
        }
        
        if($not_first_pull){
            $cant_upgrade = $owes_money; //This avoids having someone upgrade and not pay that bill, then upgrade again and not pay that bit, etc.  The system only charges
        }                                //the upgrade price and keeps the transactions on the last invoice.  This means that if you have a plan for $2, $3, $4, and $5, then
                                         //the user can simply upgrade until they're on the highest plan and and pay only a couple bucks more than the plan they started on,
        $not_first_pull = 1;             //all the while recieving free months of service.  Then they downgrade to the lowest plan the next month and it starts over again.
                                         //So we need to know if the user paid last month's bill along with the other bills before it so they can upgrade only if they did.
        }

            switch($pack_type){

                case "free":
                    if($owes_money && !$admin){
                    //Total paid is less than $0, so that means they've been charged for something.  We can't let then leave the paid plan system so the invoice isn't lost.
                    return "owe";
                    }else{
                    //Next month they become a free member
                    $upgrade_today = $this->upgrade_today($utype, $userid);
                    if($upgrade_today){
                        return "now";
                    }else{
                        return "next";
                    }
                    }
                break;

                case "paid":
                    if(($monthly > $umonthly && $owes_money) || (!$owes_money && !$cant_upgrade)){
                        if($monthly < $umonthly){
                            //Next month they will be downgraded.
                            $upgrade_today = $this->upgrade_today($utype, $userid);
                            if($upgrade_today){
                                return "now";
                            }else{
                                return "next";
                            }
                        }else{
                            //Amount they owe for their account.
                            if($cant_upgrade && !$admin){ //See comment above.
                                return "owe";
                            }else{
                                if($admin){
                                    return "now";
                                }else{
                                    return $amt_owed;
                                }
                            }
                        }
                    }else{
                        //Total paid is less than $0, so that means they've been charged for something.  We can't let then leave the paid plan system so the invoice isn't lost.
                        //They need to choose a higher paid package so they don't ignore the bill and use a higher plan until its payment time, then downgrade, pay that bill,
                        //then upgrade after cron sees its paid, and repeat that.  If they have a fully paid bill, they can downgrade.  If they want to upgrade, they can.
                        return "owe";
                    }

                break;

                case "p2h":
                

                    if($upackinfo['uadditional']['fuser']){
                    $total_posts = $navens_coupons->totalposts($userid);

                    if($total_posts < $signup){
                        $inelegible = 1;
                    }

                    }else{
                        $check = 1;
                    }
                
                    if($owes_money && !$admin){
                        //Total paid is less than $0, so that means they've been charged for something.  We can't let then leave the paid plan system so the invoice isn't lost.
                        return "owe";
                        
                    }elseif($inelegible && !$admin){
                        //They don't have the required number of posts made to be allowed to switch to this plan.
                        return "inelegible";
                        
                    }elseif($check){
                        //We need to check if they have enough posts to be allowed to change the plan.  This means the user has to enter their credentials for the forum since we don't have it yet.
                        $upgrade_today = $this->upgrade_today($utype, $userid);
                        if($upgrade_today){
                            return "check now";
                        }else{
                            return "check next";
                        }
                        
                    }elseif($prorate){
                        //Next month they become a p2h member
                        $upgrade_today = $this->upgrade_today($utype, $userid);
                        if($upgrade_today){
                            return "now";
                        }else{
                            return "next";
                        }
                    }
                break;

            }

        break;

        case "p2h":
        $total_posts = $navens_coupons->totalposts($userid);
        
        if($total_posts < $signup){
            $inelegible = 1;
        }

        if($umonthly_discount > $total_posts){
            $oweposts = 1;      //Loophole prevention.  ;)  I doubt anyone would mind that I let them upgrade to paid packages if they don't have enough posts.  Besides,
        }                       //it has its own loophole prevention anyway.

            switch($pack_type){

                case "free":
                    if($oweposts && !$admin){
                        return "oweposts";
                    }else{
                        return "now";
                    }
                    
                break;

                case "paid":
                    //No need to prorate it.
                    return "now";

                break;

                case "p2h":
                    if($inelegible && !$admin){
                        //They don't have the required number of posts made to be allowed to switch to this plan.
                        return "inelegible";
                        
                    }else{
                        if($oweposts && !$admin){
                            return "oweposts";
                        }else{
                            //They qualify, so they can upgrade.
                            return "now";
                        }
                    }
                    
                break;

            }

        break;
    
    }
    
    }
    
    
    public function upgrade_today($current_pack_type, $userid){
    global $sdk;
        if($current_pack_type == "p2h" && date("j") == "1"){
            return true;
        }elseif($current_pack_type == "paid"){
            $invoice_data = $sdk->tdata("invoices", "uid", $userid, "", 0, "ORDER BY `id` DESC LIMIT 1");
            $created = strtotime($invoice_data['created']);
            $upgrade_time = $created+(29*24*60*60); //29 Days after their invoice was created is when we will upgrade them.  This will avoid a new invoice being
                                                    //created or created for the wrong plan.  This is only called if the paid user has paid the bill or will be
                                                    //upgrading to a higher paid package.
            $upgrade_day = date("d", $upgrade_time);
            if($upgrade_day == date("d") || !$invoice_data){
                return true;
            }
        }
        return false;
    }
    
    
    //This will return the number of posts the discount is good for when multi coupons are enabled.  This pulls multicoupons.
    public function get_init_discount($userid = ""){
    global $db;
    global $sdk;
    global $navens_coupons;
    if(!$userid){
        $userid = $_SESSION['cuser'];
    }
        $coupons_query = $navens_coupons->user_coupon_data($userid, 1);
        while($coupons_used_fetch = $db->fetch_array($coupons_query)) {
            $valid_coupon = $navens_coupons->check_expire($coupons_used_fetch['coupcode'], $userid);
            if($valid_coupon) {
                $coupon_data = $sdk->tdata("mod_navens_coupons", "coupcode", $coupons_used_fetch['coupcode']);
                $total_init = $total_init+$coupon_data['p2hinitdisc'];
            }
        }

        return $total_init;
    }
    
    
    public function cron(){
    global $db;
        $upgrades_query = $db->query("SELECT * FROM <PRE>mod_navens_upgrade WHERE flags = '1' OR flags = '4'");
        while($upgrades_data = $db->fetch_array($upgrades_query)){
            $this->upgrade($upgrades_data['id'], "Update", 1);
        }
    
    }
    
    
    public function upgrade($upgradeid, $mode, $no_errors_out = 0){
    global $db;
    global $sdk;
    global $main;
    global $type;
    global $email;
    global $server;
    global $invoice;
    global $navens_coupons;
    
    //$mode
    //
    //Init   - When the upgrade info is added, we call this function.
    //Update - When cron or an admin does something with this function, we check to see if various tasks are ready to be performed.
    
    //Flag meaning:
    //
    //IMMEDIATE UPGRADE FLAGS
    //
    //0 - Upgrade is immediate.
    //5 - If admin approves the upgrade, then the upgrade will be immediate.
    //
    //6 - If admin approves the upgrade, then the new account will be created on the new server and the
    //    admin will know that they are moving to a new server so they can manually close the old account
    //    when they're ready.
    //
    //7 - The new account on the new server will be immediately created and the admin will be notified that
    //    the user is switching servers.
    //
    //NEXT MONTH UPGRADE FLAGS
    //
    //1 - Cron will upgrade them next month.
    //2 - If admin approves this, then it will be set to 1 for cron to upgrade them next month.
    //3 - The admin will be notified that the user wishes to be upgraded and if they approve it,
    //    then an account on the new server will be created so they can migrate to the new server.
    //    the admin can opt to switch the account over before next month if they both agree and the
    //    switch will be made in the admin area manually.
    //
    //4 - Cron will create a new account on the new server next month and inform the admin that the
    //    user is changing to the new server.
    //
    
    $upgrade_data = $sdk->tdata("mod_navens_upgrade", "id", $upgradeid);
    $userid   = $upgrade_data['uid'];
    $newpack  = $upgrade_data['newpack'];
    $flags    = $upgrade_data['flags'];
    $created  = $upgrade_data['created'];
    $coupcode = $upgrade_data['coupcode'];
    
    $user_data = $sdk->uidtopack($userid);
    $current_pack_type = $user_data['packages']['type'];
    $current_pack_name = $user_data['packages']['name'];
    $current_pack_id = $user_data['packages']['id'];
    
    $user_info = $sdk->tdata("users", "id", $userid);
    $user_email = $user_info['email'];
    $username   = $user_info['user'];
    
    $new_plan_data = $sdk->tdata("packages", "id", $newpack);
    $newpack_name = $new_plan_data['name'];
    $new_plan_additional = $type->additional($newpack);

    $new_server_data = $sdk->tdata("servers", "id", $new_plan_data['server']);
    $new_server_name = $new_server_data['name'];
    
    $old_server_data = $sdk->tdata("servers", "id", $user_data['packages']['server']);
    $old_server_name = $old_server_data['name'];

    switch($flags){

        case "0":
            $upgrade = 1;
        break;

        case "1":
            $upgrade_today = $this->upgrade_today($current_pack_type, $userid);
            if($upgrade_today){
                $upgrade = 1;
            }
        break;

        case "2":
            if($mode = "Init"){
                $admin_approval = 1;
            }else{
                //If its already the day for the upgrade, then we can do the upgrade now.
                $upgrade_today = $this->upgrade_today($current_pack_type, $userid);
                if($upgrade_today){
                    $upgrade = 1;
                }
                
                if(!$upgrade){
                    $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '1' WHERE id = '".$upgradeid."' LIMIT 1");
                }
            }
        break;

        case "3":
            if($mode = "Init"){
                $admin_approval = 1;
                $new_server = 1;
            }else{
                //If its already the day for the upgrade, then we can do the upgrade now.
                $upgrade_today = $this->upgrade_today($current_pack_type, $userid);
                if($upgrade_today){
                    $upgrade = 1;
                    $new_server = 1;
                }
                
                if(!$upgrade){
                    $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '4' WHERE id = '".$upgradeid."' LIMIT 1");
                }
            }
        break;

        case "4":
            //If its already the day for the upgrade, then we can do the upgrade now.
            $upgrade_today = $this->upgrade_today($current_pack_type, $userid);
            if($upgrade_today){
                $upgrade = 1;
                $new_server = 1;
                $admin_inform = 1;
            }
        break;

        case "5":
            if($mode = "Init"){
                $admin_approval = 1;
                $immediate = 1;
            }else{
                $upgrade = 1;
            }
        break;

        case "6":
            if($mode = "Init"){
                $admin_approval = 1;
                $new_server = 1;
                $immediate = 1;
            }else{
                $upgrade = 1;
                $new_server = 1;
            }
        break;

        case "7":
            $upgrade = 1;
            $new_server = 1;
            $admin_inform = 1;
        break;
    
    }
    
    $adminmsg_array['USER']      = $username;
    $adminmsg_array['NEWPLAN']   = $newpack_name;
    $adminmsg_array['OLDPLAN']   = $current_pack_name;
    $adminmsg_array['NEWSERVER'] = $new_server_name;
    $adminmsg_array['OLDSERVER'] = $old_server_name;
    if($immediate){
        $adminmsg_array['NEXTMONTH_IMMEDIATELY'] = "immediately";
    }else{
        $adminmsg_array['NEXTMONTH_IMMEDIATELY'] = "next billing cycle";
    }
    $url = $db->config("url");
    if(ADMINDIR){
        $admin_dir = ADMINDIR;
    }else{
        $admin_dir = "admin";
    }
    $adminmsg_array['APPROVE_LINK'] = $url.$admin_dir."/?page=users&sub=upgrade";

    if($upgrade){
        if($new_plan_data['type'] == "paid"){
            $new_invoice_text = "  A new invoice has been generated.";
            if($current_pack_type == "paid" && $user_data['additional']['monthly'] >= $new_plan_additional['monthly']){
                unset($new_invoice_text);
            }
        }
    
        $main->getvar['package'] = $newpack;
        $serverphp = $server->createServer($newpack);
        if($new_server){
            $main->getvar['username'] = $username;
            $main->getvar['email']    = $user_email;
            $main->getvar['fdom']     = $user_data['user_packs']['domain'];
            $main->getvar['password'] = $serverphp->GenPassword();
            $main->getvar['fplan']    = $type->determineBackend($newpack);
            $done = $serverphp->signup($type->determineServer($newpack), $new_plan_data['reseller']);
            if($done === true) {
                if($new_plan_data['reseller']){
                    $uemaildata = $db->emailTemplate("upgrade_welcome_newserv_resell");
                }else{
                    $uemaildata = $db->emailTemplate("upgrade_welcome_newserv");
                }
                $change_tht = 1;
                $main->errors("Your upgrade request has been completed.  An email has been sent to you detailing your upgraded account on the new server.".$new_invoice_text);
            }else{
                return false;
            }
        }else{
            if($serverphp->canupgrade){
                $done = $serverphp->upgrade($new_plan_data['server'], $type->determineBackend($newpack), $username);
                if($done === true){
                    if($new_plan_data['reseller']){
                        $uemaildata = $db->emailTemplate("upgrade_welcome_resell");
                    }else{
                        $uemaildata = $db->emailTemplate("upgrade_welcome");
                    }
                    $change_tht = 1;
                    $main->errors("Your upgrade request has been completed.  An email has been sent to you detailing your upgraded account.".$new_invoice_text);
                }else{
                    return false;
                }
            }else{
                $emaildata = $db->emailTemplate("admin_manual_upgrade");
                $main->errors("Your upgrade request has been added and the administrator has been emailed.");
            }
        }
        
        if($uemaildata){
            $welcomeemail_array['USER']         = $username;
            $welcomeemail_array['EMAIL']        = $user_email;
            $welcomeemail_array['PACKAGE']      = $newpack_name;
            $welcomeemail_array['SERVERIP']     = $new_server_data['ip'];
            $welcomeemail_array['LNAME']        = $user_info['lastname'];
            $welcomeemail_array['FNAME']        = $user_info['firstname'];
            $welcomeemail_array['CPPORT']       = $new_server_data['port'];
            $welcomeemail_array['PASS']         = $main->getvar['password'];
            $welcomeemail_array['RESELLERPORT'] = $new_server_data['whmport'];
            $welcomeemail_array['NAMESERVERS']  = nl2br($new_server_data['nameservers']);
            $welcomeemail_array['DOMAIN']       = $user_data['user_packs']['domain'];
            $email->send($user_email, $uemaildata['subject'], $uemaildata['content'], $welcomeemail_array);
        }
    }else{
        $main->errors("Your upgrade request has been added.");
    }
    
    //Now we need to send the admin a dozen emails.  lol  FIRE!  lol  Nah, we'll only ever send them one email at a time.  ;)
    if($admin_approval){
        if($new_server){
            $emaildata = $db->emailTemplate("adminval_upgrade_newserv");
        }else{
            $emaildata = $db->emailTemplate("adminval_upgrade");
        }
    }

    if($admin_inform){
        $emaildata = $db->emailTemplate("admin_notify_newserv");
    }

    if(!$emaildata && $change_tht){
        $emaildata = $db->emailTemplate("admin_inform_new_upgrade");
    }

    if($emaildata){
        $email->staff($emaildata['subject'], $emaildata['content'], $adminmsg_array);
    }

    if($change_tht){
        $sdk->thtlog("Upgraded from ".$current_pack_name." to ".$newpack_name, $userid, "");

        if($current_pack_type == "paid"){
            $db->query("UPDATE <PRE>invoices SET pid = '".$current_pack_id."' WHERE uid = '".$userid."' AND pid = ''");
        }

        if($new_plan_data['type'] != "p2h"){
            $db->query("DELETE FROM <PRE>mod_navens_coupons_p2h WHERE uid = '".$userid."'");
        }
        
        $db->query("DELETE FROM <PRE>user_packs_bak WHERE userid = '".$userid."' AND pid = '".$current_pack_id."'");

        $user_pack_data = $user_data['user_packs'];

        $backup_array = array("userid"     => $user_pack_data['userid'],
                              "username"   => $user_pack_data['username'],
                              "domain"     => $user_pack_data['domain'],
                              "pid"        => $user_pack_data['pid'],
                              "signup"     => $user_pack_data['signup'],
                              "status"     => $user_pack_data['status'],
                              "additional" => $user_pack_data['additional']);
                              
        $sdk->insert("user_packs_bak", $backup_array);
        $db->query("UPDATE <PRE>user_packs SET pid = '".$newpack."' WHERE userid = '".$userid."' LIMIT 1");

        if($current_pack_type == "paid"){
            $current_coupons_query = $db->query("SELECT * FROM <PRE>mod_navens_coupons_used WHERE user = '".$userid."' AND disabled = '0'");
            while($current_coupons_data = $db->fetch_array($current_coupons_query)){
                $had_coupons .= $current_coupons_data['coupcode'].",";
                $couponvals  .= $current_coupons_data['paiddisc'].",";
            }
            $had_coupons = substr($had_coupons, 0, strlen($had_coupons)-1);
            $couponvals  = substr($couponvals, 0, strlen($couponvals)-1);
            if(!$had_coupons){
                $had_coupons = "0";
                $couponvals = "0";
            }
            $db->query("UPDATE <PRE>invoices SET locked = '1', hadcoupons = '".$had_coupons."', couponvals = '".$couponvals."' WHERE uid = '".$userid."' AND hadcoupons = ''");
        }

        $multi_coupons = $navens_coupons->coupconfig("multicoupons");
        if($coupcode || $new_plan_data['type'] == "free"){
            if(!$multi_coupons || $new_plan_data['type'] == "free"){
                $time = time();
                $db->query("UPDATE <PRE>mod_navens_coupons_used SET disabled = '2', datedisabled = '".$time."' WHERE user = '".$userid."'");
            }
        }

        if($new_plan_data['type'] != "free"){

        if($new_plan_data['type'] == "paid"){
            $last_invoice = $sdk->tdata("invoices", "", "", "WHERE uid = '".$userid."' AND pid = '".$current_pack_id."' ORDER BY `id` DESC LIMIT 1");
            
            if($user_data['additional']['monthly'] < $new_plan_additional['monthly'] && $current_pack_type == "paid" && $last_invoice){

                    $last_invoice = $sdk->tdata("invoices", "", "", "WHERE uid = '".$userid."' AND pid = '".$current_pack_id."' ORDER BY `id` DESC LIMIT 1");

                    if(!$multi_coupons){
                        $current_coupon = $sdk->tdata("mod_navens_coupons_used", "", "", "WHERE user = '".$userid."' AND disabled = '0'");
                        $coupcode = $current_coupon['coupcode'];
                        $navens_coupons->remove_coupon($current_coupon['id'], $newpack, $last_invoice['id'], $userid);
                    }else{
                        $coupons_used_query = $db->query("SELECT * FROM <PRE>mod_navens_coupons_used WHERE user = '".$userid."' AND disabled = '0'");
                        while($coupons_used_data = $db->fetch_array($coupons_used_query)){
                            $use_coupons[] = $coupons_used_data['coupcode'];
                            $navens_coupons->remove_coupon($coupons_used_data['id'], $newpack, $last_invoice['id'], $userid);
                        }
                    }
                        $last_invoice = $sdk->tdata("invoices", "", "", "WHERE uid = '".$userid."' AND pid = '".$current_pack_id."' ORDER BY `id` DESC LIMIT 1");

                        $invoice_update_array = array("amount"     => $new_plan_additional['monthly'],
                                                      "due"        => $last_invoice['due']+(7*24*60*60), //We need to give them a 7 day grace period so they don't wind up having only 1 day to pay it.
                                                      "pid"        => $newpack,
                                                      "hadcoupons" => "",
                                                      "couponvals" => "",
                                                      "locked"     => "0",
                                                      "is_paid"    => "0");

                        $sdk->update("invoices", $invoice_update_array, "id", $last_invoice['id']);
            }else{
                $amount = $new_plan_additional['monthly'];
                if(!$coupcode){
                    $coupcode = $sdk->tdata("mod_navens_coupons_used", "", "", "WHERE user = '".$userid."' AND disabled = '0'");
                    $coupcode = $coupcode['coupcode'];
                }

                $coupon_data = $navens_coupons->coupon_data($coupcode);
                $coupon_data['paiddisc'] = $navens_coupons->percent_to_value("paid", "paidtype", "paiddisc", $amount);
                if($multi_coupons){
                    $amount = $navens_coupons->get_discount("paid", $amount, $userid) - $coupon_data['paiddisc'];
                }else{
                    $amount = max(0, $amount - $coupon_data['paiddisc']);
                }

                $due = time()+2592000;
                $notes = "Your current hosting package monthly invoice. Package: ".$newpack_name;
                $invoice->create($userid, $amount, $due, $notes);
                
                $last_invoice = $sdk->tdata("invoices", "", "", "WHERE uid = '".$userid."' ORDER BY `id` DESC LIMIT 1");
                $db->query("UPDATE <PRE>invoices SET pid = '".$newpack."' WHERE id = '".$last_invoice['id']."' LIMIT 1");
                $last_invoice = $sdk->tdata("invoices", "", "", "WHERE uid = '".$userid."' AND pid = '".$newpack."' ORDER BY `id` DESC LIMIT 1");

                if(!$multi_coupons){
                    $current_coupon = $sdk->tdata("mod_navens_coupons_used", "", "", "WHERE user = '".$userid."' AND disabled = '0'");
                    $navens_coupons->remove_coupon($current_coupon['id'], $newpack, $last_invoice['id'], $userid);
                }else{
                    $coupons_used_query = $db->query("SELECT * FROM <PRE>mod_navens_coupons_used WHERE user = '".$userid."' AND disabled = '0'");
                    while($coupons_used_data = $db->fetch_array($coupons_used_query)){
                        $use_coupons[] = $coupons_used_data['coupcode'];
                        $navens_coupons->remove_coupon($coupons_used_data['id'], $newpack, $last_invoice['id'], $userid);
                    }
                }
            }
        }

        $last_invoice = $sdk->tdata("invoices", "", "", "WHERE uid = '".$userid."' AND pid = '".$newpack."' ORDER BY `id` DESC LIMIT 1");
        if($coupcode){
            $coupon_data = $sdk->tdata("mod_navens_coupons", "coupcode", $coupcode);
            $navens_coupons->use_coupon($coupon_data['id'], $newpack, $last_invoice['id'], $userid, "orders");
        }

        if($multi_coupons && $use_coupons){
            for($i=0;$i<count($use_coupons);$i++){
                $coupcode = $use_coupons[$i];
                $coupon_data = $sdk->tdata("mod_navens_coupons", "coupcode", $coupcode);
                $navens_coupons->use_coupon($coupon_data['id'], $newpack, $last_invoice['id'], $userid, "orders");
            }
        }
        }
        
        //We now remove the upgrade stub.
        $db->query("DELETE FROM <PRE>mod_navens_upgrade WHERE uid = '".$userid."'");
    }

    $db->query("UPDATE <PRE>invoices SET is_paid = '1' WHERE amount = '0' || amount = '0.00'");  //This way people won't see unpaid invoices for $0.  lol

    if(!$no_errors_out){
        echo "<ERRORS>";
    }

    }
    
}

?>
