<?php

if ( post_password_required() ) {
	return;
}
?>
<div class="row">
    <div class="large-7 medium-8 small-11 small-centered columns">
		<div id="comments" class="comments-area">

			<?php if ( have_comments() ) : ?>
				<h2 class="comments-title">
					<?php
						printf( _nx( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'richflicks' ),
							number_format_i18n( get_comments_number() ), get_the_title() );
					?>
				</h2>


				<ol class="comment-list">
					<?php
						wp_list_comments( array(
							'style'       => 'ol',
							'short_ping'  => true,
							'avatar_size' => 46,
						) );
					?>
				</ol><!-- .comment-list -->

				<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
			?>
			<nav class="navigation comment-navigation">
				<div class="nav-links">
					<?php
						if ( $prev_link = get_previous_comments_link( __( '&larr; Older Comments', 'richflicks' ) ) ) :
							printf( '<div class="nav-previous">%s</div>', $prev_link );
						endif;

						if ( $next_link = get_next_comments_link( __( 'Newer Comments &rarr;', 'richflicks' ) ) ) :
							printf( '<div class="nav-next">%s</div>', $next_link );
						endif;
					?>
				</div><!-- .nav-links -->
			</nav><!-- .comment-navigation -->
			<?php endif; ?>

			<?php endif; // have_comments() ?>

			<?php
				// If comments are closed and there are comments, leave a note
				if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
			?>
				<p class="no-comments"><?php _e( 'Comments are closed.', 'richflicks' ); ?></p>
			<?php endif; ?>

			<?php comment_form(array('comment_notes_after' => '', 
			'submit_button' => '<input name="%1$s" type="submit" id="%2$s" class="%3$s button" value="%4$s" />'
			)); ?>

		</div><!-- .comments-area -->
	</div>
</div>
