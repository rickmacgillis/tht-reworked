<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Database Class
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPLv3
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class dbh{

    private $sql = array(), $con, $prefix, $conn_main;     
    
	// Connect SQL as class is called
    public function __construct(){
        
		if(INSTALL == 1){
		
			$this->conn_main = true;
			$response        = $this->connect();
			if(is_string($response)){

				die($response);
				
			}

		}
		
    }

    public function connect($host = 0, $user = 0, $pass = 0, $db = 0){

        include(INC."/conf.inc.php");
        $this->sql = $sql;
        
        if(!$host){

            $host = $this->sql['host'];
            $user = $this->sql['user'];
            $pass = $this->sql['pass'];
            $db   = $this->sql['db'];
            
        }

        $conn = @mysqli_connect($host, $user, $pass, $db);
        
        if($this->conn_main){

            $this->con = $conn;
            
        }

        if(!$conn){

            $this->conn_main = false;
            return "MySQLi Connection Error";
            
        }

        $this->prefix    = $this->sql['pre'];
        $this->conn_main = false;
        
        return $conn;
        
    }

	//Shows a SQL error from main class
    private function error($name, $mysqlerror, $func){
		
        $error['Error']       = $name;
        $error['Function']    = $func;
        $error['mySQL Error'] = $mysqlerror;
        main::error($error);
    
    }

	// Run any query and return the results
    public function query($sql, $conn = 0){
	
        $sql = preg_replace("/<PRE>/si", $this->prefix, $sql);
        
        if(!$conn){

            $conn = $this->con;
            
        }

        $response = @mysqli_query($conn, $sql);
        if(!$response){

            $this->error("mySQLi Query Failed: ", mysqli_error($conn), __FUNCTION__);
        
        }

        return $response;
    
    }

    public function num_rows($sql){

        return mysqli_num_rows($sql);
    
    }

    public function fetch_array($sql){

        return mysqli_fetch_array($sql);
    
    }

    //Returns a value without SQL Injection
    public function strip($value, $conn = 0){

        if(!$conn){

            $conn = $this->con;
            
        }

        if(is_array($value)){

            $array = array();
            foreach($value as $key => $val){

                if(is_array($val)){

                    $array[$key] = $this->strip($val);
                
                }else{

                    $val          = str_replace("\r\n", "[[n]]", $val);
                    $val          = stripslashes($val);
					
					if(INSTALL == 1){
                    
						$val = mysqli_real_escape_string($conn, $val);
					
					}
					
                    $val          = str_replace("[[n]]", "\n", $val);
                    $array[$key] = $val;
                
                }

            }

            return $array;
        
        }else{

            $value = str_replace("\r\n", "[[n]]", $value);
            $value = stripslashes($value);
					
			if(INSTALL == 1){
			
				$value = mysqli_real_escape_string($conn, $value);
			
			}
			
            $value = str_replace("[[n]]", "\n", $value);
            return $value;
        
        }

    }

	// Returns a value of a config variable
    public function config($name){
	
        $config_query = $this->select("config", array("name", "=", $name), 0, "1", 1);
        if($this->num_rows($config_query) == 0){

            $error['Error']       = "Couldn't Retrieve config value!";
            $error['Config Name'] = $name;
            main::error($error);
        
        }else{

            $value = $this->fetch_array($config_query);
            return $value['value'];
        
        }

    }

	// Returns values of a staff member
    public function staff($id){
	
        $id          = $this->strip($id);
        $staff_query = $this->select("staff", array("id", "=", $id), 0, 0, 1);
        if($this->num_rows($staff_query) == 0){

            $error['Error']    = "Couldn't retrieve staff data!";
            $error['Username'] = $id;
            main::error($error);
        
        }else{

            $value = $this->fetch_array($staff_query);
            return $value;
        
        }

    }

	// Returns values of a client
    public function client($id=0){
		
		if(!$id){
		
			$id = $_SESSION['cuser'];
		
		}
	
        $id          = $this->strip($id);
        $users_query = $this->select("users", array("id", "=", $id), 0, 0, 1);
        if($this->num_rows($users_query) == 0){

            $error['Error']    = "Couldn't retrieve client data!";
            $error['Username'] = $id;
            main::error($error);
        
        }else{

            $all_values = $this->fetch_array($users_query);
            return $all_values;
        
        }

    }

    public function updateConfig($name, $value){

		if(!$this->update("config", array("value" => $value), array("name", "=", $name))){
		
			$config_insert = array("value" => $value,
								   "name" => $name);
								   
			$this->insert("config", $config_insert);
		
		}
        
    }

    public function version(){

        $result = $this->fetch_array($this->query("SELECT Version()"));
        return $result[0];
        
    }

    //To use parenthesis, only set the 5th dimension of the array when you want to open and close the parenthesis.  To close it and not
    //use AND/OR, make the 4th dimension empty and set the fifth dimension.  When you do that, you'll open up a whole new dimension beyond Height/Width/Depth.
    //You might even jump a density as well!  =)  lol
        
    public function select($tablename, $where = 0, $order = 0, $limit = 0, $return_query = 0, $conn = 0){

		if(!$conn){

            $conn = $this->con;
			$pre = "<PRE>";
            
        }
	
        if($where){

            if(is_array($where[0])){

                foreach($where as $key => $val){

                    $column  = $val[0];
                    $operand = $val[1];
                    $field   = $this->strip($val[2]); //Let's get it buck naked.
                    $andor   = $val[3];
					
                    if(count($val) == 5){

                        if(!$parenthesees){

                            $parenthesees = 1;
                            $open_parenth = "(";
                            
                        }else{

                            $close_parenth = ")";
                            unset($open_parenth);
                            unset($parenthesees);
                            
                        }

                    }else{

                        unset($open_parenth);
                        unset($close_parenth);
                            
                    }

                    $ware_chunk .= $open_parenth.$column." ".$operand." '".$field."'".$close_parenth." ".$andor." "; //The where clause was bitten and is now a ware chunk.  lol
                    //WHERE (column = 'field' AND column2 = 'field2') OR column3 = 'field3'
                
                }

                $whereclause = "WHERE ".trim($ware_chunk);
                
            }else{

                $whereclause = "WHERE ".$where[0]." ".$where[1]." '".$this->strip($where[2])."'";
                
            }

        }else{

            $return_query = 1;
            
        }

        if($order){

            if(is_array($order[0])){

                foreach($order as $key => $val){

                    $column    = $val[0];
                    $direction = $val[1];
                    
                    $order_chunk .= $column." ".$direction.", ";
                    
                }

                $order_chunk = substr($order_chunk, 0, strlen($order_chunk) - 2);
                $orderclause = "ORDER BY ".$order_chunk;
                
            }else{

                $orderclause = "ORDER BY ".$order[0]." ".$order[1];
                
            }

        }

        if($limit){

            $limitclause = "LIMIT ".$limit;
            
        }
		
        $table_info_query = $this->query(trim("SELECT * FROM ".$pre.$tablename." ".$whereclause." ".$orderclause." ".$limitclause), $conn);
        
        if($return_query){

            return $table_info_query;
            
        }else{

            $table_info_data = $this->fetch_array($table_info_query);
            return $table_info_data;
            
        }

    }

    public function insert($table, $insert, $conn = 0){

		if(!$conn){

            $conn = $this->con;
			$pre = "<PRE>";
            
        }

        foreach($insert as $key => $val){

            $insert_query .= ", ".$key." = '".$this->strip($val)."'";
            
        }

        $insert_query = substr($insert_query, 2, strlen($insert_query));
        $response     = $this->query("INSERT INTO ".$pre.$table." SET ".$insert_query, $conn);
        
        return $response;
    
    }

    public function update($table, $update, $where = 0, $limit = 0, $all = 0, $conn = 0){

		if(!$conn){

            $conn = $this->con;
			$pre = "<PRE>";
            
        }

        if($where){

            if(is_array($where[0])){

                foreach($where as $key => $val){

                    $column  = $val[0];
                    $operand = $val[1];
                    $field   = $this->strip($val[2]);
                    $andor   = $val[3];
                    
                    if(count($val) == 5){

                        if($parenthesees == 1){

                            $parenthesees  = 2;
                            $close_parenth = ")";
                            unset($open_parenth);
                            
                        }

                        if(!$parenthesees){

                            $parenthesees = 1;
                            $open_parenth = "(";
                            
                        }

                    }else{

                        if($parenthesees == 2){

                            unset($parenthesees);
                            unset($close_parenth);
                            
                        }

                    }

                    $ware_chunk .= $open_parenth.$column." ".$operand." '".$field."'".$close_parenth." ".$andor." "; //The where clause was bitten and is now a ware chunk.  lol
                    //WHERE (column = 'field' AND column2 = 'field2') OR column3 = 'field3'
                
                }

                $whereclause = "WHERE ".trim($ware_chunk);
                
            }else{

                $whereclause = "WHERE ".$where[0]." ".$where[1]." '".$this->strip($where[2])."'";
                
            }

        }else{

            if(!$all){

                $this->error("A where clause was not specified for a MySQL update statement and the 'empty' flag was not set.", "", __FUNCTION__);
                return false;
                
            }

        }

        foreach($update as $key => $val){

            $update_query .= ", ".$key." = '".$this->strip($val)."'";
            
        }

        if($limit){

            $limitclause = "LIMIT ".$limit;
            
        }

        $update_query = substr($update_query, 2, strlen($update_query));
        $response     = $this->query("UPDATE ".$pre.$table." SET ".$update_query." ".$whereclause." ".$limitclause, $conn);
        
        return $response;
        
    }

    public function delete($table, $where = 0, $limit = 0, $empty = 0, $conn = 0){

		if(!$conn){

            $conn = $this->con;
			$pre = "<PRE>";
            
        }

        if($where){

            if(is_array($where[0])){

                foreach($where as $key => $val){

                    $column  = $val[0];
                    $operand = $val[1];
                    $field   = $this->strip($val[2]);
                    $andor   = $val[3];
                    
                    if(count($val) == 5){

                        if($parenthesees == 1){

                            $parenthesees  = 2;
                            $close_parenth = ")";
                            unset($open_parenth);
                            
                        }

                        if(!$parenthesees){

                            $parenthesees = 1;
                            $open_parenth = "(";
                            
                        }

                    }else{

                        if($parenthesees == 2){

                            unset($parenthesees);
                            unset($close_parenth);
                            
                        }

                    }

                    $ware_chunk .= $open_parenth.$column." ".$operand." '".$field."'".$close_parenth." ".$andor." "; //The where clause was bitten and is now a ware chunk.  lol
                    //WHERE (column = 'field' AND column2 = 'field2') OR column3 = 'field3'
                
                }

                $whereclause = "WHERE ".trim($ware_chunk);
                
            }else{

                $whereclause = "WHERE ".$where[0]." ".$where[1]." '".$this->strip($where[2])."'";
                
            }

        }else{

            //As it's risky to send delete queries through the DB, we need to make sure the script really MEANT to delete all table data
            //when a where clause is not specified, so we need to deliberately set the 'empty' flag to override this error.
            
            if(!$empty){

                $this->error("A where clause was not specified for a MySQL delete statement and the 'empty' flag was not set.", "", __FUNCTION__);
                return false;
                
            }

        }

        if($limit){

            $limitclause = "LIMIT ".$limit;
            
        }

        $response = $this->query("DELETE FROM ".$pre.$table." ".$whereclause." ".$limitclause, $conn);
        
        return $response;
        
    }

}

?>