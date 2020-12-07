<?php
	$is_math_panel 	= is_math_panel();
	$_page_title 	= __('Payments', 'iii-dictionary');
//var_dump($_POST);die;
	if(isset($_POST['add-to-cart']))
	{
		ik_add_to_cart($_POST);
		wp_redirect(home_url_ssl() . '/?r=payments' );
		exit;
	}
	
	if(isset($_POST['delete-cart-item']))
	{
		ik_delete_cart_item($_POST);

		wp_redirect(home_url_ssl() . '/?r=payments' );
		exit;
	}

	// process transaction
	if(isset($_POST['process']))
	{
		if($_POST['payment-method'] == 2) {
			// pay with paypal
		}
		else if ($_POST['payment-method'] == 3) {
			// pay with point balance
			ik_process_point_payment();
		}
		else {
			// pay with credit card
			ik_process_transaction();
		}
		
		if(!empty($_SESSION['return_math'])) {
			$ref = $_SESSION['return_math'];
			unset($_SESSION['return_math']);
			wp_redirect($ref);
		}else {
			wp_redirect(home_url_ssl() . '/?r=manage-subscription' );
		}
		exit;
	}

	$point_ex_rate = mw_get_option('point-exchange-rate');
	$cart_items = get_cart_items();
	$cart_amount = is_null(get_cart_amount()) ? 0 : get_cart_amount();
	//var_dump($cart_items);die;
?>
<?php if(!$is_math_panel) : ?>
	<?php get_dict_header($_page_title) ?>
<?php else : ?>
	<?php get_math_header($_page_title, 'red-brown') ?>
