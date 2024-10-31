<?php

if(!class_exists('WP_List_Table')){
   if( file_exists(ABSPATH . 'wp-admin/includes/class-wp-list-table.php') )
		require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
	else
		require_once('class-wp-list-table.php');
}

class TT_Example_List_Table extends WP_List_Table {
    
    // ???
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    function get_columns() {
        $columns = array(
                'name' => 'Name',
                'subject' => 'Subject'
                );
        return $columns;
    }
    
   // ???
    function column_default($item, $column_name){
        switch($column_name){
            case 'rating':
            case 'director':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
    function column_title($item){
        
        //Build row actions
        $actions = array(
            'edit'      => '<a href="#" onclick="if(section=prompt(\'Edit Section\', \''.$item['title'].'\'))
				window.location=\'?page=mybook-settings&action=edit_section&ID='.$item['ID'].'&post_title=\'+section">Edit</a>',
            'delete'    => '<a href="'.
				wp_nonce_url( '?page='.$_GET['page'].'&action=remove-section&section='.$item['ID'], 'remove-section-'.$item['ID']).'"
				onclick="return confirm(\''._('Are you sure to delete this section?').'\')">Delete</a>',
        );
        
        //Return the title contents
        return sprintf('%1$s %3$s',
            /*$1%s*/ $item['title'],
            /*$2%s*/ $item['ID'],
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
    
    /*
    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }
    */
    
    function prepare_items() {
        
        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 5;
        
        
        // HEADERS
        $columns = array(
           // 'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'title'     => 'Name',
            //'rating'    => 'Rating',
            //'director'  => 'Director'
        );
        $hidden = array();
        $sortable = array(
            'title'     => array('title',true),     //true means its already sorted
            //'rating'    => array('rating',false),
            //'director'  => array('director',false)
        );
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        // Actions
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
        global $wpdb;
        
		$post_name = get_bookname();
		$post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$post_name."'");
        $data = get_sections_for_table( $post_id );
                
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
           // $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        //usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}





/** ************************ REGISTER THE TEST PAGE ****************************
 *******************************************************************************
 * Now we just need to define an admin page. For this example, we'll add a top-level
 * menu item to the bottom of the admin menus.
 */
function tt_add_menu_items() {
    add_menu_page('Example Plugin List Table', 'List Table Example', 'activate_plugins', 'tt_list_test', 'tt_render_list_page');
} add_action('admin_menu', 'tt_add_menu_items');


/***************************** RENDER TEST PAGE ********************************
 *******************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
#function tt_render_list_page(){
    
    //Create an instance of our package class...
    $testListTable = new TT_Example_List_Table();
    
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();
    
    ?>
    <div class="wrap">
        
				<?php
					$external_plugin_name = 'MYBOOK';
					$external_plugin_url = 'http://araujo.cc/portfolio/mybook/';
				?>
				<div style="float:right;width:400px">
					<div style="float:right; margin-top:10px">
						 <iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode($external_plugin_url) ?>&amp;layout=box_count&amp;show_faces=false&amp;width=450&amp;action=like&amp;font=arial&amp;colorscheme=light&amp;height=21"
							scrolling="no" frameborder="0" style="overflow:hidden; width:90px; height:61px; margin:0 0 0 10px; float:right" allowTransparency="true"></iframe>
							<strong style="line-height:25px;">
								<?php echo __("Do you like <a href=\"{$external_plugin_url}\" target=\"_blank\">{$external_plugin_name}</a> Plugin? "); ?>
							</strong>
					</div>
				</div>
				
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Sections <a href="#" onclick="if($section=prompt('New Section'))
			window.location='?page=mybook-settings&action=new_section&parent=<?=$post_id?>&post_title='+$section" class="add-new-h2">Add New</a></h2>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get" style="width:500px">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $testListTable->display() ?>
        </form>
        
    </div>
    <?php
#}

