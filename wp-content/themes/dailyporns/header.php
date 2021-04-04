<!doctype html>
<html <?php language_attributes(); ?> class="no-js" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
     <?php wp_head(); ?>
  </head>

  <body <?php body_class(); ?> itemscope="itemscope" itemtype="http://schema.org/WebPage">

  <header id="top-menu" class="top-bar" itemscope="itemscope">

    <div class="menu-container-mobile" data-responsive-toggle="menu-container" data-hide-for="large">
        <button class="icon-menu" type="button"  data-toggle></button>
    </div>
    
    <div class="topbar-title title-logo" itemscope="itemscope" itemtype="http://schema.org/WPHeader" role="banner">
          <?php if (function_exists( 'the_custom_logo' ) && has_custom_logo()) { 
              the_custom_logo();
          } else { ?> 
          <a href="<?php echo esc_url(home_url( '/' )); ?>" title="<?php bloginfo( 'name', 'display' );?> - <?php bloginfo( 'description' ); ?>">
            <h1 id="sitetitle" class="icon-play-circled2"> <?php bloginfo( 'name', 'display' );?></h1>
          </a>
        <?php } ?>  
    </div>

    <div id="menu-container" class="menu-container">
      <?php
          wp_nav_menu(array(
              'container' => false,
              'menu' => __( 'Primary Menu', 'richflicks' ),
              'menu_class' => 'dropdown menu',
              'theme_location' => 'primary',
              'items_wrap'      => '<nav class="richprimarymenu" itemtype="http://schema.org/SiteNavigationElement" role="navigation"><ul id="%1$s" class="vertical large-horizontal menu" data-responsive-menu="accordion large-dropdown">%3$s</ul></nav>',
              'fallback_cb' => 'false',
              'walker' => new richflicks_F6_TOPBAR_MENU_WALKER(),
          ));
      ?>
     
      <ul id="iconmenu" class="menu richiconmenu">  
          <?php wp_nav_menu( array( 'container' => false, 'fallback_cb' => false, 'items_wrap' => '%3$s', 'menu_id' => 'iconmenu', 'menu_class' => '', 'theme_location' => 'iconmenu', 'depth' => -1 ) ); ?>
                    
          <li id="searchicon" class="icon-search menu-item">
            <a>
              <?php _e( 'Search', 'richflicks' ); ?>
            </a>
            
          </li>
      </ul>
    </div>

  </header>

<div id="searchwrap">
  <div class= "row">
    <div class="columns">
      <?php get_search_form(); ?>
    </div>
  </div>
</div>

<?php if ( is_active_sidebar( 'top-widget-area' ) ) : ?>
    <div id="top-widget-area" class="widget-area" role="complementary">
      <?php dynamic_sidebar( 'top-widget-area' ); ?>
    </div><!-- .top-widget-area -->
<?php endif; ?> 