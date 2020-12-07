			<footer class="footer" itemscope itemtype="http://schema.org/WPFooter">

				<div class="container">
					<div class="row">
						<div class="col-sm-12 col-md-12 col-lg-12">
							<div class="copyright">
								<p><?php _e('Disctionaries : Copy right by Merriam Webster, All right reserved.', 'iii-dictionary') ?><br>
								<?php _e('Software and graphics : Copy right by Innovative Knowldge, Inc. All right reserved.', 'iii-dictionary') ?></p>
								
								<div class="divider"></div>
								<a href="<?php echo site_home_url(); ?>?r=about-us" rel="nofollow" title="Innovative Knowledge">
									<img style="height: 19px; width: 92px;" src="<?php echo get_template_directory_uri(); ?>/library/images/ik_logo_at_bottom.png" alt="">
								</a>
							</div>
						</div>
					</div>
				</div>

			</footer>

		</div>

		<?php add_action('wp_footer', 'print_js_messages') ?>
		<?php wp_footer(); ?>

	</body>

</html> <!-- end of site. what a ride! -->
