<?php $user = wp_get_current_user() ?>

<div id="sideSlideMenu" class="sideSlideSlideNegative">
	<?php if ($settings['logourl']) { ?>
		<img src="<?php echo $settings['logourl']; ?>"/>
	<?php }; ?>	
	<div class="user-info">
		<?php echo get_avatar($user->ID, 64) ?> 
		<div>Hello, <br>
			<?php echo $user->display_name ?>
		</div>
	</div>

	<ul id="sideSlideUl">

	<?php
	$count = 0;
	$submenu = false;
	$icons = explode(',',  $settings['icons']);
		foreach( $menuitems as $item ):

	$parts = explode('/?r=', $item->url);
	$user_admin_cap = get_user_meta($user->ID, 'ik_admin_capabilities', true);

	if(@$user_admin_cap[$parts[1]] || is_mw_super_admin()) :

			// get page id from using menu item object id
			$id = get_post_meta( $item->ID, '_menu_item_object_id', true );
			// set up a page object to retrieve page data
			//$page = get_page( $id );
			//$link = get_page_link( $id );

			// item does not have a parent so menu_item_parent equals 0 (false)
			if ( !$item->menu_item_parent ):

			// save this id for later comparison with sub-menu items
			$parent_id = $item->ID;
		?>

		<li class="<?php if ($count == 0) echo 'ssmFCli'; ?>" >
			<a href="<?php echo $item->url; ?>" class="title">
				<span><i class="fa fa <?php echo $icons[$count]; ?>"></i><?php echo $item->title; ?></span> 
			</a>

		<?php endif; ?>

			<?php if ( $parent_id == $item->menu_item_parent ): ?>

				<?php if ( !$submenu ): $submenu = true; ?>
				<div class="ssmenuSubmenuToggle"><i class="fa fa-plus-square-o"></i></div><ul class="ssmenu-submenu">
				<?php endif; ?>

					<li>
						<a href="<?php echo $item->url; ?>" class="title"><i class="fa <?php echo $icons[$count]; ?>"></i>&#172; <?php echo $item->title; ?></a>
					</li>

				<?php if ( $menuitems[ $count + 1 ]->menu_item_parent != $parent_id && $submenu ): ?>
				</ul>
				<?php $submenu = false; endif; ?>

			<?php endif; ?>

		<?php if ( isset ($menuitems[ $count + 1 ]) && $menuitems[ $count + 1 ]->menu_item_parent != $parent_id ): ?>
		</li>                           
		<?php $submenu = false; endif; ?>

		<?php $count++; ?>

	<?php endif ?>

		<?php endforeach; ?>
	</ul>
	<div id="sideSlideFooter">
		<?php echo $settings['footertxt'] ?>
	</div>
</div>
<div id="sideSlideToggle" class="sideSlideSlideToggleNegative"><i class="fa fa-chevron-circle-right"></i></div>