<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Install Script
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

/*
 * Quick little function made to make generating a default site URL
 * easy. Hopefully this will assist alot of support topics regarding
 * bad site URLs, as the automatically generated ones should be correct.
 */

function generateSiteUrl(){

    $url = "";
    if(!empty($_SERVER["HTTPS"])){

        $url .= "https://";
    
    }else{

        $url .= "http://";
    
    }

    $url .= $_SERVER["SERVER_NAME"];
    $exploded = explode(basename($_SERVER["PHP_SELF"]), $_SERVER["PHP_SELF"]);
    $url .= dirname($exploded[0])."/";
    if(substr($url, strlen($url) - 2, 2) == "//"){

        $url = substr($url, 0, strlen($url) - 1);
    
    }

    return $url;

}

//INSTALL GLOBALS
define("CVER", "1.3.5");
define("NVER", "1.3.10 Reworked");
define("INSTALL", 0);

define("INC", "../includes"); // Set link
include(INC."/compiler.php"); // Get compiler

function writeconfig($host, $user, $pass, $db, $pre, $true){
    
    $conftemp_array['HOST'] = addcslashes($host, '\\\'');
    $conftemp_array['USER'] = addcslashes($user, '\\\'');
    $conftemp_array['PASS'] = addcslashes($pass, '\\\'');
    $conftemp_array['DB']   = addcslashes($db, '\\\'');
    $conftemp_array['PRE']  = addcslashes($pre, '\\\'');
    $conftemp_array['TRUE'] = $true;
    $tpl                     = style::replaceVar("../install/includes/tpl/conf-inc-temp.tpl", $conftemp_array);
    $link                    = INC."/conf.inc.php";
    if(is_writable($link)){

        file_put_contents($link, $tpl);
        return true;
    
    }else{

        return false;
    
    }

}

define("THEME", "bluelust"); // Set the theme
define("URL", "../"); // Set url to blank

define("NAME", "THT");
define("PAGE", "Install");
define("SUB", "Choose Method");

$install_array['VERSION']  = NVER;
$install_array['ANYTHING'] = "";
$link                       = INC."/conf.inc.php";
$disable                    = false;

if(@filesize(INC."/conf.inc.php") > 0){

    include(INC."/conf.inc.php");
    if(!writeconfig($sql['host'], $sql['user'], $sql['pass'], $sql['db'], $sql['pre'], "false")){

        $install_array['ANYTHING'] = "Your $link isn't writeable or does not exist.  Please make it writable and make sure it exists.";
        $disable                    = true;
    
    }else{

        $install_array['ANYTHING'] = "Since you've already ran the installer, your config has been re-written to the \"not installed\" state. If you are upgrading, this is normal!<br>";
    
    }

}

if(!file_exists($link)){
	
    if(!fopen($link, "w+")){

        $install_array["ANYTHING"] = "Your $link file doesn't exist. Please create it as a blank file and make it writable.";
        $disable                    = true;
    
    }

}

if(!is_writable($link)){

    if(!main::perms($link, 0666)){

        $install_array["ANYTHING"] = "Your $link isn't writeable.  Please make it writable.";
        $disable                    = true;
    
    }

}

echo style::get("header.tpl");
if($disable){

    echo '<script type="text/javascript">$(function(){
$(".twobutton").attr("disabled", "true");$("#method").attr("disabled", "true");}
);</script>';

}

$install_array["GENERATED_URL"] = generateSiteUrl();
echo style::replaceVar("../install/includes/tpl/install.tpl", $install_array);
echo style::get("footer.tpl");

include(INC."/output.php"); //Output it

?>