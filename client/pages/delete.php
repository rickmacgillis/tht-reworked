<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Client Area - Delete Account
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
		
        if(!$dbh->config("delacc")){

            die('This feature has been disabled.');
        
        }else{

			if($_POST){
			
				$user = $_SESSION['cuser'];
				$pass = $postvar['password'];
				
				$client = $dbh->client($user);

				unset($where);
				$where[]       = array("is_paid", "=", "0", "AND");
				$where[]       = array("uid", "=", $user);
				$balance_query = $dbh->select("invoices", $where, 0, "1", 1);
				if($dbh->num_rows($balance_query) != 0){

					main::errors("You can't close your account with an outstanding balance.  Please contact an administrator for assistance or pay any unpaid invoices.");
					
				}

				if(crypto::passhash($pass, $client['salt']) == $client['password']){

					if(server::terminate($client['id'], "", 1)){

						main::errors("Your account has been cancelled successfully.");
						session_destroy();
					
					}else{

						main::errors("Your account wasn't cancelled.  Please try again or contact your system administrator.");
					
					}

				}else{

					main::errors("The password entered is incorrect.");
				
				}	
			
			}

            echo style::replaceVar("tpl/client/delete-account.tpl");
        
        }

    }

}

?>