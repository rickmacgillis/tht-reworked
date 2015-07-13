<?php
//////////////////////////////
// The Hosting Tool
// Admin Area - Logs
// By KuJoe
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){die();}

define("PAGE", "Logs");

class page {
        
        public function content() { # Displays the page 
                global $style;
                global $db;
                global $main;

                if(is_numeric($main->getvar['dellogid'])){
                echo "<font color = '#FF0000'>Log entry deleted!</font>";
                $db->query("DELETE FROM `<PRE>logs` WHERE `id` = '".$main->getvar['dellogid']."'");
                }
                
                if(is_numeric($main->getvar['removeall'])){
                if($main->getvar['confirm'] != '1'){
                echo "<font color = '#FF0000'>Are you sure you wish to remove ALL log entries? &nbsp;&nbsp;<a href = '?page=logs&removeall=".$main->getvar['removeall']."&confirm=1'>Yes</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href = '?page=logs'>No</a></font>";
                }else{
                echo "<font color = '#FF0000'>All log entries have been removed!</font>";
                $db->query("DELETE FROM `<PRE>logs`");
                }
                }
                
                if(is_numeric($main->getvar['logid'])){
                $query = $db->query("SELECT * FROM `<PRE>logs` WHERE `id` = '".$main->getvar['logid']."'");
                $loginfo = $db->fetch_array($query);

                $array['MESSAGE'] = $loginfo['message'];
                echo $style->replaceVar("tpl/adminlogview.tpl", $array);
                }else{
                echo $style->replaceVar("tpl/adminlogstop.tpl", $array);
                $l = $main->getvar['l'];
                $p = $main->getvar['p'];
                if (!$main->postvar['show'] && !$main->getvar['show']) {
                        $show = "all";
                }
                if (!$main->postvar['show']) {
                        $show = $main->getvar['show'];
                }
                else {
                        $show = $main->postvar['show'];
                        $p = 0;
                }
                if (!($l)) {
                        $l = 10;
                }
                if (!($p)) {
                        $p = 0;
                }
                if ($show != all) {
                if($show == "PayPal"){
                 $paypal_wildcard = "%";  //I have no idea why the logs search the message instead of putting another entry in the DB, but this is the quick way to patch
                                          //PayPal into the search system without having to rewrite the logging system.
                }
                        $query = $db->query("SELECT * FROM `<PRE>logs` WHERE `message` LIKE '".$paypal_wildcard."$show%'");
                }
                else {
                        $query = $db->query("SELECT * FROM `<PRE>logs`");
                }
                $pages = intval($db->num_rows($query)/$l);
                                if ($db->num_rows($query)%$l) {
                                        $pages++;
                                }
                                $current = ($p/$l) + 1;
                                if (($pages < 1) || ($pages == 0)) {
                                        $total = 1;
                                }
                                else {
                                        $total = $pages;
                                }
                                $first = $p + 1;
                                if (!((($p + $l) / $l) >= $pages) && $pages != 1) {
                                        $last = $p + $l;
                                }
                                else{
                                        $last = $db->num_rows($query);
                                }
                                if ($db->num_rows($query) == 0) {
                                        echo "No logs found.";
                                }
                                else {
                                        if ($show != all) {
                                                $query2 = $db->query("SELECT * FROM `<PRE>logs` WHERE `message` LIKE '".$paypal_wildcard."$show%' ORDER BY `id` DESC LIMIT $p, $l");
                                        }
                                        else {
                                                $query2 = $db->query("SELECT * FROM `<PRE>logs` ORDER BY `id` DESC LIMIT $p, $l");
                                        }
                                        while($data = $db->fetch_array($query2)) {
                                                $message_data = explode("<", substr($data['message'], 0, 100));
                                                $array['USER'] = $data['loguser'];
                                                $array['DATE'] = $main->convertdate("n/d/Y", $data['logtime']);
                                                $array['TIME'] = $main->convertdate("g:i A", $data['logtime']);
                                                $array['MESSAGE'] = $message_data[0];
                                                $array['LOGID'] = $data['id'];
                                        echo $style->replaceVar("tpl/adminlogs.tpl", $array);
                                        }
                                }
                echo "</table></div>";
                echo "<center>";
                if ($p != 0) {
                        $back_page = $p - $l;
                        echo("<a href=\"$PHP_SELF?page=logs&show=$show&p=$back_page&l=$l\">BACK</a>    \n");
                }

                for ($i=1; $i <= $pages; $i++) {
                        $ppage = $l*($i - 1);
                        if ($ppage == $p){
                                echo("<b>$i</b>\n");
                        }
                        else{
                                echo("<a href=\"$PHP_SELF?page=logs&show=$show&p=$ppage&l=$l\">$i</a> \n");
                        }
                }

                if (!((($p+$l) / $l) >= $pages) && $pages != 1) {
                        $next_page = $p + $l;
                        echo("    <a href=\"$PHP_SELF?page=logs&show=$show&p=$next_page&l=$l\">NEXT</a>");
                }
                echo "</center>";
                }
        }
}
?>
