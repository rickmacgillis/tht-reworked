<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Order Form
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

define("INC", "../includes");
include(INC."/compiler.php");

define("PAGE", "Order Form");
define("SUB", "Account Creation");
define("INFO", "IP Logged: ".$_SERVER['REMOTE_ADDR']);

if($getvar['do'] == "logout"){

    session_destroy();
    main::redirect("../order/");

}

echo style::get("header.tpl");

if($dbh->config("general") == 0){

    $maincontent = main::table("Signups Closed", $dbh->config("message"));

}elseif(!check::IP($_SERVER['REMOTE_ADDR']) && !$dbh->config("multiple")){

    $maincontent = main::table("Account Exists", "You already have an account and may not open a second one.");

}elseif($_SESSION['clogged']){

	session_destroy();
	main::redirect("./");

}

if($maincontent){
	
	echo '<div>';
    echo $maincontent;
    echo '</div></div>';
	echo style::get("footer.tpl");
	include(INC."/output.php");	
	
	return;

}

echo '<div id="ajaxwrapper">'; //Ajax wrapper, for steps

$order_form_array['ERRORS'] = "";

if(!$getvar["domsub"]){

    if(!$getvar['id']){

        unset($where);
        $where[]        = array("is_hidden", "=", "0", "AND");
        $where[]        = array("is_disabled", "=", "0");
        $packages_query = $dbh->select("packages", $where, array("sortorder", "ASC"), 0, 1);
    
    }else{

        unset($where);
        $where[]        = array("is_disabled", "=", "0", "AND");
        $where[]        = array("id", "=", $getvar['id']);
        $packages_query = $dbh->select("packages", $where, 0, "1", 1);
    
    }

    if($dbh->num_rows($packages_query) == 0){

        echo main::table("No packages", "Sorry there are no available packages!");
        $no_packs = 1;
    
    }

}

