<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - General Settings
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

        $this->navtitle  = "General Settings Sub Menu";
        $this->navlist[] = array("General Configuration", "world.png", "general");
        $this->navlist[] = array("Security Settings", "lock.png", "security");
        $this->navlist[] = array("Signup Form", "user_red.png", "signup");
        $this->navlist[] = array("Terms of Service", "application_edit.png", "tos");
        $this->navlist[] = array("Client Area", "user_go.png", "client");
        $this->navlist[] = array("Support Area", "help.png", "support");
        $this->navlist[] = array("Email Configuration", "email.png", "email");
    
    }

    public function description(){

        return "<strong>System Settings</strong><br />
                This is where you control the main THT Functions. Change the Titles and Paths, work on the signup form,
                edit the TOS, change the Look & Feel... To get started, choose a link from the sidebar's SubMenu.";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;    

        switch($getvar['sub']){

            default:
			
				if($_POST){

					$no_check_fields = array("last_tld_update", "otherdefault");
					check::empty_fields($no_check_fields);
					if($postvar['url'] && substr($postvar['url'], -1, 1) != "/"){

						$postvar['url'] = $postvar['url']."/";
					
					}

					if($postvar['default_page'] && $postvar['default_page'] == "-other-"){

						if(!$postvar['otherdefault']){

							main::errors("Please enter the default directory to redirect to.");
						
						}else{

							if(is_dir("../".$postvar['otherdefault'])){

								$postvar['default_page'] = $postvar['otherdefault'];
							
							}else{

								main::errors("The default directory entered does not exist.");
							
							}

						}

					}

					if($postvar['last_tld_update'] == "never"){
					
						$dbh->updateConfig("last_tld_update", "never");
					
					}else{
					
						unset($postvar['last_tld_update']);
					
					}
					
					if(!main::errors()){

						foreach($postvar as $key => $value){

							$dbh->updateConfig($key, $value);

						}

						main::errors("Settings Updated!");
						
					}

				}
			
                $general_settings_array['NAME']   = $dbh->config("name");
                $general_settings_array['URL']    = $dbh->config("url");
                $general_settings_array['RECURL'] = $_SERVER['HTTP_HOST'];
                $values[]        = array("Order Form", "order");
                $values[]        = array("Client Area", "client");
                $values[]        = array("Knowledge Base", "support");
                $values[]        = array("Other", "-other-");
                
                if($dbh->config('default_page') != ADMINDIR && $dbh->config('default_page') != "order" && $dbh->config('default_page') != "client"){

                    $general_settings_array['DEFAULT_PAGE'] = main::dropDown("default_page", $values, "-other-");
                    $general_settings_array['OTHERDEFAULT'] = $dbh->config('default_page');
                
                }else{

                    $general_settings_array['OTHERDEFAULT'] = "";
                    $general_settings_array['DEFAULT_PAGE'] = main::dropDown('default_page', $values, $dbh->config('default_page'));
                
                }

                $IANA_queue_values[]      = array("No", "");
                $IANA_queue_values[]      = array("Yes", "never");
                $general_settings_array['QUEUE_IANA']      = main::dropDown("last_tld_update", $IANA_queue_values, $dbh->config("last_tld_update"));
                $general_settings_array['TLD_UPDATE_DAYS'] = $dbh->config("tld_update_days");
                echo style::replaceVar("tpl/admin/settings/general-settings.tpl", $general_settings_array);
                break;
            
            case "security": //security settings
			
				if($_POST){

					check::empty_fields();
					
					if(!check::email($postvar['email_for_cron'], 0, 0, 1)){
					
						main::errors("Please verify that the email you're using for cron output is of a valid format.");
					
					}
					
					if(!main::errors()){

						foreach($postvar as $key => $value){

							$dbh->updateConfig($key, $value);

						}

						main::errors("Settings Updated!");
						
					}

				}
				
                $values[]                   = array("Yes", "1");
                $values[]                   = array("No", "0");
                $security_settings_array['SHOW_VERSION_ID']   = main::dropDown("show_version_id", $values, $dbh->config("show_version_id"));
                $security_settings_array['SHOW_PAGE_GENTIME'] = main::dropDown("show_page_gentime", $values, $dbh->config("show_page_gentime"));
                $security_settings_array['SHOW_FOOTER']       = main::dropDown("show_footer", $values, $dbh->config("show_footer"));
                $security_settings_array['SHOW_ERRORS']       = main::dropDown("show_errors", $values, $dbh->config("show_errors"));
                $security_settings_array['EMAIL_ON_CRON']     = main::dropDown("emailoncron", $values, $dbh->config("emailoncron"));
                $security_settings_array['EMAIL_FOR_CRON']    = $dbh->config("email_for_cron");
                $security_settings_array['SESSION_TIMEOUT']   = $dbh->config("session_timeout");
                echo style::replaceVar("tpl/admin/settings/security-settings.tpl", $security_settings_array);
                break;
            
            case "tos":
			
				if($_POST){

					check::empty_fields();
					
					if(!main::errors()){

						$dbh->updateConfig("tos", $postvar['tos']);
						main::errors("Settings Updated!");
						
					}

				}
				
                $tos_array['TOS'] = $dbh->config("tos");
                echo style::replaceVar("tpl/admin/settings/tos.tpl", $tos_array);
                break;
            
            case "signup":
			
				if($_POST){

					check::empty_fields();
					
					if(!main::errors()){

						foreach($postvar as $key => $value){

							$dbh->updateConfig($key, $value);

						}

						main::errors("Settings Updated!");
						
					}

				}
				
                $values[]            = array("Enabled", "1");
                $values[]            = array("Disabled", "0");
                $signup_settings_array['MULTIPLE']   = main::dropDown("multiple", $values, $dbh->config("multiple"));
                $signup_settings_array['TLDONLY']    = main::dropDown("tldonly", $values, $dbh->config("tldonly"));
                $signup_settings_array['GENERAL']    = main::dropDown("general", $values, $dbh->config("general"));
                $signup_settings_array['MESSAGE']    = $dbh->config("message");
                echo style::replaceVar("tpl/admin/settings/signup-settings.tpl", $signup_settings_array);
                
                break;
            
            case "client":
			
				if($_POST){

					$no_check_fields = array("alerts");
					check::empty_fields($no_check_fields);
					
					if(!main::errors()){

						foreach($postvar as $key => $value){

							$dbh->updateConfig($key, $value);

						}

						main::errors("Settings Updated!");
						
					}

				}
				
                $values[]          = array("Enabled", "1");
                $values[]          = array("Disabled", "0");
                $client_area_settings_array['DELACC']   = main::dropDown("delacc", $values, $dbh->config("delacc"));
                $client_area_settings_array['ENABLED']  = main::dropDown("cenabled", $values, $dbh->config("cenabled"));
                $client_area_settings_array['ALERTS']   = $dbh->config("alerts");
                echo style::replaceVar("tpl/admin/settings/client-area-settings.tpl", $client_area_settings_array);
                break;
            
            case "support":
			
				if($_POST){

					check::empty_fields();
					
					if(!main::errors()){

						foreach($postvar as $key => $value){

							$dbh->updateConfig($key, $value);

						}

						main::errors("Settings Updated!");
						
					}

				}
				
                $values[]          = array("Enabled", "1");
                $values[]          = array("Disabled", "0");
                $support_settings_array['ENABLED'] = main::dropDown("senabled", $values, $dbh->config("senabled"));
                $support_settings_array['MESSAGE'] = $dbh->config("smessage");
                echo style::replaceVar("tpl/admin/settings/support-settings.tpl", $support_settings_array);
                break;
            
            case "email":
			
				if($_POST){

					check::empty_fields();
					
					if(!main::errors()){

						foreach($postvar as $key => $value){

							$dbh->updateConfig($key, $value);

						}

						main::errors("Settings Updated!");
						
					}

				}
				
                $values[]           = array("PHP Mail", "php");
                $values[]           = array("SMTP", "smtp");
                $email_settings_array['METHOD']    = main::dropDown("emailmethod", $values, $dbh->config("emailmethod"), 0);
                $email_settings_array['EMAILFROM'] = $dbh->config("emailfrom");
                $email_settings_array['SMTP_HOST'] = $dbh->config("smtp_host");
                $email_settings_array['SMTP_USER'] = $dbh->config("smtp_user");
                $email_settings_array['SMTP_PASS'] = $dbh->config("smtp_password");
                $email_settings_array['SMTP_PORT'] = $dbh->config("smtp_port");
                echo style::replaceVar("tpl/admin/settings/email-settings.tpl", $email_settings_array);
                break;
        
        }

    }

}

?>