<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Ajax Install Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

define("INC", "../../includes");
include(INC."/compiler.php");

class Installer{

    public function sqlcheck(){
		
        if(INSTALL != 1){

            $host = $_GET['host'];
            $user = $_GET['user'];
            $pass = $_GET['pass'];
            $db   = $_GET['db'];
            $pre  = $_GET['pre'];
            
            $con = @mysql_connect($host, $user, $pass);
            if(!$con){

                echo 0;
            
            }else{

                $seldb = mysql_select_db($db, $con);
                if(!$seldb){

                    echo 1;
                
                }else{

                    if($this->writeconfig($host, $user, $pass, $db, $pre, "false")){

                        echo 2;
                    
                    }else{

                        echo 3;
                    
                    }

                }

            }

        }else{

            echo 4;
        
        }

    }

    private function writeconfig($host, $user, $pass, $db, $pre, $true){
		
        $conf_inc_temp_array['HOST'] = $host;
        $conf_inc_temp_array['USER'] = $user;
        $conf_inc_temp_array['PASS'] = $pass;
        $conf_inc_temp_array['DB']   = $db;
        $conf_inc_temp_array['PRE']  = $pre;
        $conf_inc_temp_array['TRUE'] = $true;
        $link           = INC."/conf.inc.php";
		
        if(is_writable($link)){

            file_put_contents($link, style::replaceVar("../install/includes/tpl/conf-inc-temp.tpl", $conf_inc_temp_array));
            return true;
        
        }else{

            return false;
        
        }

    }

    public function install(){
        global $dbh, $postvar, $getvar, $instance;
		
        if(INSTALL != 1){

            include(INC."/conf.inc.php");
            $dbCon = mysql_connect($sql['host'], $sql['user'], $sql['pass']);
            $dbSel = mysql_select_db($sql['db'], $dbCon);
            if($getvar['type'] == "install"){

                $errors = $this->installsql("sql/install.sql", $sql['pre'], $dbCon);
            
            }elseif($getvar['type'] == "upgrade"){

                $errors  = $this->installsql("sql/upgrade.sql", $sql['pre'], $dbCon);
                $packages_query = mysql_query("SELECT * FROM `{$sql['pre']}packages`", $dbCon);
                $n       = 1;
                while($packages_data = mysql_fetch_array($packages_query)){

                    if($packages_data['oid'] == "0"){

                        mysql_query("UPDATE `{$sql['pre']}packages` SET `oid` = '{$n}' WHERE `id` = '{$packages_data['id']}'", $dbCon);
                        $n++;
                    
                    }

                }

                if($n > 1){

                    mysql_query("ALTER TABLE `{$sql['pre']}packages` ADD UNIQUE (`oid`)", $dbCon);
                
                }

            }else{

                echo "Eh? Fatal Error Debug: ".$getvar['type'];
            
            }

            $ver   = mysql_real_escape_string($_GET['version']);
            $config_update_query = mysql_query("UPDATE `{$sql['pre']}config` SET `value` = '{$ver}' WHERE `name` = 'version'");
            if(!$config_update_query){

                echo '<div class="errors">There was a problem editing your script version!</div>';
            
            }

            if($getvar['type'] == "install"){

                $config_update_query = mysql_query("UPDATE `{$sql['pre']}config` SET `value` = 'bluelust' WHERE `name` = 'theme'");
                if(!$config_update_query){

                    echo '<div class="errors">There was a problem setting your default theme!</div>';
                
                }

            }

            echo "Complete!<br /><strong>There were ".$errors['n']." errors while executing the SQL!</strong><br />";
            if(!$this->writeconfig($sql['host'], $sql['user'], $sql['pass'], $sql['db'], $sql['pre'], "true")){

                echo '<div class="errors">There was a problem re-writing to the config!</div>';
            
            }

            if($getvar['type'] == "install"){

                echo '<div align="center"><input type="button" name="button4" id="button4" value="Next Step" onclick="change()" /></div>';
            
            }elseif($getvar['type'] == "upgrade"){

                echo '<div class="errors">Your upgrade is now complete! You can use the script as normal.  With the changes to the password encryption, you\'ll need to reset your password and have your clients do the same.</div>';
            
            }

            if($errors['n']){

                echo "<strong>SQL Queries (Broke):</strong><br /><pre>";
                foreach($errors['errors'] as $value){

                    echo nl2br(htmlentities($value))."<br /><br />";
                
                }

                echo "</pre>";
            
            }

        }

    }