<?php endif ?>
<?php get_dict_page_title($_page_title) ?>

	<form method="post" action="<?php echo home_url_ssl() ?>/?r=payments">
		<div class="row">
			<div class="col-xs-12">											
				<div class="row">
					<div class="col-sm-12">
						<h2 class="title-border"><?php _e('Subscription to purchase', 'iii-dictionary') ?></h2>
					</div>
					<div class="col-sm-12">
						<div class="box">
							<div class="scroll-list" style="max-height: 300px">
								<table class="table table-striped table-style2 text-center">
									<thead>
										<tr>
											<th class="hidden-xs"></th>
											<th><?php _e('Type', 'iii-dictionary') ?></th>
											<th><?php _e('Months', 'iii-dictionary') ?></th>
											<th><?php _e('No. of Students', 'iii-dictionary') ?></th>
											<th><?php _e('No. of Users', 'iii-dictionary') ?></th>
											<th class="hidden-xs"><?php _e('Dictionary', 'iii-dictionary') ?></th>
											<th><?php _e('No. of Points', 'iii-dictionary') ?></th>
											<th><?php _e('Price', 'iii-dictionary') ?></th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php if(!empty($cart_items)) :
												foreach($cart_items as $key => $item) : ?>
												<tr>
													<td class="hidden-xs"><?php echo $key + 1 ?>.</td>
													<td><?php echo $item->type; echo $item->extending ? ' ' . __('(Additional)', 'iii-dictionary') : '' ?></td>
													<td><?php echo $item->no_months ?></td>
													<td><?php echo in_array($item->typeid, array(SUB_TEACHER_TOOL, SUB_TEACHER_TOOL_MATH, SUB_GROUP)) ? $item->no_students : 'N/A' ?></td>
													<td><?php echo in_array($item->typeid, array(SUB_DICTIONARY, SUB_SELF_STUDY, SUB_SELF_STUDY_MATH, SUB_GROUP)) ? $item->no_students : 'N/A' ?></td>
													<td class="hidden-xs"><?php echo empty($item->dictionary) ? 'N/A' : $item->dictionary ?></td>
													<td><?php echo empty($item->no_of_points) ? 'N/A' : $item->no_of_points ?></td>
													<td>$ <?php echo $item->price ?></td>
													<td><button type="submit" name="delete-cart-item" value="<?php echo $item->id ?>" class="btn btn-default btn-tiny orange delete-item" style="margin: 0"><?php _e('Delete', 'iii-dictionary') ?></button></td>
												</tr>
											<?php endforeach;
											else : ?>
												<tr><td colspan="9"><?php _e('Your cart is empty', 'iii-dictionary') ?></td></tr>
											<?php endif ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="col-sm-12">
						<p class="box" style="text-align: right; margin-top: 20px">
							<?php _e('Total amount:', 'iii-dictionary') ?> <span class="currency">$</span> <span id="total-amount"><?php echo $cart_amount ?></span>
						</p>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<section class="col-xs-12">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="title-border"><?php _e('Payment Method', 'iii-dictionary') ?></h2>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 form-group">
						<div class="radio radio-style1">
							<input id="exist-card" type="radio" name="payment-method" value="0" checked>  
							<label for="exist-card"><?php _e('Use existing credit card?', 'iii-dictionary') ?></label>
						</div>
					</div>
				</div>
				<div class="row" id="existing-card-block">
					<div class="col-xs-11 col-sm-6 col-md-5 form-group payments-fields">													
						<?php MWHtml::user_credit_cards() ?>
					</div>
					<div class="col-xs-4 col-sm-2 form-group payments-fields">
						<input type="text" class="form-control" name="re_ssl_cvv2cvc2" value="" placeholder="CVV" autocomplete="off">
					</div>
					<div class="col-xs-6 col-sm-3">
						<div class="card-icons"></div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 form-group">
						<div class="radio radio-style1">
							<input id="new-card" type="radio" name="payment-method" value="1">
							<label for="new-card"><?php _e('Use new credit card?', 'iii-dictionary') ?></label>
						</div>
					</div>
				</div>
				<div class="row" id="new-card-info-block" style="display: none">
					<div class="col-xs-12">
						<div class="title-border card-info" style="margin: 10px 0 10px 40px"></div>
						<div class="row card-info" style="margin-left: 25px">												
							<div class="col-sm-6 col-md-3">
								<div class="form-group">
									<label><?php _e('Select credit card type', 'iii-dictionary') ?></label>
									<?php MWHtml::credit_cards() ?>
								</div>					
							</div>
							<div class="col-sm-6 col-md-4">
								<div class="form-group">
									<label for="ssl_card_number"><?php _e('Credit card number', 'iii-dictionary') ?></label>
									<input type="text" class="form-control" id="ssl_card_number" name="ssl_card_number" value="" autocomplete="off">
								</div>
							</div>
							<div class="col-ssm-6 col-sm-6 col-md-3">
								<div class="form-group">
									<label style="width: 100%"><?php _e('Expiration date', 'iii-dictionary') ?></label>
									<select class="select-box-it sel-exp-date" name="exp_date_mm">
										<option value="">MM</option>
										<?php for($i = 1; $i <= 12; $i++) : ?>
											<?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
											<option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
										<?php endfor ?>
									</select>
									<select class="select-box-it sel-exp-date" name="exp_date_yy">
										<option value="">YY</option>
										<?php for($i = 0; $i <= 52; $i++) : ?>
											<?php $pad_str = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
											<option value="<?php echo $pad_str ?>"><?php echo $pad_str ?></option>
										<?php endfor ?>
									</select>
								</div>
							</div>
							<div class="col-ssm-6 col-sm-6 col-md-2">
								<div class="form-group">
									<label for="ssl_cvv2cvc2">CVV/CVC</label>
									<input type="text" class="form-control" id="ssl_cvv2cvc2" name="ssl_cvv2cvc2" value="" autocomplete="off">
								</div>
							</div>
						</div>
						<div class="title-border card-info" style="margin: 10px 0 10px 40px"></div>
						<div class="row card-info" style="margin-left: 25px">
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<label for="firstname"><?php _e('First name', 'iii-dictionary') ?></label>
									<input type="text" class="form-control" id="firstname" name="firstname" value="">
								</div>
							</div>
							<div class="col-xs-12 col-sm-6">
								<div class="form-group">
									<label for="lastname"><?php _e('Last name', 'iii-dictionary') ?></label>
									<input type="text" class="form-control" id="lastname" name="lastname" value="">
								</div>
							</div>
							<div class="col-xs-12">
								<div class="form-group">
									<label for="ssl_avs_address"><?php _e('Billing address', 'iii-dictionary') ?></label>
									<input type="text" class="form-control" id="ssl_avs_address" name="ssl_avs_address" value="">
								</div>
							</div>
							<div class="col-xs-12 col-sm-12 col-md-6">
								<div class="form-group">
									<label for="city"><?php _e('City', 'iii-dictionary') ?></label>
									<input type="text" class="form-control" id="city" name="city" value="">
								</div>
							</div>
							<div class="col-xs-8 col-sm-8 col-md-4">
								<div class="form-group">
									<label for="state"><?php _e('State', 'iii-dictionary') ?></label>
									<input type="text" class="form-control" id="state" name="state" value="">
								</div>
							</div>
							<div class="col-xs-4 col-sm-4 col-md-2">
								<div class="form-group">
									<label for="ssl_avs_zip"><?php _e('Zip', 'iii-dictionary') ?></label>
									<input type="text" class="form-control" id="ssl_avs_zip" name="ssl_avs_zip" value="">
								</div>
							</div>												
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 form-group">
						<div class="radio radio-style1">
							<input id="paypal" type="radio" name="payment-method" value="2">  
							<label for="paypal"><?php _e('Pay with Paypal', 'iii-dictionary') ?></label>
						</div>
					</div>
					<div class="col-xs-12" id="paypal-block" style="display: none">
						<div style="margin-left: 40px">
							<input type="image" id="paypal-submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" alt="PayPal - The safer, easier way to pay online!">
							<p class="text-alert">
								<?php _e('<strong>Note:</strong> Paypal might take sometimes to process your payment. If you don\'t see the item you paid in Subscription history, please log out and log in again after a few minutes', 'iii-dictionary') ?>
							</p>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 form-group">
						<div class="radio radio-style1">
							<input id="points-balance" type="radio" name="payment-method" value="3">  
							<label for="points-balance"><?php _e('Pay with my points balance', 'iii-dictionary') ?></label>
						</div>
					</div>
					<div class="col-xs-12" id="points-balance-block" style="display: none">
						<div style="margin-left: 40px">
							<div class="form-group">
								<label><?php _e('Your current points is', 'iii-dictionary') ?> <em class="text-info">(Exchange rate: <?php echo $point_ex_rate ?>pts = 1$)</em></label>
								<div class="box box-green">
									<h3 class="positive-amount"><?php echo number_format(ik_get_user_points(), 2) ?></h3>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row" style="margin-left: 25px">
					<div class="col-xs-12 col-sm-6 top-buffer">
						<button type="submit" name="process" id="process-btn" class="btn btn-default btn-block orange"><span class="icon-cart"></span><?php _e('Check out', 'iii-dictionary') ?></button>
					</div>
					<div class="col-xs-12 col-sm-6 top-buffer">
						<a href="<?php echo locale_home_url() ?>/?r=manage-subscription" class="btn btn-default btn-block sky-blue"><span class="icon-goto"></span><?php _e('Continue shopping', 'iii-dictionary') ?></a>
					</div>
				</div>
			</section>
		</div>
		<input type="hidden" id="item-count" value="<?php echo count($cart_items) ?>">
	</form>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" class="hidden">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="payment@innovative-knowledge.com">
		<input type="hidden" name="item_name" value="Subscription">
		<input type="hidden" name="amount" value="<?php echo $cart_amount ?>">
		<input type="hidden" name="custom" value="<?php echo get_current_user_id() ?>">
		<input type="hidden" name="return" value="<?php echo home_url_ssl() ?>/?r=manage-subscription">
		<input type="image" id="paypal-btn" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit">
		<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</form>

