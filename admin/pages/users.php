<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Clients
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();
    
}

class page{

    public $navtitle;
    public $navlist = array();
    
    public function __construct(){

        $this->navtitle  = "Clients Sub Menu";
        $this->navlist[] = array("Search Clients", "magnifier.png", "search");
        $this->navlist[] = array("Client Statistics", "book.png", "stats");
        $this->navlist[] = array("Admin Validate", "user_suit.png", "validate");
        $this->navlist[] = array("Approve Upgrades", "accept.png", "upgrade");
        $this->navlist[] = array("Import Clients", "group_go.png", "import");
        
    }

    public function description(){
        global $dbh, $postvar, $getvar, $instance;
		
        $latest_signup_query = $dbh->select("users", 0, array("signup", "DESC"));
        if($dbh->num_rows($latest_signup_query) != 0){

            $latest_signup_data = $dbh->fetch_array($latest_signup_query);
            $newest             = main::sub("Latest Signup:", $latest_signup_data['user']);
            
        }

        return "<strong>Clients</strong><br />
                This is the area where you can manage all your clients that have signed up for your service. You can perform a variety of tasks like suspend, terminate, email and also check up on their requirements and stats.".$newest;
        
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        switch($getvar['sub']){

            default:
			
				$this->ClientSearch();
                break;
            
            //Displays a list of users based on account status. - Used for Client Stats
            case "list":

				$this->ListClientStatus();
                break;
            
            case "stats":
				
				$this->ViewClientStatistics();
                break;
            
            case "validate":

				$this->ApproveClientSignups();
                break;
            
            case "upgrade":

				$this->ApproveClientUpgrades();
                break;
				
            case "import":
			
				$this->ImportClients();
                break;				
                
        }

    }
	
	///////////////////////////////////////////////////
	////
	///  Client List Page - For Client Statistics
	//
	//////////////////////////
	
	private function ListClientStatus(){
        global $dbh, $postvar, $getvar, $instance;
                
		$per_page = $getvar['limit'];
		$start    = $getvar['start'];
		$show     = $getvar['do'];
		
		if($postvar['show']){

			$show = $postvar['show'];
			
		}

		if(!$show){

			$show = "all";
			
		}

		if(!$per_page){

			$per_page = 10;
			
		}

		if(!$start){

			$start = 0;
			
		}

		if($show != "all"){

			$users_query = $dbh->select("users", array("status", "=", $show), array("user", "ASC"), $start.", ".$per_page, 1);
			
		}else{

			$users_query = $dbh->select("users", 0, array("user", "ASC"), $start.", ".$per_page, 1);
			
		}

		if($dbh->num_rows($users_query) == 0){

			$list_clients_array['CLIENTS'] = "";
			$list_clients_array['PAGING']  = "";
			main::errors("No accounts found.");
			
		}else{

			while($usersdata = $dbh->fetch_array($users_query)){

				$client_list_item_array['ID']    = $usersdata['id'];
				$client_list_item_array['USER']  = $usersdata['user'];
				$client_list_item_array['EMAIL'] = $usersdata['email'];
				$client_list_item_array['DATE']  = main::convertdate("m/d/Y", $usersdata['signup']);
				$list_clients_array['CLIENTS'] .= style::replaceVar("tpl/admin/clients/client-list-item.tpl", $client_list_item_array);
				
			}

		}

		if($start != 0){

			$back_page                = $start - $per_page;
			$list_clients_array['PAGING'] = '<a href="?page=users&sub=list&show='.$show.'&start='.$back_page.'&limit='.$per_page.'">BACK</a>';
			
		}

		$pages = ceil($dbh->num_rows($users_query) / $per_page);
		for($i = 1; $i <= $pages; $i++){

			$start_link = $per_page * ($i - 1);
			if($start_link == $start){

				$list_clients_array['PAGING'] .= "<b>".$i."</b>";
				
			}else{

				$list_clients_array['PAGING'] .= '<a href="?page=users&sub=list&show='.$show.'&start='.$start_link.'&limit='.$per_page.'">'.$i.'</a>';
				
			}

		}

		if(($start + $per_page) / $per_page < $pages && $pages != 1){

			$next_page = $start + $per_page;
			$list_clients_array['PAGING'] .= '<a href="?page=users&sub=list&show='.$show.'&start='.$next_page.'&limit='.$per_page.'">NEXT</a>';
			
		}

		echo style::replaceVar("tpl/admin/clients/list-clients.tpl", $list_clients_array);
	
	}
	
