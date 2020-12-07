<div class="wrap">
	<h2>Side Slide Menu Settings</h2>

	<form action="" method="post">
	<table class="form-table">
		<tr>
			<th scope="row">Select menu to use in Side Slide</td>
			<td>
				<select name="ssMenuID" id="ssMenuID">
					<option value="-1" <?php if ($settings['menu_id'] == '-1') echo 'selected'; ?>>No Menu</option>
					<?php 
						foreach ($existingMenus as $menu) {
							$slctd = '';
							if ($settings['menu_id'] == $menu->term_id)
								$slctd = 'selected';

							echo '<option value="'.$menu->term_id.'" '.$slctd.'>'. $menu->name .' (ID: '.$menu->term_id.')</option>';	
						}
					?>
				</select>
				<p class="description">Select which of your existing menus would you like to use with Side Slide.<br/> 
				If you don't have any menus <a href="<?php echo admin_url('nav-menus.php'); ?>">go here first and create some</a>.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">Logo</td>
			<td>
				<input type="text" class="regular-text" name="ssLogoURL" value="<?php echo $settings['logourl']; ?>">
				<p class="description">Enter full path to your logo image if you want to show it in Side Slide menu. Leave blank if empty.<br/>
				Example: http://www.example.com/images/mylogo.jpg</p>
			</td>
		</tr>
		<tr>
		<?php ?>
			<th scope="row">Menu Item Icons</td>
			<td>
				<input type="hidden" name="ssmAllIconsString" id="ssmAllIconsString" value="<?php echo $settings['icons']; ?>">
				<div id="ssmiconWrapper"><?php 
					
					if ($settings['menu_id'] == '-1') 
						echo '<strong>First select menu to use from the list above.</strong>';
					else {
						$icons = explode(',',  $settings['icons']);
						$cnt = 0;
						$mitems = wp_get_nav_menu_items( $settings['menu_id'] );
						foreach ( (array) $mitems as $key => $menu_item ) {
						    $title = $menu_item->title;
						    $url = $menu_item->url;
						    echo '<input placeholder="Font Awesome code..." style="width: 150px !important;" type="text" class="regular-text ssmIconField" value="'.$icons[$cnt].'"> <i class="fa '.$icons[$cnt].'"></i> <span>'.$title.'</span><br/>';
						    $cnt++;
						}
					}
				?>
				</div>
				<p class="description">For every menu item you can enter font-awesome icon code.<br/> 
				To see the list of all availiable icons, <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank">go here.</a></p>
			</td>
		</tr>
		<tr>
			<th scope="row">Font Family</td>
			<td>
			<select id="ssmfont_family" name="ssmfont_family">
				<?php
					$index = 'default';
					foreach ($googleFonts as $key => $font)
					{	
						$f = explode("|", $font);
						$selected = '';
						
						if ($settings['fontfamily'] == $f[1])
						{
							$selected = 'selected="selected"';
							$index = $key;
						}
						
						echo '<option value="' . $f[1] . '" data-variants="' . $gFonts['variants'][$key] . '" ' . $selected . '>' . $f[0] . '</option>';
					}
				?>
			</select>
				<p class="description">Select which font from the Google Webfonts you want to use. <br/> You can <a target="_blank" href="https://www.google.com/fonts">preview the fonts here</a></p>
			</td>
		</tr>
		<tr>
			<th scope="row">Font Style</td>
			<td>
			<select id="ssmfont_style" name="ssmfont_style">
				<?php
					$variants = explode("|", $gFonts['variants'][$index]);
					foreach ($variants as $v)
					{
						if ($v == $settings['fontstyle'])
							$selected = 'selected="selected"';
						else
							$selected = '';
							
						echo '<option value="' . $v . '" ' . $selected . '>' . $v . '</option>';
					}
				?>
			</select>
				<p class="description">Select which font style of the font selected above you want to use.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">Footer Text</td>
			<td>
				<textarea class="regular-text" name="ssmFoot"><?php echo $settings['footertxt']; ?></textarea>
				<p class="description">Text that you enter here will appear below the menu.<br/><strong>HTML is accepted.</strong></p>
			</td>
		</tr>
		<tr>
			<th scope="row">Menu Background Color</td>
			<td>
				<input class="color" type="text" class="regular-text" name="ssBGCol" value="<?php echo $settings['bgcolor']; ?>">
				<p class="description">Background color of the menu panel.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">Border and Hover Color</td>
			<td>
				<input class="color" type="text" class="regular-text" name="ssbordCol" value="<?php echo $settings['bordcolor']; ?>">
			</td>
		</tr>
		<tr>
			<th scope="row">Text Color</td>
			<td>
				<input class="color" type="text" class="regular-text" name="sstextCol" value="<?php echo $settings['txtcolor']; ?>">
			</td>
		</tr>
		<tr>
			<th scope="row">Text Hover Color</td>
			<td>
				<input class="color" type="text" class="regular-text" name="sstextHovCol" value="<?php echo $settings['txthovcol']; ?>">
			</td>
		</tr>
	</table>

	<input type="submit" class="button button-primary" value="Save options!">
	</form>
</div>