<script type="text/javascript">
var ssMenuInstance;
window.onload=function() {
	ssMenuInstance = new sideSlideMenuJS();
	ssMenuInstance.init();
}
</script>

<link type="text/css" rel="stylesheet" href="http://fonts.googleapis.com/css?family=<?php echo $settings['fontfamily']; ?>:<?php echo $settings['fontstyle']; ?>">

<?php 
	$fontWeight = strval(intval($settings['fontstyle']));
	$fontVariant = ltrim($settings['fontstyle'], $fontWeight);
	if ($fontWeight != 0) $fontStyle = $fontWeight . ' ' . $fontVariant; else $fontStyle = $fontVariant;
?>

<style type="text/css">
		#sideSlideMenu {
			background-color: #<?php echo $settings["bgcolor"]; ?>;
			font-family: '<?php echo str_replace("+", " ", $settings["fontfamily"]); ?>';
			font-weight: <?php echo $fontStyle; ?>;
		}
		#sideSlideToggle {
			background-color: #<?php echo $settings["bgcolor"]; ?>;
			color: #<?php echo $settings["txtcolor"]; ?>;
		}

		#sideSlideToggle:hover {
			color: #<?php echo $settings["txthovcol"]; ?>;
			background-color: #<?php echo $settings["bordcolor"]; ?>;
		}
		#sideSlideUl li a {
			color: #<?php echo $settings["txtcolor"]; ?>;
			border-bottom: 1px solid #<?php echo $settings["bordcolor"]; ?>;
		}
		#sideSlideUl li.ssmFCli a {
			border-top: 1px solid #<?php echo $settings["bordcolor"]; ?>;; 
			border-bottom: 1px solid #<?php echo $settings["bordcolor"]; ?>;
		}
			
		#sideSlideUl li a:hover {
			color: #<?php echo $settings["txthovcol"]; ?>;
			background-color: #<?php echo $settings["bordcolor"]; ?>;
		}

		.ssmenuSubmenuToggle, #sideSlideFooter a {
			color: #<?php echo $settings["txtcolor"]; ?>;
		}
		.ssmenuSubmenuToggle:hover, #sideSlideFooter a:hover, #sideSlideFooter {
			color: #<?php echo $settings["txthovcol"]; ?>;
		}
		#sideSlideMenu {
			width: 240px;
		}
		.sideSlideSlidePositivePadding {
			transform: translate(240px, 0px);
		}
		.sideSlideSlideTogglePositive .fa.fa-chevron-circle-right:before {
			content: "\f137";
		}
		#sideSlideToggle {
			top: 120px;
		}
		.sideSlideSlideToggleNegative {
			left: 0;
		}
		.sideSlideSlideTogglePositive {
			left: 240px;
		}
		#sideSlideMenu .user-info {
			font-size: 18px;
			color: #fff;
			padding: 15px 15px 15px 25px;
		}		
		#sideSlideMenu .user-info > img,
		#sideSlideMenu .user-info > div {
			display: inline-block;
		}
		#sideSlideMenu .user-info > img {
			border-radius: 32px;
		}
		#sideSlideMenu .user-info > div {
			margin-left: 10px;
			vertical-align: middle;
		}
</style>