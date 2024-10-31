<?php
/*
Plugin Name: MyBook
Plugin URI: http://araujo.cc/portfolio/mybook/
Description: Create chapters, sections, edit your own book.
Version: 0.9.5
Author: Arthur Araújo
Author URI: http://araujo.cc
*/

# PLUGIN SLUG
define('MYBOOK_POSTTYPE', 'mybook');

# REGISTER PLUGIN TAXONOMY
function build_taxonomies() {
	register_taxonomy( MYBOOK_POSTTYPE , array(MYBOOK_POSTTYPE, 'post'),
		array("hierarchical" => true, "label" => "Livross", "singular_label" => "Livross", "rewrite" => true)
	);
	# ACTIONS
	include 'actions.php';
}
add_action( 'init', 'build_taxonomies', 0 );


# ADMIN MENU
add_action( 'admin_menu', 'mybook_menu' );
function mybook_menu() {
	global $menu, $wpdb;
	
	$icon_url = "";
	
	add_menu_page( 'MyBook', 'MyBook', 10, MYBOOK_POSTTYPE, 'chapters_page', plugins_url( MYBOOK_POSTTYPE.'/menu-icon.png' ), 3 );
	
	$posts = $wpdb->get_results("
		SELECT * FROM $wpdb->posts
		WHERE
			post_type='".MYBOOK_POSTTYPE."'
			AND post_status='publish'
			AND post_parent='0'
		ORDER BY menu_order
	");

	#(array) get_posts( array( 'post_type' => MYBOOK_POSTTYPE, 'numberposts' => 1000, 'order'=> 'ASC', 'parent' => '0', 'orderby' => 'menu_order' ) );
	
	//foreach( $posts as $post ) {
		//add_submenu_page( MYBOOK_POSTTYPE, $post->post_title, $post->post_title,
			//'manage_options', MYBOOK_POSTTYPE.'-'.$post->post_name, 'chapters_page' ); 
		#add_submenu_page( MYBOOK_POSTTYPE, 'titulo', $post->post_title, 10, 'menu_slug', '' );
	//}

	add_submenu_page( MYBOOK_POSTTYPE, 'Setting Book', _('Sections'),
			'manage_options', MYBOOK_POSTTYPE.'-settings', 'sections_page' );

	add_submenu_page( MYBOOK_POSTTYPE, 'Export Book', _('Export'),
			'manage_options', MYBOOK_POSTTYPE.'-export', 'export_page' );

	function chapters_page() {
		include 'chapters.php';
	}

	function sections_page() {
		include 'sections.php';
	}

	function export_page() {
		include dirname(__FILE__).'/export.php';
	}

	#add_submenu_page( $page_slug, 'titulo', 'History', 10, 'menu_slug', '' );
		
}

# 2CHANGE
#wp_enqueue_script('common');
#wp_enqueue_script('wp-lists');
wp_enqueue_script('postbox');
function mybook_page() {
	include 'books.php';
	return 1;
}

/*if( $_GET['page']==MYBOOK_POSTTYPE ) {
	add_submenu_page( MYBOOK_POSTTYPE, 'titulo', 'History', 10, 'menu_slug', '' );
}*/

function get_bookname() {
	return str_replace( MYBOOK_POSTTYPE.'-', '', $_GET['page'] );
}

add_action('init', 'mybook_register');
function mybook_register() {
    /*$labels = array(
        'name' => _('MyBook'),
        'singular_name' => _x('Loja Item', 'post type singular name'),
        'add_new' => _x('Novo item', 'loja item'),
        'add_new_item' => __('Adicionar Novo Item'),
        'edit_item' => __('Editar Item'),
        'new_item' => __('Novo Item'),
        'view_item' => __('Ver Item'),
        'search_items' => __('Buscar Loja'),
        'not_found' =>  __('Nothing found'),
        'not_found_in_trash' => __('Nothing found in Trash'),
        'parent_item_colon' => ''
    );*/
    $args = array(
        'labels' => array('name'=>'MyBook'),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'hierarchical' => true,
        'menu_position' => null,
        'taxonomies' => array('categorias', MYBOOK_POSTTYPE),
        'supports' => array('title','editor','revisions')
      );
    register_post_type( MYBOOK_POSTTYPE , $args );
        
}

# REMOVE POST_TYPE MENU
function remove_menus () {
global $menu;
	$restricted = array( __('MyBook') );
	end ($menu);
	while (prev($menu)){
		$value = explode(' ',$menu[key($menu)][0]);
		if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
	}
}
add_action('admin_menu', 'remove_menus');

echo '<style style="text/css">
		.meta-box-sortables {
			margin:0 8px;
		}
		.postbox h3 {
			font-size: 15px;
			font-weight: bold;
			padding: 7px 10px;
			margin: 0;
			line-height: 1;
		}
		.postbox h4 {
			margin-top:0;
		}
	  </style>';


# AJAX SORT CHAPTERS
add_action( 'wp_ajax_chapter_order', 'chapter_order' );
function chapter_order() {
	global $wpdb;
	$data = (array) explode(',',$_POST['order']);
	foreach($data as $post_info) {
		if($post_info) {
			$i++;
			$post_id = explode( ':', $post_info );
			#echo $post_info.'-';
			$wpdb->query("UPDATE $wpdb->posts SET menu_order='$i' WHERE ID='{$post_id[1]}'");
			wp_set_object_terms( (int)$post_id[1], array( (int)$post_id[0] ), MYBOOK_POSTTYPE );
		}
	}
}


# ADD/EDIT CHAPTER
function get_sections( $book_id ) {
	global $wpdb;
	
	$obj = (wp_get_object_terms( $book_id, MYBOOK_POSTTYPE ));
	
	$parent_cat = $obj[0]->term_id;
	
	return (array) $wpdb->get_results("
		SELECT terms.name, terms.term_id FROM $wpdb->term_taxonomy tax
		INNER JOIN $wpdb->terms terms ON tax.term_id=terms.term_id
		WHERE taxonomy='".MYBOOK_POSTTYPE."'
		");
}

function get_sections_for_table( $book_id ) {
	global $wpdb;
	
	$obj = (wp_get_object_terms( $book_id, MYBOOK_POSTTYPE ));
	
	$parent_cat = $obj[0]->term_id;
	
	return (array) $wpdb->get_results("
		SELECT terms.name AS title, terms.term_id AS ID FROM $wpdb->term_taxonomy tax
		INNER JOIN $wpdb->terms terms ON tax.term_id=terms.term_id
		WHERE taxonomy='".MYBOOK_POSTTYPE."'
		", ARRAY_A);
}

add_action( 'save_post', 'mybook_save_chapter', 10);
function mybook_save_chapter( $post_id ) {
	
	$pid = wp_is_post_revision( $post_id )?wp_is_post_revision( $post_id ):$post_id;
	
	#echo $pid.'<br />';
	#echo $_POST['section'];
	
	if( ( get_post( $pid )->post_type == MYBOOK_POSTTYPE ) && $_POST && is_admin() ) {
		global $wpdb, $post_parent;
		
		#$wpdb->query("UPDATE $wpdb->posts SET post_parent='{$_POST['book_id']}' WHERE ID='$pid'");
		
		$wpdb->query("DELETE FROM $wpdb->term_relationships WHERE term_id='$pid'");
		
		wp_set_object_terms( $pid, array( (int)$_POST['section'] ), MYBOOK_POSTTYPE );
		
		#echo 1;
		
		#exit;
		
		add_filter( 'redirect_post_location', 'mybook_redirect' );
	}
	
	#exit;
	
}

function mybook_redirect( $location, $post_id=null ) {
	if( eregi('message\=([61])', $location) ) {
		wp_redirect( 'admin.php?page='.MYBOOK_POSTTYPE );
		#wp_redirect( 'admin.php?page='.MYBOOK_POSTTYPE.'-'.get_post($_POST['book_id'])->post_name );
		exit;
	} else
		return $location;
}

# ADD CHAPTER PAGE CONFIG

if( $_GET['post_type']==MYBOOK_POSTTYPE ) :
	
	add_action('admin_menu', 'dbt_remove_boxes'); 
	function dbt_remove_boxes(){
	   # remove_meta_box('postexcerpt', 'post', 'normal');
		remove_meta_box('mybookdiv', MYBOOK_POSTTYPE, 'normal');
		remove_meta_box('tagsdiv-post_tag', 'post', 'normal');
		#remove_meta_box('slugdiv', 'post', 'normal');
		#remove_meta_box('authordiv', 'post', 'normal');
		#remove_meta_box('commentstatusdiv', 'post', 'normal');
	}		
	
	add_action( 'do_meta_boxes', 'mybook_post_configs' );
	function mybook_post_configs() {
		add_meta_box( 'post_parent', __('Section'), 'mybook_chpater_metabox', MYBOOK_POSTTYPE, 'side', 'high', array( 'id' => $_GET['post_parent'] ) );
	}
	
	function mybook_chpater_metabox( $book_id ) {
		
		echo '<input type="hidden" name="book_id" value="'.$_GET['post_parent'].'" />';
		
		if( $sections = get_sections($_GET['post_parent']) ) {
			echo '<select name="section">';
			echo '<option value=""></option>';
			foreach($sections as $section) {
				echo '<option value="'.$section->term_id.'" '.($section->term_id==$_GET['section']?' selected':'').'>'.
						get_term($section->term_id, MYBOOK_POSTTYPE)->name.'</option>';
				#add_meta_box( 'chapters_'.$section->term_id, get_cat_name($section->term_id), 'chapters', 'chapters', 'normal', 'high', array( 'id' => $section->term_id ) );
			}
			echo '</select>';
		}
	}
	

endif;

function mybook_message($message = null, $class = 'updated'){
	if($message === null)
		return;
	echo '<div id="message0" class="'.$class.' fade">';
	echo '<p><strong>'.$message.'</strong></p>';
	echo '</div>';
}

# EXPORT
function export_book() {
	
	global $wpdb;
	
	$book   = '';
	$index  = '';
	$sindex = '';
	
	foreach( get_sections($post_id) as $section ) {
		
		#print_r($section);
		
		if($section->name) {
			$index .= "\r\n<dt><strong>$section->name</strong></dt>\r\n";
			$book .= "<h1>$section->name</h1>";
		}
		
		if( $section_id = $section->term_id ) {
			
			$posts = $wpdb->get_results("
				SELECT * FROM $wpdb->posts
				INNER JOIN $wpdb->term_relationships ON object_id=ID
				INNER JOIN $wpdb->term_taxonomy ON $wpdb->term_relationships.term_taxonomy_id=$wpdb->term_taxonomy.term_taxonomy_id
				WHERE
					post_status='publish'
					-- AND post_parent='$post_id'
					AND term_id='$section_id'
				ORDER BY menu_order
			");
				
		} else {
			# NO SECTION
			$sql="
				SELECT p.* FROM $wpdb->posts p
				LEFT JOIN $wpdb->term_relationships rel ON object_id=ID
				LEFT JOIN $wpdb->term_taxonomy terms ON rel.term_taxonomy_id=terms.term_taxonomy_id
				WHERE
					post_status='publish'
					AND post_type='".MYBOOK_POSTTYPE."'
					-- AND post_parent='$post_id'
					-- AND !term_id
					AND term_id IS NULL
				ORDER BY menu_order
			";
			$posts = $wpdb->get_results($sql);
			
		}
		
		#echo '<div style="padding:8px; color:#aaa; font-style:italic">Nenhum texto aqui.</div>';
		
		if($posts)	{
			$index .= '<dd><ul style="list-style:none;">';
			foreach($posts as $key=>$post) {
				$i++;
				$index .= "	<li>$i. <a href='#$post->post_name'>$post->post_title</a></li>\r\n";
				$book .= "<h2>$i. <a name='$post->post_name'>$post->post_title</a></h2>\r\n".$post->post_content;
			}
			$index .= '</ul></dd>';
		}
	}
	
	$index = "<h1>"._('Table of Contents')."</h1>\r\n\r\n <dd>$index</dd>\r\n\r\n";
	
	if( $book && $_GET['export']=='html') {
		$book = '<html><head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8"></head><body>'.$index.' '.$book.'</body></html>';
		$book_file = fopen( ABSPATH.'/wp-content/book.html', 'w+' );
		fwrite( $book_file, $book );
		fclose( $book_file );
		
		define( '__download', get_bloginfo('wpurl').'/wp-content/book.html' );
		
	} elseif( $book && $_GET['export']=='text') {
		$index = strip_tags( $index );
		$book = str_replace( '</h1>', "\r\n\r\n", $book );
		$book = str_replace( '<h2>', "\r\n", $book );
		$book = str_replace( '</h2>', "\r\n", $book );
		$book = strip_tags( $book );
		$book_file = fopen( ABSPATH.'/wp-content/book.txt', 'w+' );
		fwrite( $book_file, $index.$book );
		fclose( $book_file );
		
		define( '__download', get_bloginfo('wpurl').'/wp-content/book.txt' );
		
	}  else 
		mybook_message('Failed to export book.');

}

?>

