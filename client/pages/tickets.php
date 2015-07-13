<?php
//////////////////////////////
// The Hosting Tool
// Support Area - Tickets
// By Jonny H
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){die();}

class page {

        public $navtitle;
        public $navlist = array();

        public function __construct() {
                $this->navtitle = "Tickets Menu";
                $this->navlist[] = array("New Ticket", "page_white_add.png", "add");
                $this->navlist[] = array("View Tickets", "page_white_go.png", "view");
        }

        public function description() {
                return "<strong>Tickets Area</strong><br />
                This is the area where you can add/view tickets that you've created or just created. Any tickets, responses will be sent via email.";
        }

        private function lastUpdated($id) { # Returns a the date of last updated on ticket id
                global $db, $main;
                $query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `ticketid` = '{$db->strip($id)}' AND `reply` = '1' ORDER BY `time` DESC");
                if(!$db->num_rows($query)) {
                        return "None";
                }
                else {
                        $data = $db->fetch_array($query);
                        $username = $this->determineAuthor($data['userid'], $data['staff']);
                        return $main->convertdate("n/d/Y - g:i A", $data['time'])." by ". $username;
                }
        }

        private function status($status) { # Returns the text of the status
                switch($status) {
                        default:
                                return "Other";
                                break;

                        case 1:
                                return "Open";
                                break;

                        case 2:
                                return "On Hold";
                                break;

                        case 3:
                                return "Closed";
                                break;
                }
        }

        private function determineAuthor($id, $staff) { # Returns the text of the author of a reply
                global $db;
                switch($staff) {
                        case 0:
                                $client = $db->client($id);
                                $username = $client['user'];
                                break;

                        case 1:
                                $client = $db->staff($id);
                                $username = $client['name'];
                                break;
                }
                return $username;
        }

        private function showReply($id) { # Returns the HTML for a ticket box
                global $db, $main, $style;
                $query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `id` = '{$id}'");
                $data = $db->fetch_array($query);

                $array['CREATED'] = $main->convertdate("n/d/Y - g:i A", $data['time']);
                $array['AUTHOR'] = $this->determineAuthor($data['userid'], $data['staff']);
                $array['REPLY'] = $data['content'];
                $array['TITLE'] = $data['title'];
                $orig = $db->query("SELECT * FROM `<PRE>tickets` WHERE `id` = '{$data['ticketid']}'");
                $dataorig = $db->fetch_array($orig);
                if($dataorig['userid'] == $data['userid']) {
                        $array['DETAILS'] = "Original Poster";
                }
                elseif($data['staff'] == 1) {
                        $array['DETAILS'] = "<font color = '#FF0000'>Staff Member</font>";
                }
                else {
                        $array['DETAILS'] = "";
                }
                return $style->replaceVar("tpl/support/replybox.tpl", $array);
        }

