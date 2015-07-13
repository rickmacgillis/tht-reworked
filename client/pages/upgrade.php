<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Client Area - Upgrade / Downgrade
// By: Barrette Galt
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        $upackinfo   = main::uidtopack();
        $currentpack = $upackinfo['user_data']['pid'];
        $packsid     = $postvar['packs'];
        
        if(!$packsid){

            $packsid = $getvar['package'];
        
        }

        unset($where);
        if(is_numeric($packsid)){

            $where[] = array("id", "=", $packsid, "AND");
        
        }else{

            $where[] = array("is_hidden", "=", "0", "AND");
        
        }

        $where[] = array("is_disabled", "=", "0", "AND");
        $where[] = array("id", "!=", $currentpack);
        
        $packages_order[] = array("type", "ASC");
        $packages_order[] = array("name", "ASC");
        $packages_query   = $dbh->select("packages", $where, $packages_order, 0, 1);
        
        $upgrade_array['PACK'] = "";
        while($packages_data = $dbh->fetch_array($packages_query)){ 

            $additional = type::additional($packages_data['id']);
            $monthly    = $additional['monthly'];
            $signup     = $additional['signup'];
            
            unset($info);
            if($packages_data['type'] == "p2h"){

                $info         = "[Signup Posts: ".$signup.", Monthly Posts: ".$monthly."] ";
                $contribution = "<strong>Signup Posts:</strong> ".$signup."<br><strong>Monthly Posts:</strong> ".$monthly;
            
            }elseif($packages_data['type'] == "paid"){

                $info         = "[".main::money($monthly)."] ";
                $contribution = main::money($monthly);
            
            }else{

                $contribution = "Free";
            
            }

            $packages[] = array("[".$packages_data['type']."] ".$info.$packages_data['name'], $packages_data['id']);
            
            if($packsid && $packsid == $packages_data['id']){

                $prorate = upgrade::prorate($packages_data['id'], $postvar['coupon']);
                
                $package_array['DISABLED'] = "";
                
                if($prorate == "inelegible"){

                    main::errors("You are currently not elegible for the plan selected because you do not have enough posts.<br><br>");
                    $package_array['DISABLED'] = "disabled";
                    
                }

                if($prorate == "owe"){

                    main::errors("You have outstanding charges on your account and can only upgrade your paid package.  Your charges are outstanding!  Keep up the good work.  lol<br><br>");
                    $package_array['DISABLED'] = "disabled";
                
                }

                if($prorate == "oweposts"){

                    main::errors("You still owe your required monthly posts and can only upgrade to a paid plan until you finish your posting quota.  Our forum is a lot of fun, so come join us!  =)<br><br>");
                    $package_array['DISABLED'] = "disabled";
                
                }

                if($prorate == "next" || $prorate == "check next"){

                    main::errors("If you choose this package, you'll be upgraded at the start of your next billing cycle.  If you do not wish to wait, please contact us.<br><br>");
                    $next_month = 1;
                
                }

                if(is_numeric($prorate) && $prorate > 0){

                    $contribution .= " (You pay only <font color = '#FF0055'>".main::money($prorate)."</font> more today to upgrade.)";
                
                }

                if($prorate == "check" || $prorate == "check next" || $prorate == "check now"){

                    $fuser = $postvar['fuser'];
                    $fpass = $postvar['fpass'];
                    
                    $forum_credentials_array['FUSER'] = $fuser;
                    $forum_credentials_array['FPASS'] = $fpass;
                    $package_array['FORUMINFO']  = style::replaceVar("tpl/upgrade/forum-credentials.tpl", $forum_credentials_array);
					
                }else{

                    if($packages_data['type'] == "p2h"){

                        $fuser     = $upackinfo['uadditional']['fuser'];
                        $fpass     = $upackinfo['uadditional']['fpass'];
                        $no_fcheck = 1;
                    
                    }

                    $package_array['FORUMINFO'] = "";
                
                }

                $coupon_entry_array['COUPCODE']      = "";
                $coupon_entry_array['COUPTEXT']      = "";
                $coupon_entry_array['COUPCODEVALID'] = "";
                if($packages_data['type'] == "p2h" && $prorate != "owe"){ 
				
					//Paid users can enter them when they pay the invoice and free users don't need coupons.
                    $coupcode    = $postvar['coupon'];
                    $validcoupon = $postvar['validcoupon'];
                    if($postvar['addcoupon']){

                        $uname = main::uname($_SESSION['cuser']);
                        if($coupcode){

                            $response = coupons::validate_coupon($coupcode, "orders", $uname, $packsid);
                            if($response){

                                $coup_data     = coupons::coupon_data($coupcode);
                                $discount      = $coup_data['p2hinitdisc'];
                                $multi_coupons = $dbh->config("multicoupons");
                                if($multi_coupons){

                                    $discount = $discount + upgrade::get_init_discount();
                                
                                }

                                $total_posts = coupons::totalposts($_SESSION['cuser'], $packages_data['id']) + $discount;
                                if($total_posts < $signup){

                                    $error = 1;
                                    main::errors("You are currently not elegible for the plan selected because you do not have enough posts.<br><br>");
                                    $package_array['DISABLED']             = "disabled";
                                    $coupon_entry_array['COUPCODEVALID'] = "";
                                
                                }else{

                                    $prorate = upgrade::prorate($packages_data['id'], $coupcode);
                                    if($prorate == "next" || $prorate == "check next" || $prorate == "inelegible"){
									
										//We know they're eligible or they wouldn't be at this stage.  It just doesn't check existing coupons.
                                        main::errors("If you choose this package, you'll be upgraded at the start of your next billing cycle.  If you do not wish to wait, please contact us.<br><br>");
                                        $next_month = 1;
                                    
                                    }else{

                                        unset($_SESSION['errors']);
                                    
                                    }

                                    $package_array['DISABLED']             = "";
                                    $coupon_entry_array['COUPCODEVALID'] = $coupcode;
                                
                                }

                                $coupon_entry_array['COUPTEXT'] = $response;
                                $coupon_entry_array['COUPCODE'] = $coupcode;
                            
                            }else{

                                $coupon_entry_array['COUPTEXT']      = "<font color = '#FF0055'>The code entered was invalid.</font>";
                                $coupon_entry_array['COUPCODEVALID'] = "invalid";
                            
                            }

                        }else{

                            $coupon_entry_array['COUPTEXT']      = "<font color = '#FF0055'>The code entered was invalid.</font>";
                            $coupon_entry_array['COUPCODEVALID'] = "invalid";
                        
                        }

                    }else{

                        $coupon_entry_array['COUPTEXT']      = "";
                        $coupon_entry_array['COUPCODE']      = $coupcode;
                        $coupon_entry_array['COUPCODEVALID'] = $validcoupon;
                    
                    }

                    $package_array['COUPONS'] = style::replaceVar("tpl/upgrade/coupon-entry.tpl", $coupon_entry_array);
                
                }else{

                    $package_array['COUPONS'] = "";
                
                }

                $package_array['CONTRIBUTION'] = $contribution;
                $package_array['PACKID']       = $packages_data['id'];
                $package_array['PACKNAME']     = $packages_data['name'];
                $package_array['PACKDESC']     = $packages_data['description'];
                $package_array['ADMIN']        = $packages_data['admin'] == "1" ? "Yes" : "No";
                $package_array['RESELLER']     = $packages_data['reseller'] == "1" ? "Yes" : "No";
                $package_array['SERVER']       = $packages_data['server'] != $upackinfo['packages']['server'] ? "Yes" : "No";
                $package_array['TYPE']         = $packages_data['type'] == "p2h" ? strtoupper($packages_data['type']) : ucfirst($packages_data['type']);
                
                if($postvar['submitchange']){

                    //Someone cheated and modified the code to re-enable the button.  This stops all that.
                    if($package_array['DISABLED'] == "disabled"){

                        main::redirect("?page=upgrade");
                        return;
                        
                    }

                    if((!$fuser || !$fpass) && $packages_data['type'] == "p2h" && !$no_fcheck){

                        $error = 1;
                        main::errors("Please enter your forum username and password to continue.<br><br>");
                    
                    }

                    if($fuser && $fpass && $packages_data['type'] == "p2h" && !$no_fcheck){

                        $p2h = $instance->packtypes["p2h"];

                        $response = $p2h->checkSignup($fuser, $fpass, $postvar['packs'], $postvar['validcoupon']);
						
						switch($response){
						
							case "3":

								$error = 1;
								main::errors("The username, ".$fuser.", does not exist.<br><br>");
								break;
						
							case "4":

								$error = 1;
								main::errors("Invalid password.<br><br>");
								break;
						
							default:

								//We add this now so the post checks can use it and it also tacks it to the user's account for future reference.
								$new_additional = "fuser=".$fuser.",fpass=0";
								$dbh->update("users", array("additional" => $new_additional), array("id", "=", $upackinfo['user_data']['id']), "1");
								break;
						
						}

                    }

                    if(!$error){

                        if($validcoupon && $validcoupon != "invalid"){

                            $coup_data   = coupons::coupon_data($validcoupon);
                            $discount    = $coup_data['p2hinitdisc'];
                            $db_coupcode = $validcoupon;
                        
                        }

                        $multi_coupons = $dbh->config("multicoupons");
                        if($multi_coupons){

                            $discount = $discount + upgrade::get_init_discount();
                        
                        }

                        if($packages_data['type'] == "p2h"){

                            $total_posts = coupons::totalposts($_SESSION['cuser'], $packages_data['id']) + $discount;
                            if($total_posts < $signup){

                                $error = 1;
                                main::errors("You are currently not elegible for the plan selected because you do not have enough posts.<br><br>");
                            
                            }

                        }

                        if(!$error){

                            //The user is elegible to upgrade and all checks have passed.
                            if($packages_data['admin']){

                                $admin = 1;
                            
                            }

                            if($packages_data['server'] != $upackinfo['packages']['server']){

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

                            $pending_upgrade = $dbh->select("upgrade", array("uid", "=", $_SESSION['cuser'])); //When the upgrade is finished, the entry is removed.
                            if($pending_upgrade['id']){

                                $upgrade_update = array(
                                    "uid"      => $_SESSION['cuser'],
                                    "newpack"  => $packsid,
                                    "flags"    => $flags,
                                    "created"  => time(),
                                    "coupcode" => $db_coupcode
                                );
                                
                                $dbh->update("upgrade", $upgrade_update, array("id", "=", $pending_upgrade['id']), "1");
                            
                            }else{

                                $upgrade_insert = array(
                                    "uid"      => $_SESSION['cuser'],
                                    "newpack"  => $packsid,
                                    "flags"    => $flags,
                                    "created"  => time(),
                                    "coupcode" => $db_coupcode
                                );
                                
                                $dbh->insert("upgrade", $upgrade_insert);
                            
                            }

                            $pending_upgrade = $dbh->select("upgrade", array("uid", "=", $_SESSION['cuser']));
                            $response        = upgrade::do_upgrade($pending_upgrade['id'], "Init");
                            if($response === false){

                                echo "Your upgrade could not be completed as dialed.  Please check with your admin and try your upgrade again later.  The following tones are for the deaf community in hopes that they'll be able to hear again.  BEEEEEEEEEEEEEEEEEEEEEEEEP!!!!!!!!";
                                
                            }else{

                                echo $response;
                                
                            }

                            return;
                        
                        }

                    }

                }

            }
            
        }

		if($packsid){

			$upgrade_array['PACK'] = style::replaceVar("tpl/upgrade/package.tpl", $package_array);
		
		}else{

			$select_package_array['PACKS'] = main::dropDown("packs", $packages, '', 0);
			$upgrade_array['PACK']         = style::replaceVar("tpl/upgrade/select-package.tpl", $select_package_array);
		
		}

		echo style::replaceVar("tpl/upgrade/upgrade.tpl", $upgrade_array);
		$page_shown = 1;

        //The page doesn't show if they refresh it after the upgrade since the loop checks if they're upgrading to the same package they are on and fails if they are.
        if(!$page_shown){

            main::redirect("?page=upgrade");
            
        }

    }

}

?>