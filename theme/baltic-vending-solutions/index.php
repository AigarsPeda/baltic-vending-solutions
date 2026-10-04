<?php
get_header();
while (have_posts()) { the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('page-content'); ?>><?php the_content(); ?></article>
<?php }
get_footer();
