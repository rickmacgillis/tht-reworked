<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Mail Center
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

        $this->navtitle  = "Mail Center Sub Menu";
        $this->navlist[] = array("Email Templates", "email_open.png", "templates");
        $this->navlist[] = array("Mass Emailer", "transmit.png", "mass");
    
    }

    public function description(){

        return "<strong>Mail Center</strong><br />
                Welcome to the Mail. Here you can edit your email templates or send a mass email to all your users.<br />";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        switch($getvar['sub']){

            case "templates": //email templates

				$this->EditEmailTemplates();
                break;
            
            case "mass": //mass emailer
                
				$this->MassMailClients();
                break;
        
        }

    }
	
	///////////////////////////////////////////////////
	////
	///  Edit Email Templates Page
	//
	//////////////////////////	
	
	private function EditEmailTemplates(){
        global $dbh, $postvar, $getvar, $instance;
		
		if(main::isint($getvar['do'])){

			if($postvar['edittpl']){

				check::empty_fields();
				if(!main::errors()){

					$dbh->update("templates", array("subject" => $postvar['subject']), array("id", "=", $getvar['do']));
					$template_info  = $dbh->select("templates", array("id", "=", $getvar['do']));
					$tmpl_file_base = INC."/tpl/email/".$template_info['dir']."/".$template_info['name'];
					
					if(!is_writable($tmpl_file_base.".tpl")){

						main::errors("In order to make changes to this file (".$tmpl_file_base.".tpl), please make it writable.");
					
					}else{
					
						$contents = stripslashes($postvar['emailcontent']);
						if($contents){

							$filetochangeOpen = fopen($tmpl_file_base.".tpl", "w");							
							if(!fputs($filetochangeOpen, $contents)){
							
								main::errors("Could not write the template file, ".$tmpl_file_base.".tpl");
							
							}
							
							fclose($filetochangeOpen);
						
						}

						if(!main::errors()){

							main::errors("Template edited!");
						
						}

					}

				}

			}	

            $template_data = $dbh->select("templates", array("id", "=", $getvar['do']));
            if(!$template_data['id']){

                $error_array['Error']       = "Template not found.";
                $error_array['Template ID'] = $getvar['do'];
                main::error($error_array);
            
            }else{

                $tmpl_file_base    = INC."/tpl/email/".$template_data['dir']."/".$template_data['name'];
                $tmpl_content_file = @file_get_contents($tmpl_file_base.".tpl");
                $tmpl_descrip_file = @file_get_contents($tmpl_file_base.".desc.tpl");
                
                if(!$tmpl_content_file && !$tmpl_descrip_file){

                    $error_array['Error']              = "One of the template files don't exist.<br>";
                    $error_array['Template Locations'] = "<br>".$tmpl_file_base.".tpl<br>".$tmpl_file_base.".desc.tpl";
                    main::error($error_array);
                
                }else{

                    $edit_email_template_array['SUBJECT']     = $template_data['subject'];
					$edit_email_template_array['DESCRIPTION'] = $tmpl_descrip_file;
					$edit_email_template_array['TEMPLATE']    = $tmpl_content_file;
                
                }

            }			
			
			echo style::replaceVar("tpl/admin/mail/edit-email-template.tpl", $edit_email_template_array);	
			return;
		
		}
		
		if(main::isint($postvar['template'])){
		
			main::redirect("?page=email&sub=templates&do=".$postvar['template']);
		
		}

		$templates_query = $dbh->select("templates", 0, array("acpvisual", "ASC"));
		while($templates_data = $dbh->fetch_array($templates_query)){

			$values[] = array($templates_data['acpvisual'], $templates_data['id']);
		
		}

		$select_email_template_array['TEMPLATES'] = main::dropDown("template", $values, 0, 1);
		echo style::replaceVar("tpl/admin/mail/select-email-template.tpl", $select_email_template_array);	
	
	}
	
	///////////////////////////////////////////////////
	////
	///  Mass Mail Page
	//
	//////////////////////////	
	
	private function MassMailClients(){
        global $dbh, $postvar, $getvar, $instance;
	
		if($_POST){

			check::empty_fields();
			if(!main::errors()){
			
				$users_query = $dbh->select("users");
				while($users_data = $dbh->fetch_array($users_query)){

					$result = email::send($users_data['email'], $postvar['msgsubject'], $postvar['msgcontent']);
					if(!$result){

						$error = true;
					
					}

				}

				if(!$error){

					main::errors("The email has been sent to all your clients.");
				
				}else{

					main::errors("Houston, you have problems.  Check the THT Log to find out what all went wrong.");
				
				}
		
			}
		
		}
	
		echo style::replaceVar("tpl/admin/mail/mass-email.tpl");	
	
	}

}

?>