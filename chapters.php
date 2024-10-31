<?php


global $wpdb, $post_id, $book;

add_thickbox();

$post_name = get_bookname();
$post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$post_name."'");
$book = get_post( $post_id );

//if( $post_id ) {
	# add_meta_box( 'chapters', '<a href="post-new.php?post_type='.MYBOOK_POSTTYPE.'&post_parent='.$post_id.'" style="float:right">add chapter</a> &nbsp;', 'mybook_sections', 'mybook-chapters', 'normal', 'high', array( 'id' => get_the_title($post_id) ) );
	add_meta_box( 'chapters', '<a href="post-new.php?post_type='.MYBOOK_POSTTYPE.'" style="float:right">add chapter</a> &nbsp;', 'mybook_sections', 'mybook-chapters', 'normal', 'high', array( 'id' => get_the_title($post_id) ) );
	add_meta_box( 'new_chapter', __('New Section'), 'new_chapter', 'mybook-chapters', 'side', 'high', array( 'id' => get_the_title($post_id) ) );
	
	foreach( get_sections($post_id) as $section ) {
		#&post_parent='.$post_id.'
		add_meta_box(
			'chapters_'.$section->term_id,
			'<span style="float:right">&nbsp;<span style="color:#aaa" class="remove"><a style="color:red"
				href="'.wp_nonce_url( '?page='.$_GET['page'].'&action=remove-section&section='.$section->term_id, 'remove-section-'.$section->term_id).'"
				onclick="return confirm(\''._('Are you sure to delete this section?').'\')">'._('remove').'</a> | </span>
				<a href="post-new.php?post_type='.MYBOOK_POSTTYPE.'&section='.$section->term_id.'"
					>add chapter</a></span>'.$section->name,
				'mybook_sections',
				'mybook-chapters',
				'normal', 'high', array( 'id' => $section->term_id ) );
	}
//}

# -----------------

function new_chapter() {
	global $post_id;
	echo '
		<form action="" method="post" style="padding:8px">
			<div class="input-text-wrap">
				<input type="text" name="post_title" id="title" tabindex="1" autocomplete="off" style="width:100%">
			</div>
			<input type="hidden" name="action" value="new_section">
			<input type="hidden" name="parent" value="'.$post_id.'">
			<input type="submit" name="publish" id="publishx" accesskey="p" tabindex="5" class="button-primary" value="Create">
			
			<br class="clear">
		</form>
	';
}

function mybook_sections( $section_id, $metabox) {
	
	global $wpdb, $post_id;
	
	if( $section_id = (int)$metabox['args']['id']) {
		
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
		/*$posts = (array) get_posts( array(
			'post_type' => MYBOOK_POSTTYPE,
			'numberposts' => 1000,
			'order'=> 'ASC',
			'orderby' => 'menu_order'
			) );*/
			
	} else {
		# NO SECTION
		$sql="
			SELECT * FROM $wpdb->posts p
			LEFT JOIN $wpdb->term_relationships rel ON object_id=ID
			LEFT JOIN $wpdb->term_taxonomy terms ON rel.term_taxonomy_id=terms.term_taxonomy_id
			WHERE
				post_status='publish'
				AND post_type='".MYBOOK_POSTTYPE."'
				-- AND post_parent='$post_id'
				-- AND !term_id
				AND (term_id IS NULL || term_id=2)
			ORDER BY menu_order
		";
		$posts = $wpdb->get_results($sql);
		
	}
	
	#echo '<div style="padding:8px; color:#aaa; font-style:italic">Nenhum texto aqui.</div>';
	
	if($posts)		
		foreach($posts as $key=>$post) {
			echo '<p id="post-'.$post->ID.'" class="chapter'.($key+1==sizeof($posts)?' chapter_last':'').'"><span style="color:#aaa">&bull;</span>&nbsp; '.$post->post_title.'
					<span class="actions" style="font-size:13px; color:#aaa">
					<a href="'.wp_nonce_url( '?page='.$_GET['page'].'&action=trash&book='.$post->ID, 'trash-'.$post->ID).'" style="float:right; color:red" onclick="return confirm(\''._('Are you sure to delete this chapter?').'\')">trash</a>
					- <a href="post.php?post_type='.MYBOOK_POSTTYPE.'&post='.$post->ID.'&post_parent='.$post_id.'&section='.$section_id.'&action=edit">edit</a>
						| <a href="?page='.MYBOOK_POSTTYPE.'&mybook-chapter-preview='.$post->ID.'" class="thickbox">view</a></span></p>';
		}
	#else
	#	echo '<div style="padding:8px; color:#aaa; font-style:italic">Nenhum texto aqui.</div>';
}

