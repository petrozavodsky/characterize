<?php
/*
Plugin Name: Characterize
Plugin URI: https://themefoundation.com/
Description: Easily add code to your post and page content.
Version: 0.1.0
Author: ModularWP
Author URI: https://themefoundation.com/
License: GPLv2 or later
Text Domain: characterize_textdomain
*/


/**
 * Class dtbaker_Shortcode_Banner
 * handles the creation of [boutique_banner] shortcode
 * adds a button in MCE editor allowing easy creation of shortcode
 * creates a wordpress view representing this shortcode in the editor
 * edit/delete button on wp view as well makes for easy shortcode managements.
 *
 * separate css is in style.content.css - this is loaded in frontend and also backend with add_editor_style
 *
 * Author: dtbaker@gmail.com
 * Copyright 2014
 */

class dtbaker_Shortcode_Banner {
    private static $instance = null;
    public static function get_instance() {
        if ( ! self::$instance )
            self::$instance = new self;
        return self::$instance;
    }

	public function init(){
		// comment this 'add_action' out to disable shortcode backend mce view feature
		add_action( 'admin_init', array( $this, 'init_plugin' ), 20 );
        // add_shortcode( 'boutique_banner', array( $this, 'dtbaker_shortcode_banners' ) );
        add_shortcode( 'boutique_banner', '__return_false' );
		add_filter( 'the_content', array( $this, 'foobar_run_shortcode' ), 7 );
	}

	public function init_plugin() {
		//
		// This plugin is a back-end admin ehancement for posts and pages
		//
    	if ( current_user_can('edit_posts') || current_user_can('edit_pages') ) {
			add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'wp_ajax_dtbaker_mce_banner_button', array( $this, 'wp_ajax_dtbaker_mce_banner_button' ) );
			add_filter("mce_external_plugins", array($this, 'mce_plugin'));
			add_filter("mce_buttons", array($this, 'mce_button'));
		}
    }

    // Actual processing of the shortcode happens here
	public function foobar_run_shortcode( $content ) {
	    global $shortcode_tags;

	    // Backup current registered shortcodes and clear them all out
	    $orig_shortcode_tags = $shortcode_tags;
	    remove_all_shortcodes();

	    add_shortcode( 'boutique_banner',  array( $this, 'dtbaker_shortcode_banners' ) );

	    // Do the shortcode (only the one above is registered)
	    $content = do_shortcode( $content );

	    // Put the original shortcodes back
	    $shortcode_tags = $orig_shortcode_tags;

	    return $content;
	}

	// front end shortcode displaying:
	public function dtbaker_shortcode_banners($atts=array(), $innercontent='', $code='') {
	    $sc_atts = shortcode_atts(
    		array(
        		'id' => false,
        		'title' => 'Special:',
        		'language' => '',
        		'characters' => '',
    		),
    		$atts
	    );

	    $sc_atts['innercontent'] = $innercontent; // lets put everything in the view-data object
	    $sc_atts = (object) $sc_atts;

		// Use Output Buffering feature to have PHP use it's own enging for templating
	    ob_start();
	    include dirname(__FILE__).'/views/dtbaker_shortcode_banner_view.php';
	    return ob_get_clean();
	}

	public function mce_plugin($plugin_array){
		$plugin_array['dtbaker_mce_banner'] = plugins_url( 'js/mce-button-boutique-banner-inline.js', __FILE__ );
		return $plugin_array;
	}
	public function mce_button($buttons){
        array_push($buttons, 'dtbaker_mce_banner_button');
		return $buttons;
	}
    /**
     * Outputs the view inside the wordpress editor.
     */
    public function print_media_templates() {
        if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' )
            return;
        include_once dirname(__FILE__).'/templates/tmpl-editor-boutique-banner.html';
    }
    public function admin_head() {
		$current_screen = get_current_screen();
		if ( ! isset( $current_screen->id ) || $current_screen->base !== 'post' ) {
			return;
		}

		wp_enqueue_script( 'boutique-banner-editor-view', plugins_url( 'js/boutique-banner-editor-view.js', __FILE__ ), array( 'shortcode', 'wp-util', 'jquery' ), false, true );
    }
}

dtbaker_Shortcode_Banner::get_instance()->init();


/**
 * Adds a meta box to the post editing screen
 */
function prfx_custom_meta() {
    add_meta_box( 'prfx_meta', __( 'Meta Box Title', 'prfx-textdomain' ), 'prfx_meta_callback', 'post' );
}
add_action( 'add_meta_boxes', 'prfx_custom_meta' );

/**
 * Outputs the content of the meta box
 */
function prfx_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
    $prfx_stored_meta = get_post_meta( $post->ID );
    ?>

    <p>
        <label for="meta-text" class="prfx-row-title"><?php _e( 'Example Text Input', 'prfx-textdomain' )?></label>
        <textarea name="meta-text" id="characterize"><?php if ( isset ( $prfx_stored_meta['meta-text'] ) ) echo $prfx_stored_meta['meta-text'][0]; ?></textarea>
    </p>

    <?php
}

/**
 * Saves the custom meta input
 */
function prfx_meta_save( $post_id ) {

    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'prfx_nonce' ] ) && wp_verify_nonce( $_POST[ 'prfx_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'meta-text' ] ) ) {
        update_post_meta( $post_id, 'meta-text', wp_kses_post( $_POST[ 'meta-text' ] ) );
    }

}
add_action( 'save_post', 'prfx_meta_save' );