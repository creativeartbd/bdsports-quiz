<?php

/**
 * The default template for displaying content
 *
 * Used for single posts.
 *
 * @package Hestia
 * @since Hestia 1.0
 */

$default        = hestia_get_blog_layout_default();
$sidebar_layout = apply_filters('hestia_sidebar_layout', get_theme_mod('hestia_blog_sidebar_layout', $default));
$wrap_class     = apply_filters('hestia_filter_single_post_content_classes', 'col-md-8 single-post-container');
?>
<article id="post-<?php the_ID(); ?>" class="section section-text">
    <div class="row">
        <?php
        if (($sidebar_layout === 'sidebar-left') && !is_singular('elementor_library')) {
            get_sidebar();
        }
        ?>
        <div class="<?php echo esc_attr($wrap_class); ?>" data-layout="<?php echo esc_attr($sidebar_layout); ?>">

            <?php
            do_action('hestia_before_single_post_wrap');
            $curauth = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));
            $user_id = $curauth->data->ID;
            ?>
            <h2>Profile</h2>
            <table class="table">
                <tr>
                    <td>First Name</td>
                    <td><?php echo get_user_meta($user_id, 'first_name')[0]; ?></td>
                </tr>
                <tr>
                    <td>Last Name</td>
                    <td><?php echo get_user_meta($user_id, 'last_name')[0]; ?></td>
                </tr>
                <tr>
                    <td>Profile BIO</td>
                    <td><?php echo get_user_meta($user_id, 'description')[0]; ?></td>
                </tr>
            </table>


            <?php
            echo '</div>';
            if (($sidebar_layout === 'sidebar-right') && !is_singular('elementor_library')) {
                get_sidebar();
            }
            ?>
        </div>
</article>