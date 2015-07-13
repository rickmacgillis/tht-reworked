<?php
//////////////////////////////
// The Hosting Tool
// Admin Area - General Settings
// By Jonny H
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){die();}

class page {
        
        public $navtitle;
        public $navlist = array();
                                                        
        public function __construct() {
                $this->navtitle = "Clients Sub Menu";
                $this->navlist[] = array("Search Clients", "magnifier.png", "search");
                $this->navlist[] = array("Client Statistics", "book.png", "stats");
                $this->navlist[] = array("Admin Validate", "user_suit.png", "validate");
                $this->navlist[] = array("Approve Upgrades", "accept.png", "upgrade");
        }
        
        public function description() {
                global $db, $main;
                $query = $db->query("SELECT * FROM `<PRE>users` ORDER BY `signup` DESC");
                if($db->num_rows($query) != 0) {
                        $data = $db->fetch_array($query);
                        $newest = $main->sub("Latest Signup:", $data['user']);
                }
                return "<strong>Clients</strong><br />
                This is the area where you can manage all your clients that have signed up for your service. You can perform a variety of tasks like suspend, terminate, email and also check up on their requirements and stats.". $newest;        
        }
        
        public function content() { # Displays the page 
                global $main;
                global $style;
                global $db;
                global $server;
                global $email;
                global $type;
                global $sdk;
                global $navens_upgrade;
                switch($main->getvar['sub']) {
                        default:
                                if($main->getvar['do'] ) {
                                        if($main->postvar['submitnewpack']){
                                            $new_pack    = $main->postvar['newpackage'];
                                            $immediately = $main->postvar['immediately'];
                                            $userid      = $main->getvar['do'];
                                            if(is_numeric($new_pack) && is_numeric($userid)){

                                                $upack_info = $sdk->uidtopack($userid);
                                                if($upack_info['packages']['id'] == $new_pack){
                                                    $main->errors("The user is already on the package specified.  Please choose a different package if you wish to change their package.");
                                                }else{
                                                    $new_pack_info = $sdk->tdata("packages", "id", $new_pack);
                                                    
                                                    if($new_pack_info['server'] != $upack_info['packages']['server']){
                                                        $new_server = 1;
                                                    }

                                                    if(!$immediately){
                                                        $response = $navens_upgrade->prorate($new_pack, "", $userid, 1);
                                                        if($response == "now"){
                                                            $immediately = 1;
                                                        }
                                                        if(substr_count($response, "check")){
                                                            $no_upgrade = 1;
                                                        }
                                                    }

                                                    if($immediately){
                                                        if($new_server){
                                                            $flags = "7";
                                                            $message = "The user has been upgraded and is now <font color = '#779500'>on the new server</font>.  Please be sure to remove the account on the old server when the user has migrated their website.";
                                                            
                                                        }else{
                                                            $flags = "0";
                                                            $message = "The user has been upgraded.";
                                                        }
                                                    }else{
                                                        if($new_server){
                                                            $flags = "4";
                                                            $message = "The user has been prepared for their upgrade <font color = '#779500'>on the new server</font> at the end of their current billing cycle.";
                                                        }else{
                                                            $flags = "1";
                                                            $message = "The user has been prepared for their upgrade at the end of their current billing cycle.";
                                                        }
                                                    }

                                                    if($no_upgrade){
                                                        $main->errors("The user cannot be changed to a P2H package until they have entered their credentials.  To do this, have the user log in and try to upgrade to the P2H package.  If the upgrade fails, the credentials are saved and you'll be able to upgrade them using this method.  If the upgrade succeeds, you don't need to do anything.  If the upgrade requires your approval, you'll be notified via email.");
                                                    }else{
                                                        $existing_upgrade = $sdk->tdata("mod_navens_upgrade", "uid", $userid);
                                                        if($existing_upgrade){
                                                            $db->query("UPDATE <PRE>mod_navens_upgrade SET created = '".time()."', newpack = '".$new_pack."', flags = '".$flags."' WHERE id = '".$existing_upgrade['id']."' LIMIT 1");
                                                        }else{
                                                            $db->query("INSERT INTO <PRE>mod_navens_upgrade SET created = '".time()."', newpack = '".$new_pack."', flags = '".$flags."', uid = '".$userid."'");
                                                        }
                                                        $existing_upgrade = $sdk->tdata("mod_navens_upgrade", "uid", $userid);
                                                        
                                                        $done = $navens_upgrade->upgrade($existing_upgrade['id'], "Update", 1);
                                                        if($done === false){
                                                            $db->query("DELETE FROM <PRE>mod_navens_upgrade WHERE id = '".$existing_upgrade['id']."' LIMIT 1");
                                                            echo "<br><br>";
                                                        }else{
                                                            $main->errors($message);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                
                                        $client = $db->client($main->getvar['do']);
                                        $pack2 = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$main->getvar['do']}'");
                                        $pack = $db->fetch_array($pack2);
                                        switch ($main->getvar['func']) {
                                                case "sus":
                            if(!empty($main->getvar['reason'])) {
                                                                $command = $server->suspend($pack['id'], $main->getvar['reason']);
                            }
                            else {
                                                                $command = $server->suspend($pack['id']);
                            }
                                                        if($command === true) {
                                                                $main->errors("User has been suspended!");        
                                                        }
                                                        else {
                                                                $main->errors($command);
                                                        }
                                                        break;
                                                        
                                                case "unsus":
                                                        $command = $server->unsuspend($pack['id']);
                                                        if($command == true) {
                                                                $main->errors("User has been unsuspended!");        
                                                        }
                                                        else {
                                                                $main->errors($command);
                                                        }
                                                        break;
                                                        
                                                case "cancel":
                                                        if(!empty($main->getvar['reason'])) {
                                                                $command = $server->cancel($pack['id'], $main->getvar['reason']);
                            }
                            else {
                                                                $command = $server->cancel($pack['id']);
                            }
                                                        if($command == true) {
                                                                $main->errors("User has been cancelled!");
                                                                $main->done();
                                                        }
                                                        else {
                                                                $main->errors($command);
                                                        }
                                                        break;
                                                
                                                case "term":
                                                        if(!empty($main->getvar['reason'])) {
                                                                $command = $server->terminate($pack['id'], $main->getvar['reason']);
                            }
                            else {
                                                                $command = $server->terminate($pack['id']);
                            }
                                                        if($command == true) {
                                                                $main->errors("User has been terminated!");
                                                                $main->done();
                                                        }
                                                        else {
                                                                $main->errors($command);
                                                        }
                                                        break;
                                        }
                                }
                                if($main->getvar['do'] ) {
                                        $client = $db->client($main->getvar['do']);
                                        $pack2 = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `userid` = '{$main->getvar['do']}'");
                                        $pack = $db->fetch_array($pack2);
                                }
                                if($main->getvar['do'] ) {
                                        if($pack['status'] == "2") {
                                                $array['SUS'] = "Unsuspend";
                                                $array['FUNC'] = "unsus";
                                                $array['IMG'] = "accept.png";
                                        }
                                        elseif($pack['status'] == "1") {
                                                $array['SUS'] = "Suspend";
                                                $array['FUNC'] = "sus";        
                                                $array['IMG'] = "exclamation.png";
                                        }
                                        elseif($pack['status'] == "3") {
                                                $array['SUS'] = "<a href='?page=users&sub=validate'>Validate</a>";
                                                $array['FUNC'] = "none";        
                                                $array['IMG'] = "user_suit.png";
                                        }
                                        elseif($pack['status'] == "4") {
                                                $array['SUS'] = "Awaiting Payment";
                                                $array['FUNC'] = "none";        
                                                $array['IMG'] = "money.png";
                                        }
                                        elseif($pack['status'] == "9") {
                                                $array['SUS'] = "No Action";
                                                $array['FUNC'] = "none";        
                                                $array['IMG'] = "cancel.png";
                                        }
                                        else {
                                                $array['SUS'] = "Other Status";
                                                $array['FUNC'] = "none";        
                                                $array['IMG'] = "help.png";        
                                        }
                                        $array['ID'] = $main->getvar['do'];
                                        switch($main->getvar['func']) {
                                                default:
                                                        $array2['DATE'] = $main->convertdate("n/d/Y", $client['signup']);
                                                        $array2['EMAIL'] = $client['email'];
                                                        $query = $db->query("SELECT * FROM `<PRE>packages` WHERE `id` = '{$db->strip($pack['pid'])}'");
                                                        $data2 = $db->fetch_array($query);
                                                        $array2['UPGRADEINFO'] = "";
                                                        $existing_upgrade = $sdk->tdata("mod_navens_upgrade", "uid", $main->getvar['do']);
                                                        $all_packs_query = $db->query("SELECT * FROM <PRE>packages WHERE is_disabled = '0' ORDER BY `type` ASC");
                                                        while($all_packs_data = $db->fetch_array($all_packs_query)){
                                                            $additional = $type->additional($all_packs_data['id']);
                                                            $monthly    = $additional['monthly'];
                                                            $signup     = $additional['signup'];

                                                            unset($info);
                                                            if($all_packs_data['type'] == "p2h") {
                                                                $info = "[Signup Posts: ".$signup.", Monthly Posts: ".$monthly."] ";
                                                            }
                                                            elseif($all_packs_data['type'] == "paid") {
                                                                $info = "[".$sdk->money($monthly)."] ";
                                                            }
            
                                                            $packages[] = array("[".$all_packs_data['type']."] ".$info.$all_packs_data['name'], $all_packs_data['id']);
                                                            
                                                            if($existing_upgrade && $existing_upgrade['newpack'] == $all_packs_data['id']){
                                                                if($all_packs_data['admin']){
                                                                    $admin = " after you approve them";
                                                                }
                                                                
                                                                if($existing_upgrade['flags'] && $existing_upgrade['flags'] < 5){
                                                                    $next_cycle = " next billing cycle";
                                                                }
                                                                $array2['UPGRADEINFO'] = "NOTE: This user is slated for an upgrade to \"".$all_packs_data['name']."\"".$next_cycle.$admin.".<br><br>"; //I iz smart.  =)
                                                            }
                                                        }
                                                        
                                                        $array2['PACKAGE'] = $main->dropdown("newpackage", $packages, $pack['pid']);
                                                        $array2['USER'] = $client['user'];
                                                        $array2['DOMAIN'] = $client['domain'];
                                                        $array2['CLIENTIP'] = $client['ip'];
                                                        $array2['FIRSTNAME'] = $client['firstname'];
                                                        $array2['LASTNAME'] = $client['lastname'];
                                                        $array2['ADDRESS'] = $client['address'];
                                                        $array2['CITY'] = $client['city'];
                                                        $array2['STATE'] = $client['state'];
                                                        $array2['ZIP'] = $client['zip'];
                                                        $array2['COUNTRY'] = strtolower($client['country']);
                                                        $array2['FULLCOUNTRY'] = $main->country_code_to_country($client['country']);
                                                        $array2['PHONE'] = $client['phone'];
                                                        $invoicesq = $db->query("SELECT * FROM `<PRE>invoices` WHERE `uid` = '{$db->strip($client['id'])}' AND `is_paid` = '0'");
                                                        $array2['INVOICES'] = $db->num_rows($invoicesq);
                                                        switch($pack['status']) {
                                                                default:
                                                                        $array2['STATUS'] = "Other";
                                                                        break;
                                                                        
                                                                case "1":
                                                                        $array2['STATUS'] = "Active";
                                                                        break;
                                                                        
                                                                case "2":
                                                                        $array2['STATUS'] = "Suspended";
                                                                        break;
                                                                        
                                                                case "3":
                                                                        $array2['STATUS'] = "Awaiting Validation";
                                                                        break;
                                                                
                                                                case "4":
                                                                        $array2['STATUS'] = "Awaiting Payment";
                                                                        break;
                                                                
                                                                case "9":
                                                                        $array2['STATUS'] = "Cancelled";
                                                                        break;
                                                        }
                                                        $class = $type->determineType($pack['pid']);
                                                        $phptype = $type->classes[$class];
                                                        if($phptype->acpBox) {
                                                                $box = $phptype->acpBox();        
                                                                $array['BOX'] = $main->sub($box[0], $box[1]);
                                                        }
                                                        else {
                                                                $array['BOX'] = "";        
                                                        }
                                                        $array['CONTENT'] = $navens_upgrade->tpl("admin/clientdetails.tpl", $array2);
                                                        break;
                                                        
                                                case "email":
                                                        if($_POST) {
                                                                global $email;
                                                                $result = $email->send($client['email'] ,$main->postvar['subject'], $main->postvar['content']);
                                                                if($result) {
                                                                        $main->errors("Email sent!");
                                                                }
                                                                else {
                                                                        $main->errors("Email was not sent out!");
                                                                }
                                                        }
                                                        $array['BOX'] = "";
                                                        $array['CONTENT'] = $style->replaceVar("tpl/emailclient.tpl");
                                                        break;
                                                case "passwd":
                                                        $array['MSG'] = "This will change the user's password in ALL accounts.  (THT, WHM/DirectAdmin, and cPanel, FTP, etc.)<br><br>";
                                                        if($_POST) {
                                                                if(empty($main->postvar['passwd'])) {
                                                                        $main->errors('A password was not provided.');
                                                                        $array['BOX'] = "";
                                                                        $array['CONTENT'] = $style->replaceVar("tpl/clientpwd.tpl", $array);
                                                                }
                                                                else {
                                                                        $command = $main->changeClientPassword($pack['id'], $main->postvar['passwd']);
                                                                        if($command === true) {
                                                                                $main->errors('Password changed!');
                                                                        }
                                                                        else {
                                                                                $main->errors((string)$command);
                                                                        }
                                                                }
                                                        }
                                                        $array['BOX'] = "";
                                                        $array['CONTENT'] = $style->replaceVar("tpl/clientpwd.tpl", $array);
                                                        break;
                                        }
                                        $array["URL"] = URL;
                                        $array['USER'] = $client['user'];
                                        echo $style->replaceVar("tpl/clientview.tpl", $array);
                                }
                                else {
                                        $array['NAME'] = $db->config("name");
                                        $array['URL'] = $db->config("url");
                                        $values[] = array("Admin Area", ADMINDIR);
                                        $values[] = array("Order Form", "order");
                                        $values[] = array("Client Area", "client");
                                        $array['DROPDOWN'] = $main->dropDown("default", $values, $db->config("default"));
                                        echo $style->replaceVar("tpl/clientsearch.tpl", $array);
                                }
                                break;
                        
                        //Displays a list of users based on account status.
                        case "list":
                                echo "<div class=\"subborder\"><form id=\"filter\" name=\"filter\" method=\"post\" action=\"\"><select size=\"1\" name=\"show\"><option value=\"all\">ALL</option><option value=\"1\">Active</option><option value=\"0\">Awaiting Validation</option><option value=\"2\">Suspended</option><option value=\"9\">Cancelled</option></select><input type=\"submit\" name=\"filter\" id=\"filter\" value=\"Filter Accounts\" /></form><table width=\"100%\" cellspacing=\"2\" cellpadding=\"2\" border=\"1\" style=\"border-collapse: collapse\" bordercolor=\"#000000\"><tr bgcolor=\"#EEEEEE\">";
                                echo "<td width=\"100\" align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">Date Registered</td><td width=\"100\" align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">Username</td><td align=\"center\" style=\"border-collapse: collapse\" bordercolor=\"#000000\">E-mail</td></tr>";
                                $l = $main->getvar['l'];
                                $p = $main->getvar['p'];
                                if (!$main->postvar['show'] && !$main->getvar['show']) {
                                        $show = "all";
                                }
                                if (!$main->postvar['show']) {
                                        $show = $main->getvar['show'];
                                }
                                else {
                                        $show = $main->postvar['show'];
                                        $p = 0;
                                }
                                if (!($l)) {
                                        $l = 10;
                                }
                                if (!($p)) {
                                        $p = 0;
                                }
                                if ($show != "all") {
                                        $query = $db->query("SELECT * FROM `<PRE>users` WHERE `status` = '$show'");
                                }
                                else {
                                        $query = $db->query("SELECT * FROM `<PRE>users`");
                                }
                                $pages = intval($db->num_rows($query)/$l);
                                if ($db->num_rows($query)%$l) {
                                        $pages++;
                                }
                                $current = ($p/$l) + 1;
                                if (($pages < 1) || ($pages == 0)) {
                                        $total = 1;
                                }
                                else {
                                        $total = $pages;
                                }
                                $first = $p + 1;
                                if (!((($p + $l) / $l) >= $pages) && $pages != 1) {
                                        $last = $p + $l;
                                }
                                else{
                                        $last = $db->num_rows($query);
                                }
                                if ($show != "all") {
                                        $query2 = $db->query("SELECT * FROM `<PRE>users` WHERE `status` = '$show' ORDER BY `user` ASC LIMIT $p, $l");
                                }
                                else {
                                        $query2 = $db->query("SELECT * FROM `<PRE>users` ORDER BY `user` ASC LIMIT $p, $l");
                                }
                                if ($db->num_rows($query2) == 0) {
                                        echo "No accounts found.";
                                }
                                else {
                                        while($data = $db->fetch_array($query2)) {
                                                $array['ID'] = $data['id'];
                                                $array['USER'] = $data['user'];
                                                $array['EMAIL'] = $data['email'];
                                                $array['DATE'] = $main->convertdate("n/d/Y", $data['signup']);
                                        echo $style->replaceVar("tpl/clientlist.tpl", $array);
                                        }
                                }
                                echo "</table></div>";
                                echo "<center>";
                                if ($p != 0) {
                                        $back_page = $p - $l;
                                        echo("<a href=\"$PHP_SELF?page=users&sub=list&show=$show&p=$back_page&l=$l\">BACK</a>    \n");
                                }

                                for ($i=1; $i <= $pages; $i++) {
                                        $ppage = $l*($i - 1);
                                        if ($ppage == $p){
                                                echo("<b>$i</b>\n");
                                        }
                                        else{
                                                echo("<a href=\"$PHP_SELF?page=users&sub=list&show=$show&p=$ppage&l=$l\">$i</a> \n");
                                        }
                                }

                                if (!((($p+$l) / $l) >= $pages) && $pages != 1) {
                                        $next_page = $p + $l;
                                        echo("    <a href=\"$PHP_SELF?page=users&sub=list&show=$show&p=$next_page&l=$l\">NEXT</a>");
                                }
                                echo "</center>";
                                break;
                                
                        case "stats":
                                $query = $db->query("SELECT * FROM `<PRE>users`");
                                $array['CLIENTS'] = $db->num_rows($query);
                                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `status` = '1'");
                                $array['ACTIVE'] = $db->num_rows($query);
                                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `status` = '2'");
                                $array['SUSPENDED'] = $db->num_rows($query);
                                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `status` = '3'");
                                $array['ADMIN'] = $db->num_rows($query);
                                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `status` = '9'");
                                $array['CANCELLED'] = $db->num_rows($query);
                                echo $style->replaceVar("tpl/clientstats.tpl", $array);
                                break;
                                
                        case "validate":
                                if($main->getvar['do']) {
                                        if($main->getvar['accept'] == 1) {
                                                if($server->approve($main->getvar['do'])) {
                                                        $main->errors("Account activated!");
                                                        $emaildata = $db->emailTemplate("approvedacc");
                                                        $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$main->getvar['do']}'");
                                                        $data = $db->fetch_array($query);
                                                        $client = $db->client($data['userid']);
                                                        $db->query("UPDATE `<PRE>users` SET `status` = '1' WHERE `id` = '{$client['id']}'");
                                                        $email->send($client['email'], $emaildata['subject'], $emaildata['content']);
                                                }
                                        }
                                        else {
                                                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `id` = '{$main->getvar['do']}'");
                                                $data = $db->fetch_array($query);
                                                $client = $db->client($data['userid']);
                                                if($server->decline($main->getvar['do'])) {
                                                        $main->errors("Account declined!");
                                                }        
                                        }
                                }
                                $query = $db->query("SELECT * FROM `<PRE>user_packs` WHERE `status` = '3'");
                                if($db->num_rows($query) == 0) {
                                        echo "No clients are awaiting validation!";        
                                }
                                else {
                                        $tpl .= "<ERRORS>";
                                        while($data = $db->fetch_array($query)) {
                                                $client = $db->client($data['userid']);
                                                $array['USER'] = $client['user'];        
                                                $array['EMAIL'] = $client['email'];
                                                $array['DOMAIN'] = $data['domain'];
                                                $array['ID'] = $data['id'];
                                                $array['CLIENTID'] = $data['userid'];
                                                $tpl .= $style->replaceVar("tpl/adminval.tpl", $array);
                                        }
                                        echo $tpl;
                                }
                                break;
                                
                                case "upgrade":
                                    if(is_numeric($main->getvar['do'])){
                                        $upgrade_stubid = $main->getvar['do'];
                                        $upgrade_stub_data = $sdk->tdata("mod_navens_upgrade", "id", $upgrade_stubid);
                                        $client = $db->client($upgrade_stub_data['uid']);
                                        $user_data = $sdk->uidtopack($upgrade_stub_data['uid']);
                                        $new_pack_data = $sdk->tdata("packages", "id", $upgrade_stub_data['newpack']);
                                        
                                        if($main->getvar['accept']){
                                            switch($upgrade_stub_data['flags']){

                                                case "2";
                                                    $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '1' WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                                    $done = $navens_upgrade->upgrade($upgrade_stubid, "Update", 1);
                                                    if($done === false){
                                                        $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '2' WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                                        echo "<br><br>";
                                                    }else{
                                                        $main->errors("The user has been prepared for their upgrade at the end of their current billing cycle.<br>");
                                                    }
                                                break;

                                                case "3";
                                                    $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '4' WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                                    $done = $navens_upgrade->upgrade($upgrade_stubid, "Update", 1);
                                                    if($done === false){
                                                        $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '3' WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                                        echo "<br><br>";
                                                    }else{
                                                        $main->errors("The user has been prepared for their upgrade <font color = '#779500'>on the new server</font> at the end of their current billing cycle.<br>");
                                                    }
                                                break;

                                                case "5";
                                                    $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '0' WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                                    $done = $navens_upgrade->upgrade($upgrade_stubid, "Update", 1);
                                                    if($done === false){
                                                        $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '5' WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                                        echo "<br><br>";
                                                    }else{
                                                        $main->errors("The user has been upgraded.<br>");
                                                    }
                                                break;

                                                case "6";
                                                    $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '7' WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                                    $done = $navens_upgrade->upgrade($upgrade_stubid, "Update", 1);
                                                    if($done === false){
                                                        $db->query("UPDATE <PRE>mod_navens_upgrade SET flags = '6' WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                                        echo "<br><br>";
                                                    }else{
                                                        $main->errors("The user has been upgraded and is now <font color = '#779500'>on the new server</font>.  Please be sure to remove the account on the old server when the user has migrated their website.<br>");
                                                    }
                                                break;
                                                
                                            }
                                        }else{
                                            $db->query("DELETE FROM <PRE>mod_navens_upgrade WHERE id = '".$upgrade_stubid."' LIMIT 1");
                                            $main->errors("The user's upgrade request has been denied.<br>");
                                            
                                            $deny_array['OLDPLAN']  = $user_data['packages']['name'];
                                            $deny_array['NEWPLAN']  = $new_pack_data['name'];
                                            $uemaildata = $db->emailTemplate("upgrade_denied");
                                            $email->send($client['email'], $uemaildata['subject'], $uemaildata['content'], $deny_array);
                                            $sdk->thtlog("Upgrade denied for ".$client['user']." <br><b>Current package: </b>".$user_data['packages']['name']." <br><b>Requested package: </b>".$new_pack_data['name'], $upgrade_stub_data['uid']);
                                        }
                                    }
                                    
                                    $query = $db->query("SELECT * FROM `<PRE>mod_navens_upgrade` WHERE flags = '2' OR flags = '3' OR flags = '5' OR flags = '6'");
                                    if($db->num_rows($query) == 0) {
                                        echo "<ERRORS>No clients are awaiting upgrade approval.";
                                    }
                                    else {
                                        $tpl .= "<ERRORS>The users listed here have prequalified for upgrades, but admin approval was needed on the packages they selected.";
                                        while($data = $db->fetch_array($query)) {
                                                $client = $db->client($data['uid']);
                                                $user_data = $sdk->uidtopack($data['uid']);
                                                $new_pack_data = $sdk->tdata("packages", "id", $data['newpack']);
                                                
                                                if($data['flags'] == "2" || $data['flags'] == "3"){
                                                    $array['EFFECTIVE'] = "Next Billing Cycle";
                                                }else{
                                                    $array['EFFECTIVE'] = "Immediately";
                                                }

                                                $array['USER']      = $client['user'];
                                                $array['EMAIL']     = $client['email'];
                                                $array['DOMAIN']    = $user_data['user_packs']['domain'];
                                                $array['OLDPLAN']   = $user_data['packages']['name'];
                                                $array['NEWPLAN']   = $new_pack_data['name'];
                                                $array['NEWSERVER'] = $user_data['packages']['server'] != $new_pack_data['server'] ? "<font color = '#FF0055'>Yes</font>" : "<font color = '#779500'>No</font>";
                                                $array['ID']        = $data['id'];
                                                $array['CLIENTID']  = $data['uid'];
                                                $tpl .= $navens_upgrade->tpl("admin/approve_upgrades.tpl", $array);
                                        }
                                        echo $tpl;
                                    }
                                break;
                }
        }
}
?>
