<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Main Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

if(THT != 1){

    die();

}

class main{

	public function OS(){
	
		$os = php_uname("s");
		
		switch($os){
		
			case 'FreeBSD':
				return "Linux";
				break;
				
			case 'Linux':
				return "Linux";
				break;
				
			default:
				return "Windows";
				break;
		
		}
	
	}

	public function perms($file, $perms){
	
		if(self::OS() == "Linux"){
		
			if(!chmod($file, $perms)){
			
				return false;
			
			}
		
		}
		
		return true;
	
	}

	//Return the latest THT version and it's download URL in an array
    public function latest_version(){

        $versions_file = @file_get_contents("http://thelifemaster.com/vers/reworked-manager.txt");
        //Example: [[THT VERSION]][[THT URL]]
        
        if(substr_count($versions_file, "[[") == 2 && substr_count($versions_file, "]]") == 2){

            $versions_explode = explode("]][[", $versions_file);
            
            $recommended_tht     = str_replace("[[", "", $versions_explode[0]);
            $tht_download        = $versions_explode[1];
            
            $sugessed_array = array(
                "THT"        => $recommended_tht,
                "THT_DL"     => $tht_download
            );
            return $sugessed_array;
        
        }else{

            return false;
        
        }

    }
    
    public function countries($ddbox = 0, $default = 0){
        global $dbh, $postvar, $getvar, $instance;
        
        $countries_query = $dbh->select("countries", 0, array("country", "ASC"));
        $i = 0;
		
        while($countries_data = $dbh->fetch_array($countries_query)){

            $countries_arr[$i][0] = $countries_data['country'];
            $countries_arr[$i][1] = $countries_data['code'];
            $i++;
        
        }

        if($ddbox){

            return self::dropDown("country", $countries_arr, $default, 1);
        
        }else{

            return $countries_arr;
        
        }

    }

    public function convertdate($dtformat, $time, $user = ""){
        global $dbh, $postvar, $getvar, $instance;
		
        if($_SESSION['logged']){

            $table  = "staff";
            $userid = $_SESSION['user'];
        
        }elseif($_SESSION['clogged']){

            $table  = "users";
            $userid = $_SESSION['cuser'];
        
        }

        if($user){

            $userid = $user;
            $table  = "users";
        
        }

        if($table){

            $tz_pred_data = $dbh->select($table, array("id", "=", $userid), 0, "1");
            $gmt          = date("Y-m-d h:i:s A"); //As compiler has the time zone set to GMT, we grab GMT's value now.
            
            date_default_timezone_set($tz_pred_data['tzadjust']);
            $local = date("Y-m-d h:i:s A");
            
            date_default_timezone_set("GMT");
            $local = strtotime($local);
            $gmt   = strtotime($gmt);
            $diff  = $local - $gmt;
            
            return date($dtformat, $time + $diff); //Returns the requested date/time.
        
        }else{

            return date($dtformat, $time); //Using GMT
        
        }

    }

    //Used by tzlist()
    function array_flatten($array){

        $result = array();
        foreach($array as $key => $value){

            if(is_array($value)){

                $result = array_merge($result, self::array_flatten($value));
            
            }else{

                $result[$key] = $value;
            
            }

        }

        return $result;
    
    }

    //Creates a drop down list of all the timezones.  (Used for people to set their time zones.)
    public function tzlist($default = ""){

        static $regions = array(		
			DateTimeZone::AFRICA,
			DateTimeZone::AMERICA, 
			DateTimeZone::ANTARCTICA, 
			DateTimeZone::ASIA, 
			DateTimeZone::ATLANTIC, 
			DateTimeZone::EUROPE, 
			DateTimeZone::INDIAN, 
			DateTimeZone::PACIFIC
		);
        
        foreach($regions as $name => $mask){

            $tzlist[] = DateTimeZone::listIdentifiers($mask);
        
        }

        $tzones = "<select name = 'tzones' id = 'tzones'>\n";
        $tzones .= "<option value = 'GMT'".$selected.">GMT</option>\n";
        $tzlist = self::array_flatten($tzlist);
        foreach($tzlist as $tzone_key => $tzone_val){

            if($default == $tzone_val){

                $selected = " selected";
            
            }

            $tzones .= "<option value = '".$tzone_val."'".$selected.">".$tzone_val."</option>\n";
            unset($selected);
        
        }

        $tzones .= "</select>";
        
        return $tzones;
        
    }

