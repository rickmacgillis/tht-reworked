<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Post2Host Hosting Type
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

class p2h{

    public $acpForm = array(), $orderForm = array(), $acpNav = array(), $acpSubNav = array(); // The HTML Forms arrays
    public $name = "Post2Host"; // Human readable name of the package.
    
    private $con; // Forum SQL
    
	// Assign stuff to variables on creation
    public function __construct(){
        global $dbh, $postvar, $getvar, $instance;
		
        $this->acpForm[]   = array("Signup Posts", '<input name="signup" type="text" id="signup" size="5" />', 'signup');
        $this->acpForm[]   = array("Monthly Posts", '<input name="monthly" type="text" id="monthly" size="5" />', 'monthly');
        $this->orderForm[] = array("Forum Username", '<input name="type_fuser" type="text" id="type_fuser" />', 'fuser');
        $this->orderForm[] = array("Forum Password", '<input name="type_fpass" type="password" id="type_fpass" />', 'fpass');
        $p2h_query      = $dbh->select("p2h");
        while($p2h_data = $dbh->fetch_array($p2h_query)){

            $values[] = array($p2h_data['forumname'], $p2h_data['id']);
        
        }

        $this->acpForm[]   = array("Forum", main::dropDown("forum", $values), 'forum');
        $this->acpNav[]    = array("P2H Forums", "forums", "lightning.png", "P2H Forums");
        $this->clientNav[] = array("Forum Posting", "forums", "lightning.png", "Forum Posting");
    
    }

	public function cron(){
        global $dbh, $postvar, $getvar, $instance;
		
        if($dbh->config("p2hcheck") == ""){

            // Probably a new install. Cron has never run before.
            $dbh->updateConfig("p2hcheck", "0:0:0");
        
        }

        $checkdate = explode(":", $dbh->config("p2hcheck"));
        if($checkdate === array($dbh->config("p2hcheck"))){

            $dbh->updateConfig("p2hcheck", $dbh->config("p2hcheck").":0:0");
            $checkdate = explode(":", $dbh->config("p2hcheck"));
        
        }elseif(array_key_exists(1, $checkdate)){

            if($checkdate[1] == ""){

                $dbh->updateConfig("p2hcheck", $checkdate[0].":0:0");
                $checkdate = explode(":", $dbh->config("p2hcheck"));
            
            }

        }

        // If today is the last day of the month (and hasn't been run yet)
        if(date("d") == date("t") && ((int) $checkdate[0] < (int) date("m") || ((int) $checkdate[0] == (int) date("m") && $checkdate[2] == "0"))){

            $users_query = $dbh->select("users");
            while($users_data = $dbh->fetch_array($users_query)){
			
				//Skip this user if its marked as a free user.
				if($users_data['freeuser']){
				
					continue;
				
				}			

                $ptype = type::packagetype($users_data['pid']);
                if($ptype == "p2h"){

                    $fuser     = type::userAdditional($users_data['id']);
                    $forum     = type::additional($users_data['pid'], 'forum');
                    $this->con = $this->forumCon($forum);
                    $posts     = coupons::totalposts($users_data['id']);
                    $mposts    = $this->getMonthly($users_data['pid'], $users_data['id']);
                    if($posts < $mposts){

                        // If the user haven't posted enough...
                        $user         = $dbh->client($users_data['id']);
                        $grace_period = $dbh->config("p2hgraceperiod"); //The grace period in days
                        $grace_period = $grace_period * 24 * 60 * 60;
                        if(strtotime(date("Y-m-d")." 00:00:00") > $users_data['signup'] + $grace_period){
						
							//This gives the user a grace period.
                            // Suspend the user.
							
                            server::suspend($users_data['id'], "Only posted $posts post out of the required $mposts monthly posts");
							
                            // Output to the cron.
                            echo "<strong>".$user['user']." (".$fuser['fuser']."):</strong> Suspended for not posting the required amount. ($posts out of $mposts)<br />";
                        
                        }

                    }

                }

            }

            // We're done for this month. Prepare for the next.
            if(date("m") == 12){

                $checkmonth = "0";
            
            }else{

                $checkmonth = date("m");
            
            }
        
        }

        // If today is the warn day (and hasn't been run yet)
        elseif((int) date("d") == $dbh->config("p2hwarndate") && (int) $checkdate[1] != 1){

            $users_query = $dbh->select("users");
            while($users_data = $dbh->fetch_array($users_query)){
			
				//Skip this user if its marked as a free user.
				if($users_data['freeuser']){
				
					continue;
				
				}

                $ptype = type::packagetype($users_data['pid']);
                if($ptype == "p2h"){

                    $fuser       = type::userAdditional($users_data['id']);
                    $forum       = type::additional($users_data['pid'], 'forum');
                    $this->con   = $this->forumCon($forum);
                    $posts       = coupons::totalposts($users_data['id']);
                    $posts_text  = main::s($posts, " Post");
                    $mposts      = $this->getMonthly($users_data['pid'], $users_data['id']);
                    $mposts_text = main::s($mposts, " post");
                    
                    $config_url_data = $dbh->select("p2h", array("forumname", "=", $forum));
                    $furl            = $config_url_data['value'];
					
                    // If the user hasn't posted enough yet
                    $grace_period    = $dbh->config("p2hgraceperiod"); //The grace period in days
                    $grace_period    = $grace_period * 24 * 60 * 60;
                    $userinfo        = $dbh->client($users_data['id']);
                    $signup_date     = $userinfo['signup'];
                    
                    if(date("m") != date("m", $signup_date + $grace_period)){
					
						//If they won't be suspended on this months check, then we don't need to warn them.
                        $no_email = 1;
                    
                    }

                    if($posts < $mposts && !$no_email){

                        $emaildata           = email::emailTemplate("p2h-low-post-warning");
                        $p2hwarning_array['USERPOSTS'] = $posts;
                        $p2hwarning_array['MONTHLY']   = $mposts;
                        $p2hwarning_array['URL']       = $furl;
						
                        // Warn the user that they still have some more posting to do!
                        email::send($users_data['email'], $emaildata['subject'], $emaildata['content'], $p2hwarning_array);
						
                        // Output to the cron.
                        echo "<strong>".$users_data['user']." (".$fuser['fuser']."):</strong> Warned for not yet posting the required monthly amount. ($posts_text posted out of $mposts_text/month)<br />";
                    
                    }

                }

            }

            // This prevents the post warnings from being sent again today/this month.
            $dbh->updateConfig("p2hcheck", $checkdate[0].":1:0");
        
        }

    }
	
