<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Email Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class email{
    
	// When class is made, retrieves all details like sending method, details.
    public function __construct(){
        global $dbh, $postvar, $getvar, $instance;
		
        if(INSTALL == 1){

            $instance->method          = $dbh->config("emailmethod");
            $instance->details['from'] = $dbh->config("emailfrom");
            $config_query              = $dbh->select("config", array("name", "LIKE", "smtp_%"), 0, 0, 1);
            
            while($config_data = $dbh->fetch_array($config_query)){

                $instance->details[$config_data['name']] = $config_data['value'];
                
            }

        }

    }

	// Sends the email using PHP Mail
    private function phpmail(){
		global $dbh, $postvar, $getvar, $instance;
	
        $headers = "From: ".$instance->details['from']."\r\n".'X-Mailer: PHP/'.phpversion()."\r\n"."MIME-Version: 1.0\r\n"."Content-Type: text/html; charset=utf-8\r\n"."Content-Transfer-Encoding: 8bit\r\n\r\n";
        return mail($instance->email['to'], $instance->email['subject'], $instance->email['content'], $headers);
    
    }

    public function smtp(){
        global $dbh, $postvar, $getvar, $instance;
        
        $body = eregi_replace("[\]", '', $body);
        
        $users_data = $dbh->select("users", array("email", "=", $instance->email['to']), 0, "1");
        $to_name    = $users_data['firstname']." ".$users_data['lastname'];
        
        if($to_name == " "){

            $staff_data = $dbh->select("staff", array("email", "=", $instance->email['to']), 0, "1");
            $to_name    = $staff_data['name'];
            
        }

		if(!class_exists("PHPMailer")){
			
			include(INC."/smtp/class_phpmailer.php");
			
		}
		
        $mail = new PHPMailer();
		
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->SMTPAuth      = true; // enable SMTP authentication (Log in with credentials to send)
        $mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent
        $mail->Host          = $instance->details['smtp_host']; // sets the SMTP server
        $mail->Port          = $dbh->config('smtp_port'); // set the SMTP port for the SMTP server
        $mail->Username      = $instance->details['smtp_user']; // SMTP account username
        $mail->Password      = $instance->details['smtp_password']; // SMTP account password
        $mail->SetFrom($instance->details['from'], $dbh->config('name'));
        $mail->AddReplyTo($instance->details['from'], $dbh->config('name'));
        $mail->Subject = $instance->email['subject'];
        $mail->MsgHTML($instance->email['content']);
        $mail->AddAddress($instance->email['to'], $to_name);
        
        if(!$mail->Send()){

            $response = "Mailer Error (".$instance->email['to'].') '.$mail->ErrorInfo."\n";
            main::thtlog("SMTP Error", $response, main::userid());
            $mail->ClearAddresses();
            return false;
            
        }

        $mail->ClearAddresses();
        return true;
        
    }

	// Gets the content, edits the class vars and sends to right function
    public function send($to, $subject, $content, $array = 0){
		global $dbh, $postvar, $getvar, $instance;
	
        $instance->email['to'] = strtolower($to);
        if($array != 0){

            $instance->email['content'] = self::parseEmail($content, $array);
        
        }else{

            $instance->email['content'] = $content;
        
        }

        $instance->email['subject'] = $subject;
        $method                     = $instance->method;
        if($method == "php"){

            return self::phpmail();
        
        }elseif($method == "smtp"){

            return self::smtp();
        
        }else{
            
            $error_array['Error']         = "Email method not found!";
            $error_array['What happened'] = "The script couldn't found what way the host wants to send the email";
            $error_array['What to do']    = "Please report this to the host immediately!";
            main::error($error_array);
            return false;
        
        }

    }

	// Sends every staff member a email with the chosen content
    public function staff($subject, $content, $array = 0){	
        global $dbh, $postvar, $getvar, $instance;
		
        $staff_query = $dbh->select("staff");
        while($staff_data = $dbh->fetch_array($staff_query)){

            self::send($staff_data['email'], $subject, $content, $array);
        
        }

    }

	// Retrieves the array and replaces all the email variables with the content
    private function parseEmail($content, $array){
	
        foreach($array as $key => $value){

            $content = preg_replace("/%".$key."%/si", $value, $content);
        
        }

        return $content;
    
    }

	// Retrieves a email template with name or id
    public function emailTemplate($name = 0, $id = 0){
        global $dbh, $postvar, $getvar, $instance;
		
        if($name){

            $templates_data = $dbh->select("templates", array("name", "=", $name));
        
        }elseif($id){

            $templates_data = $dbh->select("templates", array("id", "=", $id));
        
        }else{

            $error_array['Error'] = "No name/id was sent onto the reciever!";
            main::error($error_array);
            return;
        
        }

        if(!$templates_data['id']){

            $error_array['Error']            = "That template doesn't exist!";
            $error_array['Template Name/ID'] = $name.$id;
            main::error($error_array);
        
        }else{

            $tmpl_file_base    = INC."/tpl/email/".$templates_data['dir']."/".$templates_data['name'];
            $tmpl_content_file = @file_get_contents($tmpl_file_base.".tpl");
            $tmpl_descrip_file = @file_get_contents($tmpl_file_base.".desc.tpl");
            
            if(!$tmpl_content_file && !$tmpl_descrip_file){

                $error_array['Error']              = "One of the template files don't exist.<br>";
                $error_array['Template locations'] = "<br>".$tmpl_file_base.".tpl<br>".$tmpl_file_base.".desc.tpl";
                main::error($error_array);
            
            }else{

                $template_data = array(
                    "id"          => $templates_data['id'],
                    "name"        => $templates_data['name'],
                    "acpvisual"   => $templates_data['acpvisual'],
                    "subject"     => $templates_data['subject'],
                    "content"     => $tmpl_content_file,
                    "description" => $tmpl_descrip_file
                );
                
                return $template_data;
            
            }

        }

    }

}

?>