    public function csrf_get_secret(){

        if($GLOBALS['csrf']['secret']){
		
            return $GLOBALS['csrf']['secret'];
		
		}
		
        $dir    = dirname(__FILE__);
        $file   = INC.'/csrf-secret.php';
        $secret = '';
        if(file_exists($file)){

            include $file;
            return $secret;
        
        }

        return '';
    
    }

	// Transforms an Integer Value (1/0) to a Friendly version (Yes/No)
    public function cleaninteger($var){
	
        $patterns[0]     = '/0/';
        $patterns[1]     = '/1/';
        $replacements[0] = 'No';
        $replacements[1] = 'Yes';
        return preg_replace($patterns, $replacements, $var);
    
    }

	// This shows the stylized errors that show when they're called instead of using <ERRORS> in the output.
    public function error($array){	
        global $dbh, $postvar, $getvar, $instance;
        
		if(INSTALL == 1){
		
			$show_errors = $dbh->config('show_errors');
		
		}else{
		
			$show_errors = 1;
		
		}
		
        if($show_errors){

            $error = "<strong>THT ERROR<br /></strong>";
            
            foreach($array as $key => $val){

                $error .= "<strong>".$key.":</strong> ".$val."<br />";
                
            }

            $error .= "<br />";
            $errors['ERROR'] = $error;
            
            if(!method_exists($style, "replaceVar")){

                echo $error;
                
            }else{

                echo style::replaceVar("tpl/error.tpl", $errors);
                
            }

        }

    }

	// Redirects user, default headers
    public function redirect($url, $headers = 1, $long = 0){
	
        if($headers){

            header("Location: ".$url); // Redirect with headers
        
        }else{

            echo '<meta http-equiv="REFRESH" content="'.$long.';url='.$url.'">'; // HTML Headers
        
        }

    }

	// Used for <ERRORS>
    public function errors($error=0){
	
        if(!$error){

            if($_SESSION['errors']){

                return $_SESSION['errors'];
            
            }

        }else{
		
			if($_SESSION['errors'] == "0"){
			
				$_SESSION['errors'] = "";
			
			}else{
			
				$_SESSION['errors'] .= "<br><hr><br>";
			
			}
					

            $_SESSION['errors'] .= $error."<br>";
        
        }

    }

	// Returns the HTML for a single column THT table
    public function table($header, $content = 0, $width = 0, $height = 0){	
		
        if($width){

            $props = "width:".$width.";";
        
        }

        if($height){

            $props .= "height:".height.";";
        
        }

        $table_array['PROPS']   = $props;
        $table_array['HEADER']  = $header;
        $table_array['CONTENT'] = $content;
        $table_array['ID']      = rand(0, 999999);
        $link             = INC."../themes/".THEME."/tpl/table.tpl";
        if(file_exists($link)){

            $tbl = style::replaceVar("../themes/".THEME."/tpl/table.tpl", $table_array);
        
        }else{

            $tbl = style::replaceVar("tpl/table.tpl", $table_array);
        
        }

        return $tbl;
    
    }
	
	public function tr($text, $help, $input_type, $values, $name=0){

		switch($input_type){
		
			case "input":
			
				$tr_input_array['TEXT']  = $text;
				$tr_input_array['HELP']  = $help;
				$tr_input_array['NAME']  = $name;
				$tr_input_array['VALUE'] = $values;
				$tr_input_array['INPUT_TYPE'] = "text";
				return style::replaceVar("tpl/tr-input.tpl", $tr_input_array);
				break;
		
			case "password":
			
				$tr_input_array['TEXT']  = $text;
				$tr_input_array['HELP']  = $help;
				$tr_input_array['NAME']  = $name;
				$tr_input_array['VALUE'] = $values;
				$tr_input_array['INPUT_TYPE'] = "password";
				return style::replaceVar("tpl/tr-input.tpl", $tr_input_array);
				break;
		
			case "hidden":
			
				$tr_input_array['TEXT']  = $text;
				$tr_input_array['HELP']  = $help;
				$tr_input_array['NAME']  = $name;
				$tr_input_array['VALUE'] = $values;
				$tr_input_array['INPUT_TYPE'] = "hidden";
				return style::replaceVar("tpl/tr-input.tpl", $tr_input_array);
				break;
		
			case "textarea":
			
				$tr_textarea_array['TEXT']  = $text;
				$tr_textarea_array['HELP']  = $help;
				$tr_textarea_array['NAME']  = $name;
				$tr_textarea_array['VALUE'] = $values;
				return style::replaceVar("tpl/tr-textarea.tpl", $tr_textarea_array);
				break;
		
			case "select":
			
			
				$tr_select_array['TEXT']  = $text;
				$tr_select_array['HELP']  = $help;
				$tr_select_array['DDBOX'] = $values;
				return style::replaceVar("tpl/tr-select.tpl", $tr_select_array);
				break;
		
		}
	
	}

