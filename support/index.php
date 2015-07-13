<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Support Area
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

define("INC", "../includes");
include(INC."/compiler.php");

define("PAGE", "Support Area");

ob_start();

if(!$getvar['page']){

    $getvar['page'] = "kb";

}

$supportnav_data = $dbh->select("supportnav", array("link", "=", $getvar['page']), array("id", "ASC"));
$header          = $supportnav_data['visual'];
$link            = "pages/".$getvar['page'].".php";
if($dbh->config("senabled") == 0){

    $html = $dbh->config("smessage");

}else{

    if(!file_exists($link)){

        $html = "Seems like the .php is non existant. Is it deleted?";
    
    }else{

        if($getvar['page']){

            include($link);
            $content = new page;
            if(isset($getvar['sub'])){

                ob_start();
                $content->content();
                $html = ob_get_contents();
                ob_clean();
            
            }elseif($content->navlist){

                $html = $content->description();
            
            }else{

                ob_start();
                $content->content();
                $html = ob_get_contents();
                ob_clean();
            
            }

        }

    }

}

echo '<div>';
echo main::table($header, $html);
echo '</div>';

if($_SESSION['user']){

    $showuser = $dbh->staff($_SESSION['user']);
    $showuser = $showuser['user'];
    $showuser = "back, ".$showuser;

}elseif($_SESSION['cuser']){

    $showuser = $dbh->client($_SESSION['cuser']);
    $showuser = $showuser['user'];
    $showuser = "back, ".$showuser;

}else{

    $showuser = "to <NAME>";

}

define("SUB", $header);
define("INFO", '<b>Welcome '.$showuser.'</b><br />'.SUB);

$html_buff = ob_get_contents();
ob_end_clean();

echo style::get("header.tpl");
echo $html_buff;
echo style::get("footer.tpl");

include(INC."/output.php");

?>