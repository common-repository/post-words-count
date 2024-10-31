<?php
/*
Plugin Name: Post Words Count
Description: Simple Plugin which counts <strong>Total Post Words</strong> and display the number and <strong>Post Thumbnail</strong> at All Post Section in Dashboard
Author: Zakaria Binsaifullah
Author URI: https://makegutenblock.com
Version: 2.3.1
Text Domain: post-words-count
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// include admin
require_once plugin_dir_path( __FILE__ ) . 'admin/admin.php';

class Post_Words_Count {
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
            add_filter( 'manage_posts_columns', array( $this, 'add_thumbnail_column' ) );
            add_action( 'manage_posts_custom_column', array( $this, 'display_thumbnail_column_data' ), 10, 2 );
            add_filter( 'manage_posts_columns', array( $this, 'add_word_count_column' ) );
            add_action( 'manage_posts_custom_column', array( $this, 'display_word_count_column_data' ), 10, 2 );
            add_action( 'activated_plugin', array( $this, 'redirect_to_support_page' ) );
        }
    }

    /**
     * Load Text Domain
     */
    public function load_text_domain() {
        load_plugin_textdomain( 'post-words-count', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Add Custom Thumbnail Column
     */
    public function add_thumbnail_column( $columns ) {
        $columns['post_thumb'] = __( 'Thumbnail', 'post-words-count' );
        return $columns;
    }

    /**
     * Display Thumbnail Column Data
     */
    public function display_thumbnail_column_data( $column, $post_id ) {
        if ( $column === 'post_thumb' ) {
            $post_thumbnail = get_the_post_thumbnail( $post_id, array( 60, 50 ) );
            if ( ! empty( $post_thumbnail ) ) {
                printf( "<a href='%s' target='_blank'>%s</a>", esc_url( get_the_permalink( $post_id ) ), $post_thumbnail );
            } else {
                echo __( "No thumbnail", "post-words-count" );
            }
        }
    }

    /**
     * Add Custom Word Count Column
     */
    public function add_word_count_column( $columns ) {
        $columns['word_count'] = __( 'Words', 'post-words-count' );
        return $columns;
    }

    /**
     * Display Word Count Column Data
     */
    public function display_word_count_column_data( $column, $post_id ) {
        if ( $column === 'word_count' ) {
            $content    = get_post_field( 'post_content', $post_id );
            $word_count = $this->count_words( strip_tags( $content ) );
            echo esc_html( $word_count );
        }
    }

    /**
     * Count Words in Content
     */
    private function count_words( $content ) {
        $words = preg_split( '/\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY );
        return count( $words );
    }

    /**
     * Redirect to Support Page After Activation
     */
    public function redirect_to_support_page( $plugin ) {
        if ( plugin_basename( __FILE__ ) == $plugin ) {
            wp_safe_redirect( admin_url( 'tools.php?page=post-words-count' ) );
            exit;
        }
    }
}

new Post_Words_Count();

/**
 * SDK Integration
 */
if ( ! function_exists( 'dci_plugin_post_words_counter' ) ) {
    function dci_plugin_post_words_counter() {
        if ( ! class_exists( 'DCI_SDK' ) ) {
            require_once plugin_dir_path( __FILE__ ) . '/dci/start.php';
            wp_register_style('dci-sdk-post_words_counter', plugins_url('dci/assets/css/dci.css', __FILE__), array(), '1.2.1', 'all');
            wp_enqueue_style('dci-sdk-post_words_counter');
        }

        dci_dynamic_init( array(
			'sdk_version'  => '1.2.1',
			'product_id'   => 2,
			'plugin_name'  => 'Post Words Counter',                                            // make simple, must not empty
			'plugin_title' => 'Post Words Counter',                                            // You can describe your plugin title here
			'api_endpoint' => 'https://dashboard.codedivo.com/wp-json/dci/v1/data-insights',
			'slug'         => 'post-words-count',                                             // write 'no-need' if you don't want to use
			'menu'         => array(
				'slug' => 'post-words-count',
			),
			'public_key'          => 'pk_O9vwzVkUcXxaKahVQA75fJXflftW9hb4',
			'is_premium'          => false,
			'popup_notice'        => true,
			'deactivate_feedback' => false,
			'text_domain'         => 'post-words-count',
			'plugin_msg'          => '
				<p> Thank you for using Post Words Counter! </p>
				<p>
					We collect some non-sensitive diagnostic data to help us improve the product.
				</p>
			',
        ) );

		
    }
}
add_action( 'admin_init', 'dci_plugin_post_words_counter' );