	// Returns the HTML for a two column THT table
    public function sub($left, $right){	
		
        $sub_table_array['LEFT']  = $left;
        $sub_table_array['RIGHT'] = $right;
        if(file_exists(INC."../themes/".THEME."/tpl/sub-table.tpl")){

            $tbl = style::replaceVar("../themes/".THEME."/tpl/sub-table.tpl", $sub_table_array);
        
        }else{

            $tbl = style::replaceVar("tpl/sub-table.tpl", $sub_table_array);
        
        }

        return $tbl;
    
    }

    public function done(){
        global $dbh, $postvar, $getvar, $instance;
		
        foreach($getvar as $key => $value){

            if($key != "do"){

                if($var_seperator){

                    $var_seperator = "&";
                
                }else{

                    $var_seperator = "?";
                
                }

                $url .= $var_seperator.$key."=".$value;
            
            }

        }

        main::redirect($url);
    
    }

	// Returns HTML for a drop down menu with all values and selected
    public function dropDown($name, $values, $default = 0, $top = 1, $class = "", $custom = ""){
	
        if($top){

            $html .= '<select name="'.$name.'" id="'.$name.'" class="'.$class.'" '.$custom.'>';
        
        }

        if($values){

            foreach($values as $key => $value){

                $html .= '<option value="'.$value[1].'"';
                if(is_array($default)){

                    if(in_array($value[1], $default)){

                        $html .= 'selected="selected"';
                        
                    }

                }else{

                    if(strval($default) === strval($value[1])){

                        $html .= 'selected="selected"';
                        
                    }

                }

                $html .= '>'.$value[0].'</option>';
            
            }

        }

        if($top){

            $html .= '</select>';
        
        }

        return $html;
    
    }

	// Returns the filenames of a content in a folder 
    public function folderFiles($link, $ignored = array(".", "..", ".svn", "index.html")){
	
        $folder = $link;
        if($handle = opendir($folder)){
		
            while(false !== ($file = readdir($handle))){
			
                if(!is_null($ignored) and !in_array($file, $ignored)){

                    $values[] = $file;
                
                }

            }

        }

        closedir($handle);
        return $values;
    
    }

	// Checks the staff permissions for a nav item
    public function checkPerms($id, $user = 0){	
        global $dbh, $postvar, $getvar, $instance;
		
        if(!$user){

            $user = $_SESSION['user'];
        
        }

        $staff_data = $dbh->select("staff", array("id", "=", $user));
        if(!$staff_data['id']){

            $error_array['Error']    = "Staff member not found";
            $error_array['Staff ID'] = $user;
            main::error($error_array);
        
        }else{

            $perms = explode(",", $staff_data['perms']);
            foreach($perms as $value){

                if($value == $id){

                    return false;
                
                }

            }

            return true;
        
        }

    }

	// Checks the credentials of the client and logs in, returns true or false
    public function clientLogin($user, $pass){
        global $dbh, $postvar, $getvar, $instance;
        
        if($user && $pass){

            unset($where);
            $where[]    = array("user", "=", $postvar['user'], "AND");
            $where[]    = array("status", "=", "1", "OR", "1");
            $where[]    = array("status", "=", "2", "OR");
            $where[]    = array("status", "=", "4", "", "1");
            $users_data = $dbh->select("users", $where);
            
            if(!$users_data['id']){

                return false;
            
            }else{

                $ip = $_SERVER['REMOTE_ADDR'];
                if(crypto::passhash($postvar['pass'], $users_data['salt']) == $users_data['password']){

                    $_SESSION['clogged'] = 1;
                    $_SESSION['cuser']   = $users_data['id'];
                    self::thtlog("Client Login Successful", "Login successful (".$ip.")", $users_data['id']);
                    
                    $dbh->update("users", array("ip" => $ip), array("user", "=", $postvar['user']), "1");
                    return true;
                
                }else{

                    self::thtlog("Client Login Failed", "Login failed (".$ip.")", $users_data['id']);
                    return false;
                
                }

            }

        }else{

            return false;
        
        }

    }

