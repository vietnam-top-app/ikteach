<?php
	$current_user_id = get_current_user_id();

	$flashcard_folders = MWDB::get_flashcard_folders($current_user_id, true);

	$flashcards = MWDB::get_flashcards($current_user_id);

	$teacher_sets = MWDB::get_flashcard_teacher_sets($current_user_id);

	$teacher_flashcards = MWDB::get_teacher_flashcards($current_user_id);

	$fc_sets = 'var fc_sets = [];';
	foreach($teacher_sets as $set)
	{
		$a = array();
		foreach($teacher_flashcards as $flashcard) {
			if($flashcard->teacher_set_id == $set->id) {
				$memorized = is_null($flashcard->memorized) ? 0 : $flashcard->memorized;

				// check user notes. If null, try to display teacher's sentence as user note
				if(empty($flashcard->notes)) {
					if(!is_null($flashcard->teacher_sentence)) {
						$notes = $flashcard->teacher_sentence;
					}
					else {
						$notes = '';
					}
				}
				else {
					$notes = $flashcard->notes;
				}

				$a[] = 'w' . $flashcard->id . ': {' .
							'word_id: "' . $flashcard->id . '",' .
							'word: ' . json_encode($flashcard->word) . ',' .
							'memorized: ' . $memorized . ',' .
							'notes: ' . json_encode($notes) .
						'}';
			}
		}

		$fc_sets .= 'fc_sets[' . $set->id . '] = {header: ' . json_encode($set->header_name) . ', comment: ' . json_encode($set->comments) . ', teacher: ' . json_encode($set->display_name) . ', group: ' . json_encode($set->group_name) . ', date: ' . json_encode($set->created_on) . ', words: {' . implode(',', $a) . '}};';
	}

	$ta = array();
	foreach($teacher_flashcards as $flashcard)
	{
		$memorized = is_null($flashcard->memorized) ? 0 : $flashcard->memorized;
		$notes = is_null($flashcard->notes) ? '' : $flashcard->notes;
		$ta[] = '{word_id: "' . $flashcard->id . '", word: ' . json_encode($flashcard->word) . ', memorized: ' . $memorized . ', notes: ' . json_encode($notes) . '}';
	}
	$tfc_js = 'var tfc_folders = [' . implode(',', $ta) . '];';

	$fc_js = 'var fc_folders = [];';
	foreach($flashcard_folders as $folder)
	{
		$a = array();

		foreach($flashcards as $flashcard)
		{
			if($flashcard->folder_id == $folder->id) {
				$a[] = 'w' . $flashcard->id . ': {word_id: "' . $flashcard->id . '", word: ' . json_encode($flashcard->word) . ', memorized: ' . $flashcard->memorized . ', notes: ' . json_encode($flashcard->notes) . '}';
			}
		}
		$fc_js .= 'fc_folders[' . $folder->id . '] = {' . implode(',', $a) . '};';
	}

