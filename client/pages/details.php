<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Client Area - Edit Details
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
		
        $client                   = $dbh->client($_SESSION['cuser']);
        $edit_account_array['USER']      = $client['user'];
        $edit_account_array['EMAIL']     = $client['email'];
        $edit_account_array['DOMAIN']    = $client['domain'];
        $edit_account_array['FIRSTNAME'] = $client['firstname'];
        $edit_account_array['LASTNAME']  = $client['lastname'];
        $edit_account_array['ADDRESS']   = $client['address'];
        $edit_account_array['CITY']      = $client['city'];
        $edit_account_array['STATE']     = $client['state'];
        $edit_account_array['ZIP']       = $client['zip'];
        $edit_account_array['PHONE']     = $client['phone'];
        $edit_account_array['TZADJUST']  = main::tzlist($client['tzadjust']);
        $edit_account_array['DISP']      = "<div>";
        $edit_account_array['COUNTRY']   = main::countries(1, $client['country']).'<a title="Your country." class="tooltip"><img src="<ICONDIR>information.png" /></a>';
        
        if($_POST){

            if(!check::email($postvar['email'], $_SESSION['cuser'])){

                main::errors("Your email is the wrong format!");
            
            }
			
            if(!check::state($postvar['state'])){

                main::errors("Please enter a valid state!");
            
            }

            if(!check::address($postvar['address'])){

                main::errors("Please enter a valid address!");
            
            }

            if(!check::phone($postvar['phone'])){

                main::errors("Please enter a valid phone number!");
            
            }

            if(!check::zip($postvar['zip'])){

                main::errors("Please enter a valid zip/postal code!");
            
            }

            if(!check::city($postvar['city'])){

                main::errors("Please enter a valid city!");
            
            }

            if(!check::firstname($postvar['firstname'])){

                main::errors("Please enter a valid first name!");
            
            }

            if(!check::lastname($postvar['lastname'])){

                main::errors("Please enter a valid time last name!");
            
            }

            if(!main::errors()){

				if($postvar['country']){

					$country_q = "";
				
				}

				$users_update = array(
					"email"     => $postvar['email'],
					"state"     => $postvar['state'],
					"address"   => $postvar['address'],
					"phone"     => $postvar['phone'],
					"zip"       => $postvar['zip'],
					"city"      => $postvar['city'],
					"tzadjust"  => $postvar['tzones'],
					"firstname" => $postvar['firstname'],
					"lastname"  => $postvar['lastname'],
					"country"   => $postvar['country']
				);
				
				$dbh->update("users", $users_update, array("id", "=", $_SESSION['cuser']));
				
				if($postvar['change']){

					$client = $dbh->client($_SESSION['cuser']);
					if(crypto::passhash($postvar['currentpass'], $client['salt']) == $client['password']){

						if($postvar['newpass'] === $postvar['cpass']){

							$cmd = main::changeClientPassword($_SESSION['cuser'], $postvar['newpass']);
							if($cmd === true){

								main::errors("Details updated!");
							
							}else{

								main::errors((string) $cmd);
							
							}

						}else{

							main::errors("Your passwords don't match!");
						
						}

					}else{

						main::errors("Your current password is incorrect.");
					
					}

				}else{

					$edit_account_array['DISP'] = "<div style=\"display:none;\">";
					main::errors("Details updated!");
				
				}
				
			}

        }

        echo style::replaceVar("tpl/client/edit-account.tpl", $edit_account_array);
    
    }

}

?>