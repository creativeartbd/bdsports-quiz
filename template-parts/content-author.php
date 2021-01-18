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

            global $wpdb;
            $table = $wpdb->base_prefix . 'mlw_results';
            $quiz_results = $wpdb->get_results("SELECT * FROM {$table} WHERE user = $user_id AND deleted = 0 ORDER BY result_id DESC ", OBJECT);

            $quiz_played = $wpdb->get_var("SELECT COUNT(DISTINCT user)  FROM {$table}");

            $first_name = get_user_meta($user_id, 'first_name')[0];
            $last_name = get_user_meta($user_id, 'last_name')[0];
            $description = get_user_meta($user_id, 'description')[0];
            ?>

            <h3><?php echo $first_name . ' ' . $last_name; ?> Profile</h3>
            <table class="table table-bordered">
                <tr>
                    <td><b>First Name</b></td>
                    <td><?php echo $first_name;; ?></td>
                </tr>
                <tr>
                    <td><b>Last Name</b></td>
                    <td><?php echo $last_name; ?></td>
                </tr>
                <tr>
                    <td><b>Profile BIO</b></td>
                    <td><?php echo get_user_meta($user_id, 'description')[0]; ?></td>
                </tr>
            </table>

            <?php
            if ($quiz_results) {
                echo "<h3>All Quiz Score</h3>";
                echo "<table class='table table-bordered'>";
                echo "<tr>";
                echo "<td><b>Sl.</b></td>";
                echo "<td><b>Quiz Name</b></td>";
                echo "<td><b>Score</b></td>";
                echo "<td><b>Time Taken</b></td>";
                echo "<td><b>Played On</b></td>";
                echo "</tr>";

                $count = 0;
                foreach ($quiz_results as $result) {
                    $count++;
                    $mlw_qmn_results_array = unserialize($result->quiz_results);

                    // Calculate hours
                    $mlw_complete_hours = floor($mlw_qmn_results_array[0] / 3600);
                    if ($mlw_complete_hours > 0) {
                        $actual_hour = str_pad($mlw_complete_hours, 2, '0', STR_PAD_LEFT) . 'Hours';
                    } else {
                        $actual_hour = 0;
                    }

                    // Calculate minutes
                    $mlw_complete_minutes = floor(($mlw_qmn_results_array[0] % 3600) / 60);
                    if ($mlw_complete_minutes > 0) {
                        $actual_minutes = str_pad($mlw_complete_minutes, 2, '0', STR_PAD_LEFT);
                    } else {
                        $actual_minutes = 0;
                    }

                    // Calculate seconds
                    $mlw_complete_seconds = $mlw_qmn_results_array[0] % 60;
                    $actual_seconds = str_pad($mlw_complete_seconds, 2, '0', STR_PAD_LEFT);

                    $quiz_system = $result->quiz_system; // 0 = Correct/Incorrect, 1 = Point, 3 = Correct/Incorect and Point
                    $correct_score = $result->correct_score; // Score for Correct/Incorrect
                    $point_score = $result->point_score; // Score for Point

                    if (0 == $quiz_system) {
                        $final_score = $correct_score . '%';
                    } elseif (1 == $quiz_system) {
                        $final_score = $point_score;
                    } elseif (3 == $quiz_system) {
                        $final_score = 'Point(' . $point_score . ') | Correct(' . $correct_score . '%)';
                    }
                    echo '</pre>';
                    echo "<tr>";
                    echo "<td>{$count}</td>";
                    echo "<td>{$result->quiz_name}</td>";
                    echo "<td>{$final_score}</td>";
                    echo "<td>{$actual_hour}h {$actual_minutes}m {$actual_seconds}</td>";
                    echo "<td>{$result->time_taken}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            ?>


            <?php
            echo '</div>';
            if (($sidebar_layout === 'sidebar-right') && !is_singular('elementor_library')) {
                get_sidebar();
            }
            ?>
        </div>
</article>