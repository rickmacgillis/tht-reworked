<?php
//////////////////////////////
// The Hosting Tool
// Server Class
// By Jonny H
// Released under the GNU-GPL
//////////////////////////////

class server {
        
        private $servers = array(); # All the servers in a array
        
        # Start the Functions #
        public function createServer($package = "") { # Returns the server class for the desired package
                global $type, $main, $db;
                if(!$package){
                 $userid = $_SESSION['cuser'];
                 $query_upack = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$userid}'");
                 $query_upack_data = $db->fetch_array($query_upack);
                 $package = $query_upack_data['pid'];
                }
                $server = $type->determineServerType($type->determineServer($package)); # Determine server
                if($this->servers[$server]) {
                        return;        
                }
                $link = LINK."servers/".$server.".php";
                if(!file_exists($link)) {
                        $array['Error'] = "The server .php doesn't exist!";
                        $array['Server ID'] = $server;
                        $array['Path'] = $link;
                        $main->error($array);
                        return;        
                }
                else {
                        include($link); # Get the server
                        $serverphp = new $server;
                        return $serverphp;
                }
        }
        
        public function serverhasrestore(){
                 global $db, $restoreserver;
                 if(!method_exists($restoreserver, "remote")){
                 $restoreserver = $this->createServer();
                 }
                 if(method_exists($restoreserver, "restore")){
                   return $restoreserver;
                 }else{
                   return false;
                 }
        }

        public function serverinfo(){
                global $db;
                $userid = $_SESSION['cuser'];
                $query_upack = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$userid}'");
                $query_upack_data = $db->fetch_array($query_upack);
                $query_pack = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$query_upack_data['pid']}'");
                $query_pack_data = $db->fetch_array($query_pack);
                $query_server = $db->query("SELECT * FROM `<PRE>servers` WHERE `id` = '{$query_pack_data['server']}'");
                $query_server_data = $db->fetch_array($query_server);
                if($query_server_data['ftpuser']){
                return array($query_server_data['ftpuser'], $query_server_data['ftppass'], $query_server_data['ftpport'], $query_server_data['ftppath'], $query_server_data['ftphost'], $query_server_data['id']);
                }else{
                return false;
                }
        }
        
        public function signup() { # Echos the result of signup for ajax
                global $main;
                global $db;
                global $type;
                global $email;
                global $navens_coupons, $sdk;
                
                //NOTE: I moved all of the getvar stuff up to the top of this function so its easier to manage.  The naming convention I used for the new variables is simply $[NAME_OF_GETVAR]_url
                //I also removed pointless variables that would always hold the same value as the unes up here.  ($UsrName and $newusername) I also entry condensed all the checks into checking
                //one or two checks for each.  It depends on how it was checked.  I removed the extra parenthesis as well.

                //So, since we needed to URL encode everything using JS in order to add security and handle # signs in addresses (For apartments and such), then
                //we now need to decode it to normalize the strings.  I noticed that some of them had slashes for apostrophees, so that's a good thing, but
                //as this code was all over the place, I didn't want to chance it and wind up having people get SQL injections.  So, we need to take the
                //slashes out and then add them back in to make sure that they all have slashes on them.
                $package_url = addslashes(stripslashes(urldecode($main->getvar['package'])));
                $domain_url = addslashes(stripslashes(urldecode($main->getvar['domain'])));
                $cdom_url = addslashes(stripslashes(urldecode($main->getvar['cdom'])));
                $csub_url = addslashes(stripslashes(urldecode($main->getvar['csub'])));
                $csub2_url = addslashes(stripslashes(urldecode($main->getvar['csub2'])));
                $username_url = addslashes(stripslashes(urldecode($main->getvar['username'])));
                $password_url = addslashes(stripslashes(urldecode($main->getvar['password'])));
                $confirmp_url = addslashes(stripslashes(urldecode($main->getvar['confirmp'])));
                $email_url = addslashes(stripslashes(urldecode($main->getvar['email'])));
                $human_url = addslashes(stripslashes(urldecode($main->getvar['human'])));
                $firstname_url = addslashes(stripslashes(urldecode($main->getvar['firstname'])));
                $lastname_url = addslashes(stripslashes(urldecode($main->getvar['lastname'])));
                $address_url = addslashes(stripslashes(urldecode($main->getvar['address'])));
                $city_url = addslashes(stripslashes(urldecode($main->getvar['city'])));
                $zip_url = addslashes(stripslashes(urldecode($main->getvar['zip'])));
                $state_url = addslashes(stripslashes(urldecode($main->getvar['state'])));
                $country_url = addslashes(stripslashes(urldecode($main->getvar['country'])));
                $phone_url = addslashes(stripslashes(urldecode($main->getvar['phone'])));
                $tzones_url = addslashes(stripslashes(urldecode($main->getvar['tzones'])));
                $coupon_url = addslashes(stripslashes(urldecode($main->getvar['coupon'])));
                
                //For some reason the preg_match will not see an apostrophee if its been slashed.  So, these will only be used on checking if
                //they hold legitimate values.
                $firstname_apos_chk_url = stripslashes(urldecode($main->getvar['firstname']));
                $lastname_apos_chk_url = stripslashes(urldecode($main->getvar['lastname']));

                //Check details
                $query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$package_url}' AND `is_disabled` = 0"); # Package disabled?
                if($db->num_rows($query) != 1) {
                        echo "Package is disabled.!";
                        return;
                }
                $package_data = $db->fetch_array($query);
                $package_server = $package_data['server'];
                $query = $db->query("SELECT * FROM `<PRE>subdomains` WHERE `server` = '{$package_server}' LIMIT 1");
                $subdomains_available = $db->num_rows($query);  //Check if subdomains are available as well.
                if($domain_url == "dom") { # If Domain
                        if(!$cdom_url) {
                                echo "Please fill in the domain field!";
                                return;
                        }
                        else {
                                $data = explode(".",$cdom_url);
                                if(!$data[1]) {
                                        echo "Your domain is the wrong format!";        
                                        return;
                                }
                                if($data[3]){
                                 if($db->config['tldonly'] == '1' || $subdomains_available == '0'){
                                  echo "Only Top Level Domains are allowed. (.com/.net/.org, etc)";
                                 }else{
                                  echo "If you'd like to use a subdomain, please click the 'Previous Step' button until you reach the order page (Do NOT hit your browser's back button!), then select subdomain from the drop down list.  You're entries on the previous page WILL be saved.";
                                 }
                                 return;
                                }
                                if($data[2]) { # Are we alowing TLD's Only?
                                        $ttlparts = count($data);                         //0 Counts - Just an FYI, but when you count it, 0 does not count.
                                        if ($ttlparts == 3){                              //(So, $data[0] = test, $data[1] = com,  count($data) = 2 in test.com)
                                                $dmndata = array('com', 'net', 'co', 'uk', 'org');
                                                if (!in_array($data[$ttlparts - 2], $dmndata)) {
                                                        if($db->config['tldonly'] || $subdomains_available == '0'){
                                                        echo "Only Top Level Domains are allowed. (.com/.net/.org, etc)";
                                                        }else{
                                                         echo "If you'd like to use a subdomain, please click the 'Previous Step' button until you reach the order page (Do NOT hit your browser's back button!), then select subdomain from the drop down list.  You're entries on the previous page WILL be saved.";
                                                        }
                                                        return;
                                                }
                                        } # If we get past this, its a top level domain :D yay
                                }
                        }
                        $main->getvar['fdom'] = $cdom_url;
                }
                if($domain_url == "sub") { # If Subdomain
                        if(!$csub_url && !$csub2_url) {
                                echo "Please fill in the subdomain field!";
                                return;
                        }
                        $main->getvar['fdom'] = $csub_url.".".$csub2_url;
                }
                
                if(!$username_url) {
                        echo "Please enter a username!";
                        return;
                }
                else {
                        $query = $db->query("SELECT * FROM `<PRE>users` WHERE `user` = '{$username_url}'");
                        if($db->num_rows($query) != 0) {
                                echo "That username already exists!";
                                return;
                        }
                }
                if(!$password_url) {
                   echo "Please enter a password!";
                   return;
                }
                else {
                        if($password_url != $confirmp_url) {
                                echo "Your passwords don't match!";
                                return;
                        }
                }
                if(!$email_url) {
                   echo "Please enter a email!";
                   return;
                }
                if(!$main->check_email($email_url)) {
                                echo "Your email is the wrong format!";        
                                return;
                }
                else {
                        $query = $db->query("SELECT * FROM `<PRE>users` WHERE `email` = '{$email_url}'");
                        if($db->num_rows($query) != 0) {
                                echo "That e-mail address is already in use!";
                                return;
                        }
                }
                if($human_url != $_SESSION["pass"]) {
                   echo "Human test failed!";
                   return;
                }
                if(!$firstname_url || !preg_match("/^([a-zA-Z\.\'\ \-])+$/",$firstname_apos_chk_url)) {
                   echo "Please enter a valid first name!";
                   return;
                }
                if(!$lastname_url || !preg_match("/^([a-zA-Z\.\'\ \-])+$/",$lastname_apos_chk_url)) {
                   echo "Please enter a valid last name!";
                   return;
                }
                if(!$address_url || !preg_match("/^([0-9a-zA-Z\.\#\ \-])+$/",$address_url)) {
                   echo "Please enter a valid address!";
                   return;
                }
                if(!$city_url || !preg_match("/^([a-zA-Z ])+$/",$city_url)) {
                   echo "Please enter a valid city!";
                   return;
                }
                if(!$zip_url || strlen($zip_url) > 7 || !preg_match("/^([0-9a-zA-Z\ \-])+$/",$zip_url)) {
                   echo "Please enter a valid zip/postal code!";
                   return;
                }
                if(!$state_url || !preg_match("/^([a-zA-Z\.\ -])+$/",$state_url)) {
                   echo "Please enter a valid state!";
                   return;
                }
                if(!$country_url) {
                   echo "Please select a country!";
                   return;
                }
                if(strlen($phone_url) > 15 || !preg_match("/^([0-9\-])+$/",$phone_url)) {
                   echo "Please enter a valid phone number!";
                   return;
                }
                if($coupon_url && $package_data['type'] != 'free') {
                   $coupon_response = $navens_coupons->validate_coupon($coupon_url, "orders", $username_url, $package_url);
                   if(!$coupon_response){
                        echo "Please enter a valid coupon!";
                        return;
                   }else{
                        $coupon_info = $navens_coupons->coupon_data($coupon_url);
                   }
                }
                
                $type2 = $type->createType($type->determineType($package_url));
                if($type2->signup) {
                        $pass = $type2->signup();
                        if($pass) {
                                echo $pass;
                                return;
                        }
                }
                foreach($main->getvar as $key => $value) {
                        $data = explode("_", $key);
                        if($data[0] == "type") {
                                if($n) {
                                        $additional .= ",";        
                                }
                                $additional .= $data[1]."=".$value;
                                $n++;
                        }
                }
                $main->getvar['fplan'] = $type->determineBackend($package_url);
                $serverphp = $this->createServer($package_url); # Create server class
                $pquery2 = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$package_url}'");
                $pname2 = $db->fetch_array($pquery2);
                $done = $serverphp->signup($type->determineServer($package_url), $pname2['reseller']);
                if($done == true) { # Did the signup pass?
                        $date = time();
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $salt = md5(rand(0,9999999));
                        $password = md5(md5($password_url).md5($salt));
                        if($pname2['admin'] == "1"){
                         $status = "3";
                        }else{
                         if($pname2['type'] == "paid"){
                          $status = "4";
                         }else{
                          $status = "1";
                         }
                        }
                        $db->query("INSERT INTO `<PRE>users` (user, email, password, salt, signup, ip, firstname, lastname, address, city, state, zip, country, phone, status, tzadjust) VALUES(
                                                                                                          '{$username_url}',
                                                                                                          '{$email_url}',
                                                                                                          '{$password}',
                                                                                                          '{$salt}',
                                                                                                          '{$date}',
                                                                                                          '{$ip}',
                                                                                                          '{$firstname_url}',
                                                                                                          '{$lastname_url}',
                                                                                                          '{$address_url}',
                                                                                                          '{$city_url}',
                                                                                                          '{$state_url}',
                                                                                                          '{$zip_url}',
                                                                                                          '{$country_url}',
                                                                                                          '{$phone_url}',
                                                                                                          '{$status}',
                                                                                                          '{$tzones_url}')");
                        $rdata = $db->query("SELECT * FROM `<PRE>users` WHERE `user` = '{$username_url}' LIMIT 1;");
                        $rdata_data = $db->fetch_array($rdata);
                        $db->query("INSERT INTO `<PRE>users_bak` (uid, user, email, password, salt, signup, ip, firstname, lastname, address, city, state, zip, country, phone, status) VALUES(
                                                                                                          '{$rdata_data['id']}',
                                                                                                          '{$username_url}',
                                                                                                          '{$email_url}',
                                                                                                          '{$password}',
                                                                                                          '{$salt}',
                                                                                                          '{$date}',
                                                                                                          '{$ip}',
                                                                                                          '{$firstname_url}',
                                                                                                          '{$lastname_url}',
                                                                                                          '{$address_url}',
                                                                                                          '{$city_url}',
                                                                                                          '{$state_url}',
                                                                                                          '{$zip_url}',
                                                                                                          '{$country_url}',
                                                                                                          '{$phone_url}',
                                                                                                          '{$status}')");
                        $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$rdata_data['id']}',
                                                                                                          '{$username_url}',
                                                                                                          '{$date}',
                                                                                                          'Registered.')");
                        $newSQL = "SELECT * FROM `<PRE>users` WHERE `user` = '{$username_url}' LIMIT 1;";
                        $query = $db->query($newSQL);
                        if($db->num_rows($query) == 1) {
                                $data = $db->fetch_array($query);
                                $db->query("INSERT INTO `<PRE>user_packs` (userid, pid, domain, status, signup, additional) VALUES(
                                                                                                          '{$data['id']}',
                                                                                                          '{$package_url}',
                                                                                                          '{$main->getvar['fdom']}',
                                                                                                          '{$status}',
                                                                                                          '{$date}',
                                                                                                          '{$additional}')");
                                $db->query("INSERT INTO `<PRE>user_packs_bak` (userid, pid, domain, status, signup, additional) VALUES(
                                                                                                          '{$data['id']}',
                                                                                                          '{$package_url}',
                                                                                                          '{$main->getvar['fdom']}',
                                                                                                          '{$status}',
                                                                                                          '{$date}',
                                                                                                          '{$additional}')");
                                $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$data['id']}',
                                                                                                          '{$username_url}',
                                                                                                          '{$date}',
                                                                                                          'Package created ({$main->getvar['fdom']})')");

                                if(!empty($coupon_info)){
                                $sdk->thtlog("Coupon used (".$coupon_info['coupcode'].")", $data['id']);

                                $package_info = $type->additional($package_url);
                                $packmonthly = $package_info['monthly'];
                                if($pname2['type'] == "paid"){
                                $coupon_info['p2hmonthlydisc'] = "0";
                                $coupon_info['paiddisc'] = $navens_coupons->percent_to_value("paid", $coupon_info['paidtype'], $coupon_info['paiddisc'], $packmonthly);
                                }else{
                                $coupon_info['paiddisc'] = "0";
                                $coupon_info['p2hmonthlydisc'] = $navens_coupons->percent_to_value("p2h", $coupon_info['p2hmonthlytype'], $coupon_info['p2hmonthlydisc'], $packmonthly);
                                }
                                
                                $insert_array = array("user" =>           $data['id'],
                                                      "coupcode" =>       $coupon_info['coupcode'],
                                                      "timeapplied" =>    time(),
                                                      "packages" =>       $package_url,
                                                      "goodfor" =>        $coupon_info['goodfor'],
                                                      "monthsgoodfor" =>  $coupon_info['monthsgoodfor'],
                                                      "paiddisc" =>       $coupon_info['paiddisc'],
                                                      "p2hmonthlydisc" => $coupon_info['p2hmonthlydisc']);
                                                      
                                $sdk->insert("mod_navens_coupons_used", $insert_array);
                                }

                                $query_server = $db->query("SELECT * FROM <PRE>servers WHERE id = '".$package_server."' LIMIT 1");
                                $query_server_data = $db->fetch_array($query_server);
                                $server_host = $query_server_data['host'];
                                $server_ip = $query_server_data['ip'];
                                $server_nameservers = $query_server_data['nameservers'];
                                $server_port = $query_server_data['port'];
                                $server_whmport = $query_server_data['whmport'];
                                        
                                $url = $db->config("url");
                                $array['CPPORT'] = $server_port;
                                $array['RESELLERPORT'] = $server_whmport;
                                $array['SERVERIP'] = $server_ip;
                                $array['NAMESERVERS'] = nl2br($server_nameservers);
                                $array['USER'] = $username_url;
                                $array['PASS'] = $password_url;
                                $array['EMAIL'] = $email_url;
                                $array['FNAME'] = $firstname_url;
                                $array['LNAME'] = $lastname_url;
                                $array['DOMAIN'] = $main->getvar['fdom'];
                                $array['CONFIRM'] = $url . "client/confirm.php?u=" . $username_url . "&c=" . $date;

                                //Get plan email friendly name
                                $pquery = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$package_url}'");
                                $pname = $db->fetch_array($pquery);
                                $array['PACKAGE'] = $pname['name'];
                                
                                $puser = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$data['id']}'");
                                $puser2 = $db->fetch_array($puser);
                                if($pname['admin'] == 0) {
                                if($pname2['reseller'] == "1"){
                                        $emaildata = $db->emailTemplate("newreselleracc");
                                }else{
                                        $emaildata = $db->emailTemplate("newacc");
                                }
                                        
                                        echo "<strong>Your account has been completed!</strong><br />You may now <a href = '../client'>login</a> to see your client area or proceed to your <a href = 'http://".$server_host.":".$server_port."'>control panel</a>. An email has been dispatched to the address on file.";
                                        if($type->determineType($package_url) == "paid") {
                                                echo " This will only work when you've made your payment.";
                                                $_SESSION['clogged'] = 1;
                                                $_SESSION['cuser'] = $data['id'];
                                        }
                                        $donecorrectly = true;
                                }
                                elseif($pname['admin'] == 1) {
                                        if($serverphp->suspend($username_url, $type->determineServer($package_url)) == true) {
                                                $db->query("UPDATE `<PRE>user_packs` SET `status` = '3' WHERE `id` = '{$puser2['id']}'");
                                                if($pname2['reseller'] == "1"){
                                                    $emaildata = $db->emailTemplate("newreselleraccadmin");
                                                }else{
                                                    $emaildata = $db->emailTemplate("newaccadmin");
                                                }
                                                $emaildata2 = $db->emailTemplate("adminval");
                                                $valarray['LINK'] = $db->config("url").ADMINDIR."/?page=users&sub=search&do=".$data['id'];
                                                $email->staff($emaildata2['subject'], $emaildata2['content'], $valarray);
                                                echo "<strong>Your account is awaiting admin validation!</strong><br />An email has been dispatched to the address on file. You will recieve another email when the admin has looked over your account.";
                                                $donecorrectly = true;
                                        }
                                        else {
                                                echo "Something with admin validation went wrong (suspend). Your account should be running but contact your host!";        
                                        }
                                }
                                else {
                                        echo "Something with admin validation went wrong. Your account should be running but contact your host!";        
                                }
                                $email->send($array['EMAIL'], $emaildata['subject'], $emaildata['content'], $array);
                        }
                        else {
                                echo "Your username doesn't exist in the DB meaning the query failed or it exists more than once!";        
                        }
                        if($donecorrectly && $type->determineType($package_url) == "paid") {
                                global $invoice;
                                $amountinfo = $type->additional($package_url);
                                $amount = $amountinfo['monthly'];
                                if(!empty($coupon_info)){
                                $amount = max(0, $amount - $coupon_info['paiddisc']);
                                }
                                $due = time()+2592000;
                                $notes = "Your current hosting package monthly invoice. Package: ". $pname['name'];
                                $invoice->create($data['id'], $amount, $due, $notes);
                                $serverphp->suspend($username_url, $type->determineServer($package_url));
                                $db->query("UPDATE `<PRE>user_packs` SET `status` = '".$status."' WHERE `id` = '{$data['id']}'");
                                $iquery = $db->query("SELECT * FROM `<PRE>invoices` WHERE `uid` = '{$data['id']}' AND `due` = '{$due}'");
                                $idata = $db->fetch_array($iquery);
                                if($pname['admin'] != "1"){
                                echo '<div class="errors"><b>You are being redirected to payment! It will load in a couple of seconds..</b></div>';
                                }
                        }
                }
        }
        public function terminate($id, $reason = false) { # Deletes a user account from the package ID
                global $db, $main, $type, $email;
                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist or cannot be terminated!";
                        $array['User PID'] = $id;
                        $main->error($array);
                        return;        
                }
                else {
                        $data = $db->fetch_array($query);
                        $query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
                        $data2 = $db->fetch_array($query2);
                        $server = $type->determineServer($data['pid']);
                        if(!is_object($this->servers[$server])) {
                                $this->servers[$server] = $this->createServer($data['pid']); # Create server class
                        }
                        if($this->servers[$server]->terminate($data2['user'], $server) == true) {
                                $date = time();
                                $emaildata = $db->emailTemplate("termacc");
                                if(!$reason){
                                $reason = "None given";
                                }
                                $array['REASON'] = $reason;
                                $email->send($data2['email'], $emaildata['subject'], $emaildata['content'], $array);
                                $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$db->strip($data['userid'])}',
                                                                                                          '{$data2['user']}',
                                                                                                          '{$date}',
                                                                                                          'Terminated ($reason)')");
                                $db->query("DELETE FROM `<PRE>user_packs` WHERE `id` = '{$data['id']}'");
                                $db->query("DELETE FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
                                $db->query("DELETE FROM `<PRE>mod_navens_upgrade` WHERE `uid` = '{$db->strip($data['userid'])}'");
                                return true;
                        }
                        else {
                                return false;        
                        }
                }
        }
        public function cancel($id, $reason = false) { # Deletes a user account from the package ID
                global $db, $main, $type, $email;
                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND `status` != '9'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist or cannot be cancelled! Are you trying to cancel an already cancelled account?";
                        $array['User PID'] = $id;
                        $main->error($array);
                        return;        
                }
                else {
                        $data = $db->fetch_array($query);
                        $query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
                        $data2 = $db->fetch_array($query2);
                        $server = $type->determineServer($data['pid']);
                        if(!is_object($this->servers[$server])) {
                                $this->servers[$server] = $this->createServer($data['pid']); # Create server class
                        }
                        if($this->servers[$server]->terminate($data2['user'], $server) == true) {
                                $date = time();
                                $emaildata = $db->emailTemplate("cancelacc");
                                $array['REASON'] = "Account Cancelled.";
                                $email->send($data2['email'], $emaildata['subject'], $emaildata['content'], $array);
                                $db->query("UPDATE `<PRE>user_packs` SET `status` = '9' WHERE `id` = '{$data['id']}'");
                                $db->query("UPDATE `<PRE>users` SET `status` = '9' WHERE `id` = '{$db->strip($data['userid'])}'");
                                $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$db->strip($data['userid'])}',
                                                                                                          '{$data2['user']}',
                                                                                                          '{$date}',
                                                                                                          'Cancelled  ($reason)')");
                                return true;
                        }
                        else {
                                return false;        
                        }
                }
        }
        public function decline($id) { # Deletes a user account from the package ID
                global $db, $main, $type, $email;
                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND `status` != '9'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist or cannot be cancelled! Are you trying to cancel an already cancelled account?";
                        $array['User PID'] = $id;
                        $main->error($array);
                        return;        
                }
                else {
                        $data = $db->fetch_array($query);
                        $query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
                        $data2 = $db->fetch_array($query2);
                        $server = $type->determineServer($data['pid']);
                        if(!is_object($this->servers[$server])) {
                                $this->servers[$server] = $this->createServer($data['pid']); # Create server class
                        }
                        if($this->servers[$server]->terminate($data2['user'], $server) == true) {
                                $date = time();
                                $emaildata = $db->emailTemplate("cancelacc");
                                $array['REASON'] = "Account Declined.";
                                $email->send($data2['email'], $emaildata['subject'], $emaildata['content'], $array);
                                $db->query("UPDATE `<PRE>user_packs` SET `status` = '9' WHERE `id` = '{$data['id']}'");
                                $db->query("UPDATE `<PRE>users` SET `status` = '9' WHERE `id` = '{$db->strip($data['userid'])}'");
                                $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$db->strip($data['userid'])}',
                                                                                                          '{$data2['user']}',
                                                                                                          '{$date}',
                                                                                                          'Declined  (Package ID $id)')");
                                return true;
                        }
                        else {
                                return false;        
                        }
                }
        }
        public function suspend($id, $reason = false) { # Suspends a user account from the package ID
                global $db, $main, $type, $email;
                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND `status` = '1'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist or cannot be suspended!";
                        $array['User PID'] = $id;
                        $main->error($array);
                        return;        
                }
                else {
                        $data = $db->fetch_array($query);
                        $query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
                        $data2 = $db->fetch_array($query2);
                        $server = $type->determineServer($data['pid']);
                        global $serverphp;
                        if(!is_object($this->servers[$server]) && !$serverphp) {
                                $this->servers[$server] = $this->createServer($data['pid']); # Create server class
                                $donestuff = $this->servers[$server]->suspend($data2['user'], $server, $reason);
                        }
                        else {
                                $donestuff = $serverphp->suspend($data2['user'], $server, $reason);
                        }
                        if($donestuff == true) {
                                $date = time();
                                $db->query("UPDATE `<PRE>user_packs` SET `status` = '2' WHERE `id` = '{$data['id']}'");
                                $db->query("UPDATE `<PRE>users` SET `status` = '2' WHERE `id` = '{$db->strip($data['userid'])}'");
                                $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$db->strip($data['userid'])}',
                                                                                                          '{$data2['user']}',
                                                                                                          '{$date}',
                                                                                                          'Suspended ($reason)')");
                                $emaildata = $db->emailTemplate("suspendacc");
                                if(!$reason){
                                $reason = "None given";
                                }
                                $email_arr['REASON'] = $reason;
                                $email->send($data2['email'], $emaildata['subject'], $emaildata['content'], $email_arr);
                                return true;
                        }
                        else {
                                return false;        
                        }
                }
        }
        public function changePwd($id, $newpwd) { # Changes user's password.
                global $db, $main, $type, $email;
                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist!";
                        $array['User PID'] = $id;
                        $main->error($array);
                        return;
                }
                else {
                        $data = $db->fetch_array($query);
                        $query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
                        $data2 = $db->fetch_array($query2);
                        $server = $type->determineServer($data['pid']);
                        global $serverphp;
                        if(!is_object($this->servers[$server]) && !$serverphp) {
                                $this->servers[$server] = $this->createServer($data['pid']); # Create server class
                                $donestuff = $this->servers[$server]->changePwd($data2['user'], $newpwd, $server);
                        }
                        else {
                                $donestuff = $serverphp->changePwd($data2['user'], $newpwd, $server);
                        }
                        if($donestuff == true) {
                                $date = time();
                                $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$db->strip($data['userid'])}',
                                                                                                          '{$data2['user']}',
                                                                                                          '{$date}',
                                                                                                          'cPanel password updated.')");
                                return true;
                        }
                        else {
                                return false;        
                        }
                }
        }
        
        public function unsuspend($id, $noemail = 0) { # Unsuspends a user account from the package ID
                global $db, $main, $type, $email;
                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND (`status` = '2' OR `status` = '3' OR `status` = '4')");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist or cannot be unsuspended!";
                        $array['User PID'] = $id;
                        $main->error($array);
                        return;        
                }
                else {
                        $data = $db->fetch_array($query);
                        $query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
                        $data2 = $db->fetch_array($query2);
                        $server = $type->determineServer($data['pid']);
                        if(!is_object($this->servers[$server])) {
                                $this->servers[$server] = $this->createServer($data['pid']); # Create server class
                        }
                        if($this->servers[$server]->unsuspend($data2['user'], $server) == true) {
                                $date = time();
                                $db->query("UPDATE `<PRE>user_packs` SET `status` = '1' WHERE `id` = '{$data['id']}'");
                                $db->query("UPDATE `<PRE>users` SET `status` = '1' WHERE `id` = '{$db->strip($data['userid'])}'");
                                $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$db->strip($data['userid'])}',
                                                                                                          '{$data2['user']}',
                                                                                                          '{$date}',
                                                                                                          'Unsuspended.')");
                                if($noemail == "0"){
                                $emaildata = $db->emailTemplate("unsusacc");
                                $email->send($data2['email'], $emaildata['subject'], $emaildata['content']);
                                }
                                return true;
                        }
                        else {
                                return false;        
                        }
                }
        }
        
        public function approve($id) { # Approves a user's account (Admin Validation).
                global $db, $main, $type, $email;
                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$db->strip($id)}' AND (`status` = '2' OR `status` = '3' OR `status` = '4')");
                $uquery = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$query['userid']}' AND (`status` = '1')");
                if($db->num_rows($query) == 0 AND $db->num_rows($uquery) == 0) {
                        $array['Error'] = "That package doesn't exist or cannot be approved! (Did they confirm their e-mail?)";
                        $array['User PID'] = $id;
                        $main->error($array);
                        return;        
                }
                else {
                        $data = $db->fetch_array($query);
                        $query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `id` = '{$db->strip($data['userid'])}'");
                        $data2 = $db->fetch_array($query2);
                        $server = $type->determineServer($data['pid']);
                        if(!is_object($this->servers[$server])) {
                                $this->servers[$server] = $this->createServer($data['pid']); # Create server class
                        }
                        if($this->servers[$server]->unsuspend($data2['user'], $server) == true) {
                                $date = time();
                                $db->query("UPDATE `<PRE>user_packs` SET `status` = '1' WHERE `id` = '{$data['id']}'");
                                $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                          '{$db->strip($data['userid'])}',
                                                                                                          '{$data2['user']}',
                                                                                                          '{$date}',
                                                                                                          'Approved (Package ID $id)')");
                                return true;
                        }
                        else {
                                return false;        
                        }
                }
        }
        
        public function confirm($username, $confirm) { # Set's user's account to Active when the unique link is visited.
                global $db, $main, $type, $email;
                $query = $db->query("SELECT * FROM `<PRE>users` WHERE `user` = '{$username}' AND `signup` = {$confirm} AND `status` = '3'");
                if($db->num_rows($query) == 0) {
                        $array['Error'] = "That package doesn't exist or cannot be confirmed!";
                        $main->error($array);
                        return false;        
                }
                else {
                        $data = $db->fetch_array($query);
                        $date = time();
                        $db->query("UPDATE `<PRE>users` SET `status` = '1' WHERE `user` = '{$username}'");
                        $db->query("INSERT INTO `<PRE>logs` (uid, loguser, logtime, message) VALUES(
                                                                                                  '{$db->strip($data['userid'])}',
                                                                                                  '{$data['user']}',
                                                                                                  '{$date}',
                                                                                                  'Account/E-mail Confirmed.')");
                        return true;
                }
        }
        
        public function testConnection($serverId) {
                global $db;
                $query = $db->query("SELECT `type` FROM `<PRE>servers` WHERE `id` = {$serverId}");
                if($db->num_rows($query) == 0) {
                        return "There is no server with an id of {$serverId}";
                }
                else {
                                $data = $db->fetch_array($query);
                                $type = $data["type"];
                                $link = LINK."servers/".$type.".php";
                                if(!file_exists($link)) {
                                        return "The server {$type}.php doesn't exist!";
                                }
                                else {
                                        require_once($link);
                                        $server = new $type($serverId);
                                        return $server->testConnection();
                                }
                }
        }
}
?>
