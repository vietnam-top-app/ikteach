<?php 
global $wpdb;
if ( ! function_exists( 'wp_handle_upload' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
}

//Restart send email
if(isset($_POST['send_restart'])){
	$language_type = $_POST['language_type'];
	$rate_email = $_POST['rate_email'];
	$subject_email = sanitize_text_field(trim($_POST['subject_email']));
	$email_body = $_POST['email_body'];
	$colelgiate_dictionary = sanitize_text_field(trim($_POST['colelgiate_dictionary']));
	
	$email_body = str_replace('\"','"',$email_body);
	$email_body = str_replace("\'","'",$email_body);
	
	$file_email = $_FILES['email_list'];
	$end_portion = $_FILES['end_portion'];

	if(isset($_FILES['email_list']['name']) && $_FILES['email_list']['name'] != ''){
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $file_email, $upload_overrides );
		if(!isset($movefile['error'])){
			$url_file = $movefile['url'];
			mw_set_option('email_list_url',$url_file);
			mw_set_option('email_list_name',$_FILES['email_list']['name']);
			$file_handle = fopen(mw_get_option('email_list_url'),"r");
			while (!feof($file_handle) && $i <= $rate_email ) {
				$line_of_text[] = fgetcsv($file_handle, 1024);
			}
			fclose($file_handle); 
			$total_emails = count($line_of_text);
			mw_set_option('total_emails_marketing',$total_emails-1);
			
		}	
	}

	if(isset($_FILES['end_portion']['name']) && $_FILES['end_portion']['name'] != ''){
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $end_portion, $upload_overrides );
		if(!isset($movefile['error'])){
			$url_file = $movefile['url'];
			mw_set_option('end_portion',$url_file);
			mw_set_option('end_portion_name',$_FILES['end_portion']['name']);
		}	
	}
	// Save type email
	mw_set_option('total_sent_out_email_marketing',0);
	mw_set_option('last_email_id',0);
	mw_set_option('last_day_send',0);
	mw_set_option('language_type',$language_type);
	mw_set_option('rate_email',$rate_email);
	mw_set_option('subject_email',$subject_email);
	mw_set_option('email_body',$email_body);
	mw_set_option('colelgiate_dictionary',$colelgiate_dictionary);

	$end_portion_url = mw_get_option('end_portion');
	
	$file_handle = fopen(mw_get_option('email_list_url'),"r");
	
	while (!feof($file_handle) && $i <= $rate_email ) {
		$line_of_text[] = fgetcsv($file_handle, 1024);
	}
	fclose($file_handle); 
	$error = array();
	$send_success = array();
	$total_sent_out = mw_get_option('total_sent_out_email_marketing');
	for($i = $last_email_id + 1; $i <= $last_email_id + $rate_email;$i++){
		//$message = 'content email marketing';
		if(isset($line_of_text[$i])){
			$message = '<div style="width:90%; background:#fff; padding:20px;float:left;box-shadow: 0 0 10px rgba(185, 184, 184, 0.81);margin: 10px;">
							<div style="width:100%;padding:20px;color:#000">
								<p>'.$email_body.'</p>
								<p style="font-size: 16.5pt;color:black;;margin:10px 0"><strong>'.$colelgiate_dictionary.'</strong></p>
								<p></p><p></p>';
			if($end_portion_url != ''){
				$message .= '<img src="'.$end_portion_url.'" >';
			}
			$message .= '</div></div>';
			$headers = array('Content-Type: text/html; charset=UTF-8');
			if($line_of_text[$i][1] != ''){
				if(!wp_mail($line_of_text[$i][1],$subject_email, $message,$headers)){
					$error[$i] = 1;
				}else{
					$send_success[$i] = 1;
				}
			}
			if($i == $last_email_id + $rate_email){
				mw_set_option('last_email_id',$i);	
			}
			if($i == mw_get_option('total_emails_marketing')){
				$total_success = count($send_success);
				mw_set_option('total_sent_out_email_marketing',$total_success + $total_sent_out);
				ik_enqueue_messages(__('Completed for the list of about '.mw_get_option('total_emails_marketing').' emails.The end of email marketing','iii-dictionary'),'success');
				wp_redirect( site_admin_url() . '/?r=email-marketing');
				exit;
			}
		}
		else{
			$total_success = count($send_success);
			mw_set_option('total_sent_out_email_marketing',$total_success + $total_sent_out);
			ik_enqueue_messages(__('Completed for the list of about '.mw_get_option('total_emails_marketing').' emails.The end of email marketing','iii-dictionary'),'success');
			wp_redirect( site_admin_url() . '/?r=email-marketing');
			exit;
		}
	}
	$total_error = count($error);
	$total_success = count($send_success);
	if ($total_success > 0) {
		add_history_email_marketing($total_success,$total_error,$colelgiate_dictionary);	
	}
	if($total_error == 0){
		mw_set_option('last_day_send',date('d'));
		mw_set_option('total_sent_out_email_marketing',$total_success + $total_sent_out);
		ik_enqueue_messages(__('Successfully send email marketing.','iii-dictionary'),'success');
		wp_redirect( site_admin_url() . '/?r=email-marketing' );
		exit;
	}else{
		ik_enqueue_messages(__('Send emails marketing failure.','iii-dictionary'),'error');
		wp_redirect( site_admin_url() . '/?r=email-marketing' );
		exit;	
	}
}
//Continute send email
if(isset($_POST['btn_save'])){
	$language_type = $_POST['language_type'];
	$rate_email = $_POST['rate_email'];
	$subject_email = sanitize_text_field(trim($_POST['subject_email']));
	$email_body = $_POST['email_body'];
	$colelgiate_dictionary = sanitize_text_field(trim($_POST['colelgiate_dictionary']));
	
	$email_body = str_replace('\"','"',$email_body);
	$email_body = str_replace("\'","'",$email_body);
	
	$file_email = $_FILES['email_list'];
	$end_portion = $_FILES['end_portion'];

	if(isset($_FILES['email_list']['name']) && $_FILES['email_list']['name'] != ''){
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $file_email, $upload_overrides );
		if(!isset($movefile['error'])){
			$url_file = $movefile['url'];
			mw_set_option('email_list_url',$url_file);
			mw_set_option('email_list_name',$_FILES['email_list']['name']);
			mw_set_option('last_email_id',0);
			$file_handle = fopen(mw_get_option('email_list_url'),"r");
			while (!feof($file_handle) && $i <= $rate_email ) {
				$line_of_text[] = fgetcsv($file_handle, 1024);
			}
			fclose($file_handle); 
			$total_emails = count($line_of_text);
			mw_set_option('total_emails_marketing',$total_emails-1);
			mw_set_option('total_sent_out_email_marketing',0);
			mw_set_option('last_email_id',0);
			mw_set_option('last_day_send',0);
		}	
	}

	if(isset($_FILES['end_portion']['name']) && $_FILES['end_portion']['name'] != ''){
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $end_portion, $upload_overrides );
		if(!isset($movefile['error'])){
			$url_file = $movefile['url'];
			mw_set_option('end_portion',$url_file);
			mw_set_option('end_portion_name',$_FILES['end_portion']['name']);
		}	
	}
	// Save type email
	mw_set_option('language_type',$language_type);
	mw_set_option('rate_email',$rate_email);
	mw_set_option('subject_email',$subject_email);
	mw_set_option('email_body',$email_body);
	mw_set_option('colelgiate_dictionary',$colelgiate_dictionary);
	
	if(mw_get_option('last_email_id') == ''){
		 mw_set_option('last_email_id',0);
	}
	if(mw_get_option('last_day_send') == ''){
		 mw_set_option('last_day_send',0);
	}
	$end_portion_url = mw_get_option('end_portion');
	$last_email_id = mw_get_option('last_email_id');

	$file_handle = fopen(mw_get_option('email_list_url'),"r");
	
	while (!feof($file_handle) && $i <= $rate_email ) {
		$line_of_text[] = fgetcsv($file_handle, 1024);
	}
	fclose($file_handle); 
	$error = array();
	$send_success = array();
	$total_sent_out = mw_get_option('total_sent_out_email_marketing');
	for($i = $last_email_id + 1; $i <= $last_email_id + $rate_email;$i++){
		//$message = 'content email marketing';
		if(isset($line_of_text[$i])){
			$message = '<div style="width:90%; background:#fff; padding:20px;float:left;box-shadow: 0 0 10px rgba(185, 184, 184, 0.81);margin: 10px;">
							<div style="width:100%;padding:20px;color:#000">
								<p>'.$email_body.'</p>
								<p style="font-size: 16.5pt;color:black;;margin:10px 0"><strong>'.$colelgiate_dictionary.'</strong></p>
								<p></p><p></p>';
			if($end_portion_url != ''){
				$message .= '<img src="'.$end_portion_url.'" >';
			}
			$message .= '</div></div>';
			$headers = array('Content-Type: text/html; charset=UTF-8');
			if($line_of_text[$i][1] != ''){
				if(!wp_mail($line_of_text[$i][1],$subject_email, $message,$headers)){
					$error[$i] = 1;
				}else{
					$send_success[$i] = 1;
				}
			}
			if($i == $last_email_id + $rate_email){
				mw_set_option('last_email_id',$i);	
			}
			if($i == mw_get_option('total_emails_marketing')){
				$total_success = count($send_success);
				mw_set_option('total_sent_out_email_marketing',$total_success + $total_sent_out);
				ik_enqueue_messages(__('Completed for the list of about '.mw_get_option('total_emails_marketing').' emails.The end of email marketing','iii-dictionary'),'success');
				wp_redirect( site_admin_url() . '/?r=email-marketing');
				exit;
			}
		}
		else{
			$total_success = count($send_success);
			mw_set_option('total_sent_out_email_marketing',$total_success + $total_sent_out);
			ik_enqueue_messages(__('Completed for the list of about '.mw_get_option('total_emails_marketing').' emails.The end of email marketing','iii-dictionary'),'success');
			wp_redirect( site_admin_url() . '/?r=email-marketing');
			exit;
		}
	}
	$total_error = count($error);
	$total_success = count($send_success);
	if($total_success > 0){
		add_history_email_marketing($total_success,$total_error,$colelgiate_dictionary);
	}
	if($total_error == 0){
		mw_set_option('last_day_send',date('d'));
		mw_set_option('total_sent_out_email_marketing',$total_success + $total_sent_out);
		ik_enqueue_messages(__('Successfully sent '.$total_success.' emails from the email no.'.($last_email_id + 1),'iii-dictionary'),'success');
		wp_redirect( site_admin_url() . '/?r=email-marketing' );
		exit;
	}else{
		ik_enqueue_messages(__('Send emails marketing failure.','iii-dictionary'),'error');
		wp_redirect( site_admin_url() . '/?r=email-marketing' );
		exit;	
	}
}

