	<article id="post-<?php the_ID(); ?>" <?php post_class('column postbox'); ?> >
		<div class="postboxinner">
			<a href="<?php the_permalink() ?>" rel="bookmark">
				<?php if (has_post_thumbnail( $post->ID ) ): ?>
					<div class="postlistthumb">
						<div class="postlistthumbwrap">
							<?php the_post_thumbnail( 'post-thumbnail' );?>
							<div class="viewpostdisplay">
								<video loop autoplay src="<?php echo get_post_meta($post->ID, 'video-thumbnail', true); ?>"></video>
							</div>
						</div>
					</div>
				<?php endif;?>
			
					<header class="entry-header">
						<h5 class="entry-title">
							<?php the_title();?>
						</h5>
					</header>
					 <?php if ("page" != get_post_type()) : 
					 	echo('<div class="postbox-entry-meta">');

					 	// View Counter Integration: Exchange the line below if you are using a different plug in than WP-PostViews Lester 'GaMerZ' Chan. echo('<span class="icon-archive"> </span>'); will show the eye icon and echo(' &middot; '); the separating dot between the view count and the date.
					 	if(function_exists('the_views')) { echo('<span class="icon-views"> </span>') ; the_views(); echo(' &middot; ') ;}
					 	
					 	echo richflicks_date(); 
					 	echo('</div>');
					 	   endif; ?>
					
			</a>
		</div>
	</article>