    public function acpPage(){
        global $dbh, $postvar, $getvar, $instance;
        
        switch($getvar['do']){

            default:
                if($_POST){

                    check::empty_fields(array("prefix"));
                    if(!main::errors()){

                        $forumcon = $dbh->connect($postvar['hostname'], $postvar['username'], $postvar['password'], $postvar['database']);
                        if(is_string($forumcon)){

                            main::errors($forumcon);
                        
                        }else{

                            $forums_params = $this->forumdata($postvar['forumname']);
                            if($forums_params['id']){

                                main::errors("This forum name has already been used! Please choose a new one.<br>");
                            
                            }else{

								$p2h_insert = array("forumname" => $postvar['forumname'],
													"username"  => $postvar['username'],
													"password"  => $postvar['password'],
													"forumdb"   => $postvar['database'],
													"hostname"  => $postvar['hostname'],
													"prefix"    => $postvar['prefix'],
													"forumtype" => $postvar['forum'],
													"url"       => $postvar['url']);
													
								$dbh->insert("p2h", $p2h_insert);
                                main::errors("Your forum has been added!<br>");
                            
                            }

                        }

                    }

                }

                $manage_forums_array['CONTENT'] = style::replaceVar("tpl/admin/p2h/add-forum.tpl");
                break;
            
            case "edit":
                $forums_params = $this->forumdata();
                if($dbh->num_rows($forums_params) == 0){

                    $manage_forums_array['CONTENT'] = "There are no forums to edit!<br>";
                
                }else{

                    if($getvar['id']){

                        if($_POST){

                            check::empty_fields(array("password"));
                            if(!main::errors()){

                                $forumcon = $dbh->connect($postvar['hostname'], $postvar['username'], $postvar['password'], $postvar['database']);
                                if(is_string($forumcon)){

                                    main::errors($forumcon);
                                
                                }else{
								
                                    $forums_params = $this->forumdata($getvar['id']);
                                    if(!$forums_params['id']){

                                        main::errors("This forum name does not exist.<br>");
                                    
                                    }else{

										$p2h_update = array("forumname" => $postvar['forumname'],
															"username"  => $postvar['username'],
															"forumdb"   => $postvar['database'],
															"hostname"  => $postvar['hostname'],
															"prefix"    => $postvar['prefix'],
															"url"       => $postvar['url']);
															
										$dbh->update("p2h", $p2h_update, array("id", "=", $getvar['id']));
                                        
                                        if($postvar['password']){
																
											$dbh->update("p2h", array("password" => $postvar['password']), array("id", "=", $getvar['id']));
                                        
                                        }

                                        main::errors("Forum Edited!<br>");
                                    
                                    }

                                }

                            }

                        }

                        $forumdata         = $this->forumdata($getvar['id']);
						if(!$forumdata['id']){
						
							main::done();
						
						}
                        $edit_forum_array['HOST']   = $forumdata['hostname'];
                        $edit_forum_array['NAME']   = $forumdata['forumname'];
                        $edit_forum_array['URL']    = $forumdata['url'];
                        $manage_forums_array['CONTENT'] = style::replaceVar("tpl/admin/p2h/edit-forum.tpl", $edit_forum_array);
                    
                    }else{

                        $manage_forums_array['CONTENT'] .= "<ERRORS>";
                        while($forums_params_data = $dbh->fetch_array($forums_params)){

                            $manage_forums_array['CONTENT'] .= main::sub("<strong>".$forums_params_data['forumname']."</strong>", '<a href="?page=type&type=p2h&sub=forums&do=edit&id='.$forums_params_data['id'].'"><img src="'.URL.'themes/icons/pencil.png"></a>');
                        
                        }

                    }

                }

                break;
            
            case "delete":
                $forums_params = $this->forumdata();
                if($dbh->num_rows($forums_params) == 0){

                    $manage_forums_array['CONTENT'] = "There are no forums to delete!<br>";
                
                }else{

                    if($getvar['id']){

                        $dbh->delete("p2h", array("id", "=", $getvar['id']));
                        main::errors("Forum deleted!<br>");
                        $forums_params = $this->forumdata();
                    
                    }

                    $manage_forums_array['CONTENT'] .= "<ERRORS>";
                    while($forums_params_data = $dbh->fetch_array($forums_params)){

                        $manage_forums_array['CONTENT'] .= main::sub("<strong>".$forums_params_data['forumname']."</strong>", '<a href="?page=type&type=p2h&sub=forums&do=delete&id='.$forums_params_data['id'].'"><img src="'.URL.'themes/icons/delete.png"></a>');
                    
                    }

                }

                break;
            
            case "config":
                if($_POST){

                    check::empty_fields(array("password"));
                    if(!main::errors()){

                        if(!is_numeric($postvar['p2hwarndate']) || !($postvar['p2hwarndate'] < 28)){

                            main::errors("The P2H Warn date must be a number less than 28.<br>");
                        
                        }else{

                            $dbh->updateConfig("p2hwarndate", $postvar['p2hwarndate']);
                            main::errors("Configuration updated.<br>");
                        
                        }

                    }

                }

                $forum_config_array['WARNDATE'] = $dbh->config("p2hwarndate");
                $manage_forums_array['CONTENT']         = style::replaceVar("tpl/admin/p2h/forum-config.tpl", $forum_config_array);
                break;
        
        }

        echo style::replaceVar("tpl/admin/p2h/manage-forums.tpl", $manage_forums_array);
    
    }

