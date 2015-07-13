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
        
        $tickets_data      = $dbh->select("tickets", array("id", "=", $id));
        $reply_box_array['AUTHOR']  = $this->determineAuthor($tickets_data['userid'], $tickets_data['staff']);
        $reply_box_array['CREATED'] = "Posted on: ".main::convertdate("n/d/Y - g:i A", $tickets_data['time']);
        $reply_box_array['REPLY']   = $tickets_data['content'];
        $reply_box_array['TITLE']   = $tickets_data['title'];
        
        $opening_post_data = $dbh->select("tickets", array("id", "=", $tickets_data['ticketid']));
        if($opening_post_data['userid'] == $tickets_data['userid']){

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
        
        if($getvar['mode'] == 'ticketsall'){

            $no_tickets_msg = "You currently have no tickets.";
            $view_mode_text = "<center><i><u><a href=\"?page=tickets\" title=\"View open tickets\">View open tickets</a></u></i></center>";
        
        }else{

            $where[]        = array("status", "!=", "3", "AND");
            $no_tickets_msg = "You currently have no new tickets! <i><u><a href=\"?page=tickets&mode=ticketsall\" title=\"View all tickets.\">View all tickets</a></u></i>";
            $view_mode_text = "<center><i><u><a href=\"?page=tickets&mode=ticketsall\" title=\"View all tickets\">View all tickets</a></u></i></center>";
        
        }

        if(!$getvar['do']){

            $where[]       = array("reply", "=", "0");
            $tickets_query = $dbh->select("tickets", $where, array("time", "DESC"), 0, 1);
            if(!$dbh->num_rows($tickets_query)){

                echo $no_tickets_msg;
            
            }else{

                if($getvar['mode'] == 'ticketsall'){

                    echo "<div style=\"display: none;\" id=\"nun-tickets\">You currently have no tickets!</div>";
                
                }else{

                    echo "<div style=\"display: none;\" id=\"nun-tickets\">You currently have no new tickets!</div>";
                
                }

                $num_rows = $dbh->num_rows($tickets_query);
                echo style::replaceVar("tpl/admin/tickets/tickets-js.tpl", array('NUM_TICKETS' => $num_rows));
                while($tickets_data = $dbh->fetch_array($tickets_query)){

                    $ticket_view_box_array['TITLE']         = $tickets_data['title'];
                    $ticket_view_box_array['UPDATE']        = $this->lastUpdated($tickets_data['id']);
                    $ticket_view_box_array['STATUS']        = $tickets_data['status'];
                    $ticket_view_box_array['STATUSMSG']     = $this->status($tickets_data['status']);
                    $ticket_view_box_array['ID']            = $tickets_data['id'];
                    $ticket_view_box_array['URGENCYTEXT']   = $tickets_data['urgency'];
                    $ticket_view_box_array['URGENCY_CLASS'] = strtolower(str_replace(" ", "_", $tickets_data['urgency']));
                    echo style::replaceVar("tpl/admin/tickets/ticket-view-box.tpl", $ticket_view_box_array);
                
                }

                echo $view_mode_text;
            
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

                    check::empty_fields(array("admin"));
                    if(!main::errors()){

                        $time           = time();
                        $tickets_insert = array(
                            "title"    => $postvar['title'],
                            "content"  => $postvar['content'],
                            "time"     => $time,
                            "userid"   => $_SESSION['user'],
                            "reply"    => "1",
                            "ticketid" => $getvar['do'],
                            "staff"    => "1"
                        );
                        
                        $dbh->insert("tickets", $tickets_insert);
                        main::errors("Reply has been added!");
                        
                        $last_ticket_data = $dbh->select("tickets", array("time", "=", $time), 0, "1");
                        $tickets_data     = $dbh->fetch_array($tickets_query);
                        
                        $client   = $dbh->staff($_SESSION['user']);
                        $user     = $dbh->client($tickets_data['userid']);
                        $template = email::emailTemplate("ticket-staff-responded");
                        
                        $clientresponse_array['TITLE']   = $tickets_data['title'];
                        $clientresponse_array['STAFF']   = $client['name'];
                        $clientresponse_array['CONTENT'] = $postvar['content'];
                        $clientresponse_array['LINK']    = $dbh->config("url")."/client/?page=tickets&sub=view&do=".$last_ticket_data['ticketid'];
                        
                        email::send($user['email'], $template['subject'], $template['content'], $clientresponse_array);
                        main::redirect("?page=tickets&sub=view&do=".$getvar['do']);
                    
                    }

                }

                $tickets_data = $dbh->fetch_array($tickets_query);
                
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

                $view_ticket_array['REPLIES'] = "";
                $n                 = 0;
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
                $change_status_array['DROPDOWN'] = main::dropdown("status", $values, $tickets_data['status'], 0);
                $change_status_array['ID']       = $tickets_data['id'];
                $view_ticket_array['ADDREPLY'] .= style::replaceVar("tpl/tickets/change-status.tpl", $change_status_array);
                
                $view_ticket_array['ADDREPLY'] .= "<br /><b>Add Reply</b>";
                $add_reply_array['TITLE'] = "RE: ".$tickets_data['title'];
                $view_ticket_array['ADDREPLY'] .= style::replaceVar("tpl/tickets/add-reply.tpl", $add_reply_array);
                
                echo style::replaceVar("tpl/tickets/view-ticket.tpl", $view_ticket_array);
            
            }

        }

    }

}

?>