        public function content() { # Displays the page
        global $main;
        global $style;
        global $db;
        global $email;
                switch($main->getvar['sub']) {
                        default:
                                if($_POST) {
                                        foreach($main->postvar as $key => $value) {
                                                if($value == "" && !$n && $key != "admin") {
                                                        $main->errors("Please fill in all the fields!");
                                                        $n++;
                                                }
                                        }
                                        if(!$n) {
                                                $time = time();
                                                $db->query("INSERT INTO `<PRE>tickets` (title, content, urgency, time, userid) VALUES('{$main->postvar['title']}', '{$main->postvar['content']}', '{$main->postvar['urgency']}', '{$time}', '{$_SESSION['cuser']}')");
                                                $last_ticket = $db->query("SELECT * FROM <PRE>tickets WHERE time = '".$time."' LIMIT 1");
                                                                                                $last_ticket_data = $db->fetch_array($last_ticket);
                                                $main->errors("Ticket has been added!");
                                                $template = $db->emailTemplate("newticket");
                                                $array['TITLE'] = $main->postvar['title'];
                                                $array['URGENCY'] = $main->postvar['urgency'];
                                                $array['CONTENT'] = $main->postvar['content'];
                                                $array['LINK'] = $db->config("url").ADMINDIR."/?page=tickets&sub=view&do=".$last_ticket_data['id'];
                                                $email->staff($template['subject'], $template['content'], $array);
                                        }
                                }
                                echo $style->replaceVar("tpl/support/addticket.tpl", $array);
                                break;

                        case "view":
                                if(is_numeric($_GET['deltid'])){
                                   $userid = $_SESSION['cuser'];
                                   $tid = $_GET['deltid'];
                                   $user_check = $db->query("SELECT * FROM <PRE>tickets WHERE id = '".$tid."' AND userid = '".$userid."' LIMIT 1");
                                   $user_check_rows = $db->num_rows($user_check);
                                   if($user_check_rows == "0"){
                                     echo "<font color = '#FF0000'>This ticket is not yours to delete or does not exist.</font><br>";
                                   }else{
                                     $db->query("DELETE FROM `<PRE>tickets` WHERE `id` = {$tid}");
                                     $db->query("DELETE FROM `<PRE>tickets` WHERE `ticketid` = {$tid}");
                                   }
                                }
                                if(!$main->getvar['do']) {
                                        $query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `userid` = '{$_SESSION['cuser']}' AND `reply` = '0'");
                                        if(!$db->num_rows($query)) {
                                                echo "You currently have no tickets!";
                                        }
                                        else {
                                                while($data = $db->fetch_array($query)) {
                                                        $array['TITLE'] = $data['title'];
                                                        $array['UPDATE'] = $this->lastUpdated($data['id']);
                                                        $array['ID'] = $data['id'];
                                                        $array['STATUS'] = $data['status'];
                                                        $array['STATUSMSG'] = $this->status($data['status']);
                                                        echo $style->replaceVar("tpl/support/ticketviewbox.tpl", $array);
                                                }
                                        }
                                }
                                else {
                                        $query = $db->query("SELECT * FROM `<PRE>tickets` WHERE `id` = '{$main->getvar['do']}' OR `ticketid` = '{$main->getvar['do']}' ORDER BY `time` ASC");
                                        if(!$db->num_rows($query)) {
                                                echo "That ticket doesn't exist!";
                                        }
                                        else {
                                                if($_POST) {
                                                        foreach($main->postvar as $key => $value) {
                                                                if($value == "" && !$n && $key != "admin") {
                                                                        $main->errors("Please fill in all the fields!");
                                                                        $n++;
                                                                }
                                                        }
                                                        if(!$n) {
                                                        $time = time();

                                                                $db->query("INSERT INTO `<PRE>tickets` (title, content, time, userid, reply, ticketid) VALUES('{$main->postvar['title']}', '{$main->postvar['content']}', '{$time}', '{$_SESSION['cuser']}', '1', '{$main->getvar['do']}')");
                                                                $last_ticket = $db->query("SELECT * FROM <PRE>tickets WHERE time = '".$time."' LIMIT 1");
                                                                                                                $last_ticket_data = $db->fetch_array($last_ticket);
                                                                                                                                $main->errors("Reply has been added!");
                                                                $data = $db->fetch_array($query);
                                                                $client = $db->client($_SESSION['cuser']);
                                                                $template = $db->emailTemplate("newresponse");
                                                                $array['TITLE'] = $data['title'];
                                                                $array['USER'] = $client['user'];
                                                                $array['CONTENT'] = $main->postvar['content'];
                                                                $array['LINK'] = $db->config("url").ADMINDIR."/?page=tickets&sub=view&do=".$last_ticket_data['ticketid'];
                                                                $email->staff($template['subject'], $template['content'], $array);
                                                                $main->redirect("?page=tickets&sub=view&do=". $main->getvar['do']);
                                                        }
                                                }
                                                $data = $db->fetch_array($query);
                                                $array['AUTHOR'] = $this->determineAuthor($data['userid'], $data['staff']);
                                                $array['TIME'] = $main->convertdate("n/d/Y - g:i A", $data['time']);
                                                $array['NUMREPLIES'] = $db->num_rows($query) - 1;
                                                $array['UPDATED'] = $this->lastUpdated($data['id']);
                                                $array['ORIG'] = $this->showReply($data['id']);
                                                $array['URGENCY'] = $data['urgency'];
                                                $array['STATUS'] = $this->status($data['status']);
                                                
                                                if($data['status'] == "1"){
                                                $array['STATUSCOLOR'] = "779500";
                                                }elseif($data['status'] == "2"){
                                                $array['STATUSCOLOR'] = "FF9500";
                                                }elseif($data['status'] == "3"){
                                                $array['STATUSCOLOR'] = "FF0000";
                                                }else{
                                                $array['STATUSCOLOR'] = "000000";
                                                }

                                                $n = 0;
                                                $array['REPLIES'] = "";
                                                while($reply = $db->fetch_array($query)) {
                                                        if(!$n) {
                                                                $array['REPLIES'] .= "<br /><b>Replies</b>";
                                                        }
                                                        $array['REPLIES'] .= $this->showReply($reply['id']);
                                                        $n++;
                                                }


                                                $array['ADDREPLY'] .= "<br /><b>Change Ticket Status</b>";
                                                $values[] = array("Open", 1);
                                                $values[] = array("On Hold", 2);
                                                $values[] = array("Closed", 3);
                                                $array3['DROPDOWN'] = $main->dropdown("status", $values, $data['status'], 0);
                                                $array3['ID'] = $data['id'];
                                                $array['ADDREPLY'] .= $style->replaceVar("tpl/support/clientchangestatus.tpl", $array3);

                                                //I made it so the clients could reply to closed tickets.  They can still see them anyway and it just won't show in the open tickets
                                                //list in the admin area.  So, closed still serves a purpose, but clients can change the status now, so let's let them reply as well.
                                                //if($data['status'] != 3) {
                                                        $array['ADDREPLY'] .= "<br /><b>Add Reply</b>";
                                                        $array2['TITLE'] = "RE: ". $data['title'];
                                                        $array['ADDREPLY'] .= $style->replaceVar("tpl/support/addreply.tpl", $array2);
                                                //}
                                                //else {
                                                //        $array['ADDREPLY'] = "";
                                                //}

                                                echo $style->replaceVar("tpl/support/viewticket.tpl", $array);
                                        }
                                }
                                break;
                }
        }
}
?>
