<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Support Area - Tickets
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class page{

    public $navtitle;
    public $navlist = array();
    
    public function __construct(){

        $this->navtitle  = "Tickets Menu";
        $this->navlist[] = array("New Ticket", "page_white_add.png", "add");
        $this->navlist[] = array("View Tickets", "page_white_go.png", "view");
    
    }

    public function description(){

        return "<strong>Tickets Area</strong><br />
                This is the area where you can add/view tickets that you've created or just created. Any tickets, responses will be sent via email.";
    
    }

	// Returns a the date of last updated on ticket id
    private function lastUpdated($id){
        global $dbh, $postvar, $getvar, $instance;
        
        unset($where);
        $where[]      = array("ticketid", "=", $id, "AND");
        $where[]      = array("reply", "=", "1");
        $tickets_data = $dbh->select("tickets", $where, array("time", "DESC"));
        if(!$tickets_data['ticketid']){

            return "None";
        
        }else{

            $username = $this->determineAuthor($tickets_data['userid'], $tickets_data['staff']);
            return main::convertdate("n/d/Y - g:i A", $tickets_data['time'])." by ".$username;
        
        }

    }

    private function status($status){
 // Returns the text of the status
        switch($status){

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

	// Returns the text of the author of a reply
    private function determineAuthor($id, $staff){
        global $dbh, $postvar, $getvar, $instance;
		
        switch($staff){

            case 0:
                $client   = $dbh->client($id);
                $username = $client['user'];
                break;
            
            case 1:
                $client   = $dbh->staff($id);
                $username = $client['name'];
                break;
        
        }

        return $username;
    
    }

	// Returns the HTML for a ticket box
    private function showReply($id){
        global $dbh, $postvar, $getvar, $instance;
        
        $tickets_data = $dbh->select("tickets", array("id", "=", $id));
        
        $reply_box_array['CREATED']    = main::convertdate("n/d/Y - g:i A", $tickets_data['time']);
        $reply_box_array['AUTHOR']     = $this->determineAuthor($tickets_data['userid'], $tickets_data['staff']);
        $reply_box_array['REPLY']      = $tickets_data['content'];
        $reply_box_array['TITLE']      = $tickets_data['title'];
        $opening_tickets_data = $dbh->select("tickets", array("id", "=", $tickets_data['ticketid']));
        if($opening_tickets_data['userid'] == $tickets_data['userid']){

            $reply_box_array['DETAILS'] = "Original Poster";
        
        }elseif($tickets_data['staff'] == 1){

            $reply_box_array['DETAILS'] = "<font color = '#FF0000'>Staff Member</font>";
        
        }else{

            $reply_box_array['DETAILS'] = "";
        
        }

        return style::replaceVar("tpl/tickets/reply-box.tpl", $reply_box_array);
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        switch($getvar['sub']){

            default:
                if($_POST){

                    check::empty_fields();
                    if(!main::errors()){

                        $time = time();
                        
                        $tickets_insert = array(
                            "title"   => $postvar['title'],
                            "content" => $postvar['content'],
                            "urgency" => $postvar['urgency'],
                            "time"    => $time,
                            "userid"  => $_SESSION['cuser']
                        );
                        
                        $dbh->insert("tickets", $tickets_insert);
                        $last_ticket_data = $dbh->select("tickets", array("time", "=", $time), 0, "1");
                        
                        $template         = email::emailTemplate("new-ticket");
                        $newticket_array['TITLE']   = $postvar['title'];
                        $newticket_array['URGENCY'] = $postvar['urgency'];
                        $newticket_array['CONTENT'] = $postvar['content'];
                        $newticket_array['LINK']    = $dbh->config("url").ADMINDIR."/?page=tickets&sub=view&do=".$last_ticket_data['id'];
                        email::staff($template['subject'], $template['content'], $newticket_array);
                        
                        main::errors("Ticket has been added!");
                    
                    }

                }

                echo style::replaceVar("tpl/client/tickets/add-ticket.tpl");
                break;
            
            case "view":
                if(is_numeric($getvar['deltid'])){

                    $userid = $_SESSION['cuser'];
                    $tid    = $getvar['deltid'];
                    
                    unset($where);
                    $where[]         = array("id", "=", $tid, "AND");
                    $where[]         = array("userid", "=", $userid);
                    $user_check      = $dbh->select("tickets", $where, 0, "1", 1);
                    $user_check_rows = $dbh->num_rows($user_check);
                    if($user_check_rows == "0"){

                        echo "<font color = '#FF0000'>This ticket is not yours to delete or does not exist.</font><br>";
                    
                    }else{

                        unset($where);
                        $where[] = array("id", "=", $tid, "OR");
                        $where[] = array("ticketid", "=", $tid);
                        $dbh->delete("tickets", $where);
                    
                    }

                }

                if(!$getvar['do']){

                    unset($where);
                    $where[]       = array("userid", "=", $_SESSION['cuser'], "AND");
                    $where[]       = array("reply", "=", "0");
                    $tickets_query = $dbh->select("tickets", $where, 0, 0, 1);
                    if(!$dbh->num_rows($tickets_query)){

                        echo "You currently have no tickets!";
                    
                    }else{

                        while($tickets_data = $dbh->fetch_array($tickets_query)){

                            $ticket_view_box_array['TITLE']     = $tickets_data['title'];
                            $ticket_view_box_array['UPDATE']    = $this->lastUpdated($tickets_data['id']);
                            $ticket_view_box_array['ID']        = $tickets_data['id'];
                            $ticket_view_box_array['STATUS']    = $tickets_data['status'];
                            $ticket_view_box_array['STATUSMSG'] = $this->status($tickets_data['status']);
                            echo style::replaceVar("tpl/client/tickets/ticket-view-box.tpl", $ticket_view_box_array);
                        
                        }

                    }

                }else{

                    unset($where);
                    $where[]       = array("id", "=", $getvar['do'], "OR");
                    $where[]       = array("ticketid", "=", $getvar['do']);
                    $tickets_query = $dbh->select("tickets", $where, array("time", "ASC"), 0, 1);
                    if(!$dbh->num_rows($tickets_query)){

                        echo "That ticket doesn't exist!";
                    
                    }else{

                        if($_POST){

                            check::empty_fields();
                            if(!main::errors()){

                                $time = time();
                                
                                $tickets_insert = array(
                                    "title"    => $postvar['title'],
                                    "content"  => $postvar['content'],
                                    "time"     => $time,
                                    "userid"   => $_SESSION['cuser'],
                                    "reply"    => "1",
                                    "ticketid" => $getvar['do']
                                );
                                
                                $dbh->insert("tickets", $tickets_insert);
                                $last_ticket_data = $dbh->select("tickets", array("time", "=", $time), 0, "1");
                                
                                $tickets_data = $dbh->fetch_array($tickets_query);
                                $client       = $dbh->client($_SESSION['cuser']);
                                
                                $template         = email::emailTemplate("ticket-client-responded");
                                $newresponse_array['TITLE']   = $tickets_data['title'];
                                $newresponse_array['USER']    = $client['user'];
                                $newresponse_array['CONTENT'] = $postvar['content'];
                                $newresponse_array['LINK']    = $dbh->config("url").ADMINDIR."/?page=tickets&sub=view&do=".$last_ticket_data['ticketid'];
                                email::staff($template['subject'], $template['content'], $newresponse_array);
                                
                                main::redirect("?page=tickets&sub=view&do=".$getvar['do']);
                            
                            }

                        }

                        $tickets_data                   = $dbh->fetch_array($tickets_query);
                        $view_ticket_array['AUTHOR']     = $this->determineAuthor($tickets_data['userid'], $tickets_data['staff']);
                        $view_ticket_array['TIME']       = main::convertdate("n/d/Y - g:i A", $tickets_data['time']);
                        $view_ticket_array['NUMREPLIES'] = $dbh->num_rows($tickets_query) - 1;
                        $view_ticket_array['UPDATED']    = $this->lastUpdated($tickets_data['id']);
                        $view_ticket_array['ORIG']       = $this->showReply($tickets_data['id']);
                        $view_ticket_array['URGENCY']    = $tickets_data['urgency'];
                        $view_ticket_array['STATUS']     = $this->status($tickets_data['status']);
                        
						switch($tickets_data['status']){
						
							case "1":
							
								$view_ticket_array['STATUSCOLOR'] = "779500";
								break;
						
							case "2":
							
								$view_ticket_array['STATUSCOLOR'] = "FF9500";
								break;
						
							case "3":
							
								$view_ticket_array['STATUSCOLOR'] = "FF0000";
								break;
						
							default:
							
								$view_ticket_array['STATUSCOLOR'] = "000000";
								break;
						
						}

                        $n                 = 0;
                        $view_ticket_array['REPLIES'] = "";
                        while($reply = $dbh->fetch_array($tickets_query)){

                            if(!$n){

                                $view_ticket_array['REPLIES'] .= "<br /><b>Replies</b>";
                            
                            }

                            $view_ticket_array['REPLIES'] .= $this->showReply($reply['id']);
                            $n++;
                        
                        }

                        $view_ticket_array['ADDREPLY'] .= "<br /><b>Change Ticket Status</b>";
                        $values[]            = array("Open", 1);
                        $values[]            = array("On Hold", 2);
                        $values[]            = array("Closed", 3);
                        $client_change_status_array['DROPDOWN'] = main::dropdown("status", $values, $tickets_data['status'], 0);
                        $client_change_status_array['ID']       = $tickets_data['id'];
                        $view_ticket_array['ADDREPLY'] .= style::replaceVar("tpl/tickets/change-status.tpl", $client_change_status_array);
                        $view_ticket_array['ADDREPLY'] .= "<br /><b>Add Reply</b>";
                        $add_reply_array['TITLE']        = "RE: ".$tickets_data['title'];
                        $view_ticket_array['ADDREPLY'] .= style::replaceVar("tpl/tickets/add-reply.tpl", $add_reply_array);
                        
                        echo style::replaceVar("tpl/tickets/view-ticket.tpl", $view_ticket_array);
                    
                    }

                }

                break;
        
        }

    }

}

?>