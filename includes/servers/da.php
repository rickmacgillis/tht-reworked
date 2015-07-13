<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Direct Admin Server Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class da{

    public $name = "Direct Admin";
    public $subdomains = false;  // Can you register an account with a subdomain as well as a domain?
    public $canupgrade = false;
    
    private $server;
    
    public function __construct($serverId = null){

        if(!is_null($serverId)){

            $this->server = (int) $serverId;
        
        }

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
		$help       = "The port for THT to use to talk to the server on.<br><br>Standard ports:<br>2222 = HTTP / 2222 = HTTPS";
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

    private function remote($action, $url, $get = false, $returnErrors = false){

        $server_details = $this->serverDetails($this->server);
        $ch   = curl_init();
        $ip   = gethostbyname($server_details['host']);
        
        if($server_details['https']){

            //Usually 2222
            if($server_details['apiport'] != 443){

                $port = $server_details['apiport'];
                
            }

            $serverstuff = "https://".$server_details['user'].":".$server_details['accesshash']."@".$server_details['host'].":".$port."/".$action;
            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
        }else{

            //Usually 2222
            if($server_details['apiport'] != 80){

                $port = $server_details['apiport'];
                
            }

            $serverstuff = "http://".$server_details['user'].":".$server_details['accesshash']."@".$server_details['host'].":".$port."/".$action;
            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            
        }

        if($get){

            curl_setopt($ch, CURLOPT_URL, $serverstuff.$url);
        
        }else{

            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
        
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $curl_data = curl_exec($ch);
        if($curl_data === false){

            if($returnErrors){

                return curl_error($ch);
            
            }

            main::error(array("DA Connection Error" => curl_error($ch)));
            return false;
        
        }

        curl_close($ch);
        $split = explode("&", $curl_data);
        foreach($split as $value){

            $stuff            = explode("=", $value);
            $final[$stuff[0]] = $stuff[1];
        
        }

        return $final;
    
    }

    public function signup($server, $reseller, $user, $email, $pass, $domain, $server_pack, $extra = array(), $domsub){
        global $dbh, $postvar, $getvar, $instance;
        
        $this->server = $server;
        $server_details = $this->serverDetails($this->server);
        $ip             = gethostbyname($server_details['host']);
        $string         = "action=create&add=Submit&username=".$user.""."&passwd=".$pass.""."&passwd2=".$pass.""."&domain=".$domain.""."&package=".str_replace(" ", "%20", $server_pack).""."¬ify=no"."&email=".$email."";
        if($reseller){

            $define = "CMD_API_ACCOUNT_RESELLER";
            $string .= "&ip=shared";
        
        }else{

            $define = "CMD_API_ACCOUNT_USER";
            $string .= "&ip=".$ip;
        
        }

        //echo $action."<br />". $reseller;
        $command = $this->remote($define, $string);
        if($command['error']){

            $order_error = "DA Error: <strong>".$command['text']."</strong><br />".$command['details'];
            main::thtlog("DA Error", nl2br(htmlspecialchars($order_error, ENT_QUOTES)));
            
            return "An error has occurred. Please inform your system administrator.";
            
        }else{

            return true;
            
        }

    }

    public function suspend($user, $server, $reason = false){

        $this->server = $server;
        $define       = "CMD_API_SELECT_USERS";
        $action       = "dosuspend=Suspend&suspend=suspend&location=CMD_SELECT_USERS&select0=".strtolower($user);
        $command      = $this->remote($define, $action);
        if(!$command['error']){

            return true;
        
        }else{

            return false;
        
        }

    }

    public function unsuspend($user, $server){

        $this->server = $server;
        $define       = "CMD_API_SELECT_USERS";
        $action       = "dounsuspend=Unsuspend&suspend=unsuspend&select0=".strtolower($user);
        $command      = $this->remote($define, $action);
        if(!$command['error']){

            return true;
        
        }else{

            return false;
        
        }

    }

    public function terminate($user, $server){

        $this->server = $server;
        $define       = "CMD_API_SELECT_USERS";
        $action       = "confirmed=Confirm&delete=yes&select0=".strtolower($user);
        $command      = $this->remote($define, $action);
        if(!$command['error']){

            return true;
        
        }else{

            return false;
        
        }

    }

    public function testConnection($serverId = null){

        if(!is_null($serverId)){

            $this->server = (int) $serverId;
        
        }

        // No idea if this will work. Still need a DA testing server.
        $command = $this->remote("CMD_API_ADMIN_STATS", "", true, true);
        if($command["error"] == "1"){

            return "D";
        
        }

    }

    public function changePwd($acct, $newpwd, $server){

        return true;

    }
	
}

?>