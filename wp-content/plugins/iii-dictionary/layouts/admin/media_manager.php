<?php 
	//CORE VARIABLE
	$__dir 		= 'media';
	$__parent 	= '';
	$__root 	= 1;
	$__sfile 	= '';
	
	//SECTION - NEW Directory
	if(isset($_POST['create-directory-media'])) {
		$has_err = false;
		$data = array(
		'main-dic' 	=> $_POST['main-dic-media'],
		'name-dic' 	=> trim($_POST['input-directory-media'])
		);
		$structure = $data['main-dic'] . '/' . $data['name-dic'];
		
		if(empty($data['main-dic'])) {
			ik_enqueue_messages('Please select a parent directory.', 'error');
			$has_err = true;
		}
		if(empty($data['name-dic'])) {
			ik_enqueue_messages('Please fill name of directory.', 'error');
			$has_err = true;
		}
		if(file_exists($structure) && !$has_err) {
			ik_enqueue_messages('Directory existed.', 'error');	
			$has_err = true;
		}
			
			
		if(!$has_err) {
			if(!mkdir($structure, 0777, true)) {
				ik_enqueue_messages('Directory can not make.', 'error');
			}else {
				ik_enqueue_messages('Successfully create a directory.', 'success');
			}
		}
		
	}	
	
	//SECTION - NEW MEDIA FILE
	if(isset($_POST['upload-media'])) {
		$has_err = false;
		$data = array(
			'main-path' => $_POST['main-folder-media'],
			'sub-path' 	=> $_POST['sub-folder-media'],
			'length' 	=> $_POST['length-media'],
			'count' 	=> count($_FILES['input-file-media']['name']),
			'files' 	=> $_FILES['input-file-media']
		);
		
		
		if(empty($data['main-path'])) {
			ik_enqueue_messages('Please select a main directory.', 'error');
			$has_err = true;
		}
		
		if(empty($data['length']) && $data['files']['error'][0] == 4) {
			ik_enqueue_messages('Please browse a file.', 'error');
			$has_err = true;
		}
		
		if(!$has_err) {
			$dir = $data['main-path'];
			$dir .= (!empty($data['sub-path'])) ? '/' . $data['sub-path']  : '';
			for($i = 0; $i < $data['count']; $i++ ) {
				move_uploaded_file($data['files']['tmp_name'][$i], $dir . '/' . basename($data['files']['name'][$i]));
			}
			ik_enqueue_messages('Successfully upload files.', 'success');
		}
		
	}
	
	/*Search*/
	
	if(isset($_POST['btn_search_media'])) {
		$dir = '';
		$data = array(
			'main-dic' 	=> $_POST['s_main_folder'],
			'sub-dic' 	=> $_POST['s_sub_folder'],
			'name' 		=> $_POST['s_file_name']
		);
		//store value search of user
		$_SESSION['media']['main-dic'] 	= $data['main-dic'];
		$_SESSION['media']['sub-dic'] 	= $data['sub-dic'];
		$_SESSION['media']['name'] 		= $data['name'];
		
		//
		$dir .= $data['main-dic'];
		$dir .= (!empty($data['main-dic']) && !empty($data['sub-dic'])) ? '/'. $data['sub-dic'] : '';
		
		//set again		
		if(!empty($data['name'])) {
			$__sfile 	= $data['name'];
			$_SESSION['media']['main-dic'] 	= '';
			$_SESSION['media']['sub-dic'] 	= '';
		}else {
			$_SESSION['media']['name'] = '';
			$__sfile 	= '';
			if(!empty($dir)) {
				$__dir 		= $dir;
				$__parent 	= $dir;
			}
		}
		

	}

?>

<?php get_dict_header('Media Manager') ?>
<?php get_dict_page_title('Media Manager', 'admin-page') ?>
	<form method="post" action="" id="main-form" enctype="multipart/form-data">
		<div class="row">
			<!--SECTION - Update file -->
			<div class="col-sm-12"><h2 class="title-border">New Media File</h2></div>
			<div class="col-sm-3">
				<div class="form-group">
					<label>Main directory</label>
					<?php MWHtml::get_main_folder_name() ?>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="form-group">
					<label>Sub directory</label>
					<?php MWHtml::get_sub_folder_name() ?>
				</div>
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<label for="imported-media">&nbsp;</label>
					<input type="text" class="form-control" id="imported-file-media" name="length-media" readonly />
				</div>
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<label>&nbsp;</label>
					<span class="btn btn-default btn-block grey btn-file">
						<span class="icon-browse"></span>Browse
						<input name="input-file-media[]" id="input-file-media" type="file" multiple >
					</span>
				</div>
			</div>
			<div class="col-sm-2">
				<div class="form-group">
					<label>&nbsp;</label>
					<button type="submit" name="upload-media" class="btn btn-default orange form-control"><span class="icon-plus"></span>Upload</button>
				</div>
			</div>
			<!--SECTION - Create folder -->
			<div class="col-sm-12"><h2 class="title-border">New Directory</h2></div>
			<div class="col-sm-4">
				<div class="form-group">
					<label>Parent directory</label>
					<?php MWHtml::get_main_folder_name('main-dic-media', '', 'main-dic-media', 1) ?>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<label>Name of directory</label>
					<input type="text" class="form-control" id="input-directory-media" name="input-directory-media" />
				</div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<label>&nbsp;</label>
					<button type="submit" name="create-directory-media" class="btn btn-default orange form-control"><span class="icon-plus"></span>Create directory</button>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12"><h2 class="title-border">Media File</h2></div>
			<div class="col-sm-12">
				<div class="box box-sapphire">
					<div class="row box-header">
						<div class="col-sm-4 form-group">
							<label>&nbsp;</label>
							<input type="text" class="form-control" placeholder="File name" name="s_file_name" value="<?php echo !empty($_SESSION['media']['name']) ? $_SESSION['media']['name'] : '' ?>">
						</div>
						<div class="col-sm-3 form-group">
							<label>&nbsp;</label>
							<?php MWHtml::get_main_folder_name('s_main_folder', ' select-sapphire selectboxit-btn selectboxit-enabled','s_main_folder') ?>
						</div>
						<div class="col-sm-3 form-group">
							<label>&nbsp;</label>
							<?php MWHtml::get_sub_folder_name('s_sub_folder', ' select-sapphire selectboxit-btn selectboxit-enabled','s_sub_folder') ?>
						</div>
						<div class="col-sm-2 form-group">
							<label>&nbsp;</label>
							<button type="submit"  class="btn btn-default sky-blue form-control" name="btn_search_media">Search</button>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="scroll-list2" style="max-height: 400px">
								<table class="table table-striped table-condensed ik-table1 vertical-middle text-center">
									<thead>
										<tr>
											<th>Name</th>
											<th>Directory</th>
											<th>Date modified</th>
										</tr>
									</thead>
									<tbody id="omg-media-table"><?php MWHtml::list_folder_file($__dir, $__parent, $__root, $__sfile) ?></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
<?php get_dict_footer() ?>