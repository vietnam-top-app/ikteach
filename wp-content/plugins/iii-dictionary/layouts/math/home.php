<?php 
	$link_list_group = get_option_name_link();
	//some function at home
	if(!empty($_POST['data-join'])) {
		MWDB::lang_join_group($_POST);
	}
?>
<?php get_header('math'); 

$URL = $_SERVER['REQUEST_URI'];
$segment = explode('/',$URL);
if(isset($segment) && $segment[2] == 'mathteacher'){
	include 'math_teacher.php';
}else{
	?>
	<main class="home home-math" id="home">
		<div id="content">
			<div id="top-content" style="position: relative;">
				<?php MWHtml::get_list_lang() ?>
				<?php MWHtml::manage_your_class() ?>
				<div class="column-header">
					<a href="<?php echo locale_home_url() ?>/?r=manage-subscription" class="black-color"><?php _e('Do you have an activation code? Click here', 'iii-dictionary') ?> &gt;</a>
				</div>
				<div class="left-column">
					<div class="column-content">
						<h2><?php _e('Online Math Classroom management for teachers & students', 'iii-dictionary') ?></h2>
						<small><?php _e('Using Cloud Server', 'iii-dictionary') ?></small>
					</div>
					<div class="group-left-column">
						<?php if(is_home_page()) : ?>
							<div class="block-left-column block-goto_english">
								<a href="<?php echo site_home_url(); ?>">Go to <span class="bold">ENGLISH<span></a>
							</div>
						<?php endif ?>
						<?php if(!empty($link_list_group)) : ?>
							<div class="block-left-column block-specific">
								<a href="#" id="class-with-lang"><?php echo $link_list_group ?></a>
							</div>
						<?php endif ?>
					</div>
				</div>
				<div class="right-column">
					<div class="column-content">
						<div class="benefits-list">
							<h3><?php _e('Online math assignments organized for teachers:', 'iii-dictionary') ?></h3>
							<ul>
								<li><?php _e('Covers grades 1 to 12', 'iii-dictionary') ?></li>
								<li><?php _e('Explanations, example problems, and problem assignments', 'iii-dictionary') ?></li>
								<li><?php _e('Auto graded assignments', 'iii-dictionary') ?></li>
								<li><?php _e('Chat tool with graphics to help students in real time', 'iii-dictionary') ?></li>
								<li><?php _e('Teachers can earn money selling their own homework to other teachers', 'iii-dictionary') ?></li>
							</ul>
							<div class="separator"></div>
							<h3><?php _e('Students- math self-study and prepare for the SAT:', 'iii-dictionary') ?></h3>
							<ul>
								<li><?php _e('Complete math self-study system', 'iii-dictionary') ?></li>
								<li><?php _e('Online homework from teachers', 'iii-dictionary') ?></li>
								<li><?php _e('Preparation for SAT test', 'iii-dictionary') ?></li>
								<li><?php _e('SAT practice test', 'iii-dictionary') ?></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="findout-more">
					<div>
						<a href="#new-to-our-product-dialog-math" data-toggle="modal"><?php _e('Click Here', 'iii-dictionary') ?> <span class="icon-rarrow2"></span></a>
						<p><?php _e('Find out more', 'iii-dictionary') ?></p>
					</div>
				</div>
			</div>
			<div class="bottom-content">
				<section class="block-content">
					<?php _e('<h2>Why</h2> <h3>ik-Math?</h3>', 'iii-dictionary') ?>
					<a href="#why-merriam-dialog-math" data-toggle="modal"><u><?php _e('Details', 'iii-dictionary') ?></u> &gt;</a>
				</section>

				<section class="block-content">
					<h3><?php _e('How can a person tutors students on this site?', 'iii-dictionary') ?></h3>
					<a href="<?php echo locale_home_url() ?>/?r=teaching"><u><?php _e('Details', 'iii-dictionary') ?></u> &gt;</a>
				</section>

				<section class="block-content">
					<h3><?php _e('Math Self-study the prepation of SAT I and SAT II', 'iii-dictionary') ?></h3>
					<a href="<?php echo locale_home_url() ?>/?r=sat-preparation/sat1prep&client=math-sat1"><u><?php _e('SAT I', 'iii-dictionary') ?></u> &gt;</a>
					<a id="omg_home-satii" href="<?php echo locale_home_url() ?>/?r=sat-preparation/sat2prep&client=math-sat2"><u><?php _e('SAT II', 'iii-dictionary') ?></u> &gt;</a>
				</section>
			</div>
		</div>
	</main>
	<?php
}
?>
		

