<?php get_header(); ?>

<?php
 $stickies = get_option( 'sticky_posts' );
      // Make sure we have stickies
      if (( $stickies ) && is_front_page() && is_home() && !(is_paged())) { ?>

<div id="featuredsection" class="row expanded">
      <div class="column">
        <div class="column"><h5 class="sectionheadline"><?php echo esc_html(richflicks_themeoptions('featuredheadline')); ?></h5></div>
        <div class="large-up-4 medium-up-2 row expanded">
         <?php
              $args = [
                  'post_type'           => 'post',
                  'post__in'            => $stickies,
                  'ignore_sticky_posts' => 1
              ];
              $the_query = new WP_Query($args);

              if ( $the_query->have_posts() ) { 
                  while ( $the_query->have_posts() ) { 
                      $the_query->the_post();

                      get_template_part('loop', get_post_format() );

                  }    
                  wp_reset_postdata();    
              }
         ?>
        </div>
        <?php if (is_active_sidebar( 'featured-widget-area' ) ) : ?>
          <div class="columns">
            <div id="featured-widget-area" class="widget-area" role="complementary">
              <?php dynamic_sidebar( 'featured-widget-area' ); ?>
            </div><!-- .featured-widget-area -->
          </div>
          <?php endif; ?> 
      </div>

</div>

    <?php } // if sticky ?>

<div id="container" class="row expanded">
  <div id="primary" class="columns">

          <?php // List the posts
        if ( have_posts() ) : ?>
          <div class="large-up-4 medium-up-2 row expanded">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'loop', get_post_format() ); ?>
            <?php endwhile; ?>
          </div>
      </div><!-- #primary -->
</div> <!-- #container -->
<div class="row">
    <div class="column">
    <?php // Pagination
          the_posts_pagination( array(
            'mid_size' => 2,
            'prev_text' => '<div class="icon-left-open-big"></div>',
            'next_text' => '<div class="icon-right-open-big"></div>',
          ) ); ?> 
</div>
</div>


        <?php else: ?>
            <div class="row">
               <?php if ( is_search() ) : ?>
                  <div class="large-7 medium-10 columns">
                    <div class="archive-search">
                      <h5><?php _e( 'Nothing matched your search criteria. Please try searching again:', 'richflicks' ); ?></h5>
                      <?php get_search_form(); ?>
                    </div>      
                  </div>
                <?php elseif (is_404() ) : ?>
                  <div class="large-7 medium-8 small-11 small-centered columns">
                    <h3 class="errortitle"><?php _e( '404 - Sorry, nothing was found!', 'richflicks' ); ?></h3>
                  </div>
                <?php else: ?>
                  <div class="large-7 medium-8 small-11 small-centered columns">
                    <h3 class="errortitle"><?php _e( 'Sorry, nothing was found!', 'richflicks' ); ?></h3>
                  </div>
               <?php endif; ?>
            </div> 
  </div><!-- #primary -->       
</div> <!-- #container -->
        <?php endif; ?>

<?php get_footer(); ?>