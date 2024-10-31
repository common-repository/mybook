
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
	

	<div id="icon-tools" class="icon32 icon32-posts-post"><br></div>
	<h2>Export Book - MyBook</h2>
	
	<h3>Choose what format to export</h3>
	
	<form action="" method="get" id="export-book">
		<?php wp_nonce_field( 'export-book' ) ?>
		<input type="hidden" name="page" value="<?php echo MYBOOK_POSTTYPE ?>-export">
		<p><label><input type="radio" name="export" value="html" checked> HTML</label></p>
		<p><label><input type="radio" name="export" value="text"> Text</label></p>
		<!--p><label><input type="radio" name="export" value="pdf"> PDF</label></p>
		<p><label><input type="radio" name="export" value="xml"> XML</label></p-->

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-secondary" value="Download Book"></p>
	</form>

</div>

<script type="text/javascript">
<? if(defined('__download')) echo "window.location='".__download."'"; ?>
</script>
