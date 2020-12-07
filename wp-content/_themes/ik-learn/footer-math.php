			<footer class="footer footer-math" itemscope itemtype="http://schema.org/WPFooter">

				<div class="container">
					<div class="row">
						<div class="col-sm-12">
							<div class="copyright">
								<p><?php _e('Copyright &copy; Merriam-Webster & Innovative Knowledge.. All rights reserved.', 'iii-dictionary') ?></p>

								<div class="divider"></div>
								<a href="<?php echo home_url(); ?>/?r=about-us" rel="nofollow" title="Innovative Knowledge">
									<img src="<?php echo get_template_directory_uri(); ?>/library/images/ik-logo.png" alt="">
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