    public function signup(){
        global $dbh, $postvar, $getvar, $instance;
		
        $fuser       = $postvar['type_fuser'];
        $forum       = type::additional($getvar['package'], 'forum');
        $this->con   = $this->forumCon($forum);
        $details     = $this->forumdata($forum);
        $users_query = $dbh->select("users");
        while($users_data = $dbh->fetch_array($users_query)){

            $pdetails = type::userAdditional($users_data['id']);
            if($pdetails['fuser'] == $fuser){

                $n++;
            
            }

        }

        if(!$n){

            switch($this->checkSignup($postvar['type_fuser'], $postvar['type_fpass'], $getvar['package'], $postvar['coupon'])){

                case 1:
                    $postvar['type_fpass'] = 0;
                    break;
                
                case 0:
                    $neededPosts = (int) $this->getSignup($getvar['package'], $postvar['coupon']);
                    $s           = "s";
                    if($neededPosts === 1){

                        $s = "";
                    
                    }

                    return "You haven't posted enough to be eligible for this package. You'll need at least $neededPosts post$s.";
                    break;
                
                case 3:
                    return "The provided username <em>".$fuser."</em> does not exist.";
                    break;
                
                case 4:
                    return "The provided password does not match the username.";
                    break;
            
            }

        }else{

            return "That forum username is already used!";
        
        }

    }

    public function acpBox(){
        global $dbh, $postvar, $getvar, $instance;
		
        $box[0]    = "Forum Posting:<br />";
        $user      = $getvar['do'];
        $client    = $dbh->client($user);
        $forum     = type::additional($client['pid'], 'forum');
        $user      = type::userAdditional($client['id']);
        $fdetails  = $this->forumdata($forum);
        $this->con = $this->forumCon($forum);
        $posts     = $this->checkMonthly($fdetails['forumtype'], $user['fuser'], $fdetails['prefix']);
        $box[1]    = $posts." (".$this->getMonthly($client['pid'], $client['id'])." Needed)<br />Forum Username: ".$user['fuser'];
        return $box;
    
    }

    public function clientBox(){
        global $dbh, $postvar, $getvar, $instance;
		
        $box[0]    = "Forum Posting:<br />";
        $user      = $_SESSION['cuser'];
        $client    = $dbh->client($user);
        $forum     = type::additional($client['pid'], 'forum');
        $user      = type::userAdditional($client['id']);
        $fdetails  = $this->forumdata($forum);
        $this->con = $this->forumCon($forum);
        $posts     = $this->checkMonthly($fdetails['forumtype'], $user['fuser'], $fdetails['prefix']);
        $box[1]    = $posts." (".$this->getMonthly($client['pid'])." Needed)<br />Forum Username: ".$user['fuser'];
        return $box;
    
    }

