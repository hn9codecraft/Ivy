<?php
get_header();

while (have_posts()) : the_post();

// ACF Fields
$short_desc  = get_field('course_short_description');
$learn       = get_field('what_you_learn');
$includes    = get_field('course_includes');
$content     = get_field('course_content');
$requirements = get_field('requirements');
$creator     = get_field('course_created_by');
$rating      = get_field('rating');
$related     = get_field('related_courses');

?>
<div class="course-page">
    <div class="container">

        <!-- BREADCRUMB -->
        <div class="breadcrumb">
            <a href="<?php echo home_url(); ?>">Home</a> |
            <a href="#">Course category</a> |
            <span><?php echo esc_html(get_the_title()); ?></span>
        </div>

        <!-- TOP SECTION -->
        <div class="course-grid">

            <div>
                <div class="section-heading">
                    <h1 class="course-title "><?php the_title(); ?></h1>
                    <p class="course-desc"><?php echo esc_html($short_desc); ?></p>
                    <p class="creator">Created by <strong class="text-primary"><?php echo esc_html($creator); ?></strong></p>
                </div>

                <div class="info-card">
                    <div class="premium-badge">⚡ Premium</div>
                    <div class="info-item">
                        <span>Access 28,000+ top-rated</span>
                        <span>courses with Ivy Personal Plan.</span>
                    </div>
                    <div class="info-divider"></div>
                    <div class="info-item">
                        <strong>⭐ <?php echo esc_html($rating); ?></strong>
                        <span>(<?php echo esc_html($rating); ?>/5 rating)</span>
                    </div>
                    <div class="info-divider"></div>
                    <div class="info-item">
                        <strong>2,69,533</strong>
                        <span>learners</span>
                    </div>
                </div>

                
        <!-- WHAT YOU LEARN -->
        <?php if ($learn): ?>
        <div class="card">
            <h2>What you'll <span class="highlight">learn</span></h2>
            <ul class="learn-list">
                <?php foreach ($learn as $item): ?>
                    <li><?php echo esc_html($item['item']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- INCLUDES -->
        <?php if ($includes): ?>
        <div class="card">
            <h2>This course includes</h2>
            <ul class="includes-list">
                <?php foreach ($includes as $item): ?>
                    <li><?php echo esc_html($item['item']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- COURSE CONTENT -->
        <?php if ($content): ?>
        <div class="card">
            <h2>Course content</h2>

            <?php foreach ($content as $index => $section): ?>
                <div class="accordion-section <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="accordion-header" onclick="toggleAccordion(this)">
                        <div class="accordion-title">
                            <span class="accordion-icon">+</span>
                            <span><?php echo esc_html($section['title']); ?></span>
                        </div>
                        <span class="accordion-play">Play</span>
                    </div>

                    <?php if (!empty($section['lessons'])): ?>
                        <div class="accordion-body">
                            <?php foreach ($section['lessons'] as $lesson): ?>
                                <div class="lesson">
                                    <span><?php echo esc_html($lesson['lesson_title']); ?></span>
                                    <span class="lesson-duration"><?php echo esc_html($lesson['duration']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        </div>
        <?php endif; ?>

        <!-- REQUIREMENTS -->
        <?php if ($requirements): ?>
        <div class="card">
            <h2>Requirements</h2>
            <ul class="requirements-list">
                <?php foreach ($requirements as $req): ?>
                    <li><?php echo esc_html($req['requirement']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- RELATED COURSES -->
        <?php if ($related): ?>
        <div class="card">
            <h2>Students <span class="highlight">also bought</span></h2>

            <div class="related-grid">
                <?php foreach ($related as $post): setup_postdata($post); ?>
                    <div class="related-card">
                        <?php the_post_thumbnail('medium'); ?>
                        <div class="related-info">
                            <h4><?php the_title(); ?></h4>
                            <div class="related-rating">★★★★☆ 4.5</div>
                            <a href="<?php the_permalink(); ?>" class="btn btn-small">View Program</a>
                        </div>
                    </div>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>

        </div>
        <?php endif; ?>
     
            </div>

            <!-- SIDEBAR -->
            <div class="sidebar">
                <?php $image_gallery = get_field('image_gallery');

                    if ($image_gallery) :
                        foreach ($image_gallery as $image) :
                    ?>
                            <img src="<?php echo esc_url($image['url']); ?>" 
                                alt="<?php echo esc_attr($image['alt']); ?>">
                    <?php
                        endforeach;
                    endif;
                    ?>

                <div class="sidebar-info">
                    <strong>Access 28,000+ top-rated courses</strong>
                    <span>Concept on Ivy Personal Plan.</span><br>
                    <a href="#" class="learn-more">Learn More →</a>
                </div>
                <a href="<?php echo site_url('/course-quiz/?course_id=' . get_the_ID()); ?>" class="btn">
                    Enroll Now →
                </a>
            </div>

        </div>


    </div>
</div>

<script>
function toggleAccordion(header) {
    var clickedSection = header.parentElement;
    var allSections = document.querySelectorAll('.accordion-section');

    // Accordion behavior: close all others
    allSections.forEach(function(section) {
        if (section !== clickedSection) {
            section.classList.remove('active');
        }
    });

    // Toggle the clicked one
    clickedSection.classList.toggle('active');
}
</script>

<?php endwhile; get_footer(); ?>