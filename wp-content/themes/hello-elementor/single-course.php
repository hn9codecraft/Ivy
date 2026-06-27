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
    <a href="<?php echo esc_url(home_url()); ?>">Home</a>
    <span class="text-primary"> | </span>

    <?php
    $terms = get_the_terms(get_the_ID(), 'course_category');

    if ($terms && !is_wp_error($terms)) :
        $term = $terms[0];
    ?>
        <a href="<?php echo esc_url(get_term_link($term)); ?>">
            <?php echo esc_html($term->name); ?>
        </a>
        <span class="text-primary"> | </span>
    <?php endif; ?>

    <span><?php the_title(); ?></span>
</div>

        <!-- TOP SECTION -->
        <div class="course-grid">
           
            <div>
                <div class="section-heading">
                    <h1 class="course-title "><?php the_title(); ?></h1>

                    <?php $image_gallery = get_field('image_gallery');
                        if ($image_gallery) :
                            foreach ($image_gallery as $image) :
                        ?>
                                <img class="course-img course-img-mobile" src="<?php echo esc_url($image['url']); ?>"
                                    alt="<?php echo esc_attr($image['alt']); ?>">
                        <?php
                            endforeach;
                        endif;
                        ?>

                    <p class="course-desc"><?php echo esc_html($short_desc); ?></p>
                    <p class="creator">Created by <strong class="text-primary"><?php echo esc_html($creator); ?></strong></p>
                </div>

                <div class="info-card">
                    <div class="premium-badge"> <img src="<?php echo get_template_directory_uri(); ?>/assets/images/ribbon-badge.svg"  alt="ribbon-badge">
                                 Premium</div>
                    <div class="info-item access-content">
                        <span>Access 28,000+ top-rated  courses with Ivy Personal Plan.</span>
                    </div>
                    <div class="info-divider"></div>
                    <div class="info-item">
                        <strong>⭐ <?php echo esc_html($rating); ?></strong>
                        <span>(<?php echo esc_html($rating); ?>/5 rating)</span>
                    </div>
                    <div class="info-divider"></div>
                    <div class="info-item">
                     
                        <strong>   <img src="<?php echo get_template_directory_uri(); ?>/assets/images/users-icons.svg" width="24" height="24" alt="user"> 2,69,533</strong>
                        <span>Learners</span>
                    </div>
                </div>

             <!-- COURSE CONTENT -->
        <?php if ($content): ?>
        <div class="course-card">
            <div class="accordion bg-box">
                <h2>Course Details</h2>
            <?php foreach ($content as $index => $section): ?>
                <div class="accordion-section <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="accordion-header" onclick="toggleAccordion(this)">
                        <div class="accordion-title">
                            <span><?php echo esc_html($section['title']); ?></span>
                        </div>
                        <span class="accordion-icon"> <img src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-down.svg"  alt="arrow-down"></span>
                    </div>

                    <?php if (!empty($section['lessons'])): ?>
                        <div class="accordion-body">
                            <p><?php echo esc_html($section['description']); ?></p>
                            <?php foreach ($section['lessons'] as $lesson): ?>
                                <div class="lesson">
                                    <span><?php echo esc_html($lesson['lesson_title']); ?></span>
                                    <!-- <span class="lesson-duration"><?php echo esc_html($lesson['duration']); ?></span> -->
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
           </div>
        </div>
        <?php endif; ?>
                
        <!-- WHAT YOU LEARN -->
        <?php if ($learn): ?>
        <div class="course-card bg-box">
            <h2>What you'll <span class="highlight">Learn</span></h2>
            <ul class="learn-list">
                <?php foreach ($learn as $item): ?>
                    <li><?php echo esc_html($item['item']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- INCLUDES -->
        <?php if ($includes): ?>
        <div class="course-card bg-box">
            <h2>This Course <span class="highlight">Includes </span></h2>
            <ul class="includes-list">
                <?php foreach ($includes as $item): ?>
                    <li><?php echo esc_html($item['item']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

     

        <!-- REQUIREMENTS -->
        <?php if ($requirements): ?>
        <div class="course-card  bg-box">
            <h2>Description</h2>
            <ul class="requirements-list">
                <?php foreach ($requirements as $req): ?>
                    <li><?php echo esc_html($req['requirement']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

     
     
            </div>

            <!-- SIDEBAR -->
            <div class="sidebar">
                <?php
                    if ($image_gallery) :
                        foreach ($image_gallery as $image) :
                    ?>
                            <img class="course-img course-img-desktop" src="<?php echo esc_url($image['url']); ?>"
                                alt="<?php echo esc_attr($image['alt']); ?>">
                    <?php
                        endforeach;
                    endif;
                    ?>

                <div class="sidebar-info">
                    <strong>Hands-on learning. Real-world skills.</strong>
                    <span>Master concepts, improve critical thinking, and prepare for academic success beyond the classroom.</span><br>
                    <span>The right guidance can transform curiosity into confidence</span><br>
                </div>
                <div class="book-btn">
                <a href="<?php echo site_url('/course-quiz/?course_id=' . get_the_ID()); ?>" class="btn btn-primary btn-full ">
                    Book a Demo  <svg width="17" height="12" viewBox="0 0 17 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10.1198 12L8.70301 10.55L12.2955 7H0V5H12.2955L8.70301 1.45L10.1198 0L16.1916 6L10.1198 12Z" fill="white"/>
                                </svg>

                </a>
                </div>
            </div>

        </div>

    </div>

   
        <!-- FAQ -->
        <?php $faqs = get_field('faqs'); ?>
        <?php if ($faqs): ?>
        <section class="faqs" aria-labelledby="faqs-heading">
            <div class="container">
                <h2 class="faqs-title" id="faqs-heading">Frequently Asked <span class="highlight">Questions</span></h2>

                <div class="faqs-list">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="faq-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <a href="javascript:void(0)" type="button" class="faq-question" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" onclick="toggleFaq(this)">
                                <span><?php echo esc_html($faq['question']); ?></span>
                                <span class="faq-icon" aria-hidden="true">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-down.svg" alt="">
                                </span>
                            </a>
                            <div class="faq-answer">
                                <div class="faq-answer-inner"><?php echo wp_kses_post($faq['answer']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>    
        </section>
        <?php endif; ?>

        <!-- RELATED COURSES -->
        <div class="related-course-section">
            <div class="container">

            <?php if ($related): ?>

                <?php
                $current_id = get_the_ID();

                // Remove current post
                $related_posts = array_filter($related, function ($post) use ($current_id) {
                    $rid = is_object($post) ? $post->ID : $post;
                    return $rid != $current_id;
                });

                $total_posts = count($related_posts);
                ?>

            <div class="course-card">
                <h2 class="text-center">
                    Related <span class="highlight">Courses</span>
                </h2>
              

                   <div class="swiper relatedCourseSlider">
                        <div class="swiper-wrapper">

                            <?php foreach ($related_posts as $post): ?>
                                <?php
                                $rid = is_object($post) ? $post->ID : $post;
                                setup_postdata($post);

                                $related_gallery = get_field('image_gallery', $rid);
                                $related_img = !empty($related_gallery[0]['url'])
                                    ? $related_gallery[0]['url']
                                    : get_the_post_thumbnail_url($rid, 'medium');

                                $rating = get_field('rating', $rid);
                                $short_desc = get_field('course_short_description', $rid);
                                ?>

                                <div class="swiper-slide">
                                    <div class="related-card">

                                        <?php if ($related_img): ?>
                                            <img class="related-course-img"
                                                src="<?php echo esc_url($related_img); ?>"
                                                alt="<?php echo esc_attr(get_the_title($rid)); ?>">
                                        <?php endif; ?>

                                        <div class="related-info">

                                            <?php if ($rating): ?>
                                                <div class="related-rating">
                                                    ★★★★☆
                                                    <span class="text-white">
                                                        <?php echo esc_html($rating); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            <h4><?php the_title(); ?></h4>
                                            <?php if ($short_desc): ?>
                                                <p>
                                                    <?php echo wp_trim_words($short_desc, 18, '...'); ?>
                                                </p>
                                            <?php endif; ?>

                                           

                                            <a href="<?php the_permalink(); ?>" class="btn btn-small">
                                                View Program
                                            </a>

                                        </div>

                                    </div>
                                </div>

                            <?php endforeach; wp_reset_postdata(); ?>

                        </div>

                    </div>
                    
                        <div class="swiper-button-prev" role="button" tabindex="0" aria-label="Previous slide" aria-controls="swiper-wrapper-92ff8c115639c88d" fdprocessedid="lnlrn"></div>
                         <div class="swiper-button-next" role="button" tabindex="0" aria-label="Next slide" aria-controls="swiper-wrapper-92ff8c115639c88d" fdprocessedid="jng0z"></div>

                <?php endif; ?>
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
4
    // Toggle the clicked one
    clickedSection.classList.toggle('active');
}

function toggleFaq(btn) {
    var item = btn.parentElement;
    var allItems = document.querySelectorAll('.faq-item');

    // Accordion behavior: close all others
    allItems.forEach(function(other) {
        if (other !== item) {
            other.classList.remove('active');
            var otherBtn = other.querySelector('.faq-question');
            if (otherBtn) { otherBtn.setAttribute('aria-expanded', 'false'); }
        }
    });

    // Toggle the clicked one
    var isOpen = item.classList.toggle('active');
    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

// Add/remove the course image from the DOM (not via CSS) at the 767px breakpoint.
// .course-img-desktop -> only in DOM above 767px | .course-img-mobile -> only at/below 767px.
(function () {
    var BREAKPOINT = 767;
    var desktopEls = [].slice.call(document.querySelectorAll('.course-img-desktop'));
    var mobileEls  = [].slice.call(document.querySelectorAll('.course-img-mobile'));

    function detach(el) {
        if (el._ph) return; // already removed
        el._ph = document.createComment('course-img');
        el.parentNode.replaceChild(el._ph, el);
    }
    function attach(el) {
        if (el._ph && el._ph.parentNode) {
            el._ph.parentNode.replaceChild(el, el._ph);
            el._ph = null;
        }
    }
    function apply() {
        var isMobile = window.innerWidth <= BREAKPOINT;
        desktopEls.forEach(isMobile ? detach : attach);
        mobileEls.forEach(isMobile ? attach : detach);
    }

    apply();
    window.addEventListener('resize', apply);
})();
</script>

<?php endwhile; get_footer(); ?>