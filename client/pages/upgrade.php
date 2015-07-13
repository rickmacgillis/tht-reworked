<?php

//////////////////////////////
// The Hosting Tool - Client Upgrade/Downgrade
// Client Area - Invoice Management
// Redesigned by Na'ven Enigma
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1) {
    die();
}

class page {
    public function content() {
        global $db;
        global $sdk;
        global $type;
        global $main;
        global $navens_coupons;
        global $navens_upgrade;
        
        $upackinfo = $sdk->uidtopack();
        $currentpack = $upackinfo['user_packs']['pid'];
        $packsid = $main->postvar['packs'];
        
        if(!$packsid){
            $packsid = $main->getvar['package'];
        }

        if(is_numeric($packsid)){
           $where = "id = '".$packsid."'";
        }else{
           $where = "is_hidden = '0'";
        }

        $pack_query = $db->query("SELECT * FROM `<PRE>packages` WHERE is_disabled = '0' AND ".$where." AND id != '".$currentpack."' ORDER BY `type` ASC, `name` ASC");

        $upgrade_array['PACK'] = "";
        while($pack_list = $db->fetch_array($pack_query)) {

            $additional = $type->additional($pack_list['id']);
            $monthly    = $additional['monthly'];
            $signup     = $additional['signup'];
            
            unset($info);
            if($pack_list['type'] == "p2h") {
                $info = "[Signup Posts: ".$signup.", Monthly Posts: ".$monthly."] ";
                $contribution = "<strong>Signup Posts:</strong> ".$signup."<br><strong>Monthly Posts:</strong> ".$monthly;
            }
            elseif($pack_list['type'] == "paid") {
                $info = "[".$sdk->money($monthly)."] ";
                $contribution = $sdk->money($monthly);
            }else{
                $contribution = "Free";
            }
            
            $packages[]      = array("[".$pack_list['type']."] ".$info.$pack_list['name'], $pack_list['id']);

            if($packsid && $packsid == $pack_list['id']){
                $prorate = $navens_upgrade->prorate($pack_list['id'], $main->postvar['coupon']);

                $pack_array['DISABLED'] = "";
                
                if($prorate == "inelegible"){
                    $main->errors("You are currently not elegible for the plan selected because you do not have enough posts.<br><br>");
                    $pack_array['DISABLED'] = "disabled";
                    
                }

                if($prorate == "owe"){
                    $main->errors("You have outstanding charges on your account and can only upgrade your paid package.  Your charges are outstanding!  Keep up the good work.  lol<br><br>");
                    $pack_array['DISABLED'] = "disabled";
                }

                if($prorate == "oweposts"){
                    $main->errors("You still owe your required monthly posts and can only upgrade to a paid plan until you finish your posting quota.  Our forum is a lot of fun, so come join us!  =)<br><br>");
                    $pack_array['DISABLED'] = "disabled";
                }

                if($prorate == "next" || $prorate == "check next"){
                    $main->errors("If you choose this package, you'll be upgraded at the start of your next billing cycle.  If you do not wish to wait, please contact us.<br><br>");
                    $next_month = 1;
                }

                if(is_numeric($prorate) && $prorate > 0){
                    $contribution .= " (You pay only <font color = '#FF0055'>".$sdk->money($prorate)."</font> more today to upgrade.)";
                }

                if($prorate == "check" || $prorate == "check next" || $prorate == "check now"){
                    
                    $fuser = $main->postvar['fuser'];
                    $fpass = $main->postvar['fpass'];

                    $foruminfo_array['FUSER'] = $fuser;
                    $foruminfo_array['FPASS'] = $fpass;
                    $pack_array['FORUMINFO'] = $navens_upgrade->tpl("forum_cred.tpl", $foruminfo_array);
                }else{
                    if($pack_list['type'] == "p2h"){
                        $fuser = $upackinfo['uadditional']['fuser'];
                        $fpass = $upackinfo['uadditional']['fpass'];
                        $no_fcheck = 1;
                    }
                    
                    $pack_array['FORUMINFO'] = "";
                }

                $couponentry_array['COUPCODE'] = "";
                $couponentry_array['COUPTEXT'] = "";
                $couponentry_array['COUPCODEVALID'] = "";
                if($pack_list['type'] == "p2h" && $prorate != "owe"){ //Paid users can enter them when they pay the invoice and free users don't need coupons.
                    $coupcode    = $main->postvar['coupon'];
                    $validcoupon = $main->postvar['validcoupon'];
                    if($main->postvar['addcoupon']){
                        $uname = $sdk->uname($_SESSION['cuser']);
                        if($coupcode){
                            $response = $navens_coupons->validate_coupon($coupcode, "orders", $uname, $packsid);
                            if($response){
                                $coup_data = $navens_coupons->coupon_data($coupcode);
                                $discount = $coup_data['p2hinitdisc'];
                                $multi_coupons = $navens_coupons->coupconfig("multicoupons");
                                if($multi_coupons){
                                    $discount = $discount+$navens_upgrade->get_init_discount();
                                }
                                $total_posts = $navens_coupons->totalposts($_SESSION['cuser'])+$discount;
                                if($total_posts < $signup){
                                    $error = 1;
                                    $main->errors("You are currently not elegible for the plan selected because you do not have enough posts.<br><br>");
                                    $pack_array['DISABLED'] = "disabled";
                                    $couponentry_array['COUPCODEVALID'] = "";
                                }else{
                                    $prorate = $navens_upgrade->prorate($pack_list['id'], $coupcode);
                                    if($prorate == "next" || $prorate == "check next" || $prorate == "inelegible"){  //We know they're elegible or they wouldn't be at this stage.  It just doesn't check existing coupons.
                                        $main->errors("If you choose this package, you'll be upgraded at the start of your next billing cycle.  If you do not wish to wait, please contact us.<br><br>");
                                        $next_month = 1;
                                    }else{
                                        unset($_SESSION['errors']);
                                    }
                                        $pack_array['DISABLED'] = "";
                                        $couponentry_array['COUPCODEVALID'] = $coupcode;
                                }
                                $couponentry_array['COUPTEXT']      = $response;
                                $couponentry_array['COUPCODE']      = $coupcode;
                            }else{
                                $couponentry_array['COUPTEXT'] = "<font color = '#FF0055'>The code entered was invalid.</font>";
                                $couponentry_array['COUPCODEVALID'] = "invalid";
                            }
                        }else{
                            $couponentry_array['COUPTEXT'] = "<font color = '#FF0055'>The code entered was invalid.</font>";
                            $couponentry_array['COUPCODEVALID'] = "invalid";
                        }
                    }else{
                        $couponentry_array['COUPTEXT']      = "";
                        $couponentry_array['COUPCODE']      = $coupcode;
                        $couponentry_array['COUPCODEVALID'] = $validcoupon;
                    }
                    $pack_array['COUPONS'] = $navens_upgrade->tpl("coupons.tpl", $couponentry_array);
                }else{
                    $pack_array['COUPONS'] = "";
                }

                $pack_array['CONTRIBUTION'] = $contribution;
                $pack_array['PACKID']       = $pack_list['id'];
                $pack_array['PACKNAME']     = $pack_list['name'];
                $pack_array['PACKDESC']     = $pack_list['description'];
                $pack_array['ADMIN']        = $pack_list['admin'] == "1" ? "Yes" : "No";
                $pack_array['RESELLER']     = $pack_list['reseller'] == "1" ? "Yes" : "No";
                $pack_array['SERVER']       = $pack_list['server'] != $upackinfo['packages']['server'] ? "Yes" : "No";
                $pack_array['TYPE']         = $pack_list['type'] == "p2h" ? strtoupper($pack_list['type']) : ucfirst($pack_list['type']);

                if($main->postvar['submitchange']){
                    if((!$fuser || !$fpass) && $pack_list['type'] == "p2h" && !$no_fcheck){
                        $error = 1;
                        $main->errors("Please enter your forum username and password to continue.<br><br>");
                    }

                    if($fuser && $fpass && $pack_list['type'] == "p2h" && !$no_fcheck){
                        if(!class_exists("p2h")){
                            $p2h = $type->createtype("p2h");
                        }else{
                            $p2h = new p2h();
                        }
                        $details  = $p2h->pidtoforumdata($pack_list['id']);
                        $response = $p2h->checkSignup($details['type'], $details['prefix']);
                        if($response == 3){
                            $error = 1;
                            $main->errors("The username, ".$fuser.", does not exist.<br><br>");
                        }elseif($response == 4){
                            $error = 1;
                            $main->errors("Invalid password.<br><br>");
                        }else{
                            //We add this now so the post checks can use it and it also tacks it to the user's account for future reference.
                            $new_additional = "fuser=".$fuser.",fpass=0";
                            $db->query("UPDATE <PRE>user_packs SET additional = '".$new_additional."' WHERE id = '".$upackinfo['user_packs']['id']."' LIMIT 1");
                        }
                    }

                    if(!$error){
                        if($validcoupon && $validcoupon != "invalid"){
                            $coup_data = $navens_coupons->coupon_data($validcoupon);
                            $discount = $coup_data['p2hinitdisc'];
                            $db_coupcode = $validcoupon;
                        }
                        $multi_coupons = $navens_coupons->coupconfig("multicoupons");
                        if($multi_coupons){
                            $discount = $discount+$navens_upgrade->get_init_discount();
                        }
                        $total_posts = $navens_coupons->totalposts($_SESSION['cuser'])+$discount;
                        if($total_posts < $signup){
                            $error = 1;
                            $main->errors("You are currently not elegible for the plan selected because you do not have enough posts.<br><br>");
                        }
                        if(!$error){
                            //The user is elegible to upgrade and all checks have passed.
                            if($pack_list['admin']){
                                $admin = 1;
                            }
                            
                            if($pack_list['server'] != $upackinfo['packages']['server']){
                                $different_server = 1;
                            }
                            
                            
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
                            if($next_month){
                                $flags = "1";
                                if($admin){
                                    $flags = "2";
                                    if($different_server){
                                        $flags = "3";
                                    }
                                }else{
                                    if($different_server){
                                        $flags = "4";
                                    }
                                }
                            }else{
                                $flags = "0";
                                if($admin){
                                    $flags = "5";
                                    if($different_server){
                                        $flags = "6";
                                    }
                                }else{
                                    if($different_server){
                                        $flags = "7";
                                    }
                                }
                            }
                            
                            $pending_upgrade = $sdk->tdata("mod_navens_upgrade", "uid", $_SESSION['cuser']);  //When the upgrade is finished, the entry is removed.
                            if($pending_upgrade['id']){
                                $db->query("UPDATE <PRE>mod_navens_upgrade SET uid = '".$_SESSION['cuser']."', newpack = '".$packsid."', flags = '".$flags."', created = '".time()."', coupcode = '".$db_coupcode."' WHERE id = '".$pending_upgrade['id']."' LIMIT 1");
                            }else{
                                $db->query("INSERT INTO <PRE>mod_navens_upgrade SET uid = '".$_SESSION['cuser']."', newpack = '".$packsid."', flags = '".$flags."', created = '".time()."', coupcode = '".$db_coupcode."'");
                            }
                            $pending_upgrade = $sdk->tdata("mod_navens_upgrade", "uid", $_SESSION['cuser']);  //When the upgrade is finished, the entry is removed.
                            $navens_upgrade->upgrade($pending_upgrade['id'], "Init");
                            return;
                        }
                    }
                }
            }
        }
        if($packsid){
            $upgrade_array['PACK'] = $navens_upgrade->tpl("package.tpl", $pack_array);
        }else{
            $upgrade_array['PACKS'] = $sdk->dropdown("packs", $packages, $packsid, 0);
            $upgrade_array['PACK'] = $navens_upgrade->tpl("select_pack.tpl", $pack_array);
        }

        echo $navens_upgrade->tpl("upgrade.tpl", $upgrade_array);
    }
}

?>
