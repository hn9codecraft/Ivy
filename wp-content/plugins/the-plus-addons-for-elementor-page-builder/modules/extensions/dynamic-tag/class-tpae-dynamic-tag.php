<?php
/**
 * The file that defines the core plugin class
 *
 * @link    https://posimyth.com/
 * @since   6.4.5
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

namespace ElementPack\Includes\DynamicContent;

if ( ! defined( 'WPINC' ) ) {
	exit; // Exit if accessed directly
}

/*
 * Dynamic Tag option Theplus.
 * 
 * This full module registers all dynamic tags (Post, Site, User, Archive, WooCommerce).
 * 
 * @since 6.4.5
 */
if ( ! class_exists( 'Tpae_Dynamic_Tag' ) ) {

    /**
	 * Class Tpae_Dynamic_Tag
     * 
     * Handles registering dynamic tag groups and their individual tag classes.
     * 
     * @since 6.4.5
	 */
    class Tpae_Dynamic_Tag{

		/**
		 * Holds the singleton instance of this class.
		 *
		 * @since 6.4.5
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Returns a singleton instance of the class.
		 *
		 * Ensures only one instance is created during execution.
		 *
		 * @since 6.4.5
		 *
		 * @param array $shortcodes Optional. An array of shortcodes to initialize the instance with.
		 * @return self The single instance of the class.
		 */
		public static function get_instance( $shortcodes = array() ) {

			if ( null === self::$instance ) {
				self::$instance = new self( $shortcodes );
			}

			return self::$instance;
		}

        /**
		 * Dynamic tag mappings for text-based tag classes.
		 *
		 * Key = tag slug  
		 * Value = class file name (autoloaded manually)
		 *
		 * @var array
		 */
        private $tp_dynamic_tags_text = array(
            'post-id'       => 'ThePlus_Dynamic_Tag_Post_ID',
            'post-title'    => 'ThePlus_Dynamic_Tag_Post_Title',
            'post-excerpt'  => 'ThePlus_Dynamic_Tag_Post_Excerpt',
            'post-content'  => 'ThePlus_Dynamic_Tag_Post_Content',
            'post-category' => 'ThePlus_Dynamic_Tag_Post_Category',
            'post-tag'      => 'ThePlus_Dynamic_Tag_Post_Tags',
            'post-author'   => 'ThePlus_Dynamic_Tag_Post_Author',
            'post-slug'     => 'ThePlus_Dynamic_Tag_Post_Slug',
            'post-date'     => 'ThePlus_Dynamic_Tag_Post_Date',
            'post-time'     => 'ThePlus_Dynamic_Tag_Post_Time',
            'post-terms'    => 'ThePlus_Dynamic_Tag_Post_Terms',
            'post-status'   => 'ThePlus_Dynamic_Tag_Post_Status',
            'post-type'     => 'ThePlus_Dynamic_Tag_Post_Type',
            'post-cat-desc' => 'ThePlus_Dynamic_Tag_Post_Category_Description',
            'post-tag-desc' => 'ThePlus_Dynamic_Tag_Post_Tag_Description',

            'post-cat-post-count' => 'ThePlus_Dynamic_Tag_Post_Category_Post_Count',
            'post-tag-post-count' => 'ThePlus_Dynamic_Tag_Post_Tag_Post_Count',
            'post-featured-image' => 'ThePlus_Dynamic_Tag_Post_Featured_Image_Data',

            /** Site Tags*/
            'site-title' => 'ThePlus_Dynamic_Tag_Site_Title',
            'site-tagline' => 'ThePlus_Dynamic_Tag_Site_Tagline',
            'site-current-date-time' => 'ThePlus_Dynamic_Tag_Site_Current_Date_Time',
            // 'site-req-para' => 'ThePlus_Dynamic_Tag_Site_Current_Date_Time',
            // 'site-shortcode' => 'ThePlus_Dynamic_Tag_Site_Current_Date_Time',
        );

        /**
		 * Image-related dynamic tags.
		 *
         * @since 6.4.5
		 * @var array
		 */
        private $tp_dynamic_tags_image = [
            'post-featured-image' => 'ThePlus_Dynamic_Tag_Post_Featured_Image',
            'post-author-avatar'  => 'ThePlus_Dynamic_Tag_Post_Author_Avatar',

            'post-cat-image' => 'ThePlus_Dynamic_Tag_Post_Category_Image',

            /** Site Tags*/
            'site-logo' => 'ThePlus_Dynamic_Tag_Site_Logo',
            'site-icon' => 'ThePlus_Dynamic_Tag_Site_Icon',
        ];

        /**
		 * URL-based dynamic tags.
		 *
         * @since 6.4.5
		 * @var array
		 */
        private $tp_dynamic_tags_url = [
            'post-url'        => 'ThePlus_Dynamic_Tag_Post_URL',
            'post-term-url'  => 'ThePlus_Dynamic_Tag_Post_Term_URL',
            'post-author-url' => 'ThePlus_Dynamic_Tag_Post_Author_URL',

            /** Site Tags*/
            'site-url' => 'ThePlus_Dynamic_Tag_Site_URL',
        ];

        /**
		 * Constructor.
		 *
		 * Registers tag groups and individual tags on Elementor load.
		 *
		 * @since 6.4.5
		 */
        public function __construct(){
            add_action('elementor/dynamic_tags/register', [$this, 'tpae_reg_dynamic_tag_group'], 1);
            add_action('elementor/dynamic_tags/register', [$this, 'tpae_reg_dynamic_tag']);

            /*
             * Inline background-image injection for TPAE image dynamic tags.
             *
             * WHY THIS EXISTS
             * ---------------
             * Elementor's Dynamic CSS is generated once per page load (static cache).
             * In loop/archive templates every iteration shows the same cached image
             * (the one resolved at cache-write time). On fresh loads the CSS is built
             * at wp_enqueue_scripts time, before the WP loop starts, so get_the_ID()
             * returns 0 and the background-image rule ends up empty.
             *
             * WHY `after_add_attributes` INSTEAD OF `before_render`
             * -----------------------------------------------------
             * `elementor/frontend/before_render` fires at line 472 of print_element(),
             * BEFORE `add_render_attributes()` at line 519. Using `before_render` was
             * fragile because any code path that re-initialises render attributes
             * between lines 472–519 could wipe our addition.
             *
             * `elementor/element/after_add_attributes` fires inside
             * `add_render_attributes()` (after all standard class/data attributes are
             * set), right before `before_render()` prints the wrapper tag. This is
             * the last safe window to add a `style` attribute on `_wrapper`.
             *
             * WHY `get_data('settings')` INSTEAD OF `get_settings()`
             * --------------------------------------------------------
             * `get_settings()` runs `sanitize_settings()` which may silently unset
             * `__dynamic__[control_key]` if something goes wrong with tag lookup.
             * `get_data('settings')` returns the raw saved data from the DB, which
             * always preserves the `__dynamic__` entries as the editor stored them.
             */
            add_action( 'elementor/element/after_add_attributes', [ $this, 'tpae_inject_featured_image_bg_style' ] );

            if ( ! defined( 'THEPLUS_VERSION' ) ) {
                add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'tpae_pro_dynamic_tags_show' ] );
                add_action( 'elementor/editor/after_enqueue_styles', function () {
                    wp_add_inline_style(
                        'elementor-editor',
                        '.tp-pro-tag-item{opacity:0.45;cursor:not-allowed;display:flex;align-items:center;}.tp-pro-tag-item:hover{background:transparent !important;}.tp-pro-lock{font-size: 13px;}'
                    );
                });
            }

            if ( ! get_option( 'tpae_dynamictag_notice_dismissed' ) ) {
                add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'tpae_dynamic_tags_editor_notice' ] );

                add_action( 'elementor/editor/after_enqueue_styles', function () {
                    wp_add_inline_style(
                        'elementor-editor',
                        '.tp-plus-dynamic-notice{font-size:12px;line-height:1.5;padding:10px 15px;margin-top:10px;}.tp-plus-dynamic-notice-title{font-weight:bold;font-size:12px;padding:0px;display:flex;align-items:center;}.tp-plus-dynamic-notice-desc{font-size:11px;font-style:italic;line-height:1.4;color:var(--e-a-color-txt-muted);padding-top:5px;}button.tp-plus-dynamic-notice-close{background:transparent;border:none;position:absolute;right:5px;cursor:pointer;font-size:10px;}'
                    );
                });
            }

            if ( ! get_option( 'tp_dynamic_tag_seen' ) ) {
                add_action( 'elementor/editor/after_enqueue_styles', function () {
                    wp_add_inline_style(
                        'elementor-editor',
                        '.elementor-control-unit-5 .elementor-control-dynamic-switcher,.elementor-control-type-url .elementor-control-dynamic-switcher{position: relative !important;}.elementor-control-dynamic-switcher::after{content:"";position:absolute;top:-3px;right:-3px;width:7px;height:7px;background:red;border-radius:50%;}.elementor-control-type-media .elementor-control-dynamic-switcher::after{top:-1px;right:-1px;}.tp-dt-dot-dismissed .elementor-control-dynamic-switcher::after{display: none !important;}'
                    );
                });

                add_action( 'elementor/editor/after_enqueue_scripts', function () {
                    wp_add_inline_script(
                        'elementor-editor',
                        "
                        window.top.document.addEventListener( 'click', function (e) {
                            const target = e.target;
                            if ( target.closest && target.closest('.elementor-control-dynamic-switcher') ) {
                                window.top.document.body.classList.add('tp-dt-dot-dismissed');

                                jQuery.post(ajaxurl, {
                                    action: 'tp_mark_dynamic_tag_seen',
                                    nonce: '" . wp_create_nonce( 'tp_dynamic_tag_nonce' ) . "'
                                });
                            }
                        }, true );
                        "
                    );
                });

                add_action( 'wp_ajax_tp_mark_dynamic_tag_seen', function () {
                    check_ajax_referer( 'tp_dynamic_tag_nonce', 'nonce' );
                    update_option( 'tp_dynamic_tag_seen', true );
                    wp_send_json_success();
                });
            }

            add_action( 'wp_ajax_tpae_dismiss_dynamic_notice', function () {
                check_ajax_referer( 'tpae_dismiss_dynamic_notice', 'nonce' );

                if ( ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
                }

                update_option( 'tpae_dynamictag_notice_dismissed', true );
                wp_send_json_success();
            });
        }

        public function tpae_pro_dynamic_tags_show() {

            wp_add_inline_script(
                'elementor-editor',
                "(function () {

                    const TP_PRO_TAGS = {
                        'Plus - User': [
                            'User Info',
                            'User Meta'
                        ],
                        'Plus - Archive': [
                            'Archive Title',
                            'Archive Meta',
                            'Archive Description'
                        ],
                        'Plus - Dynamic Categories': [
                            'DC Term Title',
                            'DC Term Description',
                            'DC Term Count',
                            'DC Term URL',
                            'DC Term Image',
                        ],
                        'Plus - WooCommerce': [
                            'Product Title',
                            'Product Price',
                            'Product SKU',
                            'Product Type',
                            'Product Rating',
                            'Product Attribute',
                            'Product Stock Status'
                        ],
                        'Plus - ACF': [
                            'ACF Field',
                        ]
                    };

                    const PRO_UPGRADE_URL = 'https://theplusaddons.com/pricing/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=dynamiccontent';

                    // let debounceTimer = null;

                    function makeProGroupsClickable(panelInner) {

                        panelInner.querySelectorAll('.elementor-tags-list__group-title')
                            .forEach(function(group) {

                                const title = group.textContent.trim();

                                if (!TP_PRO_TAGS.hasOwnProperty(title)) {
                                    return;
                                }

                                if (group.classList.contains('tp-pro-group-linked')) {
                                    return;
                                }

                                group.classList.add('tp-pro-group-linked');
                                group.style.cursor = 'pointer';

                                if (!group.querySelector('.theplus-i-lock')) {
                                    const lockIcon = document.createElement('i');
                                    lockIcon.className = 'theplus-i-lock';
                                    lockIcon.style.marginLeft = '3px';
                                    lockIcon.style.fontSize = '13px';
                                    lockIcon.style.lineHeight = '15px';
                                    group.appendChild(lockIcon);
                                }

                                group.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    window.open(PRO_UPGRADE_URL, '_blank');
                                });
                            });
                    }

                    function processDynamicTagsPanel(panelInner) {

                        panelInner.querySelectorAll('[data-tag-name^=\"tpae-pro-placeholder\"]').forEach(function(el){
                            el.style.display = 'none';
                        });

                        panelInner.querySelectorAll('.elementor-tags-list__group-title')
                            .forEach(function(group) {

                                const title = group.textContent.trim();
                                if (!TP_PRO_TAGS[title]) {
                                    return;
                                }

                                let insertAfter = group;

                                TP_PRO_TAGS[title].forEach(function(label) {

                                    if ( insertAfter.nextElementSibling && insertAfter.nextElementSibling.classList.contains('tp-pro-tag-item') ) {
                                        return;
                                    }

                                    const item = document.createElement('div');
                                    item.className = 'elementor-tags-list__item tp-pro-tag-item';

                                    item.innerHTML = '<span class=\"tp-pro-tag-label\">' + label + '</span>';

                                    group.parentNode.insertBefore(item, insertAfter.nextSibling);
                                    insertAfter = item;
                                });
                            });
                    }

                    function safeProcess(panelInner) {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(function () {
                            processDynamicTagsPanel(panelInner);
                            makeProGroupsClickable(panelInner);
                        }, 60); 
                    }

                    // Wait specifically for Dynamic Tags popup
                    const bodyObserver = new MutationObserver(function() {

                        const panelInner = document.querySelector('.elementor-tags-list__inner');
                        if (!panelInner) {
                            return;
                        }

                        processDynamicTagsPanel(panelInner);
                        makeProGroupsClickable(panelInner);
                        // safeProcess(panelInner);
                    });

                    bodyObserver.observe(document.body, { childList: true, subtree: true });

                })();"
            );
        }

        public function tpae_dynamic_tags_editor_notice() {
            wp_localize_script(
                'elementor-editor',
                'TPDynamicNotice',
                [
                    'title' => esc_html__( 'Dynamic Content from The Plus Addons for Elementor', 'tpebl' ),
                    'desc'  => esc_html__( 'Dynamic Content is now included with The Plus Addons for Elementor, letting you use dynamic features in Elementor even without Elementor Pro.', 'tpebl' ),
                    'learn' => esc_html__( 'Learn More', 'tpebl' ),
                    'nonce' => wp_create_nonce( 'tpae_dismiss_dynamic_notice' ),
                ]
            );

            wp_add_inline_script(
                'elementor-editor',
                <<<JS
                (function () {

                    let dismissed = false;

                    function insertDynamicNoticeAtTop() {

                        if (dismissed) {
                            return;
                        }

                        const listInner = document.querySelector('.elementor-tags-list__inner');
                        if (!listInner || listInner.querySelector('.tp-plus-dynamic-notice')) {
                            return;
                        }

                        const notice = document.createElement('div');
                        notice.className = 'tp-plus-dynamic-notice elementor-panel-alert elementor-panel-alert-info';
                        notice.innerHTML = 
                            `<button class="tp-plus-dynamic-notice-close"><i class="theplus-i-cross"></i></button>
                            <div class="tp-plus-dynamic-notice-title">\${TPDynamicNotice.title}</div>
                            <div class="tp-plus-dynamic-notice-desc">
                                \${TPDynamicNotice.desc}
                                <a href="https://theplusaddons.com/docs/add-dynamic-content-in-elementor/?utm_source=wpbackend&utm_medium=elementoreditor&utm_campaign=dynamiccontent"
                                target="_blank" rel="noopener noreferrer">
                                    \${TPDynamicNotice.learn}
                                </a>
                            </div>`;

                        notice.querySelector('.tp-plus-dynamic-notice-close').addEventListener('click', function () {

                            dismissed = true;

                            fetch(ajaxurl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'action=tpae_dismiss_dynamic_notice&nonce=' + encodeURIComponent(TPDynamicNotice.nonce)
                            });

                            notice.remove();
                        });

                        // INSERT AT VERY TOP
                        listInner.prepend(notice);
                    }

                    const observer = new MutationObserver(insertDynamicNoticeAtTop);
                    observer.observe(document.body, { childList: true, subtree: true });

                })();
                JS
            );
        }

        /**
		 * Register Dynamic Tag Groups (Post, Site, User, Archive, Dynamic Categories, WooCommerce, ACF).
         * 
         * @since 6.4.5
		 *
		 * @param object $dynamic_ele Elementor dynamic tag manager.
		 */
        public function tpae_reg_dynamic_tag_group( $dynamic_ele ) {
            $dynamic_ele->register_group(
                'plus-opt-post',
                [
                    'title' => esc_html__( 'Plus - Post', 'tpebl' )
                ]
            );

            $dynamic_ele->register_group(
                'plus-opt-site',
                [
                    'title' => esc_html__( 'Plus - Site', 'tpebl' )
                ]
            );

            if ( ! defined( 'THEPLUS_VERSION' ) ) {
                $dynamic_ele->register_group(
                    'plus-opt-user',
                    [
                        'title' => esc_html__( 'Plus - User', 'tpebl' )
                    ]
                );

                $dynamic_ele->register_group(
                    'plus-opt-archive',
                    [
                        'title' => esc_html__( 'Plus - Archive', 'tpebl' )
                    ]
                );

                $dynamic_ele->register_group(
                    'plus-opt-dc',
                    [
                        'title' => esc_html__( 'Plus - Dynamic Categories', 'tpebl' )
                    ]
                );

                if ( class_exists( 'WooCommerce' ) ) {
                    $dynamic_ele->register_group(
                        'plus-opt-woocommerce',
                        [
                            'title' => esc_html__( 'Plus - WooCommerce', 'tpebl' )
                        ]
                    );
                }

                if ( function_exists( 'acf_get_field_groups' ) ) {
                    $dynamic_ele->register_group(
                        'plus-opt-acf',
                        [
                            'title' => esc_html__( 'Plus - ACF', 'tpebl' )
                        ]
                    );
                }
            }
        }

        /**
		 * Registers all dynamic tags (text, image, url, User, Archive, Dynamic Categories, WooCommerce, ACF).
		 *
         * @since 6.4.5
         * 
		 * @param object $dynamic_ele Elementor dynamic tag manager.
		 */
        public function tpae_reg_dynamic_tag( $dynamic_ele ) {
            $this->tpae_register_text_tags( $dynamic_ele );

            $this->tpae_register_image_tags( $dynamic_ele );

            $this->tpae_register_url_tags( $dynamic_ele );

            if ( ! defined( 'THEPLUS_VERSION' ) ) {

                require_once L_THEPLUS_PATH . 'modules/extensions/dynamic-tag/tags/pro/tpae-pro-dummy.php';

                $dynamic_ele->register( new TPAE_Pro_Dummy_Tag( 'plus-opt-user', 'user' ) );
                $dynamic_ele->register( new TPAE_Pro_Dummy_Tag( 'plus-opt-archive', 'archive' ) );
                $dynamic_ele->register( new TPAE_Pro_Dummy_Tag( 'plus-opt-dc', 'dc' ) );

                if ( class_exists( 'WooCommerce' ) ) {
                    $dynamic_ele->register( new TPAE_Pro_Dummy_Tag( 'plus-opt-woocommerce', 'woo' ) );
                }

                if ( function_exists( 'acf_get_field_groups' ) ) {
                    $dynamic_ele->register( new TPAE_Pro_Dummy_Tag( 'plus-opt-acf', 'acf' ) );
                }
            }
        }
        
        /**
		 * Load and register text-based tag classes.
         * 
         * @since 6.4.5
		 */
        private function tpae_register_text_tags( $dynamic_tags_manager ){
            foreach ( $this->tp_dynamic_tags_text as $tag => $class ) {
                $file = L_THEPLUS_PATH . 'modules/extensions/dynamic-tag/tags/text/' . $tag . '.php';
                if ( file_exists( $file ) ) {
                    include( $file );
                    if ( class_exists( $class ) ) {
                        $dynamic_tags_manager->register( new $class() );
                    }
                }
            }
        }

        /**
		 * Load and register image tags.
         * 
         * @since 6.4.5
		 */
        private function tpae_register_image_tags( $dynamic_tags_manager ) {
            foreach ( $this->tp_dynamic_tags_image as $tag => $class ) {
                $file = L_THEPLUS_PATH . 'modules/extensions/dynamic-tag/tags/image/' . $tag . '.php';
                if ( file_exists( $file ) ) {
                    include( $file );
                    if ( class_exists( $class ) ) {
                        $dynamic_tags_manager->register( new $class() );
                    }
                }
            }
        }

        /**
		 * Load and register URL-based dynamic tags.
         *
         * @since 6.4.5
		 */
        private function tpae_register_url_tags( $dynamic_tags_manager ) {
            foreach ( $this->tp_dynamic_tags_url as $tag => $class ) {
                $file = L_THEPLUS_PATH . 'modules/extensions/dynamic-tag/tags/url/' . $tag . '.php';
                if ( file_exists( $file ) ) {
                    include( $file );
                    if ( class_exists( $class ) ) {
                        $dynamic_tags_manager->register( new $class() );
                    }
                }
            }
        }

        /**
         * Inject an inline background-image style for TPAE image dynamic tags
         * assigned to a background image control on any element.
         *
         * Why this is needed
         * ------------------
         * Elementor's Dynamic CSS (the per-post <style> block) is generated once and
         * cached. In loop/archive templates every iteration would get the same cached
         * image (the one resolved when the cache was written). On fresh page loads the
         * CSS generation happens at wp_enqueue_scripts time, before the WP post loop
         * runs, so get_the_ID() returns 0 and the background-image rule is often empty.
         *
         * This method hooks into `elementor/element/after_add_attributes` — which fires
         * at the end of add_render_attributes(), per-element, inside the loop — and
         * resolves the correct image URL for the current iteration, writing it as an
         * HTML inline style attribute. Inline styles have higher CSS specificity than
         * external stylesheet rules, so this value always wins regardless of what the
         * cached CSS file contains.
         *
         * @since 6.4.7
         *
         * @param \Elementor\Element_Base $element The element about to be rendered.
         */
        public function tpae_inject_featured_image_bg_style( $element ) {

            /*
             * Step 1 — fast guard: check whether any background image control on
             * this element has one of our TPAE image dynamic tags assigned.
             *
             * get_data('settings') is used instead of get_settings() because both
             * methods run sanitize_settings() under the hood, but get_data() is the
             * lower-level call that stores results back in $this->data['settings'],
             * making any subsequent call cheap (the flag is already set).
             *
             * If __dynamic__ is absent the element has no dynamic controls at all,
             * so we exit immediately.
             */
            $raw = $element->get_data( 'settings' );

            if ( empty( $raw['__dynamic__'] ) || ! is_array( $raw['__dynamic__'] ) ) {
                return;
            }

            /*
             * Background image control keys Elementor registers on sections,
             * columns, and containers. Covers: main background, overlay background,
             * and hover-state background.
             */
            $bg_image_keys = [
                'background_image',
                'background_overlay_image',
                'background_hover_image',
            ];

            /*
             * TPAE image tag slugs this method handles. Add new slugs here as
             * new image dynamic tags are introduced — no other code needs changing.
             */
            $tpae_slugs = [
                'plus-tag-post-featured-image',
            ];

            // Collect the keys that carry one of our tags.
            $inject_keys = [];

            foreach ( $bg_image_keys as $key ) {
                if ( empty( $raw['__dynamic__'][ $key ] ) ) {
                    continue;
                }
                // The stored value is an Elementor tag shortcode string, e.g.:
                // [elementor-tag id="…" name="plus-tag-post-featured-image" settings="…"]
                $dynamic_val = $raw['__dynamic__'][ $key ];

                foreach ( $tpae_slugs as $slug ) {
                    if ( false !== strpos( $dynamic_val, $slug ) ) {
                        $inject_keys[] = $key;
                        break; // inner loop only
                    }
                }
            }

            if ( empty( $inject_keys ) ) {
                return;
            }

            /*
             * Step 2 — resolve the URL via Elementor's own dynamic tag pipeline.
             *
             * get_settings_for_display() calls get_parsed_dynamic_settings() which
             * processes every __dynamic__ entry:
             *   parse_tags_text( $shortcode, ['returnType'=>'object'], $callback )
             *     → get_tag_data_content( $id, $name, $settings )
             *       → $tag->get_content()
             *         → $tag->get_value()  ← our ThePlus_Dynamic_Tag_*::get_value()
             *
             * The result for a MEDIA / IMAGE control is ['id' => …, 'url' => …].
             * Because this runs inside add_render_attributes() (line 762 of
             * element-base.php, which is the same call stack that fires
             * after_add_attributes at line 828), the result is already cached in
             * $this->parsed_active_settings — so this call is effectively free.
             *
             * This approach eliminates ALL per-tag resolution duplication: every
             * TPAE image tag's get_value() is the single source of truth for how
             * the image is resolved, and any future changes to get_value() are
             * automatically reflected here.
             */
            $display = $element->get_settings_for_display();

            foreach ( $inject_keys as $key ) {
                $image_url = ! empty( $display[ $key ]['url'] ) ? $display[ $key ]['url'] : '';

                if ( ! $image_url ) {
                    continue;
                }

                // Inject the inline style. add_render_attribute() appends to any
                // existing style value, so other background-* properties set by
                // Elementor controls are not wiped out.
                $element->add_render_attribute(
                    '_wrapper',
                    'style',
                    'background-image: url("' . esc_url( $image_url ) . '");'
                );
            }
        }
    }
}

/** Initialize the dynamic tag system.*/
Tpae_Dynamic_Tag::get_instance();
