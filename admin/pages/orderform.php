<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Order Form
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

// Check if called by script
if(THT != 1){

    die();

}

define("PAGE", "Order Form");

class page{

    public $navtitle;
    public $navlist = array();
    
    public function __construct(){

        $this->navtitle  = "Order Form Actions";
        $this->navlist[] = array("Custom Fields","table_gear.png", "customf");
    
    }

    public function description(){

        return "<strong>Client Order Form Options</strong><br />
		This is where you can modify and customize your frontend order form. Most notably,
		you can add and edit custom fields to meet your exact needs.";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
		
        // An honest attempt to make this system a little less painful (for me)...
        if(array_key_exists("sub", $getvar) && !empty($getvar["sub"])){

            $sub = "_".strtolower($getvar["sub"]);
            if(method_exists($this, $sub)){

                $this->{$sub}();
                return;
            
            }

            main::error(array(__FILE__ => "<code>\$this->$sub</code> isn't a method."));
        
        }

    }

    public function _customf(){
        global $dbh, $postvar, $getvar, $instance;
		
        echo style::replaceVar("tpl/admin/order-form/top.tpl");
        $orderfields_query = $dbh->select("orderfields", 0, array("sortorder", "ASC"));
        if($dbh->num_rows($orderfields_query) == 0){

            echo "<center>".style::notice(false, "You don't have any custom fields defined!")."</center>";
            return;
        
        }

        echo '<div id="sortableDiv">';
        while($arr = $dbh->fetch_array($orderfields_query)){

            unset($order_field_box_array);

            $order_field_box_array["ID"]          = $arr["id"];
            $order_field_box_array["TITLE"]       = htmlspecialchars($arr["title"]);
            $order_field_box_array["DESCRIPTION"] = htmlspecialchars($arr["description"]);
            if($arr["required"] == 1){

                $order_field_box_array["REQ"]  = "<span style=\"color: red;\">*</span>";
                $order_field_box_array["REQC"] = " checked=\"yes\"";
            
            }else{

                $order_field_box_array["REQ"] = "";
            
            }

            // A lame solution but I don't feel like solving this problem at 5 AM...
            $selected = array(false, false, false, false, false, false, false);
            switch($arr["type"]){

                case "text":
                    $selected[0] = true;
                    break;
                case "password":
                    $selected[1] = true;
                    break;
                case "checkbox":
                    $selected[2] = true;
                    break;
                case "select":
                    $selected[3] = true;
                    break;
                case "tel":
                    $selected[4] = true;
                    break;
                case "url":
                    $selected[5] = true;
                    break;
                case "email":
                    $selected[6] = true;
                    break;
                case "range":
                    $selected[7] = true;
                    break;
            
            }

			$value = array(
                'id'    => 'cfield-field-typelist-'.$arr["id"],
                'class' => 'cfield-field cfield-field-'.$arr["id"].' cfield-field-typelist'
			);
			
			$extra = array(
                array(
                    'text'     => '--- Standard ---',
                    'value'    => 'standard',
                    'disabled' => true
                ),
                array(
                    'text'     => 'Text',
                    'value'    => 'text',
                    'selected' => $selected[0]
                ),
                array(
                    'text'     => 'Password',
                    'value'    => 'password',
                    'selected' => $selected[1]
                ),
                array(
                    'text'     => 'Checkbox',
                    'value'    => 'checkbox',
                    'selected' => $selected[2]
                ),
                array(
                    'text'     => 'Select Box',
                    'value'    => 'select',
                    'selected' => $selected[3]
                ),
                array(
                    'text'     => '--- HTML5 ---',
                    'value'    => 'html5',
                    'disabled' => true
                ),
                array(
                    'text'     => 'Telephone #',
                    'value'    => 'tel',
                    'selected' => $selected[4]
                ),
                array(
                    'text'     => 'URL',
                    'value'    => 'url',
                    'selected' => $selected[5]
                ),
                array(
                    'text'     => 'Email',
                    'value'    => 'email',
                    'selected' => $selected[6]
                ),
                array(
                    'text'     => 'Range',
                    'value'    => 'range',
                    'selected' => $selected[7]
                )
            );
			
            $order_field_box_array["TYPELIST"] = style::createInput('select', 'cfield-field-typelist-'.$arr["id"], '', $value, $extra);
            $order_field_box_array["DEFAULTVALUE"] = htmlspecialchars($arr["default"]);
            $order_field_box_array["REGEX"]        = htmlspecialchars($arr["regex"]);
            echo style::replaceVar("tpl/admin/order-form/order-field-box.tpl", $order_field_box_array);
        
        }

        echo '</div>';
        echo style::replaceVar("tpl/admin/order-form/bottom.tpl");
    
    }

}

?>