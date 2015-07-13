<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Logs
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

define("PAGE", "Logs");

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        if(is_numeric($getvar['dellogid'])){

            $dbh->delete("logs", array("id", "=", $getvar['dellogid']), "1");
            main::errors("Log entry deleted.");
        
        }

        if(is_numeric($getvar['removeall'])){

            if($getvar['confirm'] != '1'){

                main::errors("Are you sure you wish to remove ALL log entries?   <a href = '?page=logs&removeall=".$getvar['removeall']."&confirm=1'>Yes</a>    |    <a href = '?page=logs'>No</a>");
            
            }else{

                $dbh->delete("logs", 0, 0, 1);
				main::thtlog("Logs Cleared", "All Logs were removed.", $_SESSION['user'], "", "staff");
                main::redirect("?page=logs");
            
            }

        }

        if(is_numeric($getvar['logid'])){

            $loginfo = $dbh->select("logs", array("id", "=", $getvar['logid']));
            
            $admin_log_view_array['MESSAGE'] = $loginfo['message'];
            echo style::replaceVar("tpl/admin/logs/admin-log-view.tpl", $admin_log_view_array);
            
        }else{

            $per_page = $getvar['limit'];
            $start    = $getvar['start'];
            
            if(!$postvar['show']){

                $show = $getvar['show'];
                
            }else{

                $show  = $postvar['show'];
                $start = 0;
                
            }

            if(!$show){

                $show = "all";
                
            }

            if(!$per_page){

                $per_page = 10;
                
            }

            if(!$start){

                $start = 0;
                
            }

            if($show != "all"){

                $logs_query = $dbh->select("logs", array("logtype", "=", $show), array("logtime", "DESC"), $start.", ".$per_page, 1);
                
            }else{

                $logs_query = $dbh->select("logs", 0, array("logtime", "DESC"), $start.", ".$per_page, 1);
                
            }

			$all_logs_query = $dbh->select("logs");
            $num_logs = $dbh->num_rows($all_logs_query);
            $pages    = ceil($num_logs / $per_page);
            
            if($num_logs == 0){

                $admin_logs_list_array['LOGS']   = "";
                $admin_logs_list_array['PAGING'] = "";
                main::errors("No logs found.");
                
            }else{

                while($logs_data = $dbh->fetch_array($logs_query)){

                    $message_data     = explode("<", substr($logs_data['message'], 0, 100));
                    $admin_log_item_array['USER']    = $logs_data['loguser'];
                    $admin_log_item_array['DATE']    = main::convertdate("n/d/Y", $logs_data['logtime']);
                    $admin_log_item_array['TIME']    = main::convertdate("g:i A", $logs_data['logtime']);
                    $admin_log_item_array['MESSAGE'] = $message_data[0];
                    $admin_log_item_array['LOGID']   = $logs_data['id'];
                    $admin_logs_list_array['LOGS'] .= style::replaceVar("tpl/admin/logs/admin-log-item.tpl", $admin_log_item_array);
                    
                }

            }

            if($start != 0){

                $back_page               = $start - $per_page;
                $admin_logs_list_array['PAGING'] = '<a href="?page=logs&show='.$show.'&start='.$back_page.'&limit='.$per_page.'">BACK</a>&nbsp;';
                
            }

            for($i = 1; $i <= $pages; $i++){

                $start_link = $per_page * ($i - 1);
                if($start_link == $start){

                    $admin_logs_list_array['PAGING'] .= '&nbsp;<b>'.$i.'</b>&nbsp;';
                    
                }else{

                    $admin_logs_list_array['PAGING'] .= '&nbsp;<a href="?page=logs&show='.$show.'&start='.$start_link.'&limit='.$per_page.'">'.$i.'</a>&nbsp;';
                    
                }

            }

            if(($start + $per_page) / $per_page < $pages && $pages != 1){

                $next_page = $start + $per_page;
                $admin_logs_list_array['PAGING'] .= '&nbsp;<a href="?page=logs&show='.$show.'&start='.$next_page.'&limit='.$per_page.'">NEXT</a>';
                
            }
			
			$shown = array();
			$log_type_values[] = array("Show All", "all");
			
			$logs_query = $dbh->select("logs", 0, array("logtype", "ASC"), 0, 1);
			while($logs_data = $dbh->fetch_array($logs_query)){
			
				if(!in_array($logs_data['logtype'], $shown)){
				
					$log_type_values[] = array($logs_data['logtype'], $logs_data['logtype']);
					$shown[] = $logs_data['logtype'];
					
				}
			
			}
			
			$admin_logs_list_array['SHOW_TYPE'] = main::dropdown("show", $log_type_values);
            echo style::replaceVar("tpl/admin/logs/admin-logs-list.tpl", $admin_logs_list_array);
            
        }

    }

}

?>