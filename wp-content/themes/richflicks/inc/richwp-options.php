<?php
// Options Page

	if ( ! function_exists( 'richflicks' ) ) :

		function richflicks_themeoptions( $name ) {
			$default_theme_options = array(
				'logo' => '',
				'colorhd' => '#242424',
				'colorhdfont' => '#bbbbbb',
				'colorhdfonthover' => '#ec5379',
				'color1' => '#ec5379',
				'colorfontbuttons' => '#FFFFFF',
				'displayviewcount' => '1',
				'displayrelatedposts' => '1',
				'featuredheadline' => 'Videos Recommended To You',
				'frontpageheadline' => 'New & Noteworthy',
				'relatedheadline' => 'Up Next',
				'copyright' =>  get_bloginfo( 'name' ),
				);
		
			$options = wp_parse_args( get_option( 'richflicks' ), $default_theme_options );

			return $options[$name];
		}
	endif;

add_action( 'customize_register', 'richflicks_customize_register' );
function richflicks_customize_register( $wp_customize ) {


	$wp_customize->add_section( 'richflicks_colors', array(
		'title' => __( 'Colors', 'richflicks' ),
		'priority' => 100,
	) );
	
	$wp_customize->add_setting( 'richflicks[colorhd]', array(
		'default' => richflicks_themeoptions( 'colorhd' ),
		'sanitize_callback' => 'sanitize_hex_color',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );
		
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'colorhd', array(
		'label'   => __( 'Header Color', 'richflicks' ),
		'section' => 'richflicks_colors',
		'settings'   => 'richflicks[colorhd]',
		'priority' => 5,
	) ) );
	
	$wp_customize->add_setting( 'richflicks[colorhdfont]', array(
		'default' => richflicks_themeoptions( 'colorhdfont' ),
		'sanitize_callback' => 'sanitize_hex_color',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );
		
		
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'colorhdfont', array(
		'label'   => __( 'Header Font Color', 'richflicks' ),
		'section' => 'richflicks_colors',
		'settings'   => 'richflicks[colorhdfont]',
		'priority' => 10,
	) ) );

	$wp_customize->add_setting( 'richflicks[colorhdfonthover]', array(
		'default' => richflicks_themeoptions( 'colorhdfonthover' ),
		'sanitize_callback' => 'sanitize_hex_color',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );
		
		
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'colorhdfonthover', array(
		'label'   => __( 'Header Font Hover Color', 'richflicks' ),
		'section' => 'richflicks_colors',
		'settings'   => 'richflicks[colorhdfonthover]',
		'priority' => 10,
	) ) );

	$wp_customize->add_setting( 'richflicks[color1]', array(
		'default' => richflicks_themeoptions( 'color1' ),
		'sanitize_callback' => 'sanitize_hex_color',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );
		
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'color1', array(
		'label'   => __( 'Lead Color', 'richflicks' ),
		'section' => 'richflicks_colors',
		'settings'   => 'richflicks[color1]',
		'priority' => 20,
	) ) );	
	
	$wp_customize->add_setting( 'richflicks[colorfontbuttons]', array(
		'default' => richflicks_themeoptions( 'colorfontbuttons' ),
		'sanitize_callback' => 'sanitize_hex_color',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );
		
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'colorfontbuttons', array(
		'label'   => __( 'Button Font Color', 'richflicks' ),
		'section' => 'richflicks_colors',
		'settings'   => 'richflicks[colorfontbuttons]',
		'priority' => 40,
	) ) );

    $wp_customize->add_section( 'richflicks_misc', array(
		'title' => __( 'Misc.', 'richflicks' ),
		'priority' => 120,
	) );

	$wp_customize->add_setting( 'richflicks[displayrelatedposts]', array(
		'default' => richflicks_themeoptions( 'displayrelatedposts' ),
		'sanitize_callback' => 'richflicks_sanitize_checkbox',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );
	
	$wp_customize->add_control( 'richflicks[displayrelatedposts]', array(
		'settings' => 'richflicks[displayrelatedposts]',
		'label'    => __( 'Display Related Posts', 'richflicks' ),
		'section'  => 'richflicks_misc',
		'type'     => 'checkbox',
		'priority' => 20,
	) );

	$wp_customize->add_setting( 'richflicks[relatedheadline]', array(
		'default' => richflicks_themeoptions( 'relatedheadline' ),
		'sanitize_callback' => 'richflicks_sanitize_text_html',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );

	$wp_customize->add_control( 'relatedheadline', array(
		'label' => __( 'Related Posts Headline', 'richflicks' ),
		'section' => 'richflicks_misc',
		'settings' => 'richflicks[copyright]',
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'richflicks[featuredheadline]', array(
		'default' => richflicks_themeoptions( 'featuredheadline' ),
		'sanitize_callback' => 'richflicks_sanitize_text_html',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );

	$wp_customize->add_control( 'featuredheadline', array(
		'label' => __( 'Featured Section Headline', 'richflicks' ),
		'section' => 'richflicks_misc',
		'settings' => 'richflicks[featuredheadline]',
		'priority' => 40,
	) );

	$wp_customize->add_setting( 'richflicks[frontpageheadline]', array(
		'default' => richflicks_themeoptions( 'frontpageheadline' ),
		'sanitize_callback' => 'richflicks_sanitize_text_html',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );

	$wp_customize->add_control( 'frontpageheadline', array(
		'label' => __( 'Postlist Headline on Front Page', 'richflicks' ),
		'section' => 'richflicks_misc',
		'settings' => 'richflicks[frontpageheadline]',
		'priority' => 50,
	) );
	
	$wp_customize->add_setting( 'richflicks[copyright]', array(
		'default' => richflicks_themeoptions( 'copyright' ),
		'sanitize_callback' => 'richflicks_sanitize_text_html',
		'type' => 'option',
		'capability' => 'edit_theme_options',
	) );

	$wp_customize->add_control( 'copyright', array(
		'label' => __( 'Copyright Notice in Footer', 'richflicks' ),
		'section' => 'richflicks_misc',
		'settings' => 'richflicks[copyright]',
		'priority' => 60,
	) );
	
} 

function richflicks_sanitize_text_html( $input ) {
    return wp_kses_post( force_balance_tags( $input ) );
}

function richflicks_sanitize_checkbox( $input ) {
    if ( $input == 1 ) {
        return 1;
    } else {
        return '';
    }
}

/* Add CSS */
function richflicks_add_styles() {
  if ( ! function_exists( 'get_richicon_font' ) ) {
    $richicon_font = array(
        'base' => get_template_directory_uri()."/font/richicons",
        'version' => '13499119');
  } else {
    $richicon_font = get_richicon_font();
  }
 ?>
<style type="text/css">
@font-face {
  font-family: 'richicons';
  src: url('<?php echo $richicon_font['base'].".eot?".$richicon_font['version']; ?>');
  src: url('<?php echo $richicon_font['base'].".eot?".$richicon_font['version']."#iefix"; ?>') format('embedded-opentype'),
    url('<?php echo $richicon_font['base'].".woff?".$richicon_font['version']; ?>') format('woff'),
    url('<?php echo $richicon_font['base'].".ttf?".$richicon_font['version']; ?>') format('truetype'),
    url('<?php echo $richicon_font['base'].".svg?".$richicon_font['version']."#richicons"; ?>') format('svg');
    font-weight: normal;
    font-style: normal;
  }

#top-menu,
.top-bar ul ul,
ul.submenu {
	background-color:<?php echo esc_attr( richflicks_themeoptions('colorhd'));?>;
}

<?php if (richflicks_themeoptions('colorhd') == "#ffffff") { ?>.top-bar ul ul, .menushop .is-dropdown-submenu a:hover {background: #f5f5f5;}<?php } ?>

a #sitetitle,
.top-bar a,
.icon-menu,
#iconmenu li:before,
.top-bar ul.submenu a,
.menushop .is-dropdown-submenu a{
	color:<?php echo esc_attr( richflicks_themeoptions('colorhdfont'));?>;
}



.top-bar a:hover,
.top-bar .current-menu-item a,
.top-bar ul.submenu a:hover,
#iconmenu li:hover:before,
.menushop .is-dropdown-submenu a:hover{
	color:<?php echo esc_attr( richflicks_themeoptions('colorhdfonthover'));?>;
}

a,
a:hover,
.postbox a:hover .entry-title,
.pagination .current,
.pagination .page-numbers:hover,
#copyright a:hover,
#footermenu a:hover,
#footer-widget-area a:hover, 
#top-widget-area a:hover,
.pagination .prev:hover, 
.pagination .next:hover,
.comment-metadata a:hover, 
.fn a:hover
<?php if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_woocommerce() || is_cart() ||  is_checkout() ) { ?>						
		,.woocommerce a.added_to_cart:before, .woocommerce .woocommerce-info::before<?php } }?>
	{
	color:<?php echo esc_attr( richflicks_themeoptions('color1'));?>;
}
.none
<?php if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_woocommerce() || is_cart() ||  is_checkout() ) { ?>, 
		.woocommerce-cart .wc-proceed-to-checkout a.checkout-button,
		.woocommerce #payment #place_order,
		.woocommerce-page #payment #place_order,
		.woocommerce #respond input#submit.alt:hover,
		.woocommerce button.button,
		.woocommerce button.button:hover,
		.woocommerce a.button.alt:hover,
		.woocommerce button.button.alt:hover,
		.woocommerce input.button.alt:hover,
		.woocommerce input.button,
		.woocommerce-cart a.button,
		.woocommerce-cart a.button:hover,
		.add_to_cart_button:hover,
		.woocommerce #respond input#submit.alt,
		.woocommerce a.button.alt,
		.woocommerce button.button.alt,
		.woocommerce input.button.alt<?php } } ?>
	{
	background:<?php echo esc_attr( richflicks_themeoptions('color1'));?>;
}
.button,
.button:hover, 
.button:focus,
.add_to_cart_button:hover,
.add_to_cart_button:focus
<?php if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_woocommerce() || is_cart() ||  is_checkout() ) { ?>,
		.woocommerce ul.products li.product .button,
		.woocommerce input.button:hover,
		.woocommerce span.onsale<?php } } ?>
{
	background-color:<?php echo esc_attr( richflicks_themeoptions('color1'));?>;
	color: <?php echo esc_attr( richflicks_themeoptions('colorfontbuttons'));?>;
}

.sectionheadline  {
	border-color:<?php echo esc_attr( richflicks_themeoptions('color1'));?>
}

<?php if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_woocommerce() || is_cart() ||  is_checkout() ) { ?>
		
			.woocommerce .woocommerce-info{
				border-top-color: <?php echo esc_attr( richflicks_themeoptions('color1'));?>;
			} 
<?php 	} } ?>
.entry-content a.more-link,
.button,
.add_to_cart_button
<?php if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_woocommerce() || is_cart() ||  is_checkout() ) { ?>
		.woocommerce ul.products li.product .button,
		.woocommerce input.button,
		.woocommerce input.button:hover,
		.woocommerce button.button,
		.woocommerce button.button:hover,
		.woocommerce-cart a.button,
		.woocommerce-cart a.button:hover,
		.woocommerce span.onsale<?php } } ?>
	{
	color:<?php echo esc_attr( richflicks_themeoptions('colorfontbuttons'));?>;
}
</style>
<?php } add_action('wp_head', 'richflicks_add_styles'); ?>