echo '<style style="text/css">
		div.postbox div.inside {margin:0; padding:6px 2px;}
		div.postbox div.inside p.chapter,
		div.postbox div.inside p.chapter_last
		 {xbackground:url('.plugins_url().'/'.MYBOOK_POSTTYPE.'/grippy.png) no-repeat 8px 10px;
			cursor:move; font-size:14px; display:block; margin:0 3px; padding:6px 8px; border-bottom:1px solid #e0e0e0; border-radius:2px;}
		div.postbox div.inside p.chapter_last {border:0}
		div.postbox div.inside p.chapter .actions {display:none;}
		.postbox h3 {font-weight:bold;}
	  </style>';

add_meta_box( 'mybook_author', '<a href="profile.php" style="float:right">edit</a>'.__('Author'), 'mybook_author', 'mybook-chapters', 'side', 'high' );
function mybook_author() {
	global $user_ID, $book;
	$data = get_userdata( $user_ID );
	echo $book->post_author;
	//print_r($data);
	?>
	<div style="float:left; margin:8px">
		<?= get_avatar( $data->user_email, 80 ) ?>
	</div>
	
	<div style="padding-top:8px">
		<strong style="font-size:14px"><?= $data->display_name ?></strong> <br />
		<p><?= $data->description? $data->description : __('<i>No description.</i>') ?></p>
	</div>
	
	<div style="clear:left">&nbsp;</div>
<? }


?>

<?php if ( !empty($_POST['submit'] ) ) : ?>
	<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
<?php endif; ?>
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

	<div id="icon-edit" class="icon32 icon32-posts-post" style="background:url(<?php echo plugins_url( MYBOOK_POSTTYPE.'/screen-icon.png' ) ?>) no-repeat"><br></div>
	<h2>MyBook<?#= $book->post_title ?> <a href="post-new.php?post_type=<?= MYBOOK_POSTTYPE ?>" class="button add-new-h2">New Chapter</a></h2>
	
	<h3><?php _e('Table of Contents') ?></h3>
	
	<div style="width:40%; float:right;">
		<? do_meta_boxes('mybook-chapters', 'side', null); ?>
	</div>
	
	<div id="Chapters" style="width:60%;">
		<? do_meta_boxes('mybook-chapters', 'normal', null); ?>
	</div>
</div>

<? wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
#wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>

<script type="text/javascript" >
jQuery(document).ready(function($) {
	//no_chapters=function(){
		$('#normal-sortables .postbox').each( function(e){
			$(this).mouseover( function() {
				$(this).find('.remove').css('display', '');
			} ).mouseout( function() {
				$(this).find('.remove').css('display', 'none');
			} );
		} );
	//}

	$('#Chapters .remove').each( function(index){
		$(this).css('display', 'none');
	} );

	$('#Chapters .inside').sortable({
		connectWith: '#Chapters .inside',
		cursor: 'move',
		placeholder: 'placeholder',
		forcePlaceholderSize: true,
		opacity: 0.4,
		stop: function(event, ui){
			$(ui.item).find('li').click();
			var sortorder='';
			var section;
			$('.chapter').each(function(){
				section = this.parentNode.parentNode.id.replace('chapters_', '');
				sortorder += section+':'+this.id.replace('post-', '')+',';
			});
			$.post(ajaxurl, {
				action: 'chapter_order',
				order: sortorder
			}, function(response) {
				/*alert('Got this from the server: ' + response);*/
			});
		}
	})
	.disableSelection();
	
	/*$('.hndle').sortable({
		connectWith: '.postbox',
		cursor: 'move',
		placeholder: 'placeholder',
		forcePlaceholderSize: true,
		opacity: 0.4,
		stop: function(event, ui){
			$(ui.item).find('li').click();
			var sortorder='';
			$('.hndle').each(function(){
				sortorder+=this.id.replace('post-', '')+',';
			});
			$.post(ajaxurl, {
				action: 'chapter_order',
				order: sortorder
			}, function(response) {
				/*alert('Got this from the server: ' + response);
			});
		}
	})
	.disableSelection();*/
	
	postboxes.add_postbox_toggles('postbox');
	
	postboxes.save_order = function (page) {
		var b,d=$(".columns-prefs input:checked").val()||0;
		b={
			action:"meta-box-order",
			_ajax_nonce:$("#meta-box-order-nonce").val(),
			page_columns:d,
			page:'mybook-chapters'
		};
		$(".meta-box-sortables").each(function(){
			b["order["+this.id.split("-")[0]+"]"]=$(this).sortable("toArray").join(",")
		});
		
		$.post(ajaxurl, b/*, function(response) { alert(response); }*/);
	};
	
	$('.inside p').mouseover(function() {
		$(this).css({backgroundColor:'#e9e9e9'});
		$(this).find('.actions').css({display:'inline'});
	}).mouseout(function() {
		$(this).css({background:''});
		$(this).find('.actions').css({display:'none'});
	});
});
</script>
