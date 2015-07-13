<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Server Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class server{
    
	// Returns the server class for the desired package
    public function createServer($package=0, $serv_type=0){
        global $dbh, $postvar, $getvar, $instance;
		
        if(!$package && !$serv_type){

            $userid  = $_SESSION['cuser'];
            $client  = $dbh->client($userid);
            $package = $client['pid'];
        
        }

		if(!$serv_type){
		
			$pack_server = type::packageserver($package);
			$serv_type = type::packageServerType($pack_server);
		
		}
		
        if($instance->servers[$serv_type]){

            return;
        
        }

		if($package){
		
			$pack_server = type::packageserver($package);
			
		}
		
        $link = INC."/servers/".$serv_type.".php";
		include($link); // Get the server
		$serverphp = new $serv_type($pack_server);
		return $serverphp;

    }

    public function signup($data){
        global $dbh, $postvar, $getvar, $instance;
		
		$domain     = $data['domain'];
		$username   = $data['username'];
		$password   = $data['password'];
		$user_email = $data['user_email'];
		$firstname  = $data['firstname'];
		$lastname   = $data['lastname'];
		$address    = $data['address'];
		$city       = $data['city'];
		$state      = $data['state'];
		$zip        = $data['zip'];
		$country    = $data['country'];
		$phone      = $data['phone'];
		$tzones     = $data['tzones'];
		$coupon     = $data['coupon'];
		$package    = $data['package'];
		$domsub     = $data['domsub'];
		$additional = $data['additional'];
		$subdomain  = empty($data['subdomain']) ? 0 : $data['subdomain'];
        
        //Let's make sure we're actually receiving an integer as a string.
        if(!is_numeric($package) || strpos($package, ".") !== false){

            return "The package specified is invalid.";
            
        }

        //Check to see if we have a valid domain type.
        if($domsub != "dom" && $domsub != "sub"){

            return "The domain/subdomain type is unspecified in the URL.";
            
        }

        if($domsub == "dom"){

            $cdom = $domain;
            
        }else{

            $csub2 = $domain;
            $csub  = $subdomain;
            
        }

        unset($where);
        $where[]       = array("id", "=", $package, "AND");
        $where[]       = array("is_disabled", "=", "0");
        $packages_data = $dbh->select("packages", $where);
        
        if(!$packages_data['id']){

            return "This package is disabled or doesn't exist.";
            
        }

        $package_server = $packages_data['server'];
        
        if($domsub == "dom"){

            $use_dom = $cdom;
        
        }

        if($domsub == "sub"){

            unset($where);
            $where[]         = array("server", "=", $package_server, "AND");
            $where[]         = array("domain", "=", $csub2);
            $subdomains_data = $dbh->select("subdomains", $where, 0, "1");
            
            if(!$subdomains_data['id']){

                return "The chosen domain for your subdomain is not in the allowed list of domains.";
                
            }

            $use_dom = $csub.".".$csub2;
        
        }

        if($coupon && $packages_data['type'] != 'free'){

            $coupon_response = coupons::validate_coupon($coupon, "orders", $username, $package);
            if(!$coupon_response){

                return "Please enter a valid coupon.";
                
            }else{

                $coupon_info = coupons::coupon_data($coupon);
                
            }

        }
		
		$packtype_instance = $instance->packtypes[$packages_data['type']];
		
		if(method_exists($packtype_instance, "signup")){
		
			$packtype_signup   = $packtype_instance->signup();
			
			//If this gives any response, it means it failed to validate the signup.
			if($packtype_signup){
			
				return $packtype_signup;
			
			}
		
		}

        $server_package_name = type::packageBackend($package);
        $serverfile          = self::createServer($package);
        $packages_data       = $dbh->select("packages", array("id", "=", $package));
        
        $extra['firstname'] = $firstname;
        $extra['lastname']  = $lastname;
        $extra['address']   = $address;
        $extra['city']      = $city;
        $extra['state']     = $state;
        $extra['zip']       = $zip;
        $extra['country']   = strtoupper($country);
        $extra['phone']     = $phone;
        
        $server_response = $serverfile->signup(type::packageserver($package), $packages_data['reseller'], $username, $user_email, $password, $use_dom, $server_package_name, $extra, $use_dom);
        
        if($server_response !== true){

            return $server_response;
            
        }else{

            $time     = time();
            $ip       = $_SERVER['REMOTE_ADDR'];
            $salt     = crypto::salt();
            $password_hash = crypto::passhash($password, $salt);
            
            if($packages_data['admin'] == "1"){

                $status = "3";
                
            }else{

                if($packages_data['type'] == "paid"){

                    $status = "4";
                    
                }else{

                    $status = "1";
                    
                }

            }

            $users_insert = array(
                "user"       => $username,
                "email"      => $user_email,
                "password"   => $password_hash,
                "salt"       => $salt,
                "signup"     => $time,
                "ip"         => $ip,
                "firstname"  => $firstname,
                "lastname"   => $lastname,
                "address"    => $address,
                "city"       => $city,
                "state"      => $state,
                "zip"        => $zip,
                "country"    => $country,
                "phone"      => $phone,
                "status"     => $status,
                "tzadjust"   => $tzones,
                "domain"     => $use_dom,
                "pid"        => $package,
                "additional" => $additional
            );
            
            $dbh->insert("users", $users_insert);
            
            $users_data = $dbh->select("users", array("user", "=", $username), 0, "1");
            
            $users_bak_insert = array(
                "uid"        => $users_data['id'],
                "user"       => $username,
                "email"      => $user_email,
                "password"   => $password_hash,
                "salt"       => $salt,
                "signup"     => $time,
                "ip"         => $ip,
                "firstname"  => $firstname,
                "lastname"   => $lastname,
                "address"    => $address,
                "city"       => $city,
                "state"      => $state,
                "zip"        => $zip,
                "country"    => $country,
                "phone"      => $phone,
                "status"     => $status,
                "tzadjust"   => $tzones,
                "domain"     => $use_dom,
                "pid"        => $package,
                "additional" => $additional
            );
            
            $dbh->insert("users_bak", $users_bak_insert);
            
            main::thtlog("Client Registered", 'Registered.', $users_data['id']);
            
            if(!$users_data['id']){

                $return = "Your account could not be created.  Please contact your system administrator.";
                
            }else{

                if(!empty($coupon_info)){

                    main::thtlog("Coupon Used", "Coupon used (".$coupon_info['coupcode'].")", $users_data['id']);
                    
                    $package_info = type::additional($package);
                    $packmonthly  = $package_info['monthly'];
                    
                    if($packages_data['type'] == "paid"){

                        $coupon_info['p2hmonthlydisc'] = "0";
                        $coupon_info['paiddisc']       = coupons::percent_to_value("paid", $coupon_info['paidtype'], $coupon_info['paiddisc'], $packmonthly);
                        
                    }else{

                        $coupon_info['paiddisc']       = "0";
                        $coupon_info['p2hmonthlydisc'] = coupons::percent_to_value("p2h", $coupon_info['p2hmonthlytype'], $coupon_info['p2hmonthlydisc'], $packmonthly);
                        
                    }

                    $insert_array = array(
                        "user"           => $users_data['id'],
                        "coupcode"       => $coupon_info['coupcode'],
                        "timeapplied"    => time(),
                        "packages"       => $package,
                        "goodfor"        => $coupon_info['goodfor'],
                        "monthsgoodfor"  => $coupon_info['monthsgoodfor'],
                        "paiddisc"       => $coupon_info['paiddisc'],
                        "p2hmonthlydisc" => $coupon_info['p2hmonthlydisc']
                    );
                    
                    $dbh->insert("coupons_used", $insert_array);
                
                }

                $servers_data = $dbh->select("servers", array("id", "=", $package_server), 0, "1");
                
                $server_host         = $servers_data['host'];
                $server_ip           = $servers_data['ip'];
                $server_nameservers  = $servers_data['nameservers'];
                $server_port         = $servers_data['port'];
                $server_resellerport = $servers_data['resellerport'];
                
                $url                                 = $dbh->config("url");
                $new_acc_email_array['CPPORT']       = $server_port;
                $new_acc_email_array['RESELLERPORT'] = $server_resellerport;
                $new_acc_email_array['SERVERIP']     = $server_ip;
                $new_acc_email_array['NAMESERVERS']  = nl2br($server_nameservers);
                $new_acc_email_array['USER']         = $username;
                $new_acc_email_array['PASS']         = $password;
                $new_acc_email_array['EMAIL']        = $user_email;
                $new_acc_email_array['FNAME']        = $firstname;
                $new_acc_email_array['LNAME']        = $lastname;
                $new_acc_email_array['DOMAIN']       = $use_dom;
                $new_acc_email_array['CONFIRM']      = $url."client/confirm.php?u=".$username."&c=".$time;
                $new_acc_email_array['PACKAGE']      = $packages_data['name'];
                
                if($packages_data['admin'] == 0){

                    if($packages_data['reseller'] == "1"){

                        $new_acc_email = email::emailTemplate("new-reseller-account");
                        
                    }else{

                        $new_acc_email = email::emailTemplate("new-account");
                        
                    }

                    $return = "<strong>Your account has been created!</strong><br />You may now <a href = '../client'>login</a> to see your client area or proceed to your <a href = 'http://".$server_host.":".$server_port."'>control panel</a>. An email has been dispatched to the address on file.";
                    
                    if(type::packagetype($package) == "paid"){

                        //Set the user up for when they finish their payment.
                        $_SESSION['clogged'] = 1;
                        $_SESSION['cuser']   = $users_data['id'];
                        
                    }

                    $donecorrectly = true;
                    
                }else{

                    if($serverfile->suspend($username, type::packageserver($package), 1) == false){

                        $return = "We could not suspend your account!  Please contact the admin to suspend it until they validate it.  lol";
                        
                    }else{

                        $dbh->update("users", array("status" => "3"), array("id", "=", $users_data['id']));
                        
                        if($packages_data['reseller'] == "1"){

                            $new_acc_email = email::emailTemplate("new-reseller-account-adminval");
                            
                        }else{

                            $new_acc_email = email::emailTemplate("new-account-adminval");
                            
                        }

                        $admin_val_email   = email::emailTemplate("admin-validation-requested");
                        $valarray['LINK'] = $dbh->config("url").ADMINDIR."/?page=users&sub=search&do=".$users_data['id'];
                        email::staff($admin_val_email['subject'], $admin_val_email['content'], $valarray);
                        
                        $return = "<strong>Your account is awaiting admin validation!</strong><br />An email has been dispatched to the address on file. You will recieve another email when the admin has looked over your account.";
                        
                        $donecorrectly = true;
                        
                    }

                }

                email::send($new_acc_email_array['EMAIL'], $new_acc_email['subject'], $new_acc_email['content'], $new_acc_email_array);
                
            }

            if($donecorrectly && type::packagetype($package) == "paid"){
                
                $amountinfo = type::additional($package);
                $amount     = $amountinfo['monthly'];
                $due        = time() + 2592000;
                $notes      = "Your hosting package invoice for this billing cycle. Package: ".$packages_data['name'];
                
                if(!empty($coupon_info)){

                    $amount = max(0, $amount - $coupon_info['paiddisc']);
                    
                }

                invoice::create($users_data['id'], $amount, $due, $notes);
                
                $serverfile->suspend($username, type::packageserver($package), 0, 1);
                
                $dbh->update("users", array("status" => $status), array("id", "=", $users_data['id']));
                
                if($packages_data['admin'] != "1"){

                    $return = '<div class="errors"><b>You are being redirected to payment! It will load in a couple of seconds.</b></div>';
                    return true;
                    
                }

            }

            return $return;
            
        }

    }

	// Deletes a user account from the package ID
    public function terminate($id, $reason = false, $by_client=0){
        global $dbh, $postvar, $getvar, $instance;
        
        $client = $dbh->client($id);
        if(!$client){

            $error_array['Error']    = "That client doesn't exist.";
            $error_array['User PID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            $server = type::packageserver($client['pid']);
            $serverfile = self::createServer($client['pid']);

            if($serverfile->terminate($client['user'], $server) == true){

                $emaildata = email::emailTemplate("account-terminated");
                if(!$reason){

                    $reason = "None given";
                
                }

				if($by_client){
				
					$requested = "Client Deleted Their Account";
					$reason = "Client requested account deletion";
				
				}else{
				
					$requested = "Client Account Terminated";
					
				}
				
                $terminate_array['REASON'] = $reason;
                email::send($client['email'], $emaildata['subject'], $emaildata['content'], $terminate_array);
                main::thtlog($requested, $requested." (".$reason.")", $id);
								
				$package_server_data = $dbh->select("servers", array("id", "=", type::packageserver($client['pid'])));
				$admin_notifyterm   = email::emailTemplate("notify-admin-of-termination");
                $notifyterm_array['REASON'] = $reason;
                $notifyterm_array['USER'] = $client['user'];
				$notifyterm_array['SERV_TYPE'] = $package_server_data['type'];
				$notifyterm_array['SERV_NAME'] = $package_server_data['name'];
                email::staff($admin_notifyterm['subject'], $admin_notifyterm['content'], $notifyterm_array);
                
                $dbh->delete("users", array("id", "=", $id), "1");
                $dbh->delete("upgrade", array("uid", "=", $id), "1");
                return true;
            
            }else{

                return false;
            
            }

        }

    }

	// Deletes a user account from the package ID
    public function cancel($id, $reason = false){
        global $dbh, $postvar, $getvar, $instance;
        
        unset($where);
        $where[]    = array("id", "=", $id, "AND");
        $where[]    = array("status", "!=", "9");
        $users_data = $dbh->select("users", $where);
        if(!$users_data['id']){

            $error_array['Error']    = "That client doesn't exist or cannot be cancelled.  Are you trying to cancel an already cancelled account?";
            $error_array['User PID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            $server = type::packageserver($users_data['pid']);
            $serverfile = self::createServer($users_data['pid']);

            if($serverfile->terminate($users_data['user'], $server) == true){

                if(!$reason){

                    $reason = "None given";
                
                }
				
                $emaildata        = email::emailTemplate("account-canceled");
                $cancel_array['REASON'] = $reason;
                email::send($users_data['email'], $emaildata['subject'], $emaildata['content'], $cancel_array);
				
				$package_server_data = $dbh->select("servers", array("id", "=", type::packageserver($client['pid'])));
				$admin_notifycancel   = email::emailTemplate("notify-admin-of-cancellation");
                $notifycancel_array['REASON'] = $reason;
                $notifycancel_array['USER'] = $users_data['user'];
				$notifycancel_array['SERV_TYPE'] = $package_server_data['type'];
				$notifycancel_array['SERV_NAME'] = $package_server_data['name'];
                email::staff($admin_notifycancel['subject'], $admin_notifycancel['content'], $notifycancel_array);
                
                $dbh->update("users_bak", array("status" => "9"), array("id", "=", $users_data['id']));
                $dbh->update("users", array("status" => "9"), array("id", "=", $users_data['id']));
                main::thtlog("Client Account Cancelled", "Cancelled  (".$reason.")", $users_data['id']);
                return true;
            
            }else{

                return false;
            
            }

        }

    }

	// Deletes a user account from the package ID
    public function decline($id){
        global $dbh, $postvar, $getvar, $instance;
        
        unset($where);
        $where[]    = array("id", "=", $id, "AND");
        $where[]    = array("status", "=", "3");
        $users_data = $dbh->select("users", $where);
        if(!$users_data['id']){

            $error_array['Error']    = "That client doesn't exist or is not awaiting validation.";
            $error_array['User PID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            $server = type::packageserver($users_data['pid']);
            $serverfile = self::createServer($users_data['pid']);

            if($serverfile->terminate($users_data['user'], $server) == true){

                $emaildata        = email::emailTemplate("account-declined");
                email::send($users_data['email'], $emaildata['subject'], $emaildata['content'], $declined_array);
                
                $dbh->update("users_bak", array("status" => "9"), array("id", "=", $users_data['id']));
                $dbh->update("users", array("status" => "9"), array("id", "=", $users_data['id']));
                main::thtlog("Client Account Declined", "Declined (Account Declined)')", $users_data['id']);
                return true;
            
            }else{

                return false;
            
            }

        }

    }

	// Approves a user's account (Admin Validation).
    public function approve($id){
        global $dbh, $postvar, $getvar, $instance;
        
        unset($where);
        $where[]    = array("id", "=", $id, "AND");
        $where[]    = array("status", "=", "2", "OR", 1);
        $where[]    = array("status", "=", "3", "OR");
        $where[]    = array("status", "=", "4", "", 1);
        $users_data = $dbh->select("users", $where);
        if(!$users_data['id']){

            $error_array['Error']    = "That user doesn't exist or cannot be approved! (Did they confirm their e-mail?)";
            $error_array['User PID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            $server = type::packageserver($users_data['pid']);
            $serverfile = self::createServer($users_data['pid']);

            if($serverfile->unsuspend($users_data['user'], $server) == true){
			
				$emaildata = email::emailTemplate("account-approved");
                email::send($users_data['email'], $emaildata['subject'], $emaildata['content'], $declined_array);

                $dbh->update("users", array("status" => "1"), array("id", "=", $users_data['id']));
                $dbh->update("users_bak", array("status" => "1"), array("id", "=", $users_data['id']));
                main::thtlog("Client Account Approved", "Approved (Account Approved)", $users_data['id']);
                return true;
            
            }else{

                return false;
            
            }

        }

    }

	// Suspends a user account from the package ID
    public function suspend($id, $reason = false, $noemail=0){
        global $dbh, $postvar, $getvar, $instance;
        
        unset($where);
        $where[]    = array("id", "=", $id, "AND");
        $where[]    = array("status", "=", "1");
        $users_data = $dbh->select("users", $where);
        if(!$users_data['id']){

            $error_array['Error']    = "That client doesn't exist or is not active.";
            $error_array['User PID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            $server     = type::packageserver($users_data['pid']);
            $serverfile = self::createServer($users_data['pid']);

            if($serverfile->suspend($users_data['user'], $server, $reason)){

                $emaildata = email::emailTemplate("account-suspended");
                if(!$reason){

                    $reason = "None given";
                
                }

				if(!$noemail){
				
					$email_arr['REASON'] = $reason;
					email::send($users_data['email'], $emaildata['subject'], $emaildata['content'], $email_arr);
					
					$package_server_data = $dbh->select("servers", array("id", "=", type::packageserver($client['pid'])));
					$admin_notifysuspend   = email::emailTemplate("notify-admin-of-suspension");
					$notifysuspend_array['REASON'] = $reason;
					$notifysuspend_array['USER'] = $users_data['user'];
					$notifysuspend_array['SERV_TYPE'] = $package_server_data['type'];
					$notifysuspend_array['SERV_NAME'] = $package_server_data['name'];
					email::staff($admin_notifysuspend['subject'], $admin_notifysuspend['content'], $notifysuspend_array);
					
					main::thtlog("Client Account Suspended", "Suspended (".$reason.")", $users_data['id']);
				
				}
				
                $dbh->update("users_bak", array("status" => "2"), array("id", "=", $users_data['id']));
                $dbh->update("users", array("status" => "2"), array("id", "=", $users_data['id']));
                return true;
            
            }else{

                return false;
            
            }

        }

    }

	// Unsuspends a user account from the their ID
    public function unsuspend($id, $noemail = 0){
        global $dbh, $postvar, $getvar, $instance;
        
        unset($where);
        $where[]    = array("id", "=", $id, "AND");
        $where[]    = array("status", "=", "2", "OR", 1);
        $where[]    = array("status", "=", "3", "OR");
        $where[]    = array("status", "=", "4", "", 1);
        $users_data = $dbh->select("users", $where);
        if(!$users_data['id']){

            $error_array['Error']    = "That package doesn't exist or cannot be unsuspended!";
            $error_array['User PID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            $server     = type::packageserver($users_data['pid']);
            $serverfile = self::createServer($users_data['pid']);

            if($serverfile->unsuspend($users_data['user'], $server) == true){

                if(!$noemail){

                    $emaildata = email::emailTemplate("account-unsuspended");
                    email::send($users_data['email'], $emaildata['subject'], $emaildata['content']);
				
					$package_server_data = $dbh->select("servers", array("id", "=", type::packageserver($client['pid'])));
					$admin_notifyunsuspend   = email::emailTemplate("notify-admin-of-unsuspension");
					$notifyunsuspend_array['USER'] = $users_data['user'];
					$notifyunsuspend_array['SERV_TYPE'] = $package_server_data['type'];
					$notifyunsuspend_array['SERV_NAME'] = $package_server_data['name'];
					email::staff($admin_notifyunsuspend['subject'], $admin_notifyunsuspend['content'], $notifyunsuspend_array);
                
                }

                $dbh->update("users_bak", array("status" => "1"), array("id", "=", $users_data['id']));
                $dbh->update("users", array("status" => "1"), array("id", "=", $users_data['id']));
                main::thtlog("Client Account Unsuspended", "Unsuspended", $users_data['id']);
                return true;
            
            }else{

                return false;
            
            }

        }

    }

	// Set's user's account to Active when the unique link is visited.
    public function confirm($username, $confirm){
        global $dbh, $postvar, $getvar, $instance;
        
        unset($where);
        $where[]    = array("user", "=", $username, "AND");
        $where[]    = array("signup", "=", $confirm, "AND");
        $where[]    = array("status", "=", "5");
        $users_data = $dbh->select("users", $where);
        if(!$users_data['id']){

            $error_array['Error'] = "Your account doesn't exist or the link was wrong.  Please use the link that was emailed to you or contact a system administrator.";
            main::error($error_array);
            return false;
        
        }else{

            $dbh->update("users", array("status" => "1"), array("user", "=", $username));
            $dbh->update("users_bak", array("status" => "1"), array("user", "=", $username));
            main::thtlog("Account Confirmed", "Account/E-mail Confirmed", $users_data['id']);
            return true;
        
        }

    }

	// Changes user's password.
    public function changePwd($id, $newpwd){
        global $dbh, $postvar, $getvar, $instance;
        
        $client = $dbh->client($id);
        if(!$client['id']){

            $error_array['Error']    = "That user doesn't exist.";
            $error_array['User PID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            $server     = type::packageserver($client['pid']);
            $serverfile = self::createServer($client['pid']);
            if($serverfile->changePwd($client['user'], $newpwd, $server)){

                main::thtlog("Client Password Changed", "Password changed", $client['id']);
                return true;
            
            }else{

                return false;
            
            }

        }

    }

    public function testConnection($server_id){
        global $dbh, $postvar, $getvar, $instance;
		
        $servers_data = $dbh->select("servers", array("id", "=", $server_id)); 
        if(!$servers_data['id']){

            return "There is no server with an id of ".$server_id;
        
        }else{

            $type = $servers_data["type"];
            $link = INC."/servers/".$type.".php";
            if(!file_exists($link)){

                return "The server ".$type.".php doesn't exist!";
            
            }else{

                require_once($link);
                $server = new $type($server_id);
                return $server->testConnection();
            
            }

        }

    }
	
	public function show_phpinfo(){

		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();

		// the name attribute "module_Zend Optimizer" of an anchor-tag is not xhtml valid, so replace it with "module_Zend_Optimizer"
		$phpinfo = str_replace("&nbsp;", " ", $phpinfo);
		$phpinfo_array['THEME']   = URL."themes/".THEME;
		$phpinfo_array['PHPINFO'] = str_replace("module_Zend Optimizer", "module_Zend_Optimizer", preg_replace( '%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo));
		
		return style::replaceVar("/tpl/admin/servers/phpinfo.tpl", $phpinfo_array);
	
	}

}

?>