if(isset($_POST['test_send_email'])){ // Save and Send test email
	$language_type = $_POST['language_type'];
	$rate_email = $_POST['rate_email'];
	$subject_email = sanitize_text_field(trim($_POST['subject_email']));
	$email_body = $_POST['email_body'];
	$colelgiate_dictionary = sanitize_text_field(trim($_POST['colelgiate_dictionary']));
	
	$email_body = str_replace('\"','"',$email_body);
	$email_body = str_replace("\'","'",$email_body);
	
	$file_email = $_FILES['email_list'];
	$end_portion = $_FILES['end_portion'];

	if(isset($_FILES['email_list']['name']) && $_FILES['email_list']['name'] != ''){
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $file_email, $upload_overrides );
		if(!isset($movefile['error'])){
			$url_file = $movefile['url'];
			mw_set_option('email_list_url',$url_file);
			mw_set_option('email_list_name',$_FILES['email_list']['name']);
			mw_set_option('last_email_id',0);
			mw_set_option('last_day_send',0);
			$file_handle = fopen(mw_get_option('email_list_url'),"r");
			while (!feof($file_handle) && $i <= $rate_email ) {
				$line_of_text[] = fgetcsv($file_handle, 1024);
			}
			fclose($file_handle); 
			$total_emails = count($line_of_text);
			mw_set_option('total_emails_marketing',$total_emails-1);
			mw_set_option('total_sent_out_email_marketing',0);
			mw_set_option('last_email_id',0);
			mw_set_option('last_day_send',0);
		}	
	}
	if(isset($_FILES['end_portion']['name']) && $_FILES['end_portion']['name'] != ''){
		$upload_overrides = array( 'test_form' => false );
		$movefile = wp_handle_upload( $end_portion, $upload_overrides );
		if(!isset($movefile['error'])){
			$url_file = $movefile['url'];
			mw_set_option('end_portion',$url_file);
			mw_set_option('end_portion_name',$_FILES['end_portion']['name']);
		}	
	}

	// Save type email
	mw_set_option('language_type',$language_type);
	mw_set_option('rate_email',$rate_email);
	mw_set_option('subject_email',$subject_email);
	mw_set_option('email_body',$email_body);
	mw_set_option('colelgiate_dictionary',$colelgiate_dictionary);
	
	
	$end_portion_url = mw_get_option('end_portion');
	$email_test = $_POST['email_test'];
	if($email_test != ''){
		$message = '
		<html>
		<head>
		    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		</head>
		<body>
		<div style="width:90%; background:#fff; padding:20px;float:left;box-shadow: 0 0 10px rgba(185, 184, 184, 0.81);margin: 10px;">
						<div style="width:100%;padding:20px;color:#000">
							<p>'.$email_body.'</p>
							<p style="font-weight:bold;font-size:22px;margin:10px 0">'.$colelgiate_dictionary.'</p>
							<p></p><p></p>';
		if($end_portion_url != ''){
			$message .= '<img src="'.$end_portion_url.'" >';
		}
		$message .= '</div></div>
		</body>
		</html>';
		
		if(wp_mail($email_test,$subject_email, $message)){
			add_history_email_marketing(1,0,$colelgiate_dictionary);
			ik_enqueue_messages(__('Send email test successfully.','iii-dictionary'),'success');
			wp_redirect(site_admin_url().'?r=email-marketing');
			exit;
		}
		else{
			ik_enqueue_messages(__('Send email test fail.','iii-dictionary'),'error');
			wp_redirect(site_admin_url().'?r=email-marketing');
			exit;
		}
	}else{
		ik_enqueue_messages(__('You have not type test email address.','iii-dictionary1'),'notice');
		wp_redirect(site_admin_url().'?r=email-marketing');
		exit;
	}
	
}
?>

