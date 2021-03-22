<?php /* Template Name: Full Width */ ?>
    <?php get_header(); ?>
    
    <div id="container" class="row">
        <div id="primary" class="small-12 columns">
          	<article <?php post_class('articlebox'); ?>>
        	<?php	
        		while ( have_posts() ) : the_post(); ?>
        
                	<header class="entry-header entry-header-single">
                        <h1 class="entry-title">
                            <?php the_title(); ?>
                        </h1>
                    </header>
        			<div class="entry-content">
                        <?php the_content(); ?>
                    </div><!-- .entry-content -->
            </article>
        </div><!-- #primary -->           
    </div> <!-- #container -->
    <div class="row">
        <div class="large-7 medium-8 small-11 small-centered columns">
        <?php 
            if ( comments_open() || get_comments_number() ) :
                    comments_template();
            endif;
            endwhile; 
            ?>
        </div>
    </div>
    <?php get_footer(); ?>