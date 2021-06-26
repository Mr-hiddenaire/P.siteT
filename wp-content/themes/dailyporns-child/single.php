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
				<video-js id=playerId class="vjs-default-skin"></video-js>
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
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
    	endwhile; 
	?>
</div> <!-- #container -->
<script>
jQuery("form").attr("novalidate", true);
var player = videojs("playerId", {
	fluid: true,
	controls: true,
	autoplay: false,
	poster: "<?php echo wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full', false )[0] ?? '';?>"
});
player.src({
src: "<?php echo get_post_meta($post->ID, 'm3u8', true);?>",
type: "application/x-mpegURL",
withCredentials: true
});
player.play();
</script>
<?php get_footer(); ?>