<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Admin Area - Knowledge Base
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

//Check if called by script
if(THT != 1){

    die();

}

class page{

    public $navtitle;
    public $navlist = array();
    
    public function __construct(){

        $this->navtitle  = "Knowledge Base Menu";
        $this->navlist[] = array("Add Categories", "folder_add.png", "add_category");
        $this->navlist[] = array("Edit/Delete Categories", "folder_page.png", "edit_category");
        $this->navlist[] = array("Add Articles", "page_white_text.png", "add_article");
        $this->navlist[] = array("Edit/Delete Articles", "page_white_edit.png", "edit_article");
    
    }

    public function description(){

        return "<strong>Knowledge Base</strong><br />
                Welcome to the Knowledge Base category. In this section you can manage all your catergories and articles for the knowledge base.";
    
    }

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
		
        switch($getvar['sub']){

            case "add_category":
				echo $this->add_category();
                break;

            case "edit_category":
				echo $this->edit_category();
                break;
            
            case "add_article":
				echo $this->add_article();
                break;
            
            case "edit_article":
				echo $this->edit_article();
                break;
        
        }

    }
	
	private function add_category(){
        global $dbh, $postvar, $getvar, $instance;
	
		if($_POST['add']){

			check::empty_fields();
			if(!main::errors()){

				$insert_cats = array(
					"name"        => $postvar['name'],
					"description" => $postvar['description']
				);
				$dbh->insert("cats", $insert_cats);
				main::errors("Category Added!");
			
			}

		}

		return style::replaceVar("tpl/admin/kb/add-category.tpl");
	
	}
	
	private function edit_category(){
        global $dbh, $postvar, $getvar, $instance;

		if($_POST['edit']){

			check::empty_fields();
			if(!main::errors()){

				$update_cats = array(
					"name"        => $postvar['editname'],
					"description" => $postvar['editdescription']
				);
				$dbh->update("cats", $update_cats, array("id", "=", $getvar['id']));
				main::errors("Category Edited!");
			
			}

		}

		if($getvar['del']){

			$articles_query = $dbh->select("articles", array("catid", "=", $getvar['del']), 0, "1", 1);
			if($dbh->num_rows($articles_query) != 0){
			
				main::errors("There are articles listed under this category which must be moved to a new category or deleted before you can delete this category.  Click on the category name to see the articles under it.");
			
			}else{
			
				$dbh->delete("cats", array("id", "=", $getvar['del']), "1");
				main::errors("Category Deleted!");
			
			}
		
		}
		
		if(!main::isint($getvar['id'])){

			$list_categories_array['BOXES'] = "";
			
			$cats_query = $dbh->select("cats");
			if($dbh->num_rows($cats_query)){

				while($cats_data = $dbh->fetch_array($cats_query)){

					$edit_category_box_array['NAME']        = $cats_data['name'];
					$edit_category_box_array['DESCRIPTION'] = $cats_data['description'];
					$edit_category_box_array['ID']          = $cats_data['id'];
					$list_categories_array['BOXES'] .= style::replaceVar("tpl/admin/kb/edit-category-box.tpl", $edit_category_box_array);
				
				}

			}

			return style::replaceVar("tpl/admin/kb/list-categories.tpl", $list_categories_array);
		
		}else{
			
			$category_data = $dbh->select("cats", array("id", "=", $getvar['id']), 0, "1");
			
			$edit_category_array['NAME'] = $category_data['name'];
			$edit_category_array['DESCRIPTION'] = $category_data['description'];
			
			return style::replaceVar("tpl/admin/kb/edit-category.tpl", $edit_category_array);
			
		}
	
	}
	
	private function add_article(){
        global $dbh, $postvar, $getvar, $instance;
		
		$cats_query = $dbh->select("cats");
		if(!$dbh->num_rows($cats_query)){

			echo "You need to add a category before you add an article.";
			return;
		
		}

		if($_POST['add']){

			check::empty_fields();
			if(!main::errors()){

				$articles_insert = array(
					"name"    => $postvar['name'],
					"content" => $postvar['description'],
					"catid"   => $postvar['catid']
				);
				$dbh->insert("articles", $articles_insert);
				main::errors("Article Added!");
			
			}

		}
		
		$cats_query     = $dbh->select("cats");
		while($cat = $dbh->fetch_array($cats_query)){

			$values[] = array($cat['name'], $cat['id']);
		
		}

		$add_article_array['DROPDOWN'] = main::dropDown("catid", $values);

		echo style::replaceVar("tpl/admin/kb/add-article.tpl", $add_article_array);
	
	}
	
	private function edit_article(){
        global $dbh, $postvar, $getvar, $instance;
		
		$cats_query = $dbh->select("cats");
		if(!$dbh->num_rows($cats_query)){

			echo "You need to add a category before you add an article.";
			return;
		
		}

		if($_POST['edit']){

			check::empty_fields();
			if(!main::errors()){

				$articles_update = array(
					"name"    => $postvar['editname'],
					"content" => $postvar['editdescription'],
					"catid"   => $postvar['catid']
				);
				
				$dbh->update("articles", $articles_update, array("id", "=", $getvar['id']));
				main::errors("Article Edited!");
			
			}

		}

		if($getvar['del']){

			$dbh->delete("articles", array("id", "=", $getvar['del']));
			main::errors("Article Deleted!");
		
		}
		
		if(main::isint($getvar['categoryid'])){
		
			$show_cat[] = array("catid", "=", $getvar['categoryid']);
		
		}
		
		if(!main::isint($getvar['id'])){
		
			$list_articles_array['BOXES'] = "";
			
			$articles_query = $dbh->select("articles", $show_cat, 0, 0, 1);
			if($dbh->num_rows($articles_query)){

				while($articles_data = $dbh->fetch_array($articles_query)){

					$edit_article_box_array['NAME'] = $articles_data['name'];
					$edit_article_box_array['ID']   = $articles_data['id'];
					$list_articles_array['BOXES'] .= style::replaceVar("tpl/admin/kb/edit-article-box.tpl", $edit_article_box_array);
				
				}

			}

			echo style::replaceVar("tpl/admin/kb/list-articles.tpl", $list_articles_array);
		
		}else{
					
			$cats_query = $dbh->select("cats");
			while($cat = $dbh->fetch_array($cats_query)){

				$values[] = array($cat['name'], $cat['id']);
			
			}
			
			$article_data = $dbh->select("articles", array("id", "=", $getvar['id']), 0, "1");
			
			$edit_article_array['NAME']        = $article_data['name'];
			$edit_article_array['DESCRIPTION'] = $article_data['content'];
			$edit_article_array['DROPDOWN']    = main::dropDown("catid", $values, $article_data['catid']);

			echo style::replaceVar("tpl/admin/kb/edit-article.tpl", $edit_article_array);
		
		}
	
	}

}

?>