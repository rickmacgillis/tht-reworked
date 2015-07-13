<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Account/E-mail Confirmation
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Compile THT
define("INC", "../includes");
include(INC."/compiler.php");

//THT Variables
define("PAGE", "Confirm");

echo style::get("header.tpl"); //Output Header
echo '<div align="center">';

if(!$getvar['u']){

    echo "Please use the link provided in your e-mail.";

}else{

    $username = $getvar['u'];
    $confirm  = $getvar['c'];
    $command  = server::confirm($username, $confirm);
    if($command == false){

        echo 'Confirmation failed, please try to copy and paste the link into your browser.';
    
    }else{

        echo 'Account confirmed.';
    
    }

}

echo '</div>'; //End it
echo style::get("footer.tpl"); //Output Footer

//Output
include(INC."/output.php");

?>