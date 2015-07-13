<?PHP
//////////////////////////////
// The Hosting Tool Reworked
// Support Area - Knowledgebase
// By Reworked Scripts (Original Script by http://thehostingtool.com)
// Released under the GNU-GPL
//////////////////////////////

class page{

    public function content(){
        global $dbh, $postvar, $getvar, $instance;
        
        $cats_query = $dbh->select("cats");
        if(!$dbh->num_rows($cats_query)){

            echo "There are no knowledge base categories or articles!";
        
        }else{

            if($getvar['cat']){

                $cats_data = $dbh->select("cats", array("id", "=", $getvar['cat']));
                if(!$cats_data['id']){

                    echo "That category doesn't exist!";
                
                }else{

                    echo main::sub('<img src="<ICONDIR>arrow_rotate_clockwise.png"><a href="?page=kb">Return To Category Selection</a>', '');
                    $articles_query = $dbh->select("articles", array("catid", "=", $getvar['cat']), 0, 0, 1);
                    if(!$dbh->num_rows($articles_query)){

                        echo "There are no articles in this category!";
                    
                    }else{

                        while($articles_data = $dbh->fetch_array($articles_query)){

                            $article_box_array['NAME'] = $articles_data['name'];
                            $article_box_array['ID']   = $articles_data['id'];
                            echo style::replaceVar("tpl/kb/article-box.tpl", $article_box_array);
                        
                        }

                    }

                }

                return;
            
            }

            if($getvar['art']){

                $articles_data = $dbh->select("articles", array("id", "=", $getvar['art']));
                if(!$articles_data['id']){

                    echo "That article doesn't exist!";
                
                }else{

                    $view_article_array['NAME']    = $articles_data['name'];
                    $view_article_array['CONTENT'] = $articles_data['content'];
                    $view_article_array['CATID']   = $articles_data['catid'];
                    echo style::replaceVar("tpl/kb/view-article.tpl", $view_article_array);
                
                }

                return;
                
            }

            //Show this by default.
            while($cats_data = $dbh->fetch_array($cats_query)){

                $category_box_array['NAME']        = $cats_data['name'];
                $category_box_array['DESCRIPTION'] = $cats_data['description'];
                $category_box_array['ID']          = $cats_data['id'];
                echo style::replaceVar("tpl/kb/category-box.tpl", $category_box_array);
            
            }

        }

    }

}

?>