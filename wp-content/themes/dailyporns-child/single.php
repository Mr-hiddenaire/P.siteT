<?php get_header(); ?>
<?php gt_set_post_view(); ?>
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
				// view
				echo('<span class="icon-views"> </span>');
				echo gt_get_post_view();
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
	endwhile; 
	?>
</div> <!-- #container -->

<?php get_footer(); ?>