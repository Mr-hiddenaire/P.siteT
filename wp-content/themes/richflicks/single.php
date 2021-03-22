<?php get_header(); ?>

<div id="container" class="row">
  <div id="primary" class="medium-10 small-11 small-centered columns">
  	<article <?php post_class('articlebox'); ?>>
	<?php	
		while ( have_posts() ) : the_post(); ?>
			<header class="entry-header entry-header-single">
				<h1 class="entry-title">
					<?php the_title(); ?>
				</h1>
			</header>
			<div class="entry-content">
				<div id="articleimageonsingle"><?php the_post_thumbnail();?></div>
				<?php the_content();?>
			</div><!-- .entry-content -->

			<div class="entry-meta cat-and-tags">
				<?php 
				 	// View Counter Integration: Exchange the line below if you are using a different plug in than WP-PostViews Lester 'GaMerZ' Chan. echo('<span class="icon-archive"> </span>'); will show the eye icon and echo(' &middot; '); the separating dot between the view count and the date.
					if(function_exists('the_views')) { echo('<span class="icon-views"> </span>') ; the_views(); echo(' &middot; ') ;}
				 	echo richflicks_date(); 
				?>
				<div id="categories"><span class="icon-archive"></span> <p><?php the_category( ', ' ); ?></p></div>
				<?php if(get_the_tag_list()) : ?>
					<div id="tags"><span class="icon-tags"></span> <p><?php echo get_the_tag_list('',', ','');?></p></div>
				<?php endif; ?>
    		</div>
	</article>

    </div><!-- #primary -->


	<?php 
	// Related Posts
    $displayrelatedposts = richflicks_themeoptions('displayrelatedposts'); 
    	if(!empty($displayrelatedposts)) : 
    		get_template_part( 'single-related' ); 
    endif;

	// Comments
		if ( comments_open() || get_comments_number() ) :
		comments_template();
		endif;
	endwhile; 
	?>
		    

</div> <!-- #container -->

<?php get_footer(); ?>