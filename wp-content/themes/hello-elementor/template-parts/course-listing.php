<?php
/**
 * Template part: Course Listing
 * Rendered by the [es_course_listing] shortcode (registered in functions.php).
 *
 * Expects $atts (from the shortcode callback):
 *   title, posts_per_page, orderby, order, category, demo_label, demo_url
 *
 * Real course data used: image_gallery, rating, course_created_by,
 * course_content (lecture count), category/course_badge terms, course count.
 *
 * PLACEHOLDER (no ACF field yet — marked "TODO" below): the left filter groups
 * (Language/Duration/Topic/Level/Subtitles/Price), the subtitle line, total
 * hours, level, and enrollment count. Swap these for real fields when available.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$atts = isset( $atts ) ? $atts : array();

$query_args = array(
    'post_type'      => 'course',
    'post_status'    => 'publish',
    'posts_per_page' => (int) ( $atts['posts_per_page'] ?? -1 ),
    'orderby'        => sanitize_key( $atts['orderby'] ?? 'menu_order' ),
    'order'          => ( strtoupper( $atts['order'] ?? 'ASC' ) === 'DESC' ) ? 'DESC' : 'ASC',
    'no_found_rows'  => true,
);

if ( ! empty( $atts['category'] ) ) {
    $filter_tax = taxonomy_exists( 'course_category' ) ? 'course_category' : 'category';
    $query_args['tax_query'] = array( array(
        'taxonomy' => $filter_tax,
        'field'    => 'slug',
        'terms'    => sanitize_text_field( $atts['category'] ),
    ) );
}

$courses     = get_posts( $query_args );
$total       = count( $courses );
$demo_label  = $atts['demo_label'] ?? 'Demo';
$heading     = $atts['title'] ?? 'All Certification Preparation Courses';

// Single CATEGORY filter built from the real course taxonomy.
$filter_tax = taxonomy_exists( 'course_category' ) ? 'course_category' : 'category';
$course_ids = wp_list_pluck( $courses, 'ID' );

$cat_terms = $course_ids ? get_terms( array(
    'taxonomy'   => $filter_tax,
    'hide_empty' => true,
    'object_ids' => $course_ids,
    'orderby'    => 'name',
    'order'      => 'ASC',
) ) : array();
if ( is_wp_error( $cat_terms ) ) $cat_terms = array();

// Map each course ID → its category slugs (used for client-side filtering).
$course_cats = array();
foreach ( $courses as $c ) {
    $slugs = wp_get_post_terms( $c->ID, $filter_tax, array( 'fields' => 'slugs' ) );
    $course_cats[ $c->ID ] = is_array( $slugs ) ? $slugs : array();
}
?>

<div class="course-list-page">
    <div class="container">

        <!-- HEADING -->
        <div class="course-list-heading">
            <div class="breadcrumb course-breadcrumb">
                <a href="<?php echo esc_url( home_url() ); ?>">Home</a>
                <span class="text-primary">| </span>
                <span>Courses</span>
            </div>
            <h1 ><?php echo esc_html( $heading ); ?></h1>
        </div>

        <div class="es-cl-layout">

            <!-- SIDEBAR / CATEGORY FILTER -->
            <aside class="es-cl-sidebar" id="esClSidebar">
                <div class="es-cl-filter-group is-open">
                    <div class="es-cl-filter-title" role="button" tabindex="0">
                        <span>Category</span>
                        <span class="chev" aria-hidden="true">▾</span>
                    </div>
                    <div class="es-cl-filter-body">
                        <?php if ( ! empty( $cat_terms ) ) : ?>
                            <?php foreach ( $cat_terms as $term ) : ?>
                                <label class="es-cl-check">
                                    <input type="checkbox" class="es-cl-cat-filter" value="<?php echo esc_attr( $term->slug ); ?>" />
                                    <span class="es-cl-check-label"><?php echo esc_html( $term->name ); ?></span>
                                    <span class="count">(<?php echo (int) $term->count; ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="es-cl-filter-empty">No categories yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="es-cl-filter-group is-open">
                    <div class="es-cl-filter-title" role="button" tabindex="0">
                        <span>Rating</span>
                        <span class="chev" aria-hidden="true">▾</span>
                    </div>
                    <div class="es-cl-filter-body">
                        <?php foreach ( array( '4.5', '4.0', '3.5', '3.0' ) as $r ) : ?>
                            <label class="es-cl-check es-cl-rating-opt">
                                <input type="radio" name="es-cl-rating" class="es-cl-rating-filter" value="<?php echo esc_attr( $r ); ?>" />
                                <span class="es-cl-rating-star" aria-hidden="true">★</span>
                                <span class="es-cl-check-label"><?php echo esc_html( $r ); ?> &amp; up</span>
                            </label>
                        <?php endforeach; ?>
                        <label class="es-cl-check es-cl-rating-opt">
                            <input type="radio" name="es-cl-rating" class="es-cl-rating-filter" value="" checked />
                            <span class="es-cl-check-label">All ratings</span>
                        </label>
                    </div>
                </div>
            </aside>

            <!-- MAIN -->
            <div class="es-cl-main">

                <!-- TOOLBAR -->
                <div class="es-cl-toolbar">
                    <div class="es-cl-toolbar-left">
                        <button type="button" class="es-cl-filter-btn" id="esClFilterToggle">
                            <span class="es-cl-filter-ico" aria-hidden="true">⚙</span> Filter
                        </button>
                        <span class="es-cl-count"><?php echo (int) $total; ?> Courses</span>
                    </div>
                    <div class="es-cl-toolbar-right">
                        <div class="es-cl-view" aria-label="View as">
                            <button type="button" class="is-active" title="Grid view" aria-label="Grid view">▦</button>
                            <button type="button" title="List view" aria-label="List view">≣</button>
                        </div>
                    </div>
                </div>

                <!-- LIST -->
                <div class="es-cl-list">
                    <?php if ( empty( $courses ) ) : ?>
                        <p class="es-cl-empty">No courses found.</p>
                    <?php else : foreach ( $courses as $course ) :
                        $cid       = (int) $course->ID;
                        $title     = get_the_title( $course );
                        $permalink = ! empty( $atts['demo_url'] ) ? $atts['demo_url'] : get_permalink( $course );

                        // Image: first ACF gallery image, then featured image.
                        $thumb_url = '';
                        if ( function_exists( 'get_field' ) ) {
                            $gallery = get_field( 'image_gallery', $cid );
                            if ( ! empty( $gallery[0]['url'] ) ) $thumb_url = $gallery[0]['url'];
                        }
                        if ( ! $thumb_url ) $thumb_url = get_the_post_thumbnail_url( $course, 'medium' );

                        // Real fields.
                        $rating  = function_exists( 'get_field' ) ? (float) get_field( 'rating', $cid ) : 0;
                        $creator = function_exists( 'get_field' ) ? get_field( 'course_created_by', $cid ) : '';
                        if ( ! $creator ) $creator = get_the_author_meta( 'display_name', (int) $course->post_author );

                        // Lecture count = total lessons across course_content sections (real).
                        $lectures = 0;
                        if ( function_exists( 'get_field' ) ) {
                            $cc = get_field( 'course_content', $cid );
                            if ( $cc ) foreach ( $cc as $sec ) {
                                $lectures += ! empty( $sec['lessons'] ) ? count( $sec['lessons'] ) : 0;
                            }
                        }

                     

                        // Short description (ACF) — shown above the badge.
                        $short_desc = function_exists( 'get_field' ) ? get_field( 'course_short_description', $cid ) : '';

                        // Badge: ACF select field named "Badge" (single value or multiple).
                        $badges = array();
                        if ( function_exists( 'get_field' ) ) {
                            $badge_val = get_field( 'Badge', $cid );
                            if ( $badge_val === null || $badge_val === false || $badge_val === '' ) {
                                $badge_val = get_field( 'badge', $cid ); // fallback if field key is lowercase
                            }
                            if ( ! empty( $badge_val ) ) {
                                $badges = is_array( $badge_val ) ? $badge_val : array( $badge_val );
                            }
                        }

                        $row_cats = ! empty( $course_cats[ $cid ] ) ? implode( ' ', $course_cats[ $cid ] ) : '';
                    ?>
                        <div class="es-cl-row" data-cats="<?php echo esc_attr( $row_cats ); ?>" data-rating="<?php echo esc_attr( $rating ); ?>">
                            <div class="es-cl-thumb">
                                <a href="<?php echo esc_url( $permalink ); ?>">
                                    <?php if ( $thumb_url ) : ?>
                                        <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" />
                                    <?php else : ?>
                                        <span class="es-cl-thumb-placeholder">🎓</span>
                                    <?php endif; ?>
                                </a>
                            </div>

                            <div class="es-cl-info">
                                <h3 class="es-cl-title2"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
                               

                                <?php if ( $short_desc ) : ?>
                                    <p class="es-cl-desc"><?php echo esc_html( $short_desc ); ?></p>
                                <?php endif; ?>

                                <?php if ( ! empty( $badges ) ) : ?>
                                <div class="es-cl-badges">
                                    <?php foreach ( $badges as $badge ) :
                                        $cls = 'es-cl-badge';
                                        if ( stripos( $badge, 'premium' ) !== false )      $cls .= ' es-cl-badge-premium';
                                        elseif ( stripos( $badge, 'bestsell' ) !== false ) $cls .= ' es-cl-badge-bestseller';
                                        elseif ( stripos( $badge, 'new' ) !== false )      $cls .= ' es-cl-badge-new';
                                    ?>
                                        <span class="<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $badge ); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <p class="es-cl-author"><?php echo esc_html( $creator ); ?> </p>

                                <?php if ( $rating > 0 ) : ?>
                                    <div class="es-cl-rating">
                                        <span class="es-cl-rating-num"><?php echo esc_html( number_format( min( 5, max( 0, $rating ) ), 1 ) ); ?></span>
                                        <span class="es-cl-stars" aria-hidden="true">★★★★★</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="es-cl-action">
                                <a href="<?php echo esc_url( $permalink ); ?>" class="btn btn-primary"><?php echo esc_html( $demo_label ); ?></a>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div><!-- /.es-cl-list -->

            </div><!-- /.es-cl-main -->
        </div><!-- /.es-cl-layout -->
    </div><!-- /.container -->
</div><!-- /.course-list-page -->

<script>
(function () {
    // Accordion filter groups
    document.querySelectorAll('.es-cl-filter-title').forEach(function (head) {
        head.addEventListener('click', function () {
            head.parentElement.classList.toggle('is-open');
        });
    });

    // Mobile: toggle the whole sidebar with the Filter button
    var toggle = document.getElementById('esClFilterToggle');
    var sidebar = document.getElementById('esClSidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('is-mobile-open');
        });
    }

    // View-as grid/list toggle (visual)
    document.querySelectorAll('.es-cl-view button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.es-cl-view button').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
        });
    });

    // Filters — category (any checked = match) AND minimum rating
    var checks       = document.querySelectorAll('.es-cl-cat-filter');
    var ratingRadios = document.querySelectorAll('.es-cl-rating-filter');
    var rows         = document.querySelectorAll('.es-cl-row');
    var countEl      = document.querySelector('.es-cl-count');

    function applyFilter() {
        var selected = [];
        checks.forEach(function (c) { if (c.checked) selected.push(c.value); });

        var minRating = 0;
        ratingRadios.forEach(function (r) { if (r.checked && r.value) minRating = parseFloat(r.value); });

        var visible = 0;
        rows.forEach(function (row) {
            var cats = (row.getAttribute('data-cats') || '').split(' ').filter(Boolean);
            var rowRating = parseFloat(row.getAttribute('data-rating') || '0');

            var catOk    = !selected.length || selected.some(function (s) { return cats.indexOf(s) !== -1; });
            var ratingOk = !minRating || rowRating >= minRating;

            var show = catOk && ratingOk;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (countEl) countEl.textContent = visible + ' Courses';
    }

    checks.forEach(function (c) { c.addEventListener('change', applyFilter); });
    ratingRadios.forEach(function (r) { r.addEventListener('change', applyFilter); });
})();
</script>
