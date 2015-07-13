<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Type Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

class type{
	
	public function __construct(){
	
		if(INSTALL == 1){
			
			self::createAll();
			
		}
	
	}
    
	// Returns the html of a custom form
    public function acpPadd($type){
        global $dbh, $postvar, $getvar, $instance;

        $type = $instance->packtypes[$type];

        if($type->acpForm){
            
            foreach($type->acpForm as $key => $value){

                $type_form_array['NAME'] = $value[0].":";
                $type_form_array['FORM'] = $value[1];
                $html .= style::replaceVar("tpl/type-form.tpl", $type_form_array);
            
            }

            return $html;
        
        }

    }

	// Returns the html of a custom form
    public function orderForm($type){
        global $dbh, $postvar, $getvar, $instance;

        $type = $instance->packtypes[$type];

        if($type->orderForm){

            foreach($type->orderForm as $key => $value){

                $type_form_array['NAME'] = $value[0].":";
                $type_form_array['FORM'] = $value[1];
                $html .= style::replaceVar("tpl/type-form.tpl", $type_form_array);
            
            }

            return $html;
        
        }

    }

	// Creates a class and then returns it
    private function createType($type){
		global $dbh, $postvar, $getvar, $instance;
	
        $file = INC."/types/".$type.".php";
        if(!file_exists($file)){

            echo "Type doesn't exist!";
        
        }else{

            include($file);
            $type_name = $type;
            $type      = new $type;
            
            $instance->packtypes = array_merge($instance->packtypes, array($type_name => $type));

            return $type;
        
        }

    }

	// Creates all types and returns them
    public function createAll(){
        global $dbh, $postvar, $getvar, $instance;
		
        $files = main::folderFiles(INC."/types/");
        foreach($files as $value){

            $type_filename_exp = explode(".", $value);
            if($type_filename_exp[1] == "php"){

                $instance->packtypes[$type_filename_exp[0]] = self::createtype($type_filename_exp[0]);
            
            }

        }

    }

	// Returns type of a package
    public function packagetype($id){
        global $dbh, $postvar, $getvar, $instance;

        $packages_data = $dbh->select("packages", array("id", "=", $id));
        if(!$packages_data['id']){

            $error_array['Error']      = "That package doesn't exist.";
            $error_array['Package ID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            return $packages_data['type'];
        
        }

    }

	// Returns server of a package
    public function packageserver($id){
        global $dbh, $postvar, $getvar, $instance;
        
        $packages_data = $dbh->select("packages", array("id", "=", $id));
        if(!$packages_data['id']){

            $error_array['Error']      = "That package doesn't exist.";
            $error_array['Package ID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            return $packages_data['server'];
        
        }

    }

	// Returns server of a package
    public function packageServerType($id){
        global $dbh, $postvar, $getvar, $instance;
        
        $servers_data = $dbh->select("servers", array("id", "=", $id));
        if(!$servers_data['id']){

            $error_array['Error']     = "That server doesn't exist!";
            $error_array['Server ID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            return $servers_data['type'];
        
        }

    }

	// Returns server of a package
    public function packageBackend($id){
        global $dbh, $postvar, $getvar, $instance;
        
        $packages_data = $dbh->select("packages", array("id", "=", $id));
        if(!$packages_data['id']){

            $error_array['Error']      = "That package doesn't exist!";
            $error_array['Package ID'] = $id;
            main::error($error_array);
            return;
        
        }else{

            return $packages_data['backend'];
        
        }

    }

	// Returns the type's acpForm[] content
    public function acpPedit($type, $values, $origtype){
        global $dbh, $postvar, $getvar, $instance;
		
        $usingtype = $type;
        $type = $instance->packtypes[$type];

        if($type->acpForm){
            
            if($usingtype != $origtype){

                foreach($type->acpForm as $key => $value){

                    $type_form_array['NAME'] = $value[0].":";
                    $type_form_array['FORM'] = $value[1];
                    $html .= style::replaceVar("tpl/type-form.tpl", $type_form_array);
                
                }

            }else{

                $values = explode(",", $values);
                foreach($values as $key => $value){

                    $me            = explode("=", $value);
                    $cform[$me[0]] = $me[1];
                
                }

                foreach($type->acpForm as $value){

                    $type_form_array['NAME'] = $value[0].":";
                    $hit            = explode("/>", $value[1]);
                    $default        = "";
                    
                    if(stripos($value[1], "</select>") === false){

                        $default = ' value="'.$cform[$value[2]].'" />';
                    
                    }

                    $type_form_array['FORM'] = $hit[0].$default;
                    $html .= style::replaceVar("tpl/type-form.tpl", $type_form_array);
                
                }

            }

            return $html;
        
        }

    }

	// Returns the additional values on a package
    public function additional($id, $entry=0){
        global $dbh, $postvar, $getvar, $instance;
        
        $packages_data = $dbh->select("packages", array("id", "=", $id));
        $content       = explode(",", $packages_data['additional']);
        foreach($content as $key => $value){

            $inside             = explode("=", $value);
            $values[$inside[0]] = $inside[1];
        
        }

		if($entry){
		
			return $values[$entry];
		
		}else{
		
			return $values;
		
		}
    
    }

	// Returns the additional info of a PID
    public function userAdditional($id){
        global $dbh, $postvar, $getvar, $instance;

        $client = $dbh->client($id);
        if($client['id']){

            $content = explode(",", $client['additional']);
            foreach($content as $key => $value){

                $inside             = explode("=", $value);
                $values[$inside[0]] = $inside[1];
            
            }

            return $values;
        
        }

    }

}

?>