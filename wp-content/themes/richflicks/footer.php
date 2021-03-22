	<footer id="site-footer" >
		<?php if (is_active_sidebar( 'footer-widget-area' ) ) :	?>
		<div id="footer-widget-area" class="widget-area footer-widget-area">
			<div class="row">
				<div class="large-up-4 medium-up-2 column">
							<?php dynamic_sidebar( 'footer-widget-area' ); ?>
				</div>
			</div>
		</div><!-- .footer-widget-areas -->
		<?php endif; ?>

		<div id="copyright" class="row">
			<div class="columns">
				<?php echo ('&copy;&nbsp;'); 
					  echo date_i18n( esc_html__( 'Y', 'richflicks' ) ); 
					  echo ('&nbsp;'); ?>
					  <a href="<?php echo esc_url(home_url( '/' )); ?>" title="<?php bloginfo( 'name', 'display' );?> - <?php bloginfo( 'description' ); ?>">
					  <?php echo esc_html(richflicks_themeoptions('copyright')); ?>
					  </a>
			</div>
		</div><!-- #copyright -->
		
		<?php if ( has_nav_menu( 'footer' ) ) : ?> 
			<div id="footermenu" class="row">
				<div class="columns">
					<?php wp_nav_menu( array('menu_id' => 'footer-navigation', 'fallback_cb' => false, 'depth' => -1, 'container_class' => 'menu 	footernav', 'theme_location' => 'footer' ) ); ?>
				</div>
			</div><!-- #footernav -->
		<?php endif; ?>
		
		<?php if ( is_active_sidebar( 'bottom-widget-area' ) ) : ?>
	      <div id="bottom-widget-area" class="widget-area" role="complementary">
	        <?php dynamic_sidebar( 'bottom-widget-area' ); ?>
	      </div><!-- .bottom-widget-area -->
	    <?php endif; ?> 
	    	
	</footer>

	<?php wp_footer(); ?>

  </body>
</html>