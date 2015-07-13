<?php
//////////////////////////////
// The Hosting Tool
// Admin Area - Mail Center
// By Jonny H, Nick D
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){die();}

class page {

        public $navtitle;
        public $navlist = array();
                                                        
        public function __construct() {
                $this->navtitle = "Mail Center Sub Menu";
                $this->navlist[] = array("Email Templates", "email_open.png", "templates");                
                $this->navlist[] = array("Mass Emailer", "transmit.png", "mass");
        }
        
        public function description() {
                return "<strong>Mail Center</strong><br />
                Welcome to the Mail. Here you can edit your email templates or send a mass email to all your users.<br />";                        
        }

        public function content() { # Displays the page 
                global $main, $style, $db;
                
                switch($main->getvar['sub']) {
                
                        case "templates": #email templates
                                if($_POST) {
                                        foreach($main->postvar as $key => $value) {
                                                if($value == "" && !$n) {
                                                        $main->errors("Please fill in all the fields!");
                                                        $n++;
                                                }
                                        }
                                        if(!$n) {
                                                $db->query("UPDATE `<PRE>templates` SET `subject` = '{$main->postvar['subject']}' WHERE `id` = '{$main->postvar['template']}'");
                                                
                                                $query = $db->query("SELECT * FROM `<PRE>templates` WHERE `id` = '{$main->postvar['template']}'");
                                                $template_info = $db->fetch_array($query);
                                                $tmpl_file_base = LINK."tpl/email/".$template_info['name'];
                                                
                                                if(!is_writable($tmpl_file_base.".tpl")) {
                                                $main->errors("In order to make changes to this file (".$tmpl_file_base.".tpl), please CHMOD it to 666.");
                                                }else{
                                                if($main->editemailtpl($tmpl_file_base.".tpl")){
                                                  $main->errors("Template edited!");
                                                }else{
                                                  $main->errors("Could not write the template file, ".$tmpl_file_base.".tpl");
                                                }
                                                }
                                        }
                                }
                                $query = $db->query("SELECT * FROM `<PRE>templates` ORDER BY `acpvisual` ASC");
                                while($data = $db->fetch_array($query)) {
                                        $values[] = array($data['acpvisual'], $data['id']);        
                                }
                                $array['TEMPLATES'] = $main->dropDown("LOL", $values, $dID, 0, 1);
                                echo $style->replaceVar("tpl/emailtemplates.tpl", $array);
                        break;
                        
                        case "mass": #mass emailer
                                echo $style->replaceVar("tpl/massemail.tpl");
                        break;
                }
        }
}
?>