	// Checks the credentials of a staff member, logs them in, and returns true or false
    public function staffLogin($user, $pass){	
        global $dbh, $postvar, $getvar, $instance;
		
        if($user && $pass){

            $staff_data = $dbh->select("staff", array("user", "=", $postvar['user']));
            if(!$staff_data['id']){

                return false;
            
            }else{

                $ip = $_SERVER['REMOTE_ADDR'];
                
                if(crypto::passhash($postvar['pass'], $staff_data['salt']) == $staff_data['password']){

                    $_SESSION['logged'] = 1;
                    $_SESSION['user']   = $staff_data['id'];
                    
                    self::thtlog("Staff Login Successful", "STAFF LOGIN SUCCESSFUL (".$ip.")", $staff_data['id'], "", "staff");
                    return true;
                
                }else{

                    self::thtlog("Staff Login Failed", "STAFF LOGIN FAILED (".$ip.")", $staff_data['id'], "", "staff");
                    return false;
                
                }

            }

        }else{

            return false;
        
        }

    }

    function changeClientPassword($clientid, $newpass){
        global $dbh, $postvar, $getvar, $instance;
        
        $users_data = $dbh->select("users", array("id", "=", $clientid));
        if(!$users_data['id']){

            return "That client does not exist.";
        
        }

        $command = server::changePwd($clientid, $newpass);
        if($command !== true){

            return $command;
        
        }

        $salt     = crypto::salt();
        $password = crypto::passhash($newpass, $salt);
        
        $users_update = array(
            "password" => $password,
            "salt"     => $salt
        );
        
        $dbh->update("users", $users_update, array("id", "=", $clientid));
        
        return true;
    
    }

    public function country_code_to_country($code){
        global $dbh, $postvar, $getvar, $instance;
        
        $countries_data = $dbh->select("countries", array("code", "=", $code), 0, "1");
        
        return $countries_data['country'];
    
    }

    public function canRun($function){

        if(function_exists($function) && !stripos(ini_get('disable_functions'), $function) && !stripos(ini_get('suhosin.executor.func.blacklist'), $function)){

            return true;
            
        }

        return false;
        
    }

    public function upload_theme(){

        $zip_arc = $_FILES['zip'];
        
        if(isset($zip_arc)){

            require_once('pclzip.lib.php');
            
            $upload_dir = '../themes';
            $filename   = $zip_arc['name'];
            
            if(move_uploaded_file($zip_arc['tmp_name'], $upload_dir.'/'.$filename)){

                $return = "Uploaded ".$filename." - ".$zip_arc['size']." bytes";
                
            }else{

                return "<font color='red'>Error : Unable to upload file</font>";
                
            }

            $zip_dir = basename($filename, ".zip");
            
            if(file_exists($upload_dir.'/'.$zip_dir)){

                unlink($upload_dir.'/'.$filename);
                return "<font color='red'>Error : That theme's directory already exists.</font>";
                
            }

            mkdir($upload_dir.'/'.$zip_dir);
            
            //unzip
            $archive = new PclZip($upload_dir.'/'.$filename);
            
            if($archive->extract(PCLZIP_OPT_PATH, $upload_dir.'/'.$zip_dir) == 0){

                unlink($upload_dir.'/'.$filename);
                return "<font color='red'>Error : Unable to unzip archive</font>";
                
            }

            //show what was just extracted
            $list = $archive->listContent();
            $return .= "<br><br><b>Files extracted from archive</b><br>";
            
            for($i = 0; $i < sizeof($list); $i++){

                if(!$list[$i]['folder']){

                    $bytes = " - ".$list[$i]['size']." bytes";
                    
                }else{

                    $bytes = "";
                    
                }

                $return .= $list[$i]['filename'].$bytes."<br>";
            
            }

            unlink($upload_dir.'/'.$filename);
            return $return;
            
        }

    }

    public function getLinuxDistro(){

        if(self::canRun("shell_exec")){

            $result = shell_exec("cat /etc/*-release");
            
            if(preg_match('/DISTRIB_DESCRIPTION="(.*)"/', $result, $match)){

                return $match[1];
                
            }else{

                return $result;
                
            }

        }

        return false;
    
    }

    public function isint($mixed_var){

        if(is_numeric($mixed_var) && !substr_count($mixed_var, ".") && !substr_count($mixed_var, "-")){

            return true;
        
        }else{

            return false;
        
        }

    }

    public function s($number, $prefix = ""){

        if($number != 1){

            $s = "s";
        
        }

        if($prefix){

            return $number.$prefix.$s;
        
        }else{

            return $s;
        
        }

    }

    public function addzeros($number, $commas = 0){

        if(!substr_count($number, ".")){

            $number = $number.".00";
        
        }else{

            $number_check = explode(".", $number);
            if(strlen($number_check[1]) == 1){

                $number = $number."0";
            
            }

        }

        if($commas){

            $number_check = explode(".", $number);
            $number       = strrev(chunk_split(strrev($number_check[0]), 3, ","));
            $number       = substr($number, 1, strlen($number)).".".$number_check[1];
            
        }

        return $number;
    
    }