    public function clientPage(){
        global $dbh, $postvar, $getvar, $instance;
        
        if(is_numeric($getvar['remove'])){

            coupons::remove_p2h_coupon($getvar['remove']);
            main::redirect("?page=type&type=p2h&sub=forums");
            exit;
        
        }

        if($_POST['submitaddcoupon']){

            if(!$postvar['addcoupon']){

                main::errors("Please enter a coupon code.");
            
            }else{

                $coupcode      = $postvar['addcoupon'];
                $pack_data     = main::uidtopack();
                $packid        = $pack_data['packages']['id'];
                $multi_coupons = $dbh->config("multicoupons");
                
                $coupon_info = coupons::coupon_data($coupcode);
                $coupid      = $coupon_info['id'];
                
                $use_coupon = coupons::use_coupon($coupid, $packid);
                if(!$use_coupon){

                    if(!$multi_coupons){

                        main::errors("Coupon code entered was invalid or you're already using a coupon.");
                    
                    }else{

                        main::errors("Coupon code entered was invalid.");
                    
                    }

                }else{

                    main::redirect("?page=type&type=p2h&sub=forums");
                
                }

            }

        }

        $userid       = $_SESSION['cuser'];
        $client       = $dbh->client($userid);
        $forum        = type::additional($client['pid'], 'forum');
        $user         = type::userAdditional($client['id']);
        $fdetails     = $this->forumdata($forum);
        $this->con    = $this->forumCon($forum);
        $posts        = $this->checkMonthly($fdetails['forumtype'], $user['fuser'], $fdetails['prefix']);
        $total_posts  = coupons::totalposts($userid);
        $p2h_payments = $dbh->select("coupons_p2h", array("uid", "=", $userid));
        $package_info = main::uidtopack($userid);
        $user_posts   = $this->userposts($package_info['packages']['id'], $package_info['user_data']['id']);
        $monthly      = $this->getMonthly($client['pid']);
        
        if(empty($p2h_payments)){

            $p2h_pay_array = array(
                "uid"      => $userid,
                "amt_paid" => $user_posts,
                "txn"      => $package_info['uadditional']['fuser'],
                "datepaid" => time(),
                "gateway"  => $package_info['additional']['forum']
            );
            
            $dbh->insert("coupons_p2h", $p2h_pay_array);
            $p2h_payments = $dbh->select("coupons_p2h", array("uid", "=", $userid));
        
        }

        $amt_paid = $p2h_payments['amt_paid'];
        $txn      = $p2h_payments['txn'];
        $datepaid = $p2h_payments['datepaid'];
        $gateway  = $p2h_payments['gateway'];
        
        $amt_paid = explode(",", $amt_paid);
        $txn      = explode(",", $txn);
        $datepaid = explode(",", $datepaid);
        $gateway  = explode(",", $gateway);
        
        for($i = 0; $i < count($amt_paid); $i++){

            if($txn[$i] == $package_info['uadditional']['fuser']){

                if($amt_paid[$i] != $user_posts){

                    $reload = 1;
                
                }

                $amt_paid[$i] = $user_posts;
                $datepaid[$i] = time();
            
            }

			$p2h_data = $dbh->select("p2h", array("id", "=", $gateway[$i]));
			
            $transaction_list_array['PAIDAMOUNT'] = main::s($amt_paid[$i], " Post");
            $transaction_list_array['TXN']        = $txn[$i];
            $transaction_list_array['PAIDDATE']   = main::convertdate("n/d/Y", $datepaid[$i]);
            $transaction_list_array['GATEWAY']    = str_replace(",", "", $p2h_data['forumname']);
            $invoice_transactions_array['TXNS'] .= style::replaceVar("tpl/invoices/transaction-list.tpl", $transaction_list_array);
            
            $paidamts    = $paidamts.",".$amt_paid[$i];
            $paidtxn     = $paidtxn.",".$txn[$i];
            $paiddate    = $paiddate.",".$datepaid[$i];
            $paidgateway = $paidgateway.",".$gateway[$i];
        
        }

        $paidamts    = substr($paidamts, 1, strlen($paidamts));
        $paidtxn     = substr($paidtxn, 1, strlen($paidtxn));
        $paiddate    = substr($paiddate, 1, strlen($paiddate));
        $paidgateway = substr($paidgateway, 1, strlen($paidgateway));
        
        $p2h_pay_array = array(
            "amt_paid" => $paidamts,
            "txn"      => $paidtxn,
            "datepaid" => $paiddate,
            "gateway"  => $paidgateway
        );
        
        $where[] = array("uid", "=", $userid);
        $dbh->update("coupons_p2h", $p2h_pay_array, $where);
        if($reload){

            main::redirect("?page=type&type=p2h&sub=forums");
        
        }

        $invoice_transactions_array['TOTALPAID']    = main::s($total_posts, " Post");
        $posts_array['TRANSACTIONS'] = style::replaceVar("tpl/invoices/invoice-transactions.tpl", $invoice_transactions_array);
        
        $pack_monthly = $package_info['additional']['monthly'];
        $coupon_total = $pack_monthly - coupons::get_discount("p2hmonthly", $pack_monthly, $userid);
        
        $balance = max(0, $monthly - $total_posts);
        
        unset($where);
        $where[]            = array("user", "=", $_SESSION['cuser'], "AND");
        $where[]            = array("disabled", "=", "0");
        $coupons_used_query = $dbh->select("coupons_used", $where, array("id", "ASC"), 0, 1);
        while($coupons_used_data = $dbh->fetch_array($coupons_used_query)){

            $valid_coupon = coupons::check_expire($coupons_used_data['coupcode']);
            if($valid_coupon){

                $multipost_text = main::s($coupons_used_data['p2hmonthlydisc'], " Post");
                
                $coupons_list_array['COUPONAMOUNT'] = $multipost_text;
                $coupons_list_array['COUPCODE']     = $coupons_used_data['coupcode'];
                $coupons_list_array['REMOVE']       = $balance == 0 ? "" : '(<a href = "?page=type&type=p2h&sub=forums&remove='.$coupons_used_data['id'].'">Remove</a>)';
                $posts_array['COUPONSLIST'] .= style::replaceVar("tpl/client/coupons/coupons-list.tpl", $coupons_list_array);
            
            }

        }

        if(!$posts_array['COUPONSLIST']){

            $posts_array['COUPONSLIST'] = "<tr><td></td><td align = 'center'>None</td></tr>";
        
        }

        if($total_posts >= $monthly){

            $postedcolour = "#779500";
        
        }else{

            $postedcolour = "#FF7800";
        
        }

        if($balance == "0"){

            $posts_array['ADDCOUPONS'] = "";
            $posts_array['PAIDSTATUS'] = "<font color = '#779500'>Paid</font>";
        
        }else{

            $posts_array['ADDCOUPONS'] = style::replaceVar("tpl/client/coupons/add-coupons.tpl");
            $posts_array['PAIDSTATUS'] = "<font color = '#FF7800'>Unpaid</font>";
        
        }

        $posts_array['POSTEDCOLOUR'] = $postedcolour;
        $posts_array['BASEAMOUNT']   = main::s($pack_monthly, " Post");
        $posts_array['COUPONTOTAL']  = main::s($coupon_total, " Post");
        $posts_array['USERPOSTED']   = main::s(str_replace("-", "−", $total_posts), " Post");
        $posts_array['TOTALAMOUNT']  = main::s($balance, " Post");
        
        echo style::replaceVar("tpl/client/coupons/posts.tpl", $posts_array);
    
    }

