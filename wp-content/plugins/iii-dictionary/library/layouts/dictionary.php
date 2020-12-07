<?php
	include IK_PLUGIN_DIR . '/library/formatter.php';
	$is_user_logged_in = is_user_logged_in();
	$route = get_route();
	$dictionary = $route[1];
	$link_url = ik_link_mw_apps();

	$is_dictionary_subscribed = is_dictionary_subscribed($dictionary);
	if(!$is_dictionary_subscribed) {
		$popup_remind = mw_get_option('sub-popup-times');
		$be_annoying = $_SESSION['remind_count'][$dictionary] >= $popup_remind ? true : false;
	}

	$entry = '';

	// check if dictionary slug is valid
	if(empty($dictionary)) {
		wp_redirect(locale_home_url());
		die;
	}
	
	if(isset($route[2]) && trim($route[2]) != '')
	{
		global $wpdb;
	
		// count the times user using dictionary
		$_SESSION['remind_count'][$dictionary]++;

		if(!$is_dictionary_subscribed && $_SESSION['remind_count'][$dictionary] == 1) {
			$be_annoying = true;
		}

		$dict_table = get_dictionary_table($dictionary);
		$entry = stripslashes($route[2]);
		$entry = trim($entry);
		
		$words = $wpdb->get_results($wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry = %s',
					array($entry)
				));

		// user might input inflected form. Try to get original form
		if(empty($words)) {
			$search = $wpdb->get_row($wpdb->prepare('SELECT DISTINCT entry FROM ' . $wpdb->prefix . $dict_table . ' WHERE REPLACE(inflection, \'*\', \'\') LIKE %s', array('%<if>' . $entry . '</if>%')));
		}

		// research
		if(!is_null($search))
		{
			$words = $wpdb->get_results($wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry = %s',
				array($search->entry)
			));
		}

		// store search history
		if(!empty($words)) {
			save_search_history($entry, $dictionary);
		}

		// search thesaurus dictionary
		if($words && $dictionary == 'collegiate')
		{
			$thesaurus_words = $wpdb->get_results($wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . 'dict_thesaurus WHERE entry = %s',
				array($entry)
			));
		}

		if(empty($words)) {
			$similar_words = $wpdb->get_results($wpdb->prepare(
				'SELECT DISTINCT entry, levenshtein(entry, %s) AS lev FROM ' . $wpdb->prefix . $dict_table . ' WHERE entry LIKE %s ORDER BY lev LIMIT 8',
				array( $entry, substr($entry, 0, 2) . '%' )
			));
		}
	}

	$flashcard_folders = $is_user_logged_in ? MWDB::get_flashcard_folders(get_current_user_id()) : array();
	ik_enqueue_js_messages('login_req_h', __('Login Required', 'iii-dictionary'));
	ik_enqueue_js_messages('login_req_err', __('Please login in order to continue to use this function.', 'iii-dictionary'));
	ik_enqueue_js_messages('login_req_lbl', __('Login', 'iii-dictionary'));
	ik_enqueue_js_messages('folder_sel', __('Please select a Folder', 'iii-dictionary'));
	ik_enqueue_js_messages('fc_sub_req', __('Please subscribe in order to save more than 5 flashcards!', 'iii-dictionary'));
	
	//for dic-download-register
	if(isset($_POST['wp-submit']))
	{
		$form_valid = true;

		if(is_email($_POST['user_login'])) {
			if(email_exists($_POST['user_login'])) {
				ik_enqueue_messages(__('This email address is already registered. Please choose another one.', 'iii-dictionary'), 'error');
				$form_valid = false;
			}

			$user_email = $_POST['user_login'];
		}
		else {
			// we don't accept normal string as username anymore
			ik_enqueue_messages(__('This email address is invalid. Please choose another one.', 'iii-dictionary'), 'error');
			$form_valid = false;
			/* if(username_exists($_POST['user_login'])) {
				ik_enqueue_messages(__('This username is already registered. Please choose another one', 'iii-dictionary'), 'error');
				$form_valid = false;
			} */
		}

		if(trim($_POST['password']) == '') {
			ik_enqueue_messages(__('Passwords must not be empty', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if($_POST['password'] !== $_POST['confirm_password'])
		{
			ik_enqueue_messages(__('Passwords must match', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		if(strlen($_POST['password']) < 6) 
		{
			ik_enqueue_messages(__('Passwords must be at least six characters long', 'iii-dictionary'), 'error');
			$form_valid = false;
		}

		$_POST['date_of_birth']  = '01/01/2016';

		// form valid, create new user
		if($form_valid)
		{
			if(isset($user_email)) {
				$user_id = wp_create_user($_POST['user_login'], $_POST['password'], $user_email);
			}
			else {
				$user_id = wp_create_user($_POST['user_login'], $_POST['password']);
			}

			$userdata['ID'] = $user_id;

			if(isset($_POST['first_name']) && trim($_POST['first_name']) != '')
			{
				$userdata['first_name'] = $_POST['first_name'];
			}

			if(isset($_POST['last_name']) && trim( $_POST['last_name']) != '')
			{
				$userdata['last_name'] = $_POST['last_name'];
			}

			if(isset($userdata['first_name']) && isset($userdata['last_name']))
			{
				$userdata['display_name'] = $userdata['first_name'] . ' ' . $userdata['last_name'];
			}

			$new_user_id = wp_update_user( $userdata );

			update_user_meta( $user_id, 'date_of_birth', $_POST['date_of_birth'] );

			// auto login the user
			$creds['user_login'] = $_POST['user_login'];
			$creds['user_password'] = $_POST['password'];
			$user = wp_signon( $creds, false );

			// send confirmation email
			if(is_email($user_email))
			{
				$title = __('Congratulations! You have successfully signed up for iklearn.com', 'iii-dictionary');
				$message =  __('You have successfully signed up for iklearn.com.', 'iii-dictionary') . "\r\n\r\n" .
							__('If you have questions or need support, please contact us at support@iklearn.com.', 'iii-dictionary') . "\r\n\r\n" .
							__('If you forgot your password, please click on the "forgot password" button after entering your username (email address).', 'iii-dictionary') . "\r\n\r\n" .
							__('Please enjoy the Merriam Webster dictionary and English learning tools.', 'iii-dictionary') . "\r\n\r\n" .
							__('For teachers - You may assign homework practice sheets available on this site. You can also create your own homework sheets. Please click here for details. The homework that is turned in by students is automatically graded and saved in your teacher\'s box. Currently, the available homework worksheets are (1) spelling practice and (2) vocabulary and grammar.', 'iii-dictionary') . "\r\n\r\n\r\n" .
							__('Happy learning!', 'iii-dictionary') . "\r\n\r\n\r\n" .
							__('Support', 'iii-dictionary');

				if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message ) ) {			
				}
			}

			$_SESSION['mw_download_dic'] = $_POST['mw-click-btn'];
			$_SESSION['mw_referer'] =  DIC_DWN_URL;
			wp_redirect($_SESSION['mw_referer']);
			exit;
		}
	}
	
?>
<?php get_dict_header('', $dictionary) ?>

	<header class="col-xs-12 article-header">
		<div class="row">
			<div class="col-xs-12 col-sm-10 col-sm-offset-1">								
				<div class="row">
					<div class="col-xs-12 col-sm-10 col-sm-offset-2">
						<label class="page-title" for="keyword"><?php echo get_dictionary_name($dictionary) ?></label>
					</div>
				</div>
				<form class="row" id="search-form">
					<div class="col-xs-8 col-sm-7 col-sm-offset-2 search-box-container">
						<input id="keyword" class="form-control search-box" name="keyword" type="text" autocomplete="off" value="<?php echo $entry ?>">
						<div id="seach-results"></div>
					</div>
					<div class="col-xs-4 col-sm-3">
						<button class="btn btn-default btn-block orange" type="submit" name="wp-submit"><?php _e('Search', 'iii-dictionary') ?></button>
					</div>
				</form>								
			</div>
		</div>
	</header>
	<section class="col-xs-12 entry-content dictionary">
		<div class="row">
			<div class="col-xs-12 col-sm-10 col-sm-offset-1">

		<?php /* dictionary word details page */ ?>

		<?php if( isset($route[2]) && trim($route[2]) != '' ) : ?>
			<?php if(!empty($words)) : ?>
				<div class="row">
					<div class="col-xs-12">
						<span id="save-flash-cards" class="pull-right" data-islogin="<?php echo $is_user_logged_in ? 1 : 0 ?>"><?php _e('Create flash card', 'iii-dictionary') ?></span>
					</div>
				</div>
				<?php $count = 0; foreach($words as $word) : $count++ ?>
					<div class="row">
						<div class="col-xs-5 col-sm-2 hidden-xs d-label"><?php _e('Headword', 'iii-dictionary') ?></div>
						<div class="col-xs-7 col-sm-10 hw"><?php echo WFormatter::_hw($word->headword) ?><?php echo count($words) > 1 ? '<sup>' . $count . '</sup>' : '' ?></div>
					</div>
					<?php 
						$sound = WFormatter::_sound($word->sound, $dictionary);
						$pronunciation = WFormatter::_pr($word->pronunciation);
						if($pronunciation) : ?>
						<div class="row">
							<div class="col-xs-6 col-sm-2 hidden-xs d-label"><?php _e('Pronunciation', 'iii-dictionary') ?></div>
							<div class="col-xs-6 col-sm-10 pr">
								<?php echo $sound . $pronunciation ?>
							</div>
						</div>
					<?php endif ?>

					<?php if($variant = WFormatter::_variant($word, $dictionary)) : ?>
						<div class="row">
							<div class="col-xs-6 col-sm-2 hidden-xs d-label"><?php _e('Variant spelling', 'iii-dictionary') ?></div>
							<div class="col-xs-6 col-sm-10 variant">
								<?php echo $variant ?>
							</div>
						</div>
					<?php endif ?>

					<?php if($inflection = WFormatter::_inflection($word->inflection, $dictionary)) : ?>
						<div class="row">
							<div class="col-xs-12 col-sm-2 hidden-xs d-label"><?php _e('Inflected form', 'iii-dictionary') ?></div>
							<div class="col-xs-12 col-sm-10 inflection"><?php echo $inflection ?></div>
						</div>
					<?php endif ?>
					<div class="row">
						<div class="col-xs-5 col-sm-2 hidden-xs d-label"><?php _e('Function', 'iii-dictionary') ?></div>
						<div class="col-xs-7 col-sm-10 fl"><?php echo WFormatter::_fl($word->functional_label) ?></div>
					</div>

					<?php if($label = WFormatter::_label($word, $dictionary)) : ?>
						<div class="row">
							<div class="col-xs-5 col-sm-2 hidden-xs d-label"><?php _e('Usage', 'iii-dictionary') ?></div>
							<div class="col-xs-7 col-sm-10 fl"><?php echo $label ?></div>
						</div>
					<?php endif ?>

					<div class="hr-line"></div>
					<div class="row">
						<div class="col-xs-12 col-sm-2 hidden-xs d-label"><?php _e('Definition', 'iii-dictionary') ?></div>
						<div class="col-xs-12 col-sm-10 definition">
							<?php echo WFormatter::_def($word->definition, $dictionary) ?>
							<?php echo WFormatter::_dir_cross_ref($word->dir_cross_ref, $dictionary) ?>
							<?php echo WFormatter::_art($word->art, $word->entry, $dictionary) ?>
						</div>
					</div>
					<?php if($synonyms = WFormatter::_synonyms($word, $dictionary)) : ?>
						<div class="row">
							<div class="col-xs-12 col-sm-2 d-label"><?php _e('Synonym', 'iii-dictionary') ?></div>
							<div class="col-xs-12 col-sm-10 definition"><?php echo $synonyms ?></div>
							<script>
								var $div = jQuery(".definition-syn > div");
								$div.each(function(i, e){
									if(jQuery(e).hasClass("uro") && jQuery($div[i+1]).hasClass("uro")){
										jQuery(e).css("display", "block");
									}
								});
							</script>
						</div>
					<?php endif ?>
					<?php if($etymology = WFormatter::_etymology($word->etymology)) : ?>
						<div class="row">
							<div class="col-xs-12 col-sm-2 d-label"><?php _e('Etymology', 'iii-dictionary') ?></div>
							<div class="col-xs-12 col-sm-10 definition"><?php echo $etymology ?></div>
						</div>
					<?php endif ?>
					<?php if($date = WFormatter::_date($word->definition)) : ?>
						<div class="row">
							<div class="col-xs-12 col-sm-2 d-label"><?php _e('Date', 'iii-dictionary') ?></div>
							<div class="col-xs-12 col-sm-10 definition"><?php echo $date ?></div>
						</div>
					<?php endif ?>

					<?php if(count($words) != $count) : ?>
						<div class="hr-line2"></div>
					<?php endif ?>

				<?php endforeach ?>

				<?php if(isset($thesaurus_words) && !empty($thesaurus_words)) : ?>
						<div class="hr-line2"></div>
						<div class="row thesaurus-dictionary">
							<div class="col-xs-12">
								<h2>Collegiate Thesaurus</h2>
							</div>
						</div>
						<?php $count = 0;
							foreach($thesaurus_words as $word) : $count++ ?>
						<div class="row">
							<div class="col-xs-5 col-sm-2 hidden-xs d-label"><?php _e('Headword', 'iii-dictionary') ?></div>
							<div class="col-xs-7 col-sm-10 hw"><?php echo WFormatter::_hw($word->headword) ?><?php echo count($thesaurus_words) > 1 ? '<sup>' . $count . '</sup>' : '' ?></div>
						</div>
						<?php if($inflection = WFormatter::_inflection($word->inflection, $dictionary)) : ?>
							<div class="row">
								<div class="col-xs-12 col-sm-2 hidden-xs d-label"><?php _e('Inflected form', 'iii-dictionary') ?></div>
								<div class="col-xs-12 col-sm-10 inflection"><?php echo $inflection ?></div>
							</div>
						<?php endif ?>
						<div class="row">
							<div class="col-xs-5 col-sm-2 hidden-xs d-label"><?php _e('Function', 'iii-dictionary') ?></div>
							<div class="col-xs-7 col-sm-10 fl"><?php echo WFormatter::_fl($word->functional_label) ?></div>
						</div>
						<div class="hr-line"></div>
						<div class="row">
							<div class="col-xs-12 col-sm-2 hidden-xs d-label"><?php _e('Definition', 'iii-dictionary') ?></div>
							<div class="col-xs-12 col-sm-10 definition thesaurus">
								<?php echo WFormatter::_thesaurus_def($word->definition) ?>
							</div>
						</div>

						<?php if(count($thesaurus_words) != $count) : ?>
							<div class="hr-line2"></div>
						<?php endif ?>

					<?php endforeach ?>
				<?php endif ?>

			<?php else : ?>
				<div class="row">
					<div class="col-xs-12">
						<h1><?php printf(__('No exact match found for "%s" in %s', 'iii-dictionary'), $entry, get_dictionary_name($dictionary)) ?></h1>
					</div>

					<?php if(!empty($similar_words[0]->entry)) : ?>
						<div class="col-xs-12 didYouMeanBlock">
							<p><?php _e('Did you mean', 'iii-dictionary') ?> <em><a href="<?php echo locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $similar_words[0]->entry ?>"><b><?php echo $similar_words[0]->entry ?></b></a></em>?</p>
						</div>
						<div class="col-xs-12 didYouMeanBlock">
							<h3><?php _e('Similar words:', 'iii-dictionary') ?></h3>
							<?php foreach($similar_words as $sm) : ?>
								<p><a href="<?php echo locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $sm->entry ?>"><?php echo $sm->entry ?></a></p>
							<?php endforeach ?>
						</div>
					<?php endif ?>
				</div>
			<?php endif ?>

		<?php /* dictionary home page */ ?>

		<?php else : ?>

			<div id="dictionary-home" class="row">
				<?php if($_SERVER['REQUEST_URI'] == '/dic-download') : ?>
					<div class="col-xs-12"  id="dic-download">
						<div>
							<h1 id="dd-title"><?php printf(__('Merriam-Webster\'s%s Collegiate Dictionary, Eleventh Edition', 'iii-dictionary'),'&reg; <br />') ?></h1>
							<div id="dd-content">
								<h3><?php _e('Digital <br /> dictionary <br /> download', 'iii-dictionary')?></h3>
								<div class="dd-content-btn">
									<div><a id="dd-btn-win" href="<?php echo $link_url['mac']; ?>" class="<?php echo is_user_logged_in() ? '' : 'dd-btn' ?>"><?php _e('Download for Mac', 'iii-dictionary')?></a></div>
									<div><a id="dd-btn-mac" href="<?php echo $link_url['win']; ?>" class="<?php echo is_user_logged_in() ? '' : 'dd-btn' ?>"><?php _e('Download for Win', 'iii-dictionary')?></a></div>
								</div>
								<p><?php printf(__('Windows Compatibility: Win%s7, Vista, XP/2K %s Mac Compatibility: OS X 10.4 or higher', 'iii-dictionary'), '<sup>&copy;</sup>', '<br />') ?></p>
							</div>
							<p id="dd-copyright"><?php _e('Dictionaries: Copyright by Merriam Webster, All rights reserved. Software and graphics: Copyright by Innovative Knowledge, Inc. All rights reserved.', 'iii-dictionary') ?></p>
						</div>
					</div>
				<?php endif ?>
				<div class="col-xs-6 col-sm-8">
					<section class="search-history">
						<div class="section-header">
							<h3><?php _e('Search History', 'iii-dictionary') ?> <span class="icon-history"></span></h3>
						</div>
						<div class="section-content">
							<?php
								$history = get_search_history($dictionary);
								$i = 0;
								foreach($history->items as $item) : ?>
									<span class="search-item">
										<a href="<?php echo locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $item ?>"><?php echo $item ?></a> <span class="xbtn" title="Remove" data-entry="<?php echo $item ?>">x</span>
									</span>
							<?php endforeach ?>
							<?php if($history->count > 9 && $is_dictionary_subscribed) : ?>
								<div class="history-see-more"><a href="#" class="see-more-btn"><?php _e('see more', 'iii-dictionary') ?> &gt;</a></div>
							<?php endif ?>
						</div>
					</section>
				</div>
				<div class="col-xs-6 col-sm-4">
					<section class="flash-cards">
						<div class="section-header">
							<h3><?php _e('Flash Cards', 'iii-dictionary') ?></h3>
						</div>
						<div class="section-content">
							<ul class="flash-card-items">
								<?php 
									$i = 0;
									foreach($flashcard_folders as $folder) : ?>
									<li><a href="#"><?php echo $folder->name ?></a></li>
								<?php 
									$i++;
									if($i == 3) {break;}
									endforeach ?>
							</ul>
							<div class="flash-card-go"><a href="<?php echo locale_home_url() ?>/?r=flash-cards" class="go-btn">go &gt;</a></div>
						</div>
					</section>
				</div>

				<div class="col-xs-12">
					<div class="quiz-spacing">
						<div class="visible-xs visible-sm quiz-tabs">
							<div class="english-quiz-tab-sm"></div>
							<div class="science-quiz-tab-sm"></div>
							<div class="history-quiz-tab-sm"></div>
							<div class="general-quiz-tab-sm"></div>
						</div>
					</div>
					<div class="quiz-table">
						<section class="quiz-box quiz-english">
							<div class="quiz-header">
								<h3 id="quiz-header-title">English</h3>
								<div class="painfultitle">
									<h2>Quiz</h2>
									<small>of The</small>
									<h2>Day</h2>
								</div>
							</div>
							<div class="quiz-body">
								<div class="quiz-content">
									<div id="quiz-loader">
										<div class="three-quarters-loader"><?php _e('Loading...', 'iii-dictionary') ?></div>
									</div>
									<span id="quiz-qe"></span>
									<span id="quiz-q"></span>
									<span id="quiz-a-1" class="quiz-a"></span>
									<span id="quiz-a-2" class="quiz-a"></span>
									<span id="quiz-a-3" class="quiz-a"></span>
								</div>
								<div class="quiz-nav">
									<div class="quiz-nav-control"><a id="get-answer" href="#"><?php _e('Get answer', 'iii-dictionary') ?></a></div>
									<div class="quiz-nav-control"><a id="next-quiz" href="#"><?php _e('Next', 'iii-dictionary') ?> <span class="next-arrow"></span></a></div>
									<div class="quiz-nav-control"><span id="quiz-answer"></span></div>
									<div class="quiz-get-more">
										<span class="semi-bold"><?php _e('Get more Quizzes at:', 'iii-dictionary') ?></span>
										<span id="quiz-location"><?php _e('Level', 'iii-dictionary') ?> <span id="quiz-level"></span>, <?php _e('Lesson', 'iii-dictionary') ?> <span id="quiz-lesson"></span></span>
									</div>
								</div>
							</div>
						</section>
					</div>
				</div>
			</div>

		<?php endif ?>

<?php MWHtml::subscribe_dictionary_popup($dictionary, true, $popup_remind, $is_dictionary_subscribed) ?>

<div id="search-history-modal" class="modal fade modal-white ik-modal1" aria-hidden="true">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
			<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
			<h3><?php _e('Search History', 'iii-dictionary') ?></h3>
		</div>
		<div class="modal-body">
			<?php foreach((array) $history->items as $item) : ?>
				<span class="search-item">
					<a href="<?php echo locale_home_url() . '/?r=dictionary/' . $dictionary . '/' . $item ?>"><?php echo $item ?></a> <span class="xbtn" data-entry="<?php echo $item ?>">x</span>
				</span>
			<?php endforeach ?>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-block orange" data-dismiss="modal"><span class="icon-check2"></span> <?php _e('Done', 'iii-dictionary') ?></button>
		</div>
	  </div>
	</div>
</div>

<?php if( isset($route[2]) && trim($route[2]) != '' ) : ?>
<div id="save-flashcard-modal" class="modal fade modal-white ik-modal1 ik-modal-transparent" aria-hidden="true">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
			<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
			<h3><?php _e('Save to folder', 'iii-dictionary') ?></h3>
		</div>
		<div class="modal-body">
			<div class="scroll-list3" style="max-height: 250px" title="<?php _e('Please select a Folder', 'iii-dictionary') ?>">
				<ul class="flashcard-folders">
					<?php foreach($flashcard_folders as $key => $folder) : ?>
						<li><div class="radio radio-style2">
							<input id="folder_<?php echo $key ?>" type="radio" name="flashcard-folders" value="<?php echo $folder->id ?>" checked>
							<label for="folder_<?php echo $key ?>"><?php echo $folder->name ?></label>
						</div></li>
					<?php endforeach ?>
				</ul>
			</div>
		</div>
		<div class="modal-footer">
			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<button type="button" class="btn btn-block orange" id="add-flashcard" data-loading-text="<?php _e('Adding ...', 'iii-dictionary') ?>"><span class="icon-increase"></span> <?php _e('Add', 'iii-dictionary') ?></button>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<button type="button" id="fc-folder-form" class="btn btn-block orange"><span class="icon-increase"></span> <?php _e('Create folder', 'iii-dictionary') ?></button>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>
</div>

<div id="create-folder-modal" class="modal fade modal-white ik-modal1 ik-modal-transparent" aria-hidden="true">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
			<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
			<h3><?php _e('Create folder', 'iii-dictionary') ?></h3>
		</div>
		<div class="modal-body">
			<label for="fc-folder-name"><?php _e('Folder name', 'iii-dictionary') ?></label>
			<input type="text" class="form-control" id="fc-folder-name">
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-block orange" id="create-flashcard-folder" data-loading-text="<?php _e('Saving ...', 'iii-dictionary') ?>"><span class="icon-check2"></span> <?php _e('OK', 'iii-dictionary') ?></button>
		</div>
	  </div>
	</div>
</div>

<?php endif ?>

<div id="require-modal" class="modal fade modal-white ik-modal1 ik-modal-transparent" aria-hidden="true">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
			<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
			<h3><?php _e('Subscription Required', 'iii-dictionary') ?></h3>
		</div>
		<div class="modal-body">
			<?php _e('Please subscribe in order to continue to use this program.', 'iii-dictionary') ?>
		</div>
		<div class="modal-footer">
			<a href="<?php echo locale_home_url() ?>/?r=manage-subscription#2" class="btn btn-block orange"><span class="icon-issue2"></span> <?php _e('Subscribe', 'iii-dictionary') ?></a>
		</div>
	  </div>
	</div>
</div>

<div id="mw-download-register" class="modal fade modal-red-brown">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
             <h3><?php _e('Registration', 'iii-dictionary') ?></h3>
        </div>
        <div class="modal-body">
			<form method="post" action="" name="registerform">
				<div class="row">
					<div class="col-sm-8">
						<div class="form-group">
							<label for="user_login"><?php _e('Username (e-mail address)', 'iii-dictionary') ?></label>
							<input id="user_login" class="form-control" name="user_login" type="text" value="">
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<label>&nbsp;</label>															
							<a href="#" id="check-availability" class="omg_font-14px check-availability"><?php _e('Find out availability', 'iii-dictionary') ?>
								<span class="icon-loading"></span>
								<span class="icon-availability" data-toggle="popover" data-placement="bottom" data-container="body" data-trigger="hover" data-html="true" data-max-width="420px" data-content="If username availability is “not available”, someone has already signed up with the email address you entered.<br>If username is “available”, please continue on with the sign up page."></span>
							</a>
						</div>
					</div>

					<div class="col-sm-6">
						<div class="form-group">
							<label for="password"><?php _e('Create Password', 'iii-dictionary') ?></label>
							<input id="password" class="form-control" name="password" type="password" value="">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="confirmpassword"><?php _e('Confirm Password', 'iii-dictionary') ?></label>
							<input id="confirmpassword" class="form-control" name="confirm_password" type="password" value="">
						</div>
					</div>

					<div class="col-sm-6">
						<div class="form-group">
							<label for="lastname"><?php _e('Last Name', 'iii-dictionary') ?></label>
							<input id="lastname" class="form-control" name="last_name" type="text" value="">
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label for="firstname"><?php _e('First Name', 'iii-dictionary') ?></label>
							<input id="firstname" class="form-control" name="first_name" type="text" value="">
						</div>
					</div>
					<div class="col-sm-7">
						<div class="form-group">
							<label>&nbsp;</label>
							<button class="btn btn-default btn-block orange account" type="submit" name="wp-submit"><span class="icon-plus"></span><?php _e('Register', 'iii-dictionary') ?></button>
						</div>
					</div>
				</div>
				<input type="hidden" id="mw-click-btn" name="mw-click-btn" />
			</form>
        </div>
      </div>
    </div>
</div>

<script>
	var dictionary_slug = "<?php echo $dictionary ?>";
	var annoying = <?php echo $is_dictionary_subscribed ? 'false' : 'true' ?>;
	var entry = "<?php echo $entry ?>";
	var trivia = 1;
	var trivia_a;
	var is_login = <?php echo is_user_logged_in() ? 'true' : 'false' ?>;
	(function($){
		$(function(){
			<?php if(!empty($route[2]) && !$is_dictionary_subscribed && !is_mw_admin() && $be_annoying) : ?>
				$("#subscribe-modal-dialog").modal("show");
				centerModals();

				$("#subscribe-modal-dialog").on("hide.bs.modal", function (e) {
					setTimeout(function(){
						$("#subscribe-modal-dialog").modal("show");
					}, <?php echo mw_get_option('sub-popup-interval') * 1000 ?>);
				});
			<?php endif ?>

			<?php if(empty($route[2])) : ?>
				get_quiz();
				$("#keyword").focus();
			<?php endif ?>
		});
		$(function() {
			var mw_download = $('#dic-download');
			var mw_btn 	 	= $('.dd-btn');
			if(mw_download.length > 0) {
				$(mw_download).parents('section.dictionary').css('height','800px');
				$(mw_btn).on('click',function(event) {
					event.preventDefault();
					$("#mw-click-btn").val($(this).attr("id"));
					$.get(home_url + "/?r=ajax/mw_download", {is_login : is_login} , function(data) {
						var it = JSON.parse(data);
						if(it.status == 0) {
							$("#mw-download-register").modal();
						}
					});
				});
			}
		});
		
		$(function(){
			var availability_checking = false;
			$("#check-availability").click(function(e){
				e.preventDefault();
				if(availability_checking){return;}
				var tthis = $(this);
				var user_login = $("#user_login").val().trim();
				if(user_login != "") {
					tthis.popover("destroy");
					availability_checking = true;
					tthis.find(".icon-loading").fadeIn();
					$.getJSON(home_url + "/?r=ajax/availability/user", {user_login: user_login}, function(data){
						if(data[0] == 0) {
							var p_c = '<span class="popover-alert"><?php _e('Not available', 'iii-dictionary') ?></span>';
						}else{
							var p_c = '<span class="popover-alert"><?php _e('Available', 'iii-dictionary') ?></span>';
						}
						tthis.find(".icon-loading").fadeOut();
						tthis.popover({
							placement: "bottom",
							content: p_c,
							trigger: "click hover",
							html: true
						}).popover("show");
						setTimeout(function(){tthis.popover("hide")}, 1500);
						availability_checking = false;
					});
				}
			});
		});
		$(function(){
			<?php if(!empty($_SESSION['mw_download_dic'])) : ?>
				$(location).attr('href',$('#<?php echo $_SESSION['mw_download_dic'] ?>').attr('href'));
			<?php unset($_SESSION['mw_download_dic']); endif ?>
		});
	})(jQuery);
</script>
<?php get_dict_footer() ?>