    public function thtlog($logtype, $message, $uid = "", $user = "", $usertable = "users", $time = ""){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$time){

            $time = time();
        
        }

        //Helps to keep things synced.
        if(!$uid){

            $uid = self::userid($user, $usertable);
        
        }else{

            $user = self::uname($uid, $usertable);
        
        }

        $log_array = array(
            "uid" => $uid,
            "loguser" => $user,
            "logtime" => $time,
            "logtype" => $logtype,
            "message" => $message
        );
        
        $dbh->insert("logs", $log_array);
        
    }

    public function uname($id = 0, $table = "users"){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$id){

            $id = $SESSION['cuser'];
            if(!$id){

                $id    = $SESSION['user'];
                $table = "staff";
                
            }

        }

        $user_data = $dbh->select($table, array("id", "=", $id));
        return $user_data['user'];
        
    }

    public function userid($uname = 0, $table = "users"){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$uname){

            $uname = self::uname();
            if($SESSION['user']){

                $table = "staff";
                
            }

        }

        $user_data = $dbh->select($table, array("user", "=", $uname));
        return $user_data['id'];
        
    }

    public function money($amount, $code = "", $noamount = 0){
        global $dbh, $postvar, $getvar, $instance;
  
        if(empty($code)){

            $code = $dbh->config("currency");
        
        }

        $currency_format = $dbh->config("currency_format");
        
        $currency_symbols1 = array(
            "GBP" => "£",
            "USD" => "\$",
            "AUD" => "$",
            "CAD" => "$",
            "EUR" => "€",
            "JPY" => "¥",
            "NZD" => "$",
            "CHF" => "₣",
            "HKD" => "$",
            "SGD" => "$",
            "MXN" => "$"
        );

        $currency_symbols2 = array(
            "SEK" => " kr",
            "DKK" => " kr",
            "PLN" => " zł",
            "NOK" => " kr",
            "HUF" => " Ft",
            "CZK" => " Kč",
            "ILS" => " ₪"
        );
        
        if(substr_count($amount, "-")){

            $amount   = str_replace("-", "", $amount);
            $negative = "−";
        
        }

        $amount = self::addzeros($amount, 1);
      
        if($currency_format == ","){

            $amount = str_replace(array(",", "."), array(" ", ","), $amount);
            
        }

        if($currency_symbols1[$code]){

            $amount = $currency_symbols1[$code].$amount;

            if($noamount){

                return $currency_symbols1[$code];
            
            }else{

                return $negative.$amount;
            
            }

        }elseif($currency_symbols2[$code]){

            $amount = $amount.$currency_symbols2[$code];
            
            if($noamount){

                return $currency_symbols2[$code];
            
            }else{

                return $negative.$amount;
            
            }

        }else{

            if($noamount){

                return $code;
            
            }else{

                return $negative.$amount." ".$code;
            
            }

        }

    }

    public function userAdditional($id, $backup = ""){
        global $dbh, $postvar, $getvar, $instance;
		
        $users_data    = $dbh->select("users".$backup, array("id", "=", $id));
        $content = explode(",", $users_data['additional']);
        foreach($content as $key => $value){

            $inside             = explode("=", $value);
            $values[$inside[0]] = $inside[1];
        
        }

        return $values;
    
    }

    public function uidtopack($userid = "", $pid = ""){
        global $dbh, $postvar, $getvar, $instance;
        
        if(!$userid){

            $userid = $_SESSION['cuser'];
        
        }

        $userdata = $dbh->select("users", array("id", "=", $userid));
        if(empty($userdata)){

            $package_data['removed'] = 1;
            if(!$pid){

                $backup      = "_bak";
                $userdata = $dbh->select("users_bak", array("id", "=", $userid));
            
            }else{

                $package_data = array_merge($package_data, upgrade::pidtobak($pid, $userid));
                return $package_data;
            
            }
        
        }else{

            $package_data['removed'] = 0;
        
        }
		
		if(!$pid){
		
			$pid = $userdata['pid'];
		
		}

        $packageinfo = $dbh->select("packages", array("id", "=", $pid));
        $additional  = type::additional($pid);
        $uadditional = self::userAdditional($userdata['id'], $backup);
        
        $package_data['user_data']   = $userdata;
        $package_data['packages']    = $packageinfo;
        $package_data['additional']  = $additional;
        $package_data['uadditional'] = $uadditional;
        
        return $package_data;
    
    }

}

?>