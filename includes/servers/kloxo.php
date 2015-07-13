<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Kloxo Server Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class kloxo{

    public $name = "Kloxo";
    public $canupgrade = true;
    public $subdomains = false; //Can you register an account with a subdomain as well as a domain?
    
    public $dnstemplate = "default.dnst";
    private $diagnostics;
    
    // Should Kloxo send a welcome e-mail?
    private $welcome_email = true;
    
    private $server;
    
    public function __construct($serverId = null){
        global $dbh, $postvar, $getvar, $instance;
		
        if(!is_null($serverId)){

            $this->server   = (int) $serverId;
            $server_details = $this->serverDetails($this->server);
        
        }elseif($getvar['do']){

            //Suspend and stuff
			$clients_package = main::uidtopack($getvar['do']);
			$pid             = $clients_package['packages']['id'];

            $packages_data  = $dbh->select("packages", array("id", "=", $pid), 0, "1");
            $this->server   = $packages_data['server'];
            $server_details = $this->serverDetails($this->server);

        }

        if($server_details['dnstemplate']){

            $this->dnstemplate = $server_details['dnstemplate'];
        
        }

        $this->welcome_email = $server_details['welcome'];
    
    }
	
	public function acp_packages_form($packId = null){
		
		return;
	
	}
	
	public function acp_form($serverId = null){
		global $dbh, $postvar, $getvar, $instance;
	
		if($serverId){
			
			$servers_data = $dbh->select("servers", array("id", "=", $serverId));
			
		}
		
		$yesno_opts[] = array("Yes", 1);
		$yesno_opts[] = array("No", 0);		
		
		$text       = "API Port:";
		$help       = "The port for THT to use to talk to the server on.<br><br>Standard ports:<br>7778 = HTTP / 7777 = HTTPS";
		$input_type = "input";
		$values     = $servers_data['apiport'];
		$name       = "apiport";
		$response .= main::tr($text, $help, $input_type, $values, $name);
		
		$text       = "Connect via HTTPS?";
		$help       = "Should THT connect to the server via HTTPS?";
		$input_type = "select";
		$values     = main::dropdown("https", $yesno_opts, $servers_data['https']);
		$response .= main::tr($text, $help, $input_type, $values);
		
		$text       = "Username:";
		$help       = "Username to connect to the server";
		$input_type = "input";
		$values     = $servers_data['user'];
		$name       = "user";
		$response .= main::tr($text, $help, $input_type, $values, $name);
					
		$text       = "Password:";
		$help       = "Password to connect to the server";
		$input_type = "password";
		$values     = $servers_data['pass'];
		$name       = "pass";
		$response .= main::tr($text, $help, $input_type, $values, $name);
					
		$text       = "DNS Template:";
		$help       = "The DNS template for creating new domains with - Check out the documentation for your control panel for more information.";
		$input_type = "input";
		$values     = ($servers_data['dnstemplate'] == "" ? $this->dnstemplate : $servers_data['dnstemplate']);
		$name       = "nstmp";
		$response .= main::tr($text, $help, $input_type, $values, $name);
					
		$text       = "Backend Welcome Email Too?";
		$help       = "Should the server's welcome email be sent out in addition to THT's welcome email?";
		$input_type = "select";
		$values     = main::dropdown("welcome", $yesno_opts, $servers_data['welcome']);
		$response .= main::tr($text, $help, $input_type, $values);	
		
		return $response;
		
	}	

    private function serverDetails($server){
        global $dbh, $postvar, $getvar, $instance;
		
        $servers_data = $dbh->select("servers", array("id", "=", $server));
        if(!$servers_data['id']){

            $error_array['Error']     = "That server doesn't exist!";
            $error_array['Server ID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            return $servers_data;
        
        }

    }

    private function remote($action, $test = 0){
        global $dbh, $postvar, $getvar, $instance;
        
        $server_details = $this->serverDetails($this->server);
        
        $ip = gethostbyname($server_details['host']);        
        $ch      = curl_init();
        $timeout = 5;
        
        if($server_details['https']){

            //Usually 7777
            if($server_details['apiport'] != 443){

                $port = $server_details['apiport'];
                
            }

            $serverstuff = "https://".$server_details['host'].":".$port."/webcommand.php?login-class=auxiliary&login-name=".$server_details['user']."&login-password=".$server_details['accesshash']."&".$action;
            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
        }else{

            //Usually 7778
            if($server_details['apiport'] != 80){

                $port = $server_details['apiport'];
                
            }

            $serverstuff = "http://".$server_details['host'].":".$port."/webcommand.php?login-class=auxiliary&login-name=".$server_details['user']."&login-password=".$server_details['accesshash']."&".$action;
            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $curl_data = curl_exec($ch);
        curl_close($ch);
        
        // Parse Data
        $curl_data = htmlentities($curl_data);
        
        // End
        
        if(!$test){

            if(strpos($curl_data, "success") != false){

                return true;
            
            }else{

                return false;
            
            }

        }else{

            if($curl_data != "_error_login_error"){

                return true;
            
            }else{

                $this->diagnostics["SEND"] = $serverstuff;
                $this->diagnostics["RECV"] = $curl_data;
                return false;
            
            }

        }

    }

    public function signup($server, $reseller, $user, $email, $pass, $domain, $server_pack, $extra = array(), $domsub){
        global $dbh, $postvar, $getvar, $instance;
        
        $this->server = $server;
        $server_details = $this->serverDetails($this->server);
        $ip             = gethostbyname($server_details['host']);
        
        $package = str_replace(" ", "", $server_pack);
        
        if($this->welcome_email){

            $send_email_kloxo = "on";
        
        }else{

            $send_email_kloxo = "off";
        
        }

        $string = "action=add&class=client&name=".$user.""."&v-password=".$pass.""."&v-domain_name=".$domain.""."&v-dnstemplate_name=".$this->dnstemplate.""."&v-plan_name=".$package.""."&v-send_welcome_f=".$send_email_kloxo."&v-contactemail=".$email."";
        // Reseller or Not?
        if($reseller){

            $string .= "&v-type=reseller";
        
        }else{

            $string .= "&v-type=customer";
        
        }

        //echo $action."<br />". $reseller;
        $command = $this->remote($string);
        if($command == true){

            return true;
            
        }else{

            $order_error = "ORDER ERROR: There was an error on the order form.  Below are the details to help you diagnose this.

                                        Data sent to the server:
                                        Sent to: ".$this->diagnostics["SENT"]."

                                        Response from server:

                                        Data:
                                        ".$this->diagnostics["RECV"];
            
            main::thtlog("Kloxo Error", nl2br(htmlspecialchars($order_error, ENT_QUOTES)));
            
            return "An error has occurred. Please inform your system administrator.";
            
        }

    }
    
    public function suspend($user, $server, $reason = false){

        $this->server = $server;
        $action       = "action=update&subaction=disable&class=client&name=".strtolower($user);
        return $this->remote($action);
    
    }

    public function unsuspend($user, $server){

        $this->server = $server;
        $action       = "action=update&subaction=enable&class=client&name=".strtolower($user);
        return $this->remote($action);
    
    }

    public function terminate($user, $server){

        $this->server = $server;
        $action       = "action=delete&class=client&name=".strtolower($user);
        return $this->remote($action);
    
    }

    public function testConnection($serverId = null){

        if(!is_null($serverId)){

            $this->server = (int) $serverId;
        
        }

        $command = $this->remote("", 1);
        if($command == true){

            return true;
        
        }else{

            return "Invalid login.";
        
        }

    }
	
    public function changePwd($acct, $newpwd, $server){

        $this->server = $server;
        $string       = "action=update&subaction=password&class=client&name=".$acct."&v-password=".$newpwd;
        
        $command = $this->remote($string);
        if($command == true){

            return true;
        
        }else{

            echo "Password could not be changed.";
            return false;
        
        }

    }

    public function do_upgrade($server, $pkg, $user){
        global $dbh, $postvar, $getvar, $instance;
        
        $this->server = $server;
        $action       = "action=update&subaction=change_plan&class=client&name=".$user."&v-resourceplan_name=".$pkg;
        
        $command = $this->remote($action);
        
        if($command == true){

            return true;
        
        }else{

            echo "Could not perform upgrade.  Please report this to the administrator.";
            return false;
        
        }

    }

}

?>