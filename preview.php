<?php $post = get_post( $post_ID = $_GET['mybook-chapter-preview'] ) ?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>

<body>
	<h2><?php echo $post->post_title ?></h2>
	<?php echo $post->post_content ?>
</body>
</html>