<div id="process-tran-modal" class="modal fade modal-white" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog">
	  <div class="modal-content">
		<div class="modal-header">
			<div class="three-quarters-loader"><?php _e('Loading...', 'iii-dictionary') ?></div>
			<h3><span class="icon-warning"></span><?php _e('Payment Processing...', 'iii-dictionary') ?></h3>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="col-md-12">
					<span class="icon-credit"></span>
					<p><?php _e('Now processing the payment.', 'iii-dictionary') ?><br>
						<?php _e('Please don\'t close the window until it is completed.', 'iii-dictionary') ?></p>
				</div>
			</div>
		</div>
	  </div>
	</div>
</div>

<script>
	var open_mt4 = <?php echo !is_null($_SESSION['open_method_point']) ? 1 : 0 ?>;
	(function($){
		$(function(){
			$("#process-btn").click(function(){
				$("#process-tran-modal").modal();
			});

			$("#exist-card").change(function(){
				if($(this).is(':checked')){
					$("#process-btn").prop("disabled", false);
					$("#existing-card-block").slideDown();
					$("#new-card-info-block, #paypal-block, #points-balance-block").slideUp();
				}
			});

			$("#new-card").change(function(){
				if($(this).is(':checked')){
					$("#process-btn").prop("disabled", false);
					$("#existing-card-block, #paypal-block, #points-balance-block").slideUp();
					$("#new-card-info-block").slideDown();
				}
			});

			$("#paypal").change(function(){
				if($(this).is(':checked')){
					$("#process-btn").prop("disabled", true);
					$("#existing-card-block, #new-card-info-block, #points-balance-block").slideUp();
					$("#paypal-block").slideDown();
				}
			});

			$("#points-balance").change(function(){
				if($(this).is(':checked')){
					$("#process-btn").prop("disabled", false);
					$("#existing-card-block, #paypal-block, #new-card-info-block").slideUp();
					$("#points-balance-block").slideDown();
				}
			});

			$("#paypal-submit").click(function(e){
				e.preventDefault();
				if($("#item-count").val() != "0"){
					$("#paypal-btn").click();
				}
			});
			
			if(open_mt4) {
				$("#points-balance").click();
			}
		});
	})(jQuery);
<?php //unset session to open method point  
	unset($_SESSION['open_method_point']);
?>
</script>

<?php if(!is_math_panel()) : ?>
	<?php get_dict_footer() ?>
<?php else : ?>
	<?php get_math_footer() ?>
<?php endif ?>