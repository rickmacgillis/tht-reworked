<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// cPanel/WHM Server Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class whm{

    public $name = "cPanel/WHM"; // THT Values
    public $canupgrade = true;
    public $subdomains = false; //Can you register an account with a subdomain as well as a domain?
    
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
		$help       = "The port for THT to use to talk to the server on.<br><br>Standard ports:<br>ZPanel: 80 = HTTP / 443 = HTTPS<br>WHM: 2086 = HTTP / 2087 = HTTPS<br>Kloxo: 7778 = HTTP / 7777 = HTTPS<br>DA: 2222 = HTTP / 2222 = HTTPS";
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
					
		$text       = "Access Hash:";
		$help       = "Access hash to connect to the server";
		$input_type = "textarea";
		$values     = $servers_data['accesshash'];
		$name       = "hash";
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

    private function remote($url, $xml = 0, $term = false, $returnErrors = false){
        global $dbh, $postvar, $getvar, $instance;
		
        $server_details  = $this->serverDetails($this->server);
        $cleanaccesshash = preg_replace("'(\r|\n)'", "", $server_details['accesshash']);
        $authstr         = $server_details['user'].":".$cleanaccesshash;
        $ch              = curl_init();
        
        if($server_details['https']){

            //Usually 2087
            if($server_details['apiport'] != 443){

                $port = $server_details['apiport'];
                
            }

            $serverstuff = "https://".$server_details['host'].":".$port.$url;
            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
        }else{

            //Usually 2086
            if($server_details['apiport'] != 80){

                $port = $server_details['apiport'];
                
            }

            $serverstuff = "http://".$server_details['host'].":".$port.$url;
            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $curlheaders[0] = "Authorization: WHM $authstr";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlheaders);
        $curl_data = curl_exec($ch);
        if($curl_data === false){

            if($returnErrors){

                return curl_error($ch);
            
            }

            main::error(array("WHM Connection Error" => curl_error($ch)));
            return false;
        
        }

        curl_close($ch);
		
        if($term == true){

            return true;
            
        }elseif(strstr($curl_data, "SSL encryption is required")){

            if($returnErrors){

                return "THT must connect via SSL!";
            
            }

            main::error(array("WHM Error" => "THT must connect via SSL!"));
            return false;
        
        }elseif(!$xml){

            $xml = new SimpleXMLElement($curl_data);
        
        }else{

            return $curl_data;
        
        }

        return $xml;
    
    }

    public function signup($server, $reseller, $user, $email, $pass, $domain, $server_pack, $extra = array(), $domsub){
        global $dbh, $postvar, $getvar, $instance;
        
        $this->server = $server;
        $action       = "/xml-api/createacct"."?username=".$user.""."&password=".$pass.""."&domain=".$domain.""."&plan=".str_replace(" ", "%20", $server_pack).""."&contactemail=".$email."";
        if($reseller){

            $action .= "&reseller=1";
        
        }

        //echo $action."<br />". $reseller."<br>";
        $command = $this->remote($action);
        
        if($command->result->status == 1){

            return true;
            
        }else{

            $order_error = "WHM Error: ".$command->result->statusmsg;
            main::thtlog("WHM Error", nl2br(htmlspecialchars($order_error, ENT_QUOTES)));
            
            return "An error has occurred. Please inform your system administrator.";
            
        }

    }

    public function suspend($user, $server, $reason = false){

        $this->server = $server;
        $action       = "/xml-api/suspendacct?user=".strtolower($user);
        $command      = $this->remote($action);
        if($reason == false){

            $command = $this->remote($action);
        
        }else{

            $command = $this->remote($action."&reason=".str_replace(" ", "%20", $reason));
        
        }

        if($command->result->status == 1){

            return true;
        
        }else{

            return false;
        
        }

    }

    public function unsuspend($user, $server){

        $this->server = $server;
        $action       = "/xml-api/unsuspendacct?user=".strtolower($user);
        $command      = $this->remote($action);
        if($command->result->status == 1){

            return true;
        
        }else{

            return false;
        
        }

    }

    public function terminate($user, $server){

        $this->server = $server;
        $action       = "/xml-api/removeacct?user=".strtolower($user);
        $command      = $this->remote($action, 0, true);
        if($command == true){

            return true;
        
        }else{

            return false;
        
        }

    }

    public function testConnection($serverId = null){

        if(!is_null($serverId)){

            $this->server = (int) $serverId;
        
        }

        $command = $this->remote("/xml-api/version", 0, false, true);
        if((is_object($command)) and (get_class($command) == "SimpleXMLElement")){

            if(isset($command->version)){

                return true;
            
            }else{

                if(isset($command->data->reason)){

                    return $command->data->reason;
                
                }else{

                    return print_r($command, true);
                
                }

            }

        }else{

            return $command;
        
        }

    }

    public function changePwd($acct, $newpwd, $server){

        $this->server = $server;
        $action       = '/xml-api/passwd?user='.$acct.'&pass='.$newpwd;
        $command      = $this->remote($action);
        if($command->passwd->status == 1){

            return true;
        
        }else{

            if(isset($command->passwd->statusmsg)){

                return $command->passwd->statusmsg;
            
            }else{

                return false;
            
            }

        }

    }

    public function do_upgrade($server, $pkg, $user){
        global $dbh, $postvar, $getvar, $instance;
        
        $this->server = $server;
        $action       = "/xml-api/changepackage"."?user=".$user.""."&pkg=".$pkg."";
        
        $command = $this->remote($action);
        
        if($command->result->status == 1){

            return true;
        
        }else{

            echo "WHM Error: ".$command->result->statusmsg;
        
        }

    }

    public function listaccs($server){

        $this->server = $server;
        $action       = "/xml-api/listaccts";
        $command      = $this->remote($action, 1);
        $xml          = new DOMDocument();
        $xml->loadXML($command);
        $list = $xml->getElementsByTagName('user');
		
        $i    = 0;
        foreach($list AS $element){

            foreach($element->childNodes AS $item){

                $result[$i]['user'] = $item->nodeValue;
                $i++;
            
            }

        }

        $list = $xml->getElementsByTagName('domain');
        $i    = 0;
        foreach($list AS $element){

            foreach($element->childNodes AS $item){

                $result[$i]['domain'] = $item->nodeValue;
                $i++;
            
            }

        }

        $list = $xml->getElementsByTagName('plan');
        $i    = 0;
        foreach($list AS $element){

            foreach($element->childNodes AS $item){

                $result[$i]['package'] = $item->nodeValue;
                $i++;
            
            }

        }

        $list = $xml->getElementsByTagName('unix_startdate');
        $i    = 0;
        foreach($list AS $element){

            foreach($element->childNodes AS $item){

                $result[$i]['start_date'] = $item->nodeValue;
                $i++;
            
            }

        }

        $list = $xml->getElementsByTagName('email');
        $i    = 0;
        foreach($list AS $element){

            foreach($element->childNodes AS $item){

                $result[$i]['email'] = $item->nodeValue;
                $i++;
            
            }

        }

        return $result;
    
    }	

    public function simpleXMLToarray(SimpleXMLElement $xml, $attributesKey = null, $childrenKey = null, $valueKey = null){

        if($childrenKey && !is_string($childrenKey)){

            $childrenKey = '@children';
        
        }

        if($attributesKey && !is_string($attributesKey)){

            $attributesKey = '@attributes';
        
        }

        if($valueKey && !is_string($valueKey)){

            $valueKey = '@values';
        
        }

        $return = array();
        $name   = $xml->getName();
        $_value = trim((string) $xml);		
        if(!strlen($_value)){

            $_value = null;
        
        }
        
        if($_value !== null){

            if($valueKey){

                $return[$valueKey] = $_value;
            
            }else{

                $return = $_value;
            
            }

        }

        $children = array();
        $first    = true;
        foreach($xml->children() as $elementName => $child){

            $value = simpleXMLToarray($child, $attributesKey, $childrenKey, $valueKey);
            if(isset($children[$elementName])){

                if(is_array($children[$elementName])){

                    if($first){

                        $temp = $children[$elementName];
                        unset($children[$elementName]);
                        $children[$elementName][] = $temp;
                        $first                    = false;
                    
                    }

                    $children[$elementName][] = $value;
                
                }else{

                    $children[$elementName] = array($children[$elementName], $value);
                
                }

            }else{

                $children[$elementName] = $value;
            
            }

        }

        if($children){

            if($childrenKey){

                $return[$childrenKey] = $children;
            
            }else{

                $return = array_merge($return, $children);
            
            }

        }

        $attributes = array();
        foreach($xml->attributes() as $name => $value){

            $attributes[$name] = trim($value);
        
        }

        if($attributes){

            if($attributesKey){

                $return[$attributesKey] = $attributes;
            
            }else{

                $return = array_merge($return, $attributes);
            
            }

        }

        return $return;
    
    }

}

?>