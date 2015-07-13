<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// ZPanel Server Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class zpanel{

    public $name = "ZPanel";
    public $canupgrade = true;
    public $subdomains = false; //Can you register an account with a subdomain as well as a domain?
    
    private $server;
    private $req_template;
    private $response_number;
    private $diagnostics;
    
    //ZPanel default welcome emails
    public $email_subject = "Your ZPanel Account details";
    public $email_body = "Hi{{fullname}},\r\rWe are pleased to inform you that your new hosting account is now active, you can now login to ZPanel using the following credentials:\r\rUsername:{{username}}\rPassword:{{password}}";
    
    public function __construct($serverId = null){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!is_null($serverId)){

            $this->server   = (int) $serverId;
            $server_details = $this->serverDetails($this->server);
        
        }

        $cleanaccesshash = preg_replace("'(\r|\n)'", "", $server_details['accesshash']);
        
        $this->req_template = "<"."?xml version=\"1.0\" encoding=\"UTF-8\"?".">
<xmws>
<apikey>".$cleanaccesshash."</apikey>
<request>[[REQUEST]]</request>
<authuser>".$server_details['user']."</authuser>
<authpass>".$server_details['pass']."</authpass>
<content>
[[CONTENT]]
</content>
</xmws>";
    
    }
    
    public function acp_packages_form($packId = null){
        global $dbh, $postvar, $getvar, $instance;
    
        if($packId){
    
            $packages_data = $dbh->select("packages", array("id", "=", $packId), 0, "1");
    
        }
        
        $yesno_opts[] = array("Yes", 1);
        $yesno_opts[] = array("No", 0);        
        
        $text       = "Group ID:";
        $help       = "The group ID number assigned to the group by ZPanel.  This can be found by editing a group and looking at the URL - the last number in the URL is the id.  (Ex. other=GROUPID)<br><br>By default:<br>1 = Administrator<br>2 = Reseller<br>3 = User<br>4+ = Numbers for custom created groups.";
        $input_type = "input";
        $values     = $packages_data['groupid'];
        $name       = "groupid";
        $response .= main::tr($text, $help, $input_type, $values, $name);
        
        $text       = "Send Welcome Email Below Too?";
        $help       = "Should we send the server's welcome email when the user signs up in addition to THT's welcome email?";
        $input_type = "select";
        $values     = main::dropdown("sendwelcome", $yesno_opts, $packages_data['send_email']);
        $response .= main::tr($text, $help, $input_type, $values);
        
        $text       = "Email Subject:";
        $help       = "The subject for the welcome email.  If left empty, the ZPanel default text will be used.<br><br>Usable variables:<br>{{fullname}}<br>{{username}}<br>{{password}}";
        $input_type = "input";
        $values     = ($packages_data['email_subject'] == "" ? $this->email_subject : $packages_data['email_subject']);
        $name       = "welcomesubject";
        $response .= main::tr($text, $help, $input_type, $values, $name);
                    
        $text       = "Email Body:";
        $help       = "The body for the welcome email.  (TEXT ONLY!)  If left empty, the ZPanel default text will be used.<br><br>Usable variables:<br>{{fullname}}<br>{{username}}<br>{{password}}";
        $input_type = "textarea";
        $values     = ($packages_data['email_body'] == "" ? $this->email_body : $packages_data['email_body']);
        $name       = "welcomebody";
        $response .= main::tr($text, $help, $input_type, $values, $name);
        
        return $response;
    
    }
    
    public function acp_form($serverId = null){
        global $dbh, $postvar, $getvar, $instance;
    
        if($serverId){
            
            $servers_data = $dbh->select("servers", array("id", "=", $serverId));
            
        }
        
        $yesno_opts[] = array("Yes", 1);
        $yesno_opts[] = array("No", 0);        
        
        $text       = "API Port:";
        $help       = "The port for THT to use to talk to the server on.<br><br>Standard ports:<br>80 = HTTP / 443 = HTTPS";
        $input_type = "input";
        $values     = $servers_data['apiport'];
        $name       = "apiport";
        $response .= main::tr($text, $help, $input_type, $values, $name);
        
        $text       = "Connect via HTTPS?";
        $help       = "Should THT connect to the server via HTTPS?";
        $input_type = "select";
        $values     = main::dropdown("https", $yesno_opts, $servers_data['https']);
        $response .= main::tr($text, $help, $input_type, $values);
                    
        $text       = "Reseller ID:";
        $help       = "The reseller ID for new users to be created under.";
        $input_type = "input";
        $values     = $servers_data['reseller_id'];
        $name       = "resellerid";
        $response .= main::tr($text, $help, $input_type, $values, $name);
        
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

    private function remote($action, $post_data, $test = 0, $return_data = 0){
        global $dbh, $postvar, $getvar, $instance;
        
        $server_details = $this->serverDetails($this->server);
        
        $post_data         = explode("[][]", $post_data);
        $post_data_req     = $post_data[0];
        $post_data_content = $post_data[1];
        
        $post_fields = str_replace("[[REQUEST]]", $post_data_req, $this->req_template);
        $post_fields = str_replace("[[CONTENT]]", $post_data_content, $post_fields);
        
        $ch      = curl_init();
        $timeout = 5;
        
        if($server_details['https']){

            //Usually 443
            if($server_details['apiport'] != 443){

                $port = ":".$server_details['apiport'];
                
            }

            $serverstuff = "https://".$server_details['host'].$port."/api/".$action;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
        }else{

            //Usually 80
            if($server_details['apiport'] != 80){

                $port = ":".$server_details['apiport'];
                
            }

            $serverstuff = "http://".$server_details['host'].$port."/api/".$action;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch, CURLOPT_URL, $serverstuff);
            
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $curl_data = curl_exec($ch);
        curl_close($ch);
        
        //RESPONSE CODES:
        
        /*
        
        1101 - Request successful.
        1102 - Request not found (class does not exist).
        1103 - Server API key validation failed.
        1104 - User authentication required and not specified.
        1105 - Username and password validation failed.
        1106 - Request not valid (required tags not supplied) No post data etc.
        1107 - Modular web service not found (eg. No file in the module named 'webservice.ext.php')
        
        CUSTOM
        9999 - Username already exists
        9998 - Username doesn't exist (for upgrades)
        
        */
		
        $response              = explode("<response>", $curl_data);
        $response              = explode("</response>", $response[1]);
        $this->response_number = $response[0];
        $SENT["URL"]           = $serverstuff;
        $SENT["POST"]          = $post_fields;
        $this->diagnostics     = array("SENT" => $SENT,"RECV" => $curl_data);
        
        if($test){

            if($this->response_number == "1101"){

                return true;
            
            }else{

                return false;
            
            }

        }elseif($return_data){

            return $curl_data;
            
        }else{

            if($this->response_number == "1101"){

                return true;
            
            }else{

                return false;
            
            }

        }

    }

    public function signup($server, $reseller, $user, $email, $pass, $domain, $server_pack, $extra = array(), $domsub){
        global $dbh, $postvar, $getvar, $instance;
        
        $this->server = $server;        
        
        $fullname = $extra['firstname']." ".$extra['lastname'];
        $address  = $extra['address']."<br>".$extra['city'].", ".$extra['state']." ".$extra['zip']." ".$extra['country'];
        $zip_code = $extra['zip'];
        $phone    = $extra['phone'];
        
        $packages_data = $dbh->select("packages", array("backend", "=", $server_pack), 0, "1");
        $groupid       = $packages_data['groupid'];
        $send_email    = $packages_data['send_email'];
        $email_subject = $packages_data['email_subject'];
        $email_body    = $packages_data['email_body'];
        
        if(!$email_subject){

            $email_subject = $this->email_subject;
        
        }

        if(!$email_body){

            $email_body = $this->email_body;
        
        }

        $server_details = $this->serverDetails($this->server);
        $reseller_id    = $server_details['reseller_id'];
        
        if(!is_numeric($server_pack) || strpos($server_pack, ".")){

            //The package backend must be the ID (number) of the package.  You can find that by editing the
            //created package and looking at the URL.
            //Ex: ?module=packages&show=Edit&other=PACKAGEID
            //
            //Enter the package ID for the package instead of using the name of the package.
            
            return "An package error has occurred. Please inform your system administrator.";
            
        }

        if(!is_numeric($groupid) || strpos($groupid, ".")){

            //The group ID must be the ID (number) of the group.  You can find that by editing the
            //created group and looking at the URL.
            //Ex: ?module=manage_groups&show=Edit&other=GROUPID
            //
            //Enter the group ID for the group instead of using the name of the group.
            
            return "An grouping error has occurred. Please inform your system administrator.";
            
        }

        if($email_body){

            $email_body = str_replace("\n", "", $email_body);
        
        }

        $content_str = "<resellerid>".$reseller_id."</resellerid>
                                <packageid>".$server_pack."</packageid>
                                <groupid>".$groupid."</groupid>

                                <fullname>".$fullname."</fullname>
                                <email>".$email."</email>
                                <address>".$address."</address>
                                <postcode>".$zip_code."</postcode>
                                <phone>".$phone."</phone>

                                <username>".$user."</username>
                                <password>".$pass."</password>

                                <sendemail>".$send_email."</sendemail>
                                <emailsubject>".$email_subject."</emailsubject>
                                <emailbody>".$email_body."</emailbody>";
        
        $command = $this->remote("manage_clients", "CreateClient[][]".$content_str);
        if($command === true){

            //If we're using a domain and not a sub domain
            if($domsub == "dom"){
            
                $domain_exists = $this->domain_exists($domain);
                if(!$domain_exists){

                    $uid = $this->ZPanel_UID($user);
                    if($uid){

                        $content_str = "<uid>".$uid."</uid>
                                                <domain>".$domain."</domain>
                                                <destination></destination>
                                                <autohome>1</autohome>";
                        
                        $command = $this->remote("domains", "CreateDomain[][]".$content_str);
                        
                    }

                }

            }

            //No matter what we return true because the user can always add a domain in ZPanel if automatic creation fails.
            return true;
            
        }else{

            if($this->response_number != "9999"){

                $order_error = "ORDER ERROR: There was an error on the order form.  Below are the details to help you diagnose this.

                                        Error number code list:
                                        1101 - Request successful.
                                        1102 - Request not found (class does not exist in ZPanel). (Suggestion: Report this on the forums for help.)
                                        1103 - Server API key validation failed.  (Suggestion: Check your server settings.)
                                        1104 - User authentication required and not specified.  (Suggestion: Check your server settings.)
                                        1105 - Username and password validation failed.  (Suggestion: Check your server settings.)
                                        1106 - Request not valid (required tags not supplied) No post data etc. (Suggestion: Report this on the forums for help.)
                                        1107 - Modular web service not found (eg. No file in the module named 'webservice.ext.php') (Suggestion: Report this on the forums for help.)

                                        9999 - Username already exists in ZPanel (This should never show in the logs.  The user will see this and be able to go back and change their username.)
                                        9997 - The account could not be created on the server.  (Most likely the email address for the client already exists on ZPanel's accounts table.)

                                        Error code given: ".$this->response_number."
                                        Server type: ZPanel

                                        Data sent to the server:
                                        Sent to: ".$this->diagnostics["SENT"]["URL"]."
                                        
                                        Data:
                                        ".$this->diagnostics["SENT"]["POST"]."

                                        Response from server:
                                        
                                        Data:
                                        ".$this->diagnostics["RECV"]."

                                        If the solution to this problem was to request help on the forums, be sure you've patched ZPanel and are using the latest version of this module and THT.  Also check to see if you're running a compatible version of ZPanel.";
                
                main::thtlog("ZPanel Error", nl2br(htmlspecialchars($order_error, ENT_QUOTES)));
                
                return "An error has occurred. Please inform your system administrator.";
                
            }else{

                return "That username already exists.";
                
            }

            return false;
        
        }

    }

    public function suspend($user, $server, $reason = false){

        $zpanel_uid  = $this->ZPanel_UID($user);
        $content_str = "<uid>".$zpanel_uid."</uid>";
        
        return $this->remote("manage_clients", "DisableClient[][]".$content_str);
    
    }

    public function unsuspend($user, $server){

        $zpanel_uid  = $this->ZPanel_UID($user);
        $content_str = "<uid>".$zpanel_uid."</uid>";
        
        return $this->remote("manage_clients", "EnableClient[][]".$content_str);
    
    }

    public function terminate($user, $server){

        $zpanel_uid  = $this->ZPanel_UID($user);
        $content_str = "<uid>".$zpanel_uid."</uid>
						<resellerid>1</resellerid>"; //Move all sub accounts to the master zadmin account.  (Reseller ID 1)
        
        return $this->remote("manage_clients", "DeleteClient[][]".$content_str);
    
    }

    public function testConnection($serverId = null){

        if(!is_null($serverId)){

            $this->server = (int) $serverId;
        
        }

        $command = $this->remote("zpanelconfig", "GetAllSystemOptions[][]", 1);
        if($command == true){

            return true;
        
        }else{

            return "Invalid login.";
        
        }

    }
    
    public function changePwd($acct, $newpwd, $server){

        $zpanel_uid  = $this->ZPanel_UID($acct);
        $content_str = "<uid>".$zpanel_uid."</uid>
                <newpassword>".$newpwd."</newpassword>";
        
        return $this->remote("password_assistant", "ResetUserPassword[][]".$content_str);
        
    }

    public function do_upgrade($server, $pkg, $user){
        global $dbh, $postvar, $getvar, $instance;
        
        $this->server = $server;
        $uid          = $this->ZPanel_UID($user);
        
        $tht_pack = $postvar["newpackage"];
        if(!$tht_pack){

            $tht_pack = $postvar["packs"];
        
        }

        if($tht_pack){
        
            $packdata = $dbh->select("packages", array("id", "=", $tht_pack));
        
        }else{
        
            $packdata = $dbh->select("packages", array("backend", "=", $pkg));
        
        }
        
        $content_str = "<uid>".$uid."</uid>
                                <packageid>".$pkg."</packageid>
                                <groupid>".$packdata['groupid']."</groupid>
                                <username>".$user."</username>";
        
        $command = $this->remote("manage_clients", "UpdateClientPackage[][]".$content_str);
        if($command == true){

            return true;
        
        }

    }

    public function listaccs($server){
        global $dbh, $postvar, $getvar, $instance;
        
        $this->server = $server;
        
        $serverdata = $dbh->select("servers", array("id", "=", $server));
        
        $content_str = "<uid>".$serverdata['reseller_id']."</uid>";
        $command     = str_replace("<br>", "", ($this->remote("manage_clients", "GetAllClients[][]".$content_str, 0, 1)));
        
        $xml = new DOMDocument();
        $xml->loadXML($command);
        $list = $xml->getElementsByTagName('client');
        
        $i = 0;
        foreach($list AS $element){

            foreach($element->childNodes AS $item){

                if($item->nodeName == "username"){

                    $result[$i]['user'] = $item->nodeValue;
                
                }

                if($item->nodeName == "packageid"){

                    $result[$i]['package'] = $item->nodeValue;
                
                }

                if($item->nodeName == "email"){

                    $result[$i]['email'] = $item->nodeValue;
                
                }

                if($item->nodeName == "userid"){

                    $result[$i]['uid'] = $item->nodeValue;
                
                }

            }

            if(!$ts_set){

                $result[$i]['start_date'] = time();
            
            }

            $i++;
        
        }

        //return the result array
        return $result;
    
    }

    private function ZPanel_UID($uname){
        global $dbh, $postvar, $getvar, $instance;
        
        $users_data = $dbh->select("users", array("user", "=", $uname), 0, "1");
        
        if($users_data['zpanel_uid']){

            return $users_data['zpanel_uid'];
        
        }else{

            $xml = str_replace(array('<?xml version="1.0" encoding="ISO-8859-1"?>', "<br>"), "", $this->remote("manage_clients", "GetAllClients[][]", 0, 1));
            $xml = new SimpleXMLElement($xml);
            
            $num_clients = $xml->content->client->count();
            
            for($i = 0; $i < $num_clients; $i++){

                if($uname == $xml->content->client[$i]->username){

                    $user_id = $xml->content->client[$i]->userid;
                    break;
                
                }

            }

            $dbh->update("users", array("zpanel_uid" => $user_id), array("user", "=", $uname));
            $dbh->update("users_bak", array("zpanel_uid" => $user_id), array("user", "=", $uname));
            
            return $user_id;
        
        }

    }
    
    public function domain_exists($domain){

        $command = $this->remote("domains", "GetAllDomains[][]", 0, 1);
        
        $xml = new DOMDocument();
        $xml->loadXML($command);
        $list = $xml->getElementsByTagName('domain');
        
        foreach($list AS $element){

            foreach($element->childNodes AS $item){

                if(substr_count($item->nodeValue, ".") != 0){

                    $result[] = $item->nodeValue;
                
                }

            }

        }

        if(in_array($domain, $result)){

            return true;
        
        }else{

            return false;
        
        }

    }
    
}

?>
