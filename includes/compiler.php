<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Compiler
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

define("THT", 1);
define("LANG", "en_US");

date_default_timezone_set("GMT");

$path     = dirname($_SERVER['PHP_SELF']);
$position = strrpos($path, '/') + 1;
define("FOLDER", substr($path, $position)); // Add current folder name to global

if(!($_GET['page'] == 'invoices' && FOLDER == "client")){

    //As this prevents PayPal from using the site, disable this script when PayPal might be trying to get through.
    
    // Helps prevent against CSRF attacks and PayPal execution.
    require_once("csrf-magic.php");

}

// We don't want this to be called directly.
$compile = explode("/", $_SERVER["SCRIPT_FILENAME"]);
if($compile[count($compile) - 1] == "compiler.php"){

    die("Please do not call \"compiler.php\" directly.");

}

//Page generated
$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];

//Start us up
if(CRON != 1){

    session_start();
    header("Cache-control: private");

}

//Stop the output
ob_start();

//Check for Dependencies
$deps = checkForDependencies();
if($deps !== true){

    die((string) $deps);

}

//Check PHP Version
$version = explode(".", phpversion());

//Grab DB First
require INC."/instances/class_db.php";
if(file_exists(INC."/conf.inc.php")){

    include INC."/conf.inc.php";

}

//Is THT installed?
if($sql['install']){

    define("INSTALL", 1);

}

$dbh = new dbh;
global $dbh;

include(INC."/instances/class_instance.php");
$instance = new instance;
global $instance;

$folder = INC."/classes";
if($handle = opendir($folder)){

    while(false !== ($file = readdir($handle))){
	
        if($file != "." && $file != ".."){
		
            $base = explode(".", $file); 
            if($base[1] == "php"){
			
                $base2 = explode("_", $base[0]);
                if($base2[0] == "class"){

                    require $folder."/".$file;
					new $base2[1]; //Even though these aren't instances being used right now, they do store instance data in class instance.
					
                }

            }

        }

    }

}

closedir($handle); 

//Define the Admin directory
if(!defined("ADMINDIR")){

    $admin_dir = find_admin_dir("../");
    define("ADMINDIR", $admin_dir);
    
}

if(INSTALL == 1){

	$session_timeout = $dbh->config("session_timeout") * 60; //Make minutes into seconds.
	if($session_timeout){

		if(time() - $session_timeout > $_SESSION['time'] && $_SESSION['time']){

			session_destroy();
			main::redirect("./");
			
		}

		//Keep it alive when there's activity.
		$_SESSION['time'] = time();
		
	}
    
    define("THEME", $dbh->config("theme")); // Set the default theme
    // Sets the URL THT is located at
    if($_SERVER["HTTPS"]){

        // HTTPS support
        define("URL", str_replace("http://", "https://", $dbh->config("url")));
        
    }else{

        define("URL", $dbh->config("url"));
        
    }

    define("NAME", $dbh->config("name")); // Sets the name of the website
    
}

// Converts the $_POST global array into $postvar - DB Friendly.
$postvar = array();
if(isset($_POST)){

    foreach($_POST as $key => $value){

        $postvar[$key] = $dbh->strip($value);
        
    }

}

global $postvar;

// Converts the $_GET global array into $getvar - DB Friendly.
$getvar = array();
if(isset($_GET)){

    foreach($_GET as $key => $value){

        $getvar[$key] = $dbh->strip($value);
        
    }

}

global $getvar;

// Cheap. I know.
if(!is_dir("../includes") && !is_dir("../themes") && !is_dir("../".ADMINDIR)){

    $check = explode("/", dirname($_SERVER["SCRIPT_NAME"]));
    
    if($check[count($check) - 1] == "install"){

        die("Please change your THT directory's name from something else other than \"install\". Please?");
        
    }

}

if(FOLDER != "install" && FOLDER != "includes" && INSTALL != 1){
    
    // Lets just redirect to the installer, shall we?
    $installURL = INC."../install";
    header("Location: $installURL");
    
}

$_SESSION['errors'] = 0;

// If payment..
if(FOLDER == "client" && $getvar['page'] == "invoices" && $getvar['iid'] && $_SESSION['clogged'] == 1){

    invoice::pay($getvar['iid'], "client/index.php?page=invoices");
    echo "You made it this far.. something went wrong.";
    
}

function checkForDependencies(){

    $needed  = array();
	
    $version = explode(".", phpversion());
    if($version[0] < 5){

        die("PHP Version 5 or greater is required! You're currently running: ".phpversion());
        
    }

    if(!function_exists("curl_init")){

        $needed[] = "cURL";
        
    }

    if(!function_exists("mysqli_connect")){

        $needed[] = "MySQLi";
        
    }

    if(!function_exists("hash_algos")){

        $needed[] = "PECL hash";
        
    }

    if(count($needed) == 0){

        return true;
        
    }

    $output = "The following function(s) are/is needed for TheHostingTool to run properly: <ul>";
    foreach($needed as $key => $value){

        $output .= "<li>$value</li>";
        
    }

    $output .= "</ul>";
    return $output;
    
}

function find_admin_dir($dir){

    foreach(glob($dir.'/*', GLOB_ONLYDIR) as $dir_search){

        if(is_file($dir_search."/ADMIN_DIR")){

            $admindir = str_replace($dir."/", "", $dir_search);
            return $admindir;
            
        }

    }

}

?>