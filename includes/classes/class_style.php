<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Style Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

class style{

	//Shows a SQL error from main class
    private function error($name, $template, $func){
	
        if(INSTALL){

            $error['Error']    = $name;
            $error['Function'] = $func;
            $error['Template'] = $mysqlerror;
            main::error($error);
        
        }

    }

	// Returns the content of a file
    private function getFile($name, $prepare = 1, $override = 0, $showit = 0){
	    global $dbh, $postvar, $getvar, $instance;
		
        $link = "../themes/".THEME."/".$name;
        if(!file_exists($link) || $override == 1){

            $link = INC."/".$name;
        
        }

        if(!file_exists($link)){

            $link = "../install/".$name;
        
        }

        if(!file_exists($link)){

            $link = $name;
        
        }

        if(!file_exists($link) && INSTALL == 1){

            $error['Error'] = "File doesn't exist!";
            $error['Path']  = $link;
            main::error($error);
        
        }else{

            if($prepare){

                return self::prepare(file_get_contents($link));
            
            }else{

                return file_get_contents($link);
            
            }

        }

    }

	// Returns the content with the THT variables replaced
    public function prepare($page_data){
	
        include(INC."/variables.php");
        return $page_data;
    
    }

	// Fetch a template
    public function get($template){
	
        return self::getFile($template);
    
    }

	// Fetches the CSS and prepares it
    public function css(){
	    global $dbh, $postvar, $getvar, $instance;
		
        $css = '<style type="text/css">';
        $css .= self::getFile("style.css", 0, 0);
        $css .= '</style>'."\n";
        if(FOLDER != "install" && FOLDER != "includes"){

            $css .= '<link rel="stylesheet" href="'.URL.'includes/css/'.$dbh->config('ui-theme').'/jquery-ui.css" type="text/css" />';
        
        }

        return $css;
    
    }

	//Fetches a template then replaces all the variables in it with that key
    public function replaceVar($template, $array = 0, $style = 0){

        $file_contents = self::getFile($template, 0, $style);
        if($array){

            foreach($array as $key => $value){

                $file_contents = str_replace("%".$key."%", $value, $file_contents);
            
            }

        }
		
        return $file_contents;
    
    }

	// Returns the HTML code for the header that includes all the JS in the javascript folder
    public function javascript(){
	
        $folder = INC."/javascript/";
        $html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/jquery.js'></script>\n";
        if($handle = opendir($folder)){
		
            while(false !== ($file = readdir($handle))){
			
                if($file != "." && $file != ".." && $file != "jquery.js"){
				
                    $base = explode(".", $file); 
                    if($base[count($base) - 1] == "js"){
					
                        $html .= "<script type=\"text/javascript\" src='".URL."includes/javascript/".$file."'></script>\n"; 
                    
                    }

                }

            }

        }

        $html .= "<script type=\"text/javascript\" src='<WYSIWYG_EDITOR>'></script>";
        closedir($handle); 
        return $html;
    
    }

    public function notice($good, $message){

        if($good){

            $color = "green";
        
        }else{

            $color = "red";
        
        }

        $notice = '<strong><em style="color: '.$color.';">';
        $notice .= $message;
        $notice .= '</em></strong>';
        return $notice;
    
    }

    // Returns a form input element according to the parameters given
    public function createInput($type, $name, $value = "", $extra = array(), $options = array()){

        $type      = strtolower(trim($type));
        $extraHtml = "";
        
        foreach($extra as $k => $v){

            $extraHtml .= $k.'="'.$v.'" ';
            
        }

        switch($type){

            case "textarea":
                
                return '<textarea name="'.$name.'" '.$extraHtml.'>'.htmlspecialchars($value).'</textarea>';
                break;
            
            case "select":
                
                $return = '<select name="'.$name.'" '.$extraHtml.'>';
                
                foreach($options as $o){

                    if(array_key_exists("disabled", $o) && $o["disabled"]){

                        $d = " disabled";
                        
                    }else{

                        $d = "";
                        
                    }

                    if(array_key_exists("selected", $o) && $o["selected"]){

                        $s = " selected";
                        
                    }else{

                        $s = "";
                        
                    }

                    $return .= '<option value="'.$o["value"].'"'.$d.$s.'>'.$o["text"].'</option>';
                    
                }

                $return .= '</select>';
                return $return;
                break;
            
            default:
                
                return '<input type="'.$type.'" name="'.$name.'" value="'.$value.'" '.$extraHtml.'/>';
                break;
                
        }

    }

}

?>