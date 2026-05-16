<?php
/*
Template Name: Course Quiz Template
*/

get_header();

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id && get_post_status($course_id)):

    $post = get_post($course_id);

    if ($post):
?>

<div style="max-width:900px;margin:auto;padding:20px;color:#fff;">

    <!-- Course Title -->
    <h1><?php echo esc_html($post->post_title); ?></h1>

    <div>
    <?php 
	    if (!is_user_logged_in()) {
	        echo do_shortcode('[eduschedule_auth]');
	    } else {

	        $rows = get_field('course_relationship', 'options');
	        $found = false;

	        if (!empty($rows) && is_array($rows)) {

	            foreach ($rows as $row) {

	                if (!empty($row['courses']) && !empty($row['shortcode_'])) {

	                    foreach ($row['courses'] as $course) {

	                        $course_match_id = is_object($course) ? $course->ID : $course;

	                        if ($course_match_id == $course_id) {

	                            echo do_shortcode($row['shortcode_']);
	                            $found = true;
	                            break 2;
	                        }
	                    }
	                }
	            }
	        }

	        if (!$found) {
	            echo "<p>No quiz available for this course.</p>";
	        }
	    }
    ?>
</div>

</div>

<?php
    endif;

else:
    echo "<p>Invalid or missing Course ID</p>";
endif;

get_footer();
?>