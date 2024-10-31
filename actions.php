<?php

require_once ABSPATH.'/wp-includes/pluggable.php';

# EXPORT BOOK
	if( $_GET['export'] && wp_verify_nonce( $_GET['_wpnonce'], 'export-book' ) ) {
		export_book();
	}
	
# DELETE SECTION
	if( $_GET['section'] && $_GET['action']=='remove-section' && wp_verify_nonce( $_GET['_wpnonce'], 'remove-section-'.$_GET['section'] ) ) {
		wp_delete_term( $_GET['section'], MYBOOK_POSTTYPE );
		mybook_message('Section removed.');
	}
	
# DELETE CHAPTER
	if( $_GET['book'] && $_GET['action']=='trash' && wp_verify_nonce( $_GET['_wpnonce'], 'trash-'.$_GET['book'] ) ) {
		wp_delete_post($_GET['book']);
		mybook_message('Chapter moved to the Trash.');

# NEW BOOK
	} elseif( $_POST['action']=='new_book' ) {
		
		require_once ABSPATH.'/wp-admin/includes/taxonomy.php';
		$cat_id = wp_insert_category(array(
			'cat_name' => $_POST['post_title'],
			'taxonomy' => MYBOOK_POSTTYPE,
		));
		
		$post_id = wp_insert_post(array(
			'post_title'  => $_POST['post_title'],
			'post_status' => ( $_POST['hidden']? 'private' : 'publish' ),
			'post_excerpt'=> $_POST['content'],
			//'post_category'=> array( $cat_id ),
			'post_type'   => MYBOOK_POSTTYPE,
			'post_author' => $user_ID,
		));
		
		wp_set_object_terms( $post_id, array( $cat_id ), MYBOOK_POSTTYPE );
		
		update_post_meta( $post_id, 'license', $_POST['license'] );
		
		mybook_message('Chapter added.');
		
		#wp_redirect('index.php');
		#exit();
	}

# NEW SECTION
	if( $_REQUEST['action']=='new_section' ) {
		
		//if( $cat_id = reset(wp_get_object_terms( $_POST['parent'], MYBOOK_POSTTYPE ))->term_id ) {			
			#require_once ABSPATH.'/wp-includes/taxonomy.php';
			wp_insert_term( $_REQUEST['post_title'], MYBOOK_POSTTYPE );
		//}
		
		mybook_message('Section added.');
	}

# EDIT SECTION
	if( $_REQUEST['action']=='edit_section' ) {
		
		//if( $cat_id = reset(wp_get_object_terms( $_POST['parent'], MYBOOK_POSTTYPE ))->term_id ) {			
			#require_once ABSPATH.'/wp-includes/taxonomy.php';
			wp_update_term( $_REQUEST['ID'], MYBOOK_POSTTYPE, array('name'=>$_REQUEST['post_title'], 'slug'=>$_REQUEST['post_title']) );
		//}
		
		mybook_message('Section updated.');
	}


# PREVIEW
	if( !empty($_GET['mybook-chapter-preview']) ) {
		include 'preview.php';
		exit;
	}
?>