<?php get_dict_header('Email Marketing') ?>
<?php get_dict_page_title('Admin Email Marketing', 'admin-page'); 
?>
	<form method="post" action="<?php echo site_admin_url(); ?>?r=email-marketing" id="form_email_marketing" enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">
				<h2 class="title-border"><?php _e('Email Type','iii-dictionary'); ?></h2>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12"> 
				<div class="form-group">
					<label><?php _e('Language Type','iii-dictionary'); ?></label>
					<?php 
					if(mw_get_option('language_type') != ''){
						$default_lang = mw_get_option('language_type');
					}else{
						$default_lang = 'en';
					}
					
					MWHtml::language_type($default_lang); ?>
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12"> 
				<div class="form-group">
					<label><?php _e('Rate of Emil to send out (Daily)','iii-dictionary'); ?></label>
					<input type="number" min="1" name="rate_email" value="<?php echo mw_get_option('rate_email'); ?>" class="form-control w100">
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12"> 
				<div class="form-group">
					<label><?php _e('Subject in the email','iii-dictionary'); ?></label>
					<input type="text" name="subject_email" required="required" value="<?php echo mw_get_option('subject_email'); ?>" class="form-control w100">
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12"> 
				<div class="form-group">
					<label><?php _e('UPLOAD'); ?></label>
                    <?php $email_list_url = mw_get_option('email_list_url');
					if($email_list_url != ''){ ?>
                    <p class="show_file"><label><?php echo mw_get_option('email_list_name'); ?></label><button class="btn btn-default form-control btn-tiny grey remove_file" style="float:right;width:auto" type="button" ><?php _e('Remove File','iii-dictionary'); ?></button></p>
                    <input type="file" name="email_list" style="display:none" class="form-control btn_upload_file" accept=".csv">
                    <?php }else{ ?>
					<input type="file" name="email_list" class="form-control btn_upload_file" accept=".csv">
					<?php } ?>
				</div>
			</div>
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="form-group">
					<label><?php _e('Email Body','iii-dictionary'); ?></label>
					<?php
					wp_enqueue_media();
					$cotent = '<div id="email_body_editor">'.mw_get_option('email_body').'</div>';
					$setting = array(
						'media_buttons' => false,
						'textarea_name' => 'email_body',
						'textarea_rows' => 10,
						'quicktags' => false,
						'media_buttons' => true
					);
					wp_editor($cotent,'email_body',$setting);
					?>
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-12">
				<div class="form-group">
					<label><?php _e('A word from Collegiate Dictionary','iii-dictionary'); ?></label>
					<input type="text" name="colelgiate_dictionary" value="<?php echo mw_get_option('colelgiate_dictionary'); ?>" class="form-control w100 colelgiate_dictionary_em">
				</div>
			</div>
			<div class="col-md-6 col-sm-6 col-xs-6">
				<div class="form-group">
					<label><?php _e('The Ending portion of email','iii-dictionary'); ?></label>
					<?php if(mw_get_option('end_portion') != ''){ ?>
						<p class="show_image">
							<label><?php echo mw_get_option('end_portion_name'); ?></label>
							<button class="btn btn-default form-control btn-tiny grey remove_image" style="float:right;width:auto" type="button" ><?php _e('Remove File','iii-dictionary'); ?></button>
						</p>
						<input type="file" style="display:none" name="end_portion" class="form-control upload_image" accept="image/*">
					<?php }else{ ?>
						<input type="file" name="end_portion" class="form-control upload_image" accept="image/*">
					<?php } ?>
				</div>
			</div>
			<div class="col-md-7 col-sm-7 col-xs-12">
			</div>
			<div class="col-md-5 col-sm-5 col-xs-12">
				<div class="form-group">
					<button class="btn btn-default orange form-control" type="submit" name="send_restart"><span class="glyphicon glyphicon-floppy-save" style="margin-right: 5px;"></span> <?php _e('Send','iii-dictionary'); ?></button>
				</div>
			</div>
			<div class="col-md-7 col-sm-7 col-xs-12" style="text-align:right">	
				<label style="padding:5px 0">
					<?php 
					$total_sent_out = mw_get_option('total_sent_out_email_marketing');
					if($total_sent_out > '0'){
						_e('We sent','iii-dictionary'); 
						echo ' '.$total_sent_out.' ';
						_e('emails in list','iii-dictionary');
						echo ' '.mw_get_option('total_emails_marketing').' ';
						_e('emails.','iii-dictionary');
					}
					?> 
				</label>
			</div>
			<div class="col-md-5 col-sm-5 col-xs-12">
				<div class="form-group">
					<?php 
					if(mw_get_option('last_email_id') >=  mw_get_option('total_emails_marketing')){ ?>
						<button class="btn btn-default orange form-control sent-all-email" type="button" name="btn_save"><span class="glyphicon glyphicon-floppy-save" style="margin-right: 5px;"></span> <?php _e('Send the next','iii-dictionary'); ?> <?php echo mw_get_option('rate_email'); ?></button>
					<?php 
					}else{ 
						if(mw_get_option('last_day_send') == date('d')){ ?>
						<button class="btn btn-default orange form-control continute-send-email" type="button" name="btn_save"><span class="glyphicon glyphicon-floppy-save" style="margin-right: 5px;"></span> <?php _e('Send the next','iii-dictionary'); ?> <?php echo mw_get_option('rate_email'); ?></button>
					<?php }else{ ?>
						<button class="btn btn-default orange form-control" type="submit" name="btn_save"><span class="glyphicon glyphicon-floppy-save" style="margin-right: 5px;"></span> <?php _e('Send the next','iii-dictionary'); ?> <?php echo mw_get_option('rate_email'); ?></button>
					<?php 
						} 
					}?>
					
				</div>
			</div>
            <?php //} ?>
            <div class="col-md-12 col-sm-12 col-xs-12">
				<h2><?php _e('Email Test','iii-dictionary'); ?></h2>
				<label><?php _e('Test E-mail address','iii-dictionary'); ?> </label>
			</div>
			<div class="col-md-8 col-sm-8 col-xs-12">
				<div class="form-group">
					<input type="email" name="email_test" class="form-control"> 
				</div>
			</div>
			<div class="col-md-5 col-sm-5 col-xs-12">
					<div class="form-group">
						<button class="btn btn-default orange form-control" type="submit" name="test_send_email"><span class="glyphicon glyphicon-floppy-save" style="margin-right: 5px;"> </span><?php _e('Send','iii-dictionary'); ?></button>
				</div>
			</div>
		</div>
		<div class="modal fade modal-red-brown modal-large "  id="continute-send-tomorrow-modal" tabindex="-1" role="dialog" aria-hidden="true">
		  	<div class="modal-dialog">
				<div class="modal-content">
				  	<div class="modal-header">
						<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
						<h3 class="modal-title">Message</h3>
				  	</div>
				  	<div class="modal-body">
						<p>
						<?php _e('Today you have sent emails. Do you want to send the next set again?','iii-dictionary'); ?>
						</p>
						<p style="text-align:right">
							<button type="submit" name="btn_save" style="padding:6px 30px" class="btn btn-default orange" id="send_next_set">Yes</button>
							<a href="#" style="padding:6px 30px" data-dismiss="modal" class="btn btn-default orange" aria-hidden="true">No</a>
						</p>	
					</div>
		      	</div>
		    </div>
		</div>
	</form>