    private function installsql($data, $pre, $con = 0){
        global $dbh, $postvar, $getvar, $instance;
		
        $array['PRE'] = $pre;
        $sContents     = style::replaceVar($data, $array, 0, 1);
        // replace slash quotes so they don't get in the way during parse
        // tried a replacement array for this but it didn't work
        // what's a couple extra lines of code, anyway?
        
        $sDoubleSlash = '~~DOUBLE_SLASH~~';
        $sSlashQuote  = '~~SLASH_QUOTE~~';
        $sSlashSQuote = '~~SLASH_SQUOTE~~';
        
        $sContents = str_replace('\\\\', $sDoubleSlash, $sContents); //'
        $sContents = str_replace('\"', $sSlashQuote, $sContents);
        $sContents = str_replace("\'", $sSlashSQuote, $sContents);
        
        $iContents         = strlen($sContents);
        $sDefaultDelimiter = ';';
        
        $aSql       = array();
        $sSql       = '';
        $bInQuote   = false;
        $sDelimiter = $sDefaultDelimiter;
        $iDelimiter = strlen($sDelimiter);
        $aQuote     = array("'", '"');
        for($i = 0; $i < $iContents; $i++){

            if($sContents[$i] == "\n" || $sContents[$i] == "\r"){

                // Check for Delimiter Statement
                if(preg_match('/delimiter\s+(.+)/i', $sSql, $aMatches)){

                    $sDelimiter = $aMatches[1];
                    $iDelimiter = strlen($sDelimiter);
                    $sSql       = '';
                    continue;
                
                }

            }

            if(in_array($sContents[$i], $aQuote)){

                $bInQuote = !$bInQuote;
                if($bInQuote){

                    $aQuote = array($sContents[$i]);
                
                }else{

                    $aQuote = array("'", '"');
                
                }

            }

            if($bInQuote){

                $sSql .= $sContents[$i];
            
            }else{

                // fill a var with the potential delimiter - aka read-ahead
                if(substr($sContents, $i, $iDelimiter) == $sDelimiter){

                    // Clear Comments
                    $sSql = preg_replace("/^(-{2,}.+)/", '', $sSql);
                    $sSql = preg_replace("/(?:\r|\n)(-{2,}.+)/", '', $sSql);
                    
                    // Put quotes back where you found them
                    $sSql = str_replace($sDoubleSlash, '\\\\', $sSql); //'
                    $sSql = str_replace($sSlashQuote, '\\"', $sSql);
                    $sSql = str_replace($sSlashSQuote, "\\'", $sSql);
                    
                    // FIXME: odd replacement issue, just fix it for now and move on
                    $sSql = str_replace('IFEXISTS`', 'IF EXISTS `', $sSql);
                    
                    $aSql[] = $sSql;
                    $sSql   = '';
                    
                    // pass delimiter
                    $i += $iDelimiter;
                
                }else{

                    $sSql .= $sContents[$i];
                
                }

            }

        }

        $aSql = array_map('trim', $aSql);
        $aSql = array_filter($aSql);
        
        $n = 0;
        foreach($aSql as $sSql){

            if($con){

                $query = mysql_query($sSql, $con);
            
            }else{

                $query = $dbh->query($sSql);
            
            }

            if(!$query){

                $n++;
                $errors[] = $sSql;
            
            }

        }

        if(!$n){

            $n = 0;
        
        }

        $stuff['n']      = $n;
        $stuff['errors'] = $errors;
        return $stuff;
    
    }

    public function installfinal(){
        global $dbh, $postvar, $getvar, $instance;

        $staff_query = $dbh->select("staff");
        if(!$dbh->num_rows($staff_query)){

            foreach($getvar as $key => $value){

                if(!$value){

                    $n++;
                
                }

            }

            if(!$n){

                $dbh->updateConfig("url", $getvar['url']);
                $salt     = crypto::salt();
                $password = crypto::passhash($getvar['pass'], $salt);
                
                $staff_insert = array(
                    "user"     => $getvar['user'],
                    "email"    => $getvar['email'],
                    "password" => $password,
                    "salt"     => $salt,
                    "name"     => $getvar['name']
                );
                $dbh->insert("staff", $staff_insert);
                echo 1;
            
            }else{

                echo 0;
            
            }

        }

    }

}

if(isset($_REQUEST['function']) and $_REQUEST['function'] != ""){

    $Ajaxinstaller = new Installer();
    if(method_exists($Ajaxinstaller, $_REQUEST['function'])){

        $Ajaxinstaller->{$_REQUEST['function']}();
    
    }

}

?>