if(!$no_packs){

    if(!$getvar["domsub"]){

        if($_POST){

            //This allows the user to see the form properly and be able to click the back button on their browser
            //if the server says the server side creation failed later.  If the server isn't running on HTTPS, then it'll
            //show all of their data for them.
            
            foreach($postvar as $key => $val){

                if(strpos($key, 'submitpack')){

                    $package_chk = str_replace('submitpack', '', $key);
                    
                    if(is_numeric($package_chk) && !strpos($package_chk, ".")){

                        $package = $package_chk;
                        break;
                    
                    }

                }

            }

            if($package && ($postvar['domsub'] == "dom" || $postvar['domsub'] == "sub")){

                main::redirect("?package=".$package."&domsub=".$postvar['domsub']);
                exit;
            
            }else{

                main::redirect("./");
                exit;
            
            }

        }

        while($packages_data = $dbh->fetch_array($packages_query)){

            if(!$n){

                $order_form_array['PACKAGES'] .= "<tr>";
            
            }

            $order_packages_array['NAME']        = $packages_data['name'];
            $order_packages_array['DESCRIPTION'] = $packages_data['description'];
            $order_packages_array['ID']          = $packages_data['id'];
            $packages_list_array['PACKAGES'] .= style::replaceVar("tpl/order/order-packages.tpl", $order_packages_array);
            $n++;
            if($n == 1){

                $packages_list_array['PACKAGES'] .= '<td width="2%"></td>';
            
            }

            if($n == 2){

                $packages_list_array['PACKAGES'] .= "</tr>";
                $n = 0;
            
            }

        }

        //Subdomains
        $subdomains_query = $dbh->select("subdomains");
        $tldonly          = $dbh->config("tldonly");
        if($dbh->num_rows($subdomains_query) == 0 || $tldonly == "1"){

            $packages_list_array["CANHASSUBDOMAIN"] = "";
        
        }else{

            $packages_list_array["CANHASSUBDOMAIN"] = '<option value="sub">Subdomain</option>';
        
        }

        $maincontent = style::replaceVar("tpl/order/packages-list.tpl", $packages_list_array);
        
    }else{

        if($_POST['submitfinish']){

            check::empty_fields(array("coupon"));
            if(main::errors()){

                $order_form_array['ERRORS'] = style::replaceVar("tpl/order/errors.tpl", array())."<br><br>";
                
            }else{
			
				//We don't check that the TOS is checked because it will be seen as an empty field and get caught by check:empty_fields.

                if(!check::user($postvar['username'])){

                    main::errors(nl2br("The username was either taken or was in an invalid format.

                                           Usernames:
                                           1.) Must be between 4-8 characters long
                                           2.) Must not start with a number
                                           3.) Must be alphanumeric"));
                
                }

                if(!check::pass($postvar['password'], $postvar['confirmp'])){

                    main::errors(nl2br("Your passwords either do not match or contain invalid characters.  (< and > are not allowed.  O>.<O  Nor is #"));
                
                }

                if(!check::email($postvar['email'])){

                    main::errors(nl2br("Your email address didn't match one or more of the following:

                                           Your email address:
                                           1.) Must be in the correct format
                                           2.) Must not be associated with another client
                                           3.) If you're using an email address hosted with us, please make sure your email address has the proper \"MX\" or \"A\" DNS record."));
                
                }

                if(!check::firstname($postvar['firstname'])){

                    main::errors(nl2br("In our system, your first and last name may only contain letters and the following characters:.' - and space."));
                
                }

                if(!check::lastname($postvar['lastname'])){

                    main::errors(nl2br("In our system, your first and last name may only contain letters and the following characters:.' - and space."));
                
                }

                if(!check::address($postvar['address'])){

                    main::errors(nl2br("Your address may only contain letters, numbers and the following characters:.- and space."));
                
                }

                if(!check::city($postvar['city'])){

                    main::errors(nl2br("Your city may only contain letters and spaces."));
                
                }

                if(!check::state($postvar['state'])){

                    main::errors(nl2br("Your state may only contain letters and the following characters:.- and space"));
                
                }

                if(!check::zip($postvar['zip'])){

                    main::errors(nl2br("Your zip may only be 10 characters long and contain only  letters, numbers, and the following characters: - and space"));
                
                }

                if(!check::country($postvar['country'])){

                    main::errors(nl2br("Your country code may only contain 2 letters."));
                
                }

                if(!check::phone($postvar['phone'])){

                    main::errors(nl2br("Your phone number may only contain numbers and the - character and must be between 10 and 20 numbers long."));
                
                }

                if(!check::tzone($postvar['tzones'])){

                    main::errors(nl2br("Your time zone may only contain letters and the / _ and - characters."));
                
                }

                if(!check::human($postvar['human'])){

                    main::errors(nl2br("You're not human.  Neither am I for that matter.  =P  lol  Check the code you entered for the captcha."));
                
                }
				
                $coupon_chk = check::coupon($postvar['coupon'], $postvar['username'], $getvar['package']); //Can return text as well.  Text is seen as true unless it's "0"
                $getvar['coupon'] = $postvar['coupon']; //For any types (Ex. p2h) that might need to grab a coupon.
                if(!$coupon_chk){

                    main::errors(nl2br("The coupon code entered was invalid."));
                
                }

                $domain = $postvar['cdom'];
                if(!check::domain($postvar['cdom']) && $postvar['cdom']){

                    main::errors(nl2br("Your domain is in the wrong format.  Domains must be alphanumerical and have a valid TLD.  (Domain suffix)"));
                
                }

                if($postvar['csub2'] || $postvar['csub']){

                    if(!check::domain($postvar['csub2'])){

                        main::errors(nl2br("Your domain is in the wrong format.  Domains must be alphanumerical and have a valid TLD.  (Domain suffix)"));
                    
                    }else{

                        if(!ctype_alnum($postvar['csub'])){

                            main::errors(nl2br("Your chosen subdomain must be alphanumerical."));
                        
                        }

                        $domain    = $postvar['csub2'];
                        $subdomain = $postvar['csub'];
                    
                    }

                }

                if(!check::extra_fields()){

                    main::errors(nl2br("The fields in step 4 contain invalid characters.  (>, <, or #)"));
                
                }

                if(main::errors()){

                    $order_form_array['ERRORS'] = style::replaceVar("tpl/order/errors.tpl", array())."<br><br>";
                    
                }else{

					foreach($postvar as $key => $value){

						$key_exp = explode("_", $key);
						if($key_exp[0] == "type"){

							if($n){

								$additional .= ",";
								
							}

							if($key == "type_fpass"){
							
								$value = 0;
							
							}
							
							$additional .= $key_exp[1]."=".$value;
							$n++;
							
						}

					}
				
                    //Now we try to process it through the server.
					$data['domain']     = $domain;
					$data['username']   = $postvar['username'];
					$data['password']   = $postvar['password'];
					$data['user_email'] = $postvar['email'];
					$data['firstname']  = $postvar['firstname'];
					$data['lastname']   = $postvar['lastname'];
					$data['address']    = $postvar['address'];
					$data['city']       = $postvar['city'];
					$data['state']      = $postvar['state'];
					$data['zip']        = $postvar['zip'];
					$data['country']    = $postvar['country'];
					$data['phone']      = $postvar['phone'];
					$data['tzones']     = $postvar['tzones'];
					$data['coupon']     = $postvar['coupon'];
					$data['package']    = $getvar['package'];
					$data['domsub']     = $getvar['domsub'];
					$data['additional'] = $additional;
					$data['subdomain']  = $subdomain;
					
                    $response = server::signup($data);
                    
                    if($response === true){

                        //class_server adds an invoice for paid accounts.  Do we have one to pay?
                        $invoice = check::ispaid($getvar['package'], $postvar['username']);
                        
                        //It's not false and has to be > 0 if it returns something.
                        if($invoice){

                            main::redirect("../client/?page=invoices&iid=".$invoice);
                            exit;
                            
                        }

                    }

                    if($response === false){

                        $response = "An unknown error has orrured.  Please contact your system administrator.";
                        
                    }

                    //It's not redirecting to the payment page, so we display the finishing text.
                    $order_finished_array['FINISHEDTEXT'] = $response;
                    
                    $step5       = 1;
                    $maincontent = style::replaceVar("tpl/order/order-finished.tpl", $order_finished_array);
                
                }

            }

        }

        if(!$step5 && is_numeric($getvar['package']) && !strpos($getvar['package'], ".") && ($getvar['domsub'] == "dom" || $getvar['domsub'] == "sub")){

            if($postvar['tzones']){

                $tz_default = $postvar['tzones'];
            
            }else{

                $tz_default = "GMT";
            
            }

            $order_form_array['AGREE']       = $postvar['agree'] == 1 ? "checked" : "";
            $order_form_array['USERNAME']    = $postvar['username'];
            $order_form_array['PASSWORD']    = $postvar['password'];
            $order_form_array['CONFPASS']    = $postvar['confirmp'];
            $order_form_array['EMAIL']       = $postvar['email'];
            $order_form_array['FIRSTNAME']   = stripslashes($postvar['firstname']);
            $order_form_array['LASTNAME']    = stripslashes($postvar['lastname']);
            $order_form_array['ADDRESS']     = $postvar['address'];
            $order_form_array['CITY']        = $postvar['city'];
            $order_form_array['STATE']       = $postvar['state'];
            $order_form_array['ZIP']         = $postvar['zip'];
            $order_form_array['PHONE']       = $postvar['phone'];
            $order_form_array['COUPON']      = $postvar['coupon'] == "" ? $getvar['coupon'] : $postvar['coupon'];
            $order_form_array['PACKID']      = $getvar['package'];
            $domain_array['DOMAIN']          = $postvar['cdom'];
            
            if(empty($domain_array['DOMAIN'])){

                $domain_array['DOMAIN'] = $postvar['csub'];
            
            }

            $order_form_array['TOS']       = $dbh->config("tos");
            $order_form_array['TZADJUST']  = main::tzlist($tz_default);
            $order_form_array['COUNTRIES'] = main::countries(1, $postvar['country']); //1 = Make it a drop down instead of pulling an array.  The second part makes it set it's default.

            $ptype                           = type::packagetype($getvar['package']);
            $order_form_array['TYPESPECIFIC'] = type::orderForm($ptype);
            
            if($getvar['domsub'] == "dom"){

                $order_form_array['DOMORSUB'] = style::replaceVar("tpl/order/domain.tpl", $domain_array);
            
            }else{

                $pack            = $getvar['package'];
                $server_type     = type::packageserver($pack);
                $serverfile      = server::createServer($server_type);
                $can_create_subs = $serverfile->subdomains;
                if($can_create_subs == false){

                    $maincontent = main::table("Subdomain Error", "Sorry, but the server for this package doesn't allow subdomains to be used without a unique domain present on the account.  If you'd like to
                                                                   use a domain, please go back and select the domain option.");
                    
                }else{

                    $subdomains_query = $dbh->select("subdomains", array("server", "=", $server_type), array("subdomain", "ASC"), 0, 1);
                    while($subdomains_data = $dbh->fetch_array($subdomains_query)){

                        $subdomains[] = array($subdomains_data['domain'], $subdomains_data['domain']);
                    
                    }

                    if($postvar['csub2']){

                        $subtld = $postvar['csub2'];
                    
                    }else{

                        $subtld = $subdomains[0]['domain'];
                    
                    }

                    $subdomain_array['SUBDOMTLDLIST'] = main::dropdown("csub2", $subdomains, $subtld);
                    $order_form_array['DOMORSUB']       = style::replaceVar("tpl/order/subdomain.tpl", $subdomain_array);
                    
                }

            }

        }

    }

    //Spit out the page
    if(!$maincontent){

        $maincontent = style::replaceVar("tpl/order/order-form.tpl", $order_form_array);
    
    }

    echo '<div>';
    echo $maincontent;
    echo '</div>';

}

echo '</div>';

echo style::get("footer.tpl");

include(INC."/output.php");

?>