	// Returns the required signup posts for a package
    private function getSignup($id, $coupcode){
		
        $type_additional            = type::additional($id);
        $coupon_data                = coupons::coupon_data($coupcode);
        $coupon_data['p2hinitdisc'] = coupons::percent_to_value("p2h", $coupon_data['p2hinittype'], $coupon_data['p2hinitdisc'], $type_additional['signup']);
        $type_additional['signup']             = max(0, $type_additional['signup'] - $coupon_data['p2hinitdisc']);
        return $type_additional['signup'];
    
    }

	// Returns the monthly required posts for a package
    private function getMonthly($id, $user = ""){

        $type_additional = type::additional($id);
        
        if(!$user){

            $user = $_SESSION['cuser'];
        
        }

        if(!is_numeric($user)){

            $user = main::userid($user);
        
        }

        $type_additional['monthly'] = coupons::get_discount("p2hmonthly", $type_additional['monthly'], $user);
        return $type_additional['monthly'];
    
    }

	// Returns how many posts the user has had in a month.
    private function checkMonthly($forum, $fuser, $prefix){
        global $dbh, $postvar, $getvar, $instance;
		
        $nmonth = date("m");
        $nyear  = date("y");
		
        $n = 0;
        
        switch($forum){

            case "ipb":
                
                $ipb_members_data = $dbh->select($prefix."members", array("name", "=", $fuser), 0, 0, 0, $this->con);
                $ipb_posts_query  = $dbh->select($prefix."posts", array("author_name", "=", $ipb_members_data['members_display_name']), 0, 0, 1, $this->con);
                
                while($ipb_posts_data = $dbh->fetch_array($ipb_posts_query)){

                    $date = explode(":", main::convertdate("m:y", $ipb_posts_data['post_date']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                }

                break;
            case "ipb3":
                $ipb3_members_data = $dbh->select($prefix."members", array("name", "=", $fuser), 0, 0, 0, $this->con);
                $ipb3_posts_query  = $dbh->select($prefix."posts", array("author_name", "=", $ipb3_members_data['members_display_name']), 0, 0, 1, $this->con);
                
                while($ipb3_posts_data = $dbh->fetch_array($ipb3_posts_query)){

                    $date = explode(":", main::convertdate("m:y", $ipb3_posts_data['post_date']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                }

                break;
            
            case "mybb":
                $mybb_posts_query = $dbh->select($prefix."posts", array("username", "=", $fuser), 0, 0, 1, $this->con);
                
                while($mybb_posts_data = $dbh->fetch_array($mybb_posts_query)){

                    $date = explode(":", main::convertdate("m:y", $mybb_posts_data['dateline']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                }

                break;
            
            case "phpbb":
                $phpbb_users_data  = $dbh->select($prefix."users", array("username", "=", $fuser), 0, 0, 0, $this->con);
                $phpbb_posts_query = $dbh->select($prefix."posts", array("poster_id", "=", $phpbb_users_data['user_id']), 0, 0, 1, $this->con);
                
                while($phpbb_posts_data = $dbh->fetch_array($phpbb_posts_query)){

                    $date = explode(":", main::convertdate("m:y", $phpbb_posts_data['post_time']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                }

                break;
            
            case "phpbb2":
                $phpbb2_users_data  = $dbh->select($prefix."users", array("username", "=", $fuser), 0, 0, 0, $this->con);
                $phpbb2_posts_query = $dbh->select($prefix."posts", array("poster_id", "=", $phpbb2_users_data['user_id']), 0, 0, 1, $this->con);
                
                while($phpbb2_posts_data = $dbh->fetch_array($phpbb2_posts_query)){

                    $date = explode(":", main::convertdate("m:y", $phpbb2_posts_data['post_time']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                }

                break;
            
            case "vb":
                $vb_post_query = $dbh->select($prefix."post", array("username", "=", $fuser), 0, 0, 1, $this->con);
                
                while($vb_post_data = $dbh->fetch_array($vb_post_query)){

                    $date = explode(":", main::convertdate("m:y", $vb_post_data['dateline']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                }

                break;
            
            case "smf":
                $smf_messages_query = $dbh->select($prefix."messages", array("posterName", "=", $fuser), 0, 0, 1, $this->con);
                
                while($smf_messages_data = $dbh->fetch_array($smf_messages_query)){

                    $date = explode(":", main::convertdate("m:y", $smf_messages_data['posterTime']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                }

                break;
            
            case "aef":
                $aef_users_data  = $dbh->select($prefix."users", array("username", "=", $fuser), 0, 0, 0, $this->con);
                $aef_posts_query = $dbh->select($prefix."posts", array("poster_id", "=", $aef_users_data['id']), 0, 0, 1, $this->con);
                
                while($aef_posts_data = $dbh->fetch_array($aef_posts_query)){

                    $date = explode(":", main::convertdate("m:y", $aef_posts_data['ptime']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                }

                break;
            
            case "drupal":
                $drupal_users_data = $dbh->select($prefix."users", array("name", "=", $fuser), 0, "1", 0, $this->con);
                
                unset($where);
                $where[]           = array("type", "=", "forum", "AND");
                $where[]           = array("uid", "=", $drupal_users_data["uid"]);
                $drupal_node_query = $dbh->select($prefix."node", $where, 0, 0, 1, $this->con);
                while($drupal_node_data = $dbh->fetch_array($drupal_node_query)){

                    $date = explode(":", main::convertdate("m:y", $drupal_node_data['created']));
                    if($nmonth == $date[0] && $nyear == $date[1]){

                        $n++;
                    
                    }

                    unset($where);
                    $where[]               = array("nid", "=", $drupal_node_data["nid"], "AND");
                    $where[]               = array("uid", "=", $drupal_users_data["uid"]);
                    $drupal_comments_query = $dbh->select($prefix."comments", $where, 0, 0, 1, $this->con);
                    
                    unset($comments);
                    while($drupal_comments_data = $dbh->fetch_array($drupal_comments_query)){

                        $date = explode(":", main::convertdate("m:y", $drupal_comments_data['timestamp']));
                        if($nmonth == $date[0] && $nyear == $date[1]){

                            $n++;
                        
                        }

                    }

                }

                break;
        
        }

        return $n;
    
    }

    // This function is used to check a forum user when they signup.
    public function checkSignup($fuser, $fpass, $package, $coupon){
        global $dbh, $postvar, $getvar, $instance;

        // Gets the number of posts the user needs to signup				
        $signup = $this->getSignup($package, $coupon);        		
		
		$forumid   = type::additional($package, 'forum');		
        $this->con = $this->forumCon($forumid);
		
        $details   = $this->forumdata($forumid);		
		$forumtype = $details['forumtype'];
		$prefix    = $details['prefix'];
		
        //RETURN CODES:
        //
        //0 = Post count is insufficient
        //1 = Post count is sufficient
        //3 = User doesn't exist
        //4 = Bad login
		
        switch($forumtype){

            case "ipb":
                
                $ipb_members_data          = $dbh->select($prefix."members", array("name", "=", $fuser), 0, 0, 0, $this->con, 1);
                $ipb_members_converge_data = $dbh->select($prefix."members_converge", array("converge_email", "=", $ipb_members_data['email']), 0, 0, 0, $this->con);
                
                if($ipb_members_data['name']){

                    if(md5(md5($ipb_members_converge_data['converge_pass_salt']).md5($fpass)) == $ipb_members_converge_data['converge_pass_hash']){

                        if($signup <= $ipb_members_data['posts']){

                            return 1;
                        
                        }

                        else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                
                }

                break;
            
            case "ipb3":
                $ipb3_members_data = $dbh->select($prefix."members", array("name", "=", $fuser), 0, 0, 0, $this->con);
                
                if($ipb3_members_data['name']){

                    if(md5(md5($ipb3_members_data['members_pass_salt']).md5($fpass)) == $ipb3_members_data['members_pass_hash']){

                        if($signup <= $ipb3_members_data['posts']){

                            return 1;
                        
                        }

                        else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                
                }

                break;
            
            case "mybb":
                
                $mybb_users_data = $dbh->select($prefix."users", array("username", "=", $fuser), 0, 0, 0, $this->con);
                
                if($mybb_users_data['username']){

                    if(md5(md5($mybb_users_data['salt']).md5($fpass)) == $mybb_users_data['password']){

                        if($signup <= $mybb_users_data['postnum']){

                            return 1;
                        
                        }else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                    
                }

                break;
            
            case "phpbb":
			
                $phpbb_users_data = $dbh->select($prefix."users", array("username", "=", $fuser), 0, 0, 0, $this->con);
                
                if($phpbb_users_data['username']){

                    if($this->phpbb_check_hash($fpass, $phpbb_users_data['user_password'])){

                        $phpbb_posts_query = $dbh->select($prefix."posts", array("poster_id", "=", $phpbb_users_data['user_id']), 0, 0, 1, $this->con);
                        if($signup <= $dbh->num_rows($phpbb_posts_query)){

                            return 1;
                        
                        }else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                    
                }

                break;
            
            case "phpbb2":
			
                $phpbb2_users_data = $dbh->select($prefix."users", array("username", "=", $fuser), 0, 0, 0, $this->con);
                
                if($phpbb2_users_data['username']){

                    if(md5($fpass) == $phpbb2_users_data['user_password']){

                        if($signup <= $phpbb2_users_data['user_posts']){

                            return 1;
                        
                        }else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                    
                }

                break;
            
            case "vb":
			
                $vb_user_data = $dbh->select($prefix."user", array("username", "=", $fuser), 0, 0, 0, $this->con);
				
                if($vb_user_data['username']){

                    if(md5(md5($fpass).$vb_user_data['salt']) == $vb_user_data['password']){

                        if($signup <= $vb_user_data['posts']){

                            return 1;
                        
                        }else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                    
                }

                break;
            
            case "smf":
			
                $smf_members_data = $dbh->select($prefix."members", array("memberName", "=", $fuser), 0, 0, 0, $this->con);
				
                if($smf_members_data['memberName']){

                    if(sha1(strtolower($smf_members_data['memberName']).$fpass) == $smf_members_data['passwd']){

                        if($signup <= $smf_members_data['posts']){

                            return 1;
                        
                        }else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                    
                }

                break;
            
            case "aef":
			
                $aef_users_data = $dbh->select($prefix."users", array("username", "=", $fuser), 0, 0, 0, $this->con);
				
                if($aef_users_data['username']){

                    if(md5($aef_users_data['salt'].$fpass) == $aef_users_data['password']){

                        if($signup <= $aef_users_data['posts']){

                            return 1;
                        
                        }else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                    
                }

                break;
            
            case "drupal":
                
                $drupal_users_data = $dbh->select($prefix."users", array("name", "=", $fuser), 0, "1", 0, $this->con);
                
                if($drupal_users_data['name']){

                    if(md5($fpass) == $drupal_users_data['pass']){

                        $uid         = $drupal_users_data['uid'];
                        $drupalPosts = 0;
                        
                        unset($where);
                        $where[]           = array("type", "=", "forum", "AND");
                        $where[]           = array("uid", "=", $uid);
                        $drupal_node_query = $dbh->select($prefix."node", $where, 0, 0, 1, $this->con);
                        
                        $drupalPosts = $dbh->num_rows($drupal_node_query);
                        while($drupal_node_data = $dbh->fetch_array($drupal_node_query)){

                            unset($where);
                            $where[]               = array("nid", "=", $drupal_node_data["nid"], "AND");
                            $where[]               = array("uid", "=", $uid);
                            $drupal_comments_query = $dbh->select($prefix."comments", $where, 0, 0, 1, $this->con);
                            $drupalPosts           = $drupalPosts + $dbh->num_rows($drupal_comments_query);
                            
                        }

                        if($signup <= $drupalPosts){

                            return 1;
                        
                        }else{

                            return 0;
                        
                        }

                    }else{

                        return 4;
                    
                    }

                }else{

                    return 3;
                    
                }

                break;
        
        }

    }

	// Returns a forum connection
    private function forumCon($id){
        global $dbh, $postvar, $getvar, $instance;
		
        $forum_conn_details = $this->forumdata($id);
        $forumcon = $dbh->connect($forum_conn_details['hostname'], $forum_conn_details['username'], $forum_conn_details['password'], $forum_conn_details['forumdb']);
        if(is_string($forumcon)){

            $error_array['Error']    = $forumcon;
            $error_array['Forum ID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            return $forumcon;
        
        }

    }

	// Returns the query for the forums in config table
    private function forumdata($id=0){
        global $dbh, $postvar, $getvar, $instance;
		
        if($id){

            return $dbh->select("p2h", array("id", "=", $id), array("forumname", "DESC"));
        
        }else{

            return $dbh->select("p2h");
        
        }

    }

	//Uses various packages to find the credentials to the forum the user could have posted in.  It first checks the package specified (the
	//package the user is upgrading to usually) and then if that fails, it checks the current package the user is on.  This way we know if the
	//user has an outstanding balance when upgrading, or we can see the user's current post counts.  If the user isn't on a p2h package or
	//the user isn't upgrading to one, then we just state that the post count is 0 as it doesn't matter if they have any posts or not.
    public function userposts($packid, $uid){
        global $dbh, $postvar, $getvar, $instance;
        		
		if($postvar['fuser']){
		
			$fuser = $postvar['fuser'];
			
		}else{
		
			$user  = type::userAdditional($uid);
			$fuser = $user['fuser'];
			
		}
		
        $forum = type::additional($packid, 'forum');
		
		if(!$forum){
		
			$client = $dbh->client();
			$forum  = type::additional($client['pid'], 'forum');
		
		}
		
		if(!$forum){
		
			//As a last resort, we assume the user doesn't have any posts as we couldn't connect to the DB using their current package's
			//details or the one for upgrading to.  This stops fatal errors as well.			
			return 0;
		
		}
		
        $fdetails  = $this->forumdata($forum);
        $this->con = $this->forumCon($forum);
        $posts     = $this->checkMonthly($fdetails['forumtype'], $fuser, $fdetails['prefix']);

        return $posts;
    
    }

    ///////////////////////////////////////////
    // phpBB Password functions - All written by the phpBB team. All credit to them.
    // Why don't you use a salt?
    ///////////////////////////////////////////
    
    function phpbb_check_hash($password, $hash){

        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        if(strlen($hash) == 34){

            return ($this->_hash_crypt_private($password, $hash, $itoa64) === $hash) ? true : false;
        
        }

        return (md5($password) === $hash) ? true : false;
    
    }

    /**
     * Encode hash
     */
    function _hash_encode64($input, $count, &$itoa64){

        $output = '';
        $i      = 0;
        
        do{

            $value = ord($input[$i++]);
            $output .= $itoa64[$value & 0x3f];
            
            if($i < $count){

                $value |= ord($input[$i]) << 8;
            
            }

            $output .= $itoa64[($value >> 6) & 0x3f];
            
            if($i++ >= $count){

                break;
            
            }

            if($i < $count){

                $value |= ord($input[$i]) << 16;
            
            }

            $output .= $itoa64[($value >> 12) & 0x3f];
            
            if($i++ >= $count){

                break;
            
            }

            $output .= $itoa64[($value >> 18) & 0x3f];
        
        }
		while($i < $count);
        
        return $output;
    
    }

    /**
     * The crypt function/replacement
     */
    function _hash_crypt_private($password, $setting, &$itoa64){

        $output = '*';
        
        // Check for correct hash
        if(substr($setting, 0, 3) != '$H$'){

            return $output;
        
        }

        $count_log2 = strpos($itoa64, $setting[3]);
        
        if($count_log2 < 7 || $count_log2 > 30){

            return $output;
        
        }

        $count = 1 << $count_log2;
        $salt  = substr($setting, 4, 8);
        
        if(strlen($salt) != 8){

            return $output;
        
        }

        /**
         * We're kind of forced to use MD5 here since it's the only
         * cryptographic primitive available in all versions of PHP
         * currently in use.  To implement our own low-level crypto
         * in PHP would result in much worse performance and
         * consequently in lower iteration counts and hashes that are
         * quicker to crack (by non-PHP code).
         */
        if(PHP_VERSION >= 5){

            $hash = md5($salt.$password, true);
            do{

                $hash = md5($hash.$password, true);
            
            }
			while(--$count);
        
        }else{

            $hash = pack('H*', md5($salt.$password));
            do{

                $hash = pack('H*', md5($hash.$password));
            
            }
			while(--$count);
        
        }

        $output = substr($setting, 0, 12);
        $output .= $this->_hash_encode64($hash, 16, $itoa64);
        
        return $output;
    
    }

}

?>