?>
<?php get_dict_header(__('Flash Cards', 'iii-dictionary'), 'green') ?>
<?php get_dict_page_title(__('Vocabulary Builder', 'iii-dictionary'), '', __('Flash Cards', 'iii-dictionary'), array(), get_info_tab_cloud_url('Popup_info_16.jpg')) ?>

	<div class="row">
		<div class="col-sm-6">
			<label><?php _e('Flashcard type', 'iii-dictionary') ?></label>
			<select class="select-box-it select-green form-control" id="sel-fc-type">
				<option value="my-own">My Own</option>
				<option value="teacher-sets">Teacher</option>
			</select>
		</div>
		<div class="col-sm-6">
			<label>&nbsp;</label>
			<button type="button" id="flash-card-mode" class="btn btn-default btn-block grey form-control"><span class="icon-flashcard"></span> <?php _e('Flash card mode', 'iii-dictionary') ?></button>
		</div>
		<div class="col-sm-12"><hr></div>
		<div class="col-sm-6" id="my-own-block" style="display: none">
			<div class="form-group">
				<label><?php _e('Select a folder', 'iii-dictionary') ?></label>
				<select class="select-box-it select-green form-control" id="sel-fc-folders">
					<?php foreach($flashcard_folders as $folder) :
							if($folder->id != TEACHER_FLASHCARD_FOLDER) : ?>
								<option value="<?php echo $folder->id ?>"><?php echo $folder->name ?></option>
					<?php 	endif; 
						endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-sm-6" id="teacher-sets-block" style="display: none">
			<div class="form-group">
				<label><?php _e('Select a set sent by your teacher', 'iii-dictionary') ?></label>
				<select class="select-box-it select-green form-control" id="sel-teacher-sets">
					<?php foreach($teacher_sets as $set) : ?>
						<option value="<?php echo $set->id ?>"><?php echo $set->header_name ?></option>
					<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-sm-12">
			<div class="row" id="flashcard-set-header" style="display: none">
				<div class="col-sm-4 col-md-3" id="set-header"><?php _e('Set:', 'iii-dictionary') ?> <span></span></div>
				<div class="col-sm-4 col-md-3" id="set-teacher"><?php _e('Teacher:', 'iii-dictionary') ?> <span></span></div>
				<div class="col-sm-4 col-md-3" id="set-group"><?php _e('Group:', 'iii-dictionary') ?> <span></span></div>
				<div class="col-sm-4 col-md-3" id="set-date"><?php _e('Date:', 'iii-dictionary') ?> <span></span></div>
				<div class="col-sm-12" id="set-comment"><?php _e('Teacher\'s comment:', 'iii-dictionary') ?> <span></span></div>
			</div>
			<div class="flashcard-table">
				<div class="flashcard-table-header">
					<div><?php _e('Words', 'iii-dictionary') ?></div>
					<div><?php _e('Own sentence', 'iii-dictionary') ?></div>
					<div><?php _e('Memorized?', 'iii-dictionary') ?></div>
				</div>
				<div class="flashcard-table-content box scroll-list2" style="max-height: 375px">
					<table class="table table-striped table-condensed ik-table2 ik-table-noborder ik-table-green" id="fc-table">
						<tbody>
							<?php if(empty($teacher_flashcards)) : ?>
									<tr><td><?php _e('There\'s no flashcard in this folder', 'iii-dictionary') ?></td></tr>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-sm-12" style="margin-top: 25px; display: none" id="dictionary-block">
			<div class="box box-white flashcard-des scroll-list2 dictionary" style="max-height: 200px">
				<div id="fc-meaning"></div>
			</div>
		</div>
	</div>

<div id="flashcard-modal" class="modal fade modal-green" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"><?php _e('Close', 'iii-dictionary') ?></a>
             <h3><?php _e('Flash Card', 'iii-dictionary') ?></h3>
        </div>
        <div class="modal-body">
            <div id="answer-block">
			</div>
			<div id="hint-block" class="box">
				<div id="hints"></div>
			</div>
			<div class="hr-line-green"></div>
        </div>
        <div class="modal-footer">
			<div class="row">
				<div class="col-sm-6">
					<div class="form-group">
						<div class="radio radio-style3">
							<input id="memorized-radio" name="memorized" value="1" type="radio">
							<label for="memorized-radio"><?php _e('Do not show again', 'iii-dictionary') ?></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						<button type="button" id="next-flashcard" class="btn btn-default btn-block orange"><span class="icon-start"></span> <?php _e('Next', 'iii-dictionary') ?></button>
					</div>
				</div>
			</div>
        </div>
      </div>
    </div>
</div>

<div id="require-modal" class="modal fade modal-white ik-modal1 ik-modal-transparent" aria-hidden="true">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
			<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
			<h3><?php _e('Messages', 'iii-dictionary') ?></h3>
		</div>
		<div class="modal-body">
			<?php _e('You have memorized all flash cards or there\'s no flash card in this folder', 'iii-dictionary') ?>
		</div>
		<div class="modal-footer">
		</div>
	  </div>
	</div>
</div>

<script>
	<?php echo $fc_sets ?>
	<?php echo $tfc_js ?>
	<?php echo $fc_js ?>
</script>
<?php get_dict_footer() ?>