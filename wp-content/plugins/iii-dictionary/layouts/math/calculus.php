<?php
	$levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'parent_id' => MATH_CALCULS, 'orderby' => 'ordering', 'order-dir' => 'asc'));

?>
<?php get_math_header(__('Calculus', 'iii-dictionary')) ?>
<?php get_dict_page_title(__('Calculus', 'iii-dictionary')) ?>

<?php MWHtml::select_math_level_page($levels) ?>

<?php get_math_footer() ?>