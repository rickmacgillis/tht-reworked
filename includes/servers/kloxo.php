<?php

/**
 * TheHostingTool :: Kloxo Server Class
 * Created by Liam Demafelix (c) 2012
 * http://www.pinoytechie.net/
 * Kloxo Class Version 1.2.2 - March 13, 2012
**/

class kloxo {

    public $name        = "Kloxo";
    public $hash        = false;
    public $ns_tmpl     = true;
    public $has_welcome = true;
    public $canupgrade  = true;

    private $port = 7778;
    private $sport = 7777;
    public  $dnstemplate = "default.dnst";

    # Should Kloxo send a welcome e-mail?
    private $welcome_email = true;

    private $server;


    public function __construct($serverId = null) {
        global $db, $main;
            if(!is_null($serverId)) {
                $this->server = (int)$serverId;
                $server_details = $this->serverDetails($this->server);
            }else{
                $pid = $main->getvar['package']; //For the admin page.
                if(is_numeric($pid)){
                    $serv_query = $db->query("SELECT * FROM <PRE>packages WHERE id = '".$pid."' LIMIT 1");
                    $serv_data = $db->fetch_array($serv_query);
                    $this->server = $serv_data['server'];
                    $server_details = $this->serverDetails($this->server);
                }
            }

            if($server_details['dnstemplate']){
                $this->dnstemplate   = $server_details['dnstemplate'];
            }

            $this->welcome_email = $server_details['welcome'];
    }

    private function serverDetails($server) {
        global $db;
        global $main;
        $query = $db->query("SELECT * FROM `<PRE>servers` WHERE `id` = '{$db->strip($server)}'");
        if($db->num_rows($query) == 0) {
            $array['Error'] = "That server doesn't exist!";
            $array['Server ID'] = $id;
            $main->error($array);
            return;
        }
        else {
            return $db->fetch_array($query);
        }
    }

    private function remote($action, $test = 0) {
    global $db;

        $data = $this->serverDetails($this->server);

        $ip = gethostbyname($data['host']);

        // Connect
        /** As per THT team recommendation (by Kevin), this plugin now uses cURL to connect **/
        $ch = curl_init();
        $timeout = 5;
                if($db->config("whm-ssl") == 1) {
                        $serverstuff = "https://" . $data['host'] . ":" . $this->sport . "/webcommand.php?login-class=auxiliary&login-name=" . $data['user'] . "&login-password=" . $data['accesshash'] . "&" . $action;
                        curl_setopt($ch, CURLOPT_URL, $serverstuff);
                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }
                else {
                        $serverstuff = "http://" . $data['host'] . ":" . $this->port . "/webcommand.php?login-class=auxiliary&login-name=" . $data['user'] . "&login-password=" . $data['accesshash'] . "&" . $action;
                        curl_setopt($ch, CURLOPT_URL, $serverstuff);
                }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        // Parse Data
        $data = htmlentities($data);

        // End

        if(!$test){
            if(strpos($data, "success") != false) {
                return true;
            }
            else {
                return false;
            }
        }else{
            if($data != "_error_login_error") {
                return true;
            }
            else {
                return false;
            }
        }

    }

     public function GenUsername() {
            $t = rand(5,8);
            for ($digit = 0; $digit < $t; $digit++) {
                $r = rand(0,1);
                $c = ($r==0)? rand(65,90) : rand(97,122);
                $user .= chr($c);
            }
            return $user;
    }

    public function GenPassword() {
            for ($digit = 0; $digit < 5; $digit++) {
                $r = rand(0,1);
                $c = ($r==0)? rand(65,90) : rand(97,122);
                $passwd .= chr($c);
            }
            return $passwd;
    }

    public function signup($server, $reseller, $user = '', $email = '', $pass = '') {
                global $main;
                global $db;


                if ($user == '') { $user = $main->getvar['username']; }
                if ($email == '') { $email = $main->getvar['email']; }
                if ($pass == '') { $pass = $main->getvar['password']; }
                $this->server = $server;
                $data = $this->serverDetails($this->server);
                $ip = gethostbyname($data['host']);

                /**
                 * As of Version 1.2.2 : Perform Validation Checks on Variables
                **/
                $user = trim(stripslashes($main->getvar['username']));
                $email = trim(stripslashes($main->getvar['email']));
                $pass = trim(stripslashes($main->getvar['password']));
                $package = $main->getvar['fplan'];
                $package = str_replace(" ", "", $package);

                /**
                 * As of Version 1.2.2 : Kloxo Welcome E-Mail Configuration
                **/
                if($this->welcome_email) {
                    $send_email_kloxo = "on";
                } else {
                    $send_email_kloxo = "off";
                }

                $string =   "action=add&class=client&name=". $user . "".
                                        "&v-password=". $pass ."".
                                        "&v-domain_name=". $main->getvar['fdom'] ."".
                                        "&v-dnstemplate_name=" . $this->dnstemplate ."".
                                        "&v-plan_name=". $package ."".
                                        "&v-send_welcome_f=" . $send_email_kloxo .
                                        "&v-contactemail=".$email."";
                // Reseller or Not?
                if($reseller) {
                        $string .= "&v-type=reseller";
                }
                else {
                        $string .= "&v-type=customer";
                }
                //echo $action."<br />". $reseller;
                $command = $this->remote($string);
                if($command == true) {
                        return true;
                }
                else {
                      echo "An error has occurred. Please inform your system administrator.";
                       return false;
                    }

        }

        /**
         * Thanks to Days and Kevin for fixing the suspend, unsuspend and terminate functions
        **/

        public function suspend($user, $server, $reason = false) {
                $this->server = $server;
                $action = "action=update&subaction=disable&class=client&name=" . strtolower($user);
                return $this->remote($action);
        }

        public function unsuspend($user, $server) {
                $this->server = $server;
                $action = "action=update&subaction=enable&class=client&name=" . strtolower($user);
                return $this->remote($action);
        }

        public function terminate($user, $server) {
                $this->server = $server;
                $action = "action=delete&class=client&name=" . strtolower($user);
                return $this->remote($action);
        }

        public function changePwd($acct, $newpwd, $server)
        {
                $this->server = $server;
                $string =   "action=update&subaction=password&class=client&name=".$acct."&v-password=".$newpwd;

                $command = $this->remote($string);
                if($command == true) {
                        return true;
                }
                else {
                      echo "Password could not be changed.";
                       return false;
                }

        }

        public function testConnection($serverId = null) {
                if(!is_null($serverId)) {
                        $this->server = (int)$serverId;
                }
                $command = $this->remote("", 1);
                if($command == true) {
                    return true;
                }
                else {
                    return "Invalid login.";
                }
        }

        public function upgrade($server, $pkg, $user){
                global $main;
                global $db;

                $this->server = $server;
                $action = "action=update&subaction=change_plan&class=client&name=".$user."&v-resourceplan_name=".$pkg;

                $command = $this->remote($action);

                if($command == true) {
                    return true;
                }
                else {
                    echo "Could not perform upgrade.  Please report this to the administrator.";
                    return false;
                }
        }
}