<div id="new-to-our-product-dialog-math" class="modal fade modal-white" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close2"></a>
        </div>
		<div class="modal-body visible-md visible-lg">
			<ul>
				<li><a class="view-sub-modal" href="#" data-img="<?php echo get_info_tab_cloud_url_math('MPopup_info_3.jpg') ?>"><?php _e('Designed to support self-study with tutorials and plenty of excercises', 'iii-dictionary') ?></a></li>
				<li><a class="view-sub-modal" href="#" data-img="<?php echo get_info_tab_cloud_url_math('MPopup_info_4.jpg') ?>"><?php _e('Start at any level from 1 to 12 grade', 'iii-dictionary') ?></a></li>
				<li><a class="view-sub-modal" href="#" data-img="<?php echo get_info_tab_cloud_url_math('MPopup_info_5.jpg') ?>"><?php _e('Complete Preparation for SAT I and II', 'iii-dictionary') ?></a></li>
				<li><a class="view-sub-modal" href="#" data-img="<?php echo get_info_tab_cloud_url_math('MPopup_info_6.jpg') ?>"><?php _e('Increase the confidence in Math in school', 'iii-dictionary') ?></a></li>
			</ul>
		</div>
      </div>
    </div>
</div>

<div id="why-merriam-dialog-math" class="modal fade modal-white" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close2"></a>
        </div>
      </div>
    </div>
</div>

<div id="made-teacher-dialog" class="modal fade modal-white" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
        </div>
        <a href="#" data-dismiss="modal" aria-hidden="true" class="btn orange dismiss-modal"><span class="icon-switch"></span> <?php _e('Go back', 'iii-dictionary') ?></a>
      </div>
    </div>
</div>

<div id="popup-info-dialog" class="modal fade modal-white modal-no-padding" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body">
            <a href="#" data-dismiss="modal" aria-hidden="true" class="close-transp"></a>
			<img id="popup-info-img" src="#" alt="">
        </div>
      </div>
    </div>
</div>
<script>
	(function($){
		$(function(){
			$(".view-sub-modal").click(function(e){
				e.preventDefault();
				var _img = $("#popup-info-img");
				var _m = $("#popup-info-dialog");
				_img.attr("src", $(this).attr("data-img")).load(function(){
					_m.find(".modal-dialog").width(this.width);
				});
				$("#new-to-our-product-dialog-math").modal("hide").one("hidden.bs.modal", function(){_m.modal()});
			});

			$("#popup-info-dialog").on("hidden.bs.modal", function(){
				$("#new-to-our-product-dialog-math").modal();
			});
		});
	})(jQuery);
</script>
<?php if(is_user_logged_in() && isset($_SESSION['newuser'])) : ?>
	<div id="signup-success-dialog" class="modal fade modal-red-brown" aria-hidden="true">
		<div class="modal-dialog">
		  <div class="modal-content">
			<div class="modal-header">
				<a href="#" data-dismiss="modal" aria-hidden="true" class="close"></a>
				 <h3><?php _e('Thank you for signing up!', 'iii-dictionary') ?></h3>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-12">
						<p><?php _e('Thank you for signing up on Merriam-Webster and English Learning System.', 'iii-dictionary') ?></p>
						<p><?php _e('If you have a group name for homework assignment, you can check it at <strong><em>My Account</em></strong> Area.', 'iii-dictionary') ?></p>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
							<a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-block orange secondary"><span class="icon-check"></span><?php _e('Let\'s Begin!', 'iii-dictionary') ?></a>
						</div>
					</div>
				</div>
			</div>
		  </div>
		</div>
	</div>
	<script>
		(function($){ $(function(){ $('#signup-success-dialog').modal('show'); }); })(jQuery);
	</script>
<?php $_SESSION['newuser'] = null; endif ?>
<?php MWHtml::ik_site_messages(); 
get_footer('math') ?>