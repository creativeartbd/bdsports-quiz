<?php

/**
 * The template for displaying all single posts and attachments.
 *
 * @package Hestia
 * @since Hestia 1.0
 */

get_header();

do_action('hestia_before_single_post_wrapper');
?>

<div class="<?php echo hestia_layout(); ?>">
    <div class="blog-post blog-post-wrapper">
        <div class="container">
            <?php get_template_part('template-parts/content', 'author'); ?>
        </div>
    </div>
</div>

<?php
if (!is_singular('elementor_library')) {
    //do_action( 'hestia_blog_related_posts' );
}
?>
<div class="footer-wrapper">
    <?php get_footer(); ?>