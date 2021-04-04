<div id="author-info" class="columns row callout"> 
		    <div class="small-2 columns">
		        <?php $author_bio_avatar_size = apply_filters( 'richwp_author_bio_avatar_size', 120 );
		        echo get_avatar( get_the_author_meta( 'user_email' ), $author_bio_avatar_size ); ?>
		    </div>
		    <div class="small-10 columns">
		        <h5 class="author-title"><?php echo get_the_author(); ?></h5>
		        <?php the_author_meta( 'description' ); ?>
		    </div>
</div><!-- #author-info -->