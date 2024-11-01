<?php
/**
 * Plugin Name: Storefront Google Fonts
 * Plugin URI: https://atlantisthemes.com
 * Description: Lets you add custom Google Fonts to Storefront WooCommerce Theme.
 * Author: Atlantis Themes
 * Author URI: http://atlantisthemes.com
 * Version: 0.1
 * Text Domain: storefront-google-fonts
 *
 *
 * Storefront Google Fonts is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Storefront Google Fonts is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'SGF_VERSION', '0.1' );

add_action( 'customize_register', 'sgf_storefront_customize_register' );
add_action( 'wp_head', 'sgf_storefront_customizer_head_styles' );
add_action( 'wp_enqueue_scripts', 'sgf_storefront_scripts' );

function sgf_storefront_customize_register( $wp_customize ) {

    /* Google Fonts */
    class SGF_Google_Font_Dropdown_Custom_Control extends WP_Customize_Control{

		private $fonts = false;

	    public function __construct( $manager, $id, $args = array(), $options = array() ) {
	        $this->fonts = $this->get_google_fonts();
	        parent::__construct( $manager, $id, $args );
	    }

	    public function render_content() {
	        ?>
	            <label class="customize_dropdown_input">
	                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
	            	<select id="<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" data-customize-setting-link="<?php echo esc_attr( $this->id ); ?>">
	                    <?php
	                        foreach ( $this->fonts as $k => $v ) {
	                            echo '<option value="'.$v['family'].'" ' . selected( $this->value(), $v['family'], false ) . '>'.$v['family'].'</option>';
	                        }
	                    ?>
	                </select>
	            </label>
	        <?php
	    }

		public function get_google_fonts() {

		        $googleApi = 'https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyBsRGfUH8gFdOh-qTc3I5X4oFqo_nhXsT8';

		        $fontContent = wp_remote_get( $googleApi, array( 'sslverify' => false ) );

		        if ( is_array( $fontContent ) && ! empty( $fontContent['body'] ) ) {
		        	$content = json_decode( $fontContent['body'], true );
		        	set_transient( 'um_google_font_family', $content, 0 );
		        } else {
		        	return false;
		        }

		    return $content['items'];
		}

    }

	// Advance Typography Panel
	$wp_customize->add_panel( 'sgf_advance_typography', array(
		'priority'       => 4,
		'capability'     => 'edit_theme_options',
		'title'          => esc_html__( 'Advance Typography','storefront-google-fonts' ),
	) );


	// Advance Typography : Section - Content Font
	$wp_customize->add_section('sgf_typography_body_fonts', array(
			'title' 			=> esc_html__( 'Content', 'storefront-google-fonts' ),
			'capability' 		=> 'edit_theme_options',
			'priority' 			=> 1,
			'panel' 			=> 'sgf_advance_typography',
		)
	);

	// Advance Typography : Section - Title Font
	$wp_customize->add_section('sgf_typography_title_fonts', array(
			'title' 			=> esc_html__( 'Title', 'storefront-google-fonts' ),
			'capability' 		=> 'edit_theme_options',
			'priority' 			=> 1,
			'panel' 			=> 'sgf_advance_typography',
		)
	);

	// Typography Body Font
    $wp_customize->add_setting( 'sgf_storefront_typography_body_font',
    	array(
        	'default'           => 'Source Sans Pro',
        	'sanitize_callback' => 'sanitize_text_field',
        	'transport' 		=> 'refresh'
        )
    );

	    $wp_customize->add_control( new SGF_Google_Font_Dropdown_Custom_Control( $wp_customize, 'sgf_storefront_typography_body_font',
	    	array(
				'label' 		=> esc_html__( 'Body Font', 'storefront-google-fonts' ),
	        	'section'    	=> 'sgf_typography_body_fonts',
	        	'settings'  	=> 'sgf_storefront_typography_body_font',
	        	'priority'   	=> 2,
	    )));


	// Typography Body Font Size
	$wp_customize->add_setting( 'sgf_storefront_typography_body_font_size', array(
		'default' 			=> '16px',
		'sanitize_callback' => 'esc_attr',
		'transport' 		=> 'postMessage',
	) );

			$wp_customize->add_control( 'sgf_storefront_typography_body_font_size', array(
				'type' 			=> 'text',
				'label' 		=> esc_html__( 'Body Font Size', 'storefront-google-fonts' ),
				'section' 		=> 'sgf_typography_body_fonts',
				'settings' 		=> 'sgf_storefront_typography_body_font_size',
				'priority'   	=> 3,
               	'input_attrs' 	=> array(
            		'placeholder' => __( 'example: 16px', 'storefront-google-fonts' ),
        		)
			) );


	// Typography Title Font
    $wp_customize->add_setting( 'sgf_storefront_typography_title_font',
    	array(
        	'default'           => 'Source Sans Pro',
        	'sanitize_callback' => 'sanitize_text_field',
        	'transport' 		=> 'refresh'
        )
    );

	    $wp_customize->add_control( new SGF_Google_Font_Dropdown_Custom_Control( $wp_customize, 'sgf_storefront_typography_title_font',
	    	array(
				'label' 		=> esc_html__( 'Title Font', 'storefront-google-fonts' ),
	        	'section'    	=> 'sgf_typography_title_fonts',
	        	'settings'   	=> 'sgf_storefront_typography_title_font',
	        	'priority'   	=> 2,
	    )));

}



/**
 * Google Fonts URL
 */
if ( ! function_exists( 'sgf_storefront_fonts_url' ) ) {
    function sgf_storefront_fonts_url() {

        $fonts_url          = '';
        $content_font       = get_theme_mod( 'sgf_storefront_typography_body_font', 'Source Sans Pro' );
        $header_font        = get_theme_mod( 'sgf_storefront_typography_title_font', 'Source Sans Pro' );

        if ( 'off' !== $content_font || 'off' !== $header_font  ) {
            $font_families = array();


            if ( 'off' !== $content_font ) {
                $font_families[] = $content_font . ':300,400,700';
            }

            if ( 'off' !== $header_font ) {
                $font_families[] = $header_font . ':300,400,700';
            }

            $query_args = array(
                'family' => rawurlencode( implode( '|', array_unique( $font_families ) ) ),
            );

            $fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
        }

        return esc_url_raw( $fonts_url );
    }
}



/**
 * Enqueue scripts and styles.
 */
if ( ! function_exists( 'sgf_storefront_scripts' ) ) {
	function sgf_storefront_scripts() {
        wp_enqueue_style( 'sgf-fonts', sgf_storefront_fonts_url(), array(), SGF_VERSION, 'all' );
	}
}



function sgf_storefront_customizer_head_styles() {
?>
<style type="text/css">
body, button, input, textarea{
	font-family: "<?php echo esc_attr( get_theme_mod( 'sgf_storefront_typography_body_font', 'Source Sans Pro' ) );?>",sans-serif;
	font-size:<?php echo esc_attr( get_theme_mod( 'sgf_storefront_typography_body_font_size', '16px' ) );?>;
}

.section-title,
.entry-title{
	font-family: "<?php echo esc_attr( get_theme_mod( 'sgf_storefront_typography_title_font', 'Source Sans Pro' ) );?>",sans-serif;
}

</style>
<?php }
