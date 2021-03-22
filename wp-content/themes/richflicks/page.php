<?php get_header(); ?>

<?php $herofeaturedimage = richflicks_themeoptions('herofeaturedimage'); 
	if ((!empty($herofeaturedimage))  && (has_post_thumbnail( $post->ID ) ) ): ?>       
              <div id="herofeaturedimage" class="coverimage">
              	<?php the_post_thumbnail( 'richflicks_single-post-cover' );?>
              </div>
<?php endif; ?>

<div id="container" class="row">
	<div id="primary" class="large-7 medium-9 small-11 small-centered columns">
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
			<?php
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
			endwhile;
			?>
	    </article>
	</div><!-- #primary -->           

</div> <!-- #container -->

<?php get_footer(); ?>