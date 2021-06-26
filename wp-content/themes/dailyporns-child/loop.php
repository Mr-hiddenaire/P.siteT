	<article id="post-<?php the_ID(); ?>" <?php post_class('column postbox'); ?> >
		<div class="postboxinner">
			<a href="<?php the_permalink() ?>" rel="bookmark">
				<?php if (has_post_thumbnail( $post->ID ) ): ?>
					<div class="postlistthumb">
						<div class="postlistthumbwrap">
							<?php the_post_thumbnail( 'post-thumbnail' );?>
							<div class="viewpostdisplay">
								<video class="vp" data-src="<?php echo get_post_meta($post->ID, 'preview', true); ?>"></video>
							</div>
						</div>
						<div class="duration"><span class="quality"><?php echo get_post_meta($post->ID, 'quality', true);?></span><var class="time"><?php echo get_post_meta($post->ID, 'duration', true);?></var></div>
					</div>
				<?php endif;?>
			
					<header class="entry-header">
						<h5 class="entry-title">
							<?php the_title();?>
						</h5>
					</header>
					 <?php
					 if ("page" != get_post_type()):
					     echo('<div class="postbox-entry-meta">');
					     // view
					     echo('<span class="icon-views"> </span>');
					     echo gt_get_post_view();
                         echo('</div>');
					 endif; ?>
			</a>
		</div>
	</article>