<?php
$sql1 = "SELECT * FROM {$wpdb->prefix}dict_history_email_marketing ORDER BY id DESC";
$record_email_marketing = $wpdb->get_results($sql1);
//var_dump(ik_get_message_queue());
?>
<div class="row">
	<div class="col-md-6 col-sm-6 col-xs-12 words_list">
		<label><?php _e('The words you sent out'); ?></label>
		<div class="scroll-list2" style="max-height: 200px">
			<table style="width:100%">
				<?php if($record_email_marketing){
					foreach ($record_email_marketing as $key => $value) { ?>
					<tr>
						<td style="width:70%"><?php echo $value->word; ?></td>
						<td><?php echo date("Y-m-d",strtotime($value->time_sent)); ?></td>
						<td><?php echo number_format($value->success); ?></td>
					</tr>	
				<?php }
				} ?>
			</table>
		</div>
	</div>
</div>

<div class="modal fade modal-red-brown modal-large "  id="sent-all-email-modal" tabindex="-1" role="dialog" aria-hidden="true">
  	<div class="modal-dialog">
		<div class="modal-content">
	  	<div class="modal-header">
			<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
			<h3 class="modal-title">Message</h3>
			<div class="modal-body">
				<p>
				<?php _e('You have sent email to all emails in the list','iii-dictionary'); ?>
				</p>	
			</div>
	  	</div>
      	</div>
    </div>
</div>

<?php MWHtml::ik_site_messages(); 

get_dict_footer() ?>