	///////////////////////////////////////////////////
	////
	///  Client Statistics Page
	//
	//////////////////////////
	
	private function ViewClientStatistics(){
        global $dbh, $postvar, $getvar, $instance;
	
		$total_users_query              = $dbh->select("users");
		$client_stats_array['CLIENTS'] = $dbh->num_rows($total_users_query);
		
		$active_users_query            = $dbh->select("users", array("status", "=", "1"), 0, 0, 1);
		$client_stats_array['ACTIVE'] = $dbh->num_rows($active_users_query);
		
		$suspended_users_query            = $dbh->select("users", array("status", "=", "2"), 0, 0, 1);
		$client_stats_array['SUSPENDED'] = $dbh->num_rows($suspended_users_query);
		
		$adminval_query               = $dbh->select("users", array("status", "=", "3"), 0, 0, 1);
		$client_stats_array['ADMIN'] = $dbh->num_rows($adminval_query);
		
		$init_payment_query             = $dbh->select("users", array("status", "=", "4"), 0, 0, 1);
		$client_stats_array['PAYMENT'] = $dbh->num_rows($init_payment_query);
		
		$confirm_query                 = $dbh->select("users", array("status", "=", "5"), 0, 0, 1);
		$client_stats_array['CONFIRM'] = $dbh->num_rows($confirm_query);
		
		$canceled_users_query             = $dbh->select("users", array("status", "=", "9"), 0, 0, 1);
		$client_stats_array['CANCELLED'] = $dbh->num_rows($canceled_users_query);
		
		echo style::replaceVar("tpl/admin/clients/client-stats.tpl", $client_stats_array);	
	
	}
	
	///////////////////////////////////////////////////
	////
	///  Client Sign Up Approval Page
	//
	//////////////////////////
	
	private function ApproveClientSignups(){
        global $dbh, $postvar, $getvar, $instance;
	
		if($getvar['do']){

			$client = $dbh->client($getvar['do']);
			
			if($getvar['accept'] == 1){

				if(server::approve($getvar['do'])){

					$emaildata = email::emailTemplate("client-account-approved");
					$dbh->update("users", array("status" => "1"), array("id", "=", $client['id']));
					email::send($client['email'], $emaildata['subject'], $emaildata['content']);
					main::errors("Account activated!");
					
				}

			}else{

				if(server::decline($getvar['do'])){

					main::errors("Account declined!");
					
				}

			}

		}

		$user_adminval_query = $dbh->select("users", array("status", "=", "3"), 0, 0, 1);
		if($dbh->num_rows($user_adminval_query) == 0){

			echo "No clients are awaiting validation!";
			
		}else{

			$tpl .= "<ERRORS>";
			while($user_adminval_data = $dbh->fetch_array($user_adminval_query)){

				$admin_validate_array['USER']     = $user_adminval_data['user'];
				$admin_validate_array['EMAIL']    = $user_adminval_data['email'];
				$admin_validate_array['DOMAIN']   = $user_adminval_data['domain'];
				$admin_validate_array['ID']       = $user_adminval_data['id'];
				$admin_validate_array['CLIENTID'] = $user_adminval_data['id'];
				$tpl .= style::replaceVar("tpl/admin/clients/admin-validate.tpl", $admin_validate_array);
				
			}

			echo $tpl;
			
		}	
	
	}
	
	///////////////////////////////////////////////////
	////
	///  Client Upgrade Approval Page
	//
	//////////////////////////
	
	private function ApproveClientUpgrades(){
        global $dbh, $postvar, $getvar, $instance;
	
		if(is_numeric($getvar['do'])){

			$upgrade_stubid    = $getvar['do'];
			$upgrade_stub_data = $dbh->select("upgrade", array("id", "=", $upgrade_stubid));
			$client            = $dbh->client($upgrade_stub_data['uid']);
			$user_data         = main::uidtopack($upgrade_stub_data['uid']);
			$new_pack_data     = $dbh->select("packages", array("id", "=", $upgrade_stub_data['newpack']));
			
			if($getvar['accept']){

				switch($upgrade_stub_data['flags']){

					case "2";
						
						$dbh->update("upgrade", array("flags" => "1"), array("id", "=", $upgrade_stubid), "1");
						if(upgrade::do_upgrade($upgrade_stubid, "Update", 1) === false){

							$dbh->update("upgrade", array("flags" => "2"), array("id", "=", $upgrade_stubid), "1");
							echo "<br><br>";
							
						}else{

							main::errors("The user has been prepared for their upgrade at the end of their current billing cycle.<br>");
							
						}

						break;
					
					case "3";
						$dbh->update("upgrade", array("flags" => "4"), array("id", "=", $upgrade_stubid), "1");
						if(upgrade::do_upgrade($upgrade_stubid, "Update", 1) === false){

							$dbh->update("upgrade", array("flags" => "3"), array("id", "=", $upgrade_stubid), "1");
							echo "<br><br>";
							
						}else{

							main::errors("The user has been prepared for their upgrade <font color = '#779500'>on the new server</font> at the end of their current billing cycle.<br>");
							
						}

						break;
					
					case "5";
						$dbh->update("upgrade", array("flags" => "0"), array("id", "=", $upgrade_stubid), "1");
						if(upgrade::do_upgrade($upgrade_stubid, "Update", 1) === false){

							$dbh->update("upgrade", array("flags" => "5"), array("id", "=", $upgrade_stubid), "1");
							echo "<br><br>";
							
						}else{

							main::errors("The user has been upgraded.<br>");
							
						}

						break;
					
					case "6";
						$dbh->update("upgrade", array("flags" => "7"), array("id", "=", $upgrade_stubid), "1");
						if(upgrade::do_upgrade($upgrade_stubid, "Update", 1) === false){

							$dbh->update("upgrade", array("flags" => "6"), array("id", "=", $upgrade_stubid), "1");
							echo "<br><br>";
							
						}else{

							main::errors("The user has been upgraded and is now <font color = '#779500'>on the new server</font>.  Please be sure to remove the account on the old server when the user has migrated their website.<br>");
							
						}

						break;
						
				}

			}else{

				$dbh->delete("upgrade", array("id", "=", $upgrade_stubid), "1");
				main::errors("The user's upgrade request has been denied.<br>");
				
				$deny_array['OLDPLAN'] = $user_data['packages']['name'];
				$deny_array['NEWPLAN'] = $new_pack_data['name'];
				$uemaildata            = email::emailTemplate("client-upgrade-denied");
				email::send($client['email'], $uemaildata['subject'], $uemaildata['content'], $deny_array);
				main::thtlog("Upgrade Denied", "Upgrade denied for ".$client['user']." <br><b>Current package: </b>".$user_data['packages']['name']." <br><b>Requested package: </b>".$new_pack_data['name'], $upgrade_stub_data['uid']);
				
			}

		}

		unset($where);
		$where[] = array("flags", "=", "2", "OR");
		$where[] = array("flags", "=", "3", "OR");
		$where[] = array("flags", "=", "5", "OR");
		$where[] = array("flags", "=", "6");
		
		$upgrade_req_query = $dbh->select("upgrade", $where, 0, 0, 1);
		if($dbh->num_rows($upgrade_req_query) == 0){

			echo "<ERRORS>No clients are awaiting upgrade approval.";
			
		}else{

			$tpl .= "<ERRORS>The users listed here have prequalified for upgrades, but admin approval was needed on the packages they selected.";
			while($upgrade_req_data = $dbh->fetch_array($upgrade_req_query)){

				$client        = $dbh->client($upgrade_req_data['uid']);
				$user_data     = main::uidtopack($upgrade_req_data['uid']);
				$new_pack_data = $dbh->select("packages", array("id", "=", $upgrade_req_data['newpack']));
				
				if($upgrade_req_data['flags'] == "2" || $upgrade_req_data['flags'] == "3"){

					$approve_upgrades_array['EFFECTIVE'] = "Next Billing Cycle";
					
				}else{

					$approve_upgrades_array['EFFECTIVE'] = "Immediately";
					
				}

				$approve_upgrades_array['USER']      = $client['user'];
				$approve_upgrades_array['EMAIL']     = $client['email'];
				$approve_upgrades_array['DOMAIN']    = $user_data['user_data']['domain'];
				$approve_upgrades_array['OLDPLAN']   = $user_data['packages']['name'];
				$approve_upgrades_array['NEWPLAN']   = $new_pack_data['name'];
				$approve_upgrades_array['NEWSERVER'] = $user_data['packages']['server'] != $new_pack_data['server'] ? "<font color = '#FF0055'>Yes</font>" : "<font color = '#779500'>No</font>";
				$approve_upgrades_array['ID']        = $upgrade_req_data['id'];
				$approve_upgrades_array['CLIENTID']  = $upgrade_req_data['uid'];
				$tpl .= style::replaceVar("tpl/admin/upgrades/approve-upgrades.tpl", $approve_upgrades_array);
				
			}

			echo $tpl;
			
		}	
	
	}
	
	///////////////////////////////////////////////////
	////
	///  Client Import Page
	//
	//////////////////////////
	
	private function ImportClients(){
        global $dbh, $postvar, $getvar, $instance;
	
		$files = main::folderFiles(INC."/import/");
		foreach($files as $value){

			$filename_exp = explode(".", $value);			
			include(INC."/import/".$value);
			
			$import_types[$filename_exp[0]] = new $filename_exp[0];
			
			$server_type = $import_types[$filename_exp[0]]->server;
			$server_exists = $dbh->select("servers", array("type", "=", $server_type), 0, "1");
			
			if(!$server_type || ($server_type && $server_exists)){
			
				$values[] = array($import_types[$filename_exp[0]]->name, $filename_exp[0]);			
			
			}
			
		}

		if(!$getvar['do']){

			if($_POST){

				main::redirect("?page=users&sub=import&do=".$postvar['do']);
			
			}

			$import_array['DROPDOWN'] = main::dropdown("do", $values);
			echo style::replaceVar("tpl/admin/import/import.tpl", $import_array);
		
		}else{

			if($import_types[$getvar['do']]){

				$import_types[$getvar['do']]->import();
			
			}else{

				echo "That method doesn't exist.";
			
			}

		}	
	
	}
	
	///////////////////////////////////////////////////
	////
	///  Client Search Page
	//
	//////////////////////////	
	
	private function ClientSearch(){
        global $dbh, $postvar, $getvar, $instance;
	
		if($getvar['do']){

			if($postvar['submitnewpack']){

				$this->ChangeClientPackage();

			}

			$client = $dbh->client($getvar['do']);
			$client_view_array['ID'] = $client['id'];
			$link = "?page=users&sub=search&do=".$getvar['do']."&func=";
			
			switch($client['status']){

				default:
					$client_view_array['TEXT'] = "Other Status";
					$client_view_array['LINK'] = "";
					$client_view_array['IMG']  = "help.png";
					break;
				
				case '1':
					$client_view_array['TEXT'] = "Suspend";
					$client_view_array['LINK'] = $link."sus";
					$client_view_array['IMG']  = "exclamation.png";
					break;
				
				case '2':
					$client_view_array['TEXT'] = "Unsuspend";
					$client_view_array['LINK'] = $link."unsus";
					$client_view_array['IMG']  = "accept.png";
					break;
				
				case '3':
					$client_view_array['TEXT'] = "Validate";
					$client_view_array['LINK'] = "?page=users&sub=validate";
					$client_view_array['IMG']  = "user_suit.png";
					break;
				
				case '4':
					$client_view_array['TEXT'] = "Awaiting Payment";
					$client_view_array['LINK'] = "";
					$client_view_array['IMG']  = "money.png";
					break;
				
				case '5':
					$client_view_array['TEXT'] = "Awaiting Email Confirmation";
					$client_view_array['LINK'] = $link."confirm";
					$client_view_array['IMG']  = "email.png";
					break;
				
				case '9':
					$client_view_array['TEXT'] = "No Action";
					$client_view_array['LINK'] = "";
					$client_view_array['IMG']  = "cancel.png";
					break;
					
			}
			
			switch($getvar['func']){

				default:

					if(!$client_view_array['CONTENT']){

						$client_view_array = array_merge($client_view_array, $this->ViewClient($client));
					
					}
					
					break;
			
				case "sus":
					
					$response = $this->SuspendClient($client);
					if($response){
					
						$client_view_array = array_merge($client_view_array, $response);
					
					}
					
					break;
				
				case "unsus":

					$this->UnsuspendClient($client);
					break;
				
				case "cancel":

					$response = $this->CancelClient($client);
					if($response){
					
						$client_view_array = array_merge($client_view_array, $response);
					
					}
					
					break;
				
				case "term":
					
					$response = $this->TerminateClient($client);
					if($response){
					
						$client_view_array = array_merge($client_view_array, $response);
					
					}
					
					break;
				
				case "email":

					$client_view_array = array_merge($client_view_array, $this->EmailClient($client));
					break;
					
				case "passwd":

					$client_view_array = array_merge($client_view_array, $this->ChangePassword($client));
					break;
		
				case 'freeuser':
				
					$this->MakeFreeClient($client);
					break;
		
				case 'confirm':
		
					$this->ConfirmClientEmail($client);
					break;
					
				case "loginas":
					
					$this->LoginAsClient($client);
					break;
					
			}

			$client_view_array["URL"]  = URL;
			$client_view_array['USER'] = $client['user'];	
					
			$freeuser = 0;
			
			if($client['freeuser']){
			
				$freeuser = 1;
			
			}

			if($freeuser){
			
				$client_view_array['FREE_USER'] = "no";
				$client_view_array['NON_FREE']  = "Non-";
			
			}else{
			
				$client_view_array['FREE_USER'] = "yes";
				$client_view_array['NON_FREE']  = "";
			
			}
			
			echo style::replaceVar("tpl/admin/clients/client-view.tpl", $client_view_array);
			
		}else{

			$client_search_array['URL'] = $dbh->config("url");
			echo style::replaceVar("tpl/admin/clients/client-search.tpl", $client_search_array);
			
		}	
	
	}
	
	private function ChangeClientPackage(){
        global $dbh, $postvar, $getvar, $instance;
	
		$new_pack    = $postvar['newpackage'];
		$immediately = $postvar['immediately'];
		$userid      = $getvar['do'];
		if(is_numeric($new_pack) && is_numeric($userid)){

			$upack_info = main::uidtopack($userid);
			if($upack_info['packages']['id'] == $new_pack){

				main::errors("The user is already on the package specified.  Please choose a different package if you wish to change their package.");
				
			}else{

				$new_pack_info = $dbh->select("packages", array("id", "=", $new_pack));
				
				if($new_pack_info['server'] != $upack_info['packages']['server']){

					$new_server = 1;
					
				}

				if(!$immediately || $new_pack_info['type'] == "p2h"){

					$response = upgrade::prorate($new_pack, "", $userid, 1);
					if($response == "now"){

						$immediately = 1;
						
					}

					if(substr_count($response, "check")){

						$no_upgrade = 1;
						
					}

				}

				if($immediately){

					if($new_server){

						$flags   = "7";
						$message = "The user has been upgraded and is now <font color = '#779500'>on the new server</font>.  Please be sure to remove the account on the old server when the user has migrated their website.";
						
					}else{

						$flags   = "0";
						$message = "The user has been upgraded.";
						
					}

				}else{

					if($new_server){

						$flags   = "4";
						$message = "The user has been prepared for their upgrade <font color = '#779500'>on the new server</font> at the end of their current billing cycle.";
						
					}else{

						$flags   = "1";
						$message = "The user has been prepared for their upgrade at the end of their current billing cycle.";
						
					}

				}

				if($no_upgrade){

					main::errors("The user cannot be changed to a P2H package until they have entered their credentials.  To do this, have the user log in and try to upgrade to the P2H package.  If the upgrade fails, the credentials are saved and you'll be able to upgrade them using this method.  If the upgrade succeeds, you don't need to do anything.  If the upgrade requires your approval, you'll be notified via email.");
					
				}else{

					$existing_upgrade = $dbh->select("upgrade", array("uid", "=", $userid));
					if($existing_upgrade){

						$upgrade_update = array(
							"created" => time(),
							"newpack" => $new_pack,
							"flags"   => $flags
						);
						
						$dbh->update("upgrade", $upgrade_update, array("id", "=", $existing_upgrade['id']), "1");
						
					}else{

						$upgrade_insert = array(
							"created" => time(),
							"newpack" => $new_pack,
							"flags"   => $flags,
							"uid"     => $userid
						);
						
						$dbh->insert("upgrade", $upgrade_insert);
						
					}

					$existing_upgrade = $dbh->select("upgrade", array("uid", "=", $userid));
					
					$done = upgrade::do_upgrade($existing_upgrade['id'], "Update", 1);
					if($done === false){

						$dbh->delete("upgrade", array("id", "=", $existing_upgrade['id']), "1");
						echo "<br><br>";
						
					}else{

						main::errors($message);
						
					}

				}

			}

		}	
	
	}
	
	///////////////////////////////////////////////////
	////
	///  Client View You Screwed Me Functions
	//
	//////////////////////////
	
	private function SuspendClient($client){
        global $dbh, $postvar, $getvar, $instance;
	
		if(!$postvar['submitreason']){

			$reason_array['WARNTEXT']   = 'Please state your reason for suspending this client. Leave it blank if you just feel like suspending them for the fun of it.';
			$reason_array['ACTION']     = 'suspending';
			$reason_array['ACTIONBUTT'] = 'Suspend Client';
			
			$clientview_array['BOX']     = "";
			$clientview_array['CONTENT'] = style::replaceVar("tpl/admin/clients/reason.tpl", $reason_array);
			return $clientview_array;
			
		}else{

			$command = server::suspend($client['id'], $postvar['reason']);
			
			if($command === true){

				main::redirect("?page=users&sub=search&do=".$client['id']);
				
			}else{

				main::errors($command);
				
			}

		}	
	
	}
	
	private function UnsuspendClient($client){
        global $dbh, $postvar, $getvar, $instance;
	
		$command = server::unsuspend($client['id']);
		if($command == true){

			main::redirect("?page=users&sub=search&do=".$client['id']);
			
		}else{

			main::errors($command);
			
		}	
	
	}
	
	private function CancelClient($client){
        global $dbh, $postvar, $getvar, $instance;
	
		if(!$postvar['submitreason']){

			$reason_array['WARNTEXT']   = 'Why are you cancelling this account? Leave blank if you do not wish to provide a reason or just want to see what the client does when their account gets cancelled.';
			$reason_array['ACTION']     = 'cancelling';
			$reason_array['ACTIONBUTT'] = 'Cancel Client';
			
			$clientview_array['BOX']     = "";
			$clientview_array['CONTENT'] = style::replaceVar("tpl/admin/clients/reason.tpl", $reason_array);
			return $clientview_array;
			
		}else{

			$command = server::cancel($client['id'], $postvar['reason']);
			
			if($command == true){

				//Cancelled
				main::done();
				
			}else{

				main::errors($command);
				
			}

		}	
	
	}
	
	private function TerminateClient($client){
        global $dbh, $postvar, $getvar, $instance;
	
		if(!$postvar['submitreason']){

			$client_uname = main::uname($client['id']);
			
			$reason_array['WARNTEXT']   = 'CAUTION: If you proceed, the account "'.$client_uname.'" will be completely and irrevocably removed from the server and THT.<br><br>Why are you terminating this account? Leave blank if you just feel like terminating them.';
			$reason_array['ACTION']     = 'terminating';
			$reason_array['ACTIONBUTT'] = 'Terminate Client';
			
			$clientview_array['BOX']     = "";
			$clientview_array['CONTENT'] = style::replaceVar("tpl/admin/clients/reason.tpl", $reason_array);
			return $clientview_array;
			
		}else{

			$command = server::terminate($client['id'], $postvar['reason']);
			
			if($command == true){

				//Terminated
				main::done();
				
			}else{

				main::errors($command);
				
			}

		}	
	
	}
	
	///////////////////////////////////////////////////
	////
	///  Client View Friendly Functions
	//
	//////////////////////////
	
	private function ViewClient($client){
        global $dbh, $postvar, $getvar, $instance;
	
		$client_details_array['DATE']        = main::convertdate("n/d/Y", $client['signup']);
		$client_details_array['EMAIL']       = $client['email'];
		$client_details_array['UPGRADEINFO'] = "";
		$existing_upgrade                    = $dbh->select("upgrade", array("uid", "=", $client['id']));
		$all_packs_query                     = $dbh->select("packages", array("is_disabled", "=", "0"), array("type", "ASC"), 0, 1);
		while($all_packs_data = $dbh->fetch_array($all_packs_query)){

			$additional = type::additional($all_packs_data['id']);
			$monthly    = $additional['monthly'];
			$signup     = $additional['signup'];
			
			unset($info);
			if($all_packs_data['type'] == "p2h"){

				$info = "[Signup Posts: ".$signup.", Monthly Posts: ".$monthly."] ";
				
			}elseif($all_packs_data['type'] == "paid"){

				$info = "[".main::money($monthly)."] ";
				
			}

			$packages[] = array("[".$all_packs_data['type']."] ".$info.$all_packs_data['name'], $all_packs_data['id']);
			
			if($existing_upgrade && $existing_upgrade['newpack'] == $all_packs_data['id']){

				if($all_packs_data['admin']){

					$admin = " after you approve them";
					
				}

				if($existing_upgrade['flags'] && $existing_upgrade['flags'] < 5){

					$next_cycle = " next billing cycle";
					
				}

				$client_details_array['UPGRADEINFO'] = "NOTE: This user is slated for an upgrade to \"".$all_packs_data['name']."\"".$next_cycle.$admin.".<br><br>";
				
			}

		}
		
		$client_details_array['PACKAGE']     = main::dropdown("newpackage", $packages, $client['pid']);
		$client_details_array['USER']        = $client['user'];
		$client_details_array['DOMAIN']      = $client['domain'];
		$client_details_array['CLIENTIP']    = $client['ip'];
		$client_details_array['FIRSTNAME']   = $client['firstname'];
		$client_details_array['LASTNAME']    = $client['lastname'];
		$client_details_array['ADDRESS']     = $client['address'];
		$client_details_array['CITY']        = $client['city'];
		$client_details_array['STATE']       = $client['state'];
		$client_details_array['ZIP']         = $client['zip'];
		$client_details_array['COUNTRY']     = strtolower($client['country']);
		$client_details_array['FULLCOUNTRY'] = main::country_code_to_country($client['country']);
		$client_details_array['PHONE']       = $client['phone'];
		
		unset($where);
		$where[] = array("uid", "=", $client['id'], "AND");
		$where[] = array("is_paid", "=", "0");
		
		$invoices_query                   = $dbh->select("invoices", $where, 0, 0, 1);
		$client_details_array['INVOICES'] = $dbh->num_rows($invoices_query);
		
		switch($client['status']){

			default:
				$client_details_array['STATUS'] = "Other";
				break;
			
			case "1":
				$client_details_array['STATUS'] = "Active";
				break;
			
			case "2":
				$client_details_array['STATUS'] = "Suspended";
				break;
			
			case "3":
				$client_details_array['STATUS'] = "Awaiting Validation";
				break;
			
			case "4":
				$client_details_array['STATUS'] = "Awaiting Payment";
				break;
			
			case "5":
				$client_details_array['STATUS'] = "Awaiting Email Confirmation";
				break;
			
			case "9":
				$client_details_array['STATUS'] = "Cancelled";
				break;
				
		}

		$class    = type::packagetype($client['pid']);
		$packtype = $instance->packtypes[$class];
		if(method_exists($packtype, "acpBox")){

			$box                     = $packtype->acpBox();
			$clientview_array['BOX'] = main::sub($box[0], $box[1]);
			
		}else{

			$clientview_array['BOX'] = "";
			
		}

		$clientview_array['CONTENT'] = style::replaceVar("tpl/admin/clients/client-details.tpl", $client_details_array);
		return $clientview_array;
	
	}
	
	private function EmailClient($client){
        global $dbh, $postvar, $getvar, $instance;
	
		if($_POST){

			$result = email::send($client['email'], $postvar['subject'], $postvar['content']);
			if($result){

				main::errors("Email sent!");
				
			}else{

				main::errors("Email was not sent out!");
				
			}

		}

		$clientview_array['BOX']     = "";
		$clientview_array['CONTENT'] = style::replaceVar("tpl/admin/clients/email-client.tpl");	
		return $clientview_array;
	
	}
	
	private function ChangePassword($client){
        global $dbh, $postvar, $getvar, $instance;
	
		$change_password_array['MSG'] = "This will change the user's password in THT and the control panel.<br><br>";
		if($_POST){

			if(empty($postvar['passwd'])){

				main::errors('A password was not provided.');
				
			}else{

				$command = main::changeClientPassword($client['id'], $postvar['passwd']);
				if($command === true){

					main::errors('Password changed!');
					
				}else{

					main::errors($command);
					
				}

			}

		}

		$clientview_array['BOX']     = "";
		$clientview_array['CONTENT'] = style::replaceVar("tpl/admin/clients/change-password.tpl", $change_password_array);	
		return $clientview_array;
	
	}

	private function MakeFreeClient($client){
        global $dbh, $postvar, $getvar, $instance;
	
		$time = time();
		$due  = $time + 30 * 24 * 60 * 60;

		if($getvar['set'] == "no"){
		
			$invoices_update = array("created" => $time,
									 "due"     => $due);
									 
			unset($where);
			$where[] = array("uid", "=", $client['id'], "AND");
			$where[] = array("is_paid", "=", "0");
			
			$dbh->update("invoices", $invoices_update, $where);
			$dbh->update("users", array("freeuser" => "0"), array("id", "=", $client['id']));
			main::thtlog("Set To Non-Free", "Admin set to 'non-free user'", $client['id']);
		
		}else{
		
			$dbh->update("users", array("freeuser" => "1"), array("id", "=", $client['id']));
			main::thtlog("Set To Free User", "Admin set to 'free user'", $client['id']);
		
		}

		main::redirect("?page=users&sub=search&do=".$client['id']);	
	
	}
	
	private function ConfirmClientEmail($client){
        global $dbh, $postvar, $getvar, $instance;
	
		$dbh->update("users", array("status" => "1"), array("id", "=", $client['id']));
		$dbh->update("users_bak", array("status" => "1"), array("uid", "=", $client['id']));
		main::thtlog("Account Confirmed", "Account/E-mail Confirmed - By Admin", $client['id']);
		main::redirect("?page=users&sub=search&do=".$client['id']);
	
	}
	
	private function LoginAsClient($client){
        global $dbh, $postvar, $getvar, $instance;
	
		session_destroy();
		session_start();
		$_SESSION['clogged'] = 1;
		$_SESSION['cuser']   = $client['id'];
		main::redirect("../client");
	
	}

}

?>