<?php

add_meta_box( 'dashboard_quick_press', __('Create new book'), 'box_new_book', 'mybook', 'normal', 'high', array( 'id' => get_the_title($post->ID) ) );
add_meta_box( 'mybook_list', __('My books'), 'box_list_books', 'mybook', 'normal', 'high', array( 'id' => get_the_title($post->ID) ) );
//add_meta_box( 'post_parent', __('Section'), 'metabox_post_parent', 'post', 'side', 'high', array( 'id' => $_GET['post_parent'] ) );

function box_list_books() {

	$post_id = $metabox['args']['id'];
	$posts = (array) get_posts( array( 'post_type' => MYBOOK_POSTTYPE, 'numberposts' => 1000, 'order'=> 'ASC', 'parent' => '0', 'orderby' => 'menu_order' ) );
	
	#function custom_menu_page(){ echo 123; }
	#add_menu_page('titulo Meu WP', 'Meu WP',10, 'meu-wp/meu-wp-config.php');
	
	echo '<ul>';
	foreach( $posts as $post ) {
		echo '<li>&bullet; '.get_the_title($post->ID).' - <a>edit</a>
				| <a href="'.wp_nonce_url( "index.php?book=$post->ID&action=trash", "trash-$post->ID" ).'">trash</a></li>';
	}
	echo '</ul>';
}

function box_new_book() {
	
	echo '
		<form action="" method="post">
			<h4 id="quick-post-title"><label for="title">Title</label></h4>
			<div class="input-text-wrap">
				<input type="text" name="post_title" id="title" tabindex="1" autocomplete="off" value="">
			</div>
			<h4 id="content-label"><label for="description">Description</label></h4>
			<div class="textarea-wrap">
				<textarea name="content" id="description" class="mceEditor" rows="3" cols="15" tabindex="2"></textarea>
			</div>
			<h4 id="content-label"><label for="license">License</label></h4>
			<select name="license">
				 <option value="CC BY">Creative Commons Attribution</option>
				 <option value="CC BY-ND">Creative Commons Attribution-NoDerivs</option>
				 <option value="CC BY-NC">Creative Commons Attribution-NonCommercial</option>
				 <option value="CC BY-NC-ND">Creative Commons Attribution-NonCommercial-NoDerivs</option>
				 <option value="CC BY-NC-SA">Creative Commons Attribution-NonCommercial-ShareAlike</option>
				 <option value="CC BY-SA">Creative Commons Attribution-ShareAlike</option>
				 <option value="CC0">Creative Commons No Rights Reserved</option>
				 <option value="GPL">GNU General Public License</option>
				 <option value="MIT">MIT License</option>
				 <option value="PD">Public Domain</option>
			</select>
			<div id="media-buttons">
				<label><input type="checkbox" name="hidden">initially hide from others</label>
			</div>
			<p class="submit">
				<input type="hidden" name="action" value="new_book">
				<input type="submit" name="publish" id="publishx" accesskey="p" tabindex="5" class="button-primary" value="Create">
				<span id="publishing-action">
					<input type="reset" value="Reset" class="button">
					<img class="waiting" src="/buscadajuda/wp-admin/images/wpspin_light.gif" alt="">
				</span>
				<br class="clear">
			</p>
		</form>
	';
}

 ?>

<script type="text/javascript" >
jQuery(document).ready( function($) {
	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	// postboxes
	<?php
	global $wp_version;
	if(version_compare($wp_version,"2.7-alpha", "<")){
		echo "add_postbox_toggles('mybook');"; //For WP2.6 and below
	}
	else{
		echo "postboxes.add_postbox_toggles('mybook');"; //For WP2.7 and above
	}
	?>

});
</script>

<?php if ( !empty($_POST['submit'] ) ) : ?>
	<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
		
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php _e('My Books'); ?></h2>

	<div style="width:40%; float:right;">
		<? do_meta_boxes('mybook-author', 'side', null); ?>
	</div>
	
	<div style="width:60%; float:left;">
		<? do_meta_boxes('mybook', 'normal', null); ?>
	</div>

</div>
