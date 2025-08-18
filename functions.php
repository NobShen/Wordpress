<?php
/**
 * ggm functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ggm
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function ggm_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on ggm, use a find and replace
		* to change 'ggm' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'ggm', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'ggm' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'ggm_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'ggm_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function ggm_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'ggm_content_width', 640 );
}
add_action( 'after_setup_theme', 'ggm_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function ggm_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'ggm' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'ggm' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'ggm_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function ggm_scripts() {
	wp_enqueue_style( 'ggm-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'ggm-style', 'rtl', 'replace' );

	wp_enqueue_script( 'ggm-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	// =================================================================
	// Final Method: Load Math Wiz Assets on All Pages for Reliability
	// =================================================================
	
	// Enqueue Tailwind CSS from CDN (as a script).
	wp_enqueue_script( 'tailwind-css', 'https://cdn.tailwindcss.com', array(), null, false );

	// Enqueue the Math Wiz script.
	wp_enqueue_script( 'ggm-math-wiz', get_template_directory_uri() . '/js/math-wiz.js', array(), _S_VERSION, true );
	
	// =================================================================

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ggm_scripts' );

// Also hook into the editor to load scripts there for a proper preview.
function ggm_editor_scripts() {
    wp_enqueue_script( 'tailwind-css-editor', 'https://cdn.tailwindcss.com', array(), null, false );
    wp_enqueue_script( 'ggm-math-wiz-editor', get_template_directory_uri() . '/js/math-wiz.js', array(), _S_VERSION, true );
}
add_action( 'enqueue_block_editor_assets', 'ggm_editor_scripts' );

// Also hook into the editor to load scripts there for a proper preview.
add_action( 'enqueue_block_editor_assets', 'ggm_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Block Patterns.
 * =================================================================
 */
require get_template_directory() . '/inc/block-patterns.php';
/**
 * =================================================================
 */



/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}


// Add media namespace to RSS feed
function add_media_namespace_to_rss() {
    echo 'xmlns:media="http://search.yahoo.com/mrss/"';
}
add_action('rss2_ns', 'add_media_namespace_to_rss');

// Add the following only at the end of the theme (ggm in this case)
// Add featured image as media:content element (outside content:encoded)
function add_featured_image_to_rss_item() {
    global $post;
    
    if (has_post_thumbnail($post->ID)) {
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        $thumbnail_url = wp_get_attachment_image_src($thumbnail_id, 'large');
        $thumbnail_meta = wp_get_attachment_metadata($thumbnail_id);
        $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
        
        // Start media:content tag
        echo '<media:content url="' . esc_url($thumbnail_url[0]) . '" type="' . get_post_mime_type($thumbnail_id) . '"';
        
        if (isset($thumbnail_meta['height'])) {
            echo ' height="' . $thumbnail_meta['height'] . '"';
        }
        if (isset($thumbnail_meta['width'])) {
            echo ' width="' . $thumbnail_meta['width'] . '"';
        }
        
        echo '>' . "\n";
        
        // Add description if available
        if ($alt_text) {
            echo '<media:description type="plain">' . "\n";
            echo esc_html($alt_text) . "\n";
            echo '</media:description>' . "\n";
        }
        
        // Add credit if available (you can customize this)
        $credit = get_post_meta($thumbnail_id, '_media_credit', true);
        if ($credit) {
            echo '<media:credit role="author" scheme="urn:ebu">' . esc_html($credit) . '</media:credit>' . "\n";
        }
        
        // Close media:content tag
        echo '</media:content>' . "\n";
    }
}
add_action('rss2_item', 'add_featured_image_to_rss_item');
