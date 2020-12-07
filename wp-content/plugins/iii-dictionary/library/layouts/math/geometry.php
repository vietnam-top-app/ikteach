<?php
	$levels = MWDB::get_grades(array('type' => 'MATH', 'level' => 1, 'parent_id' => MATH_GEOMETRY, 'orderby' => 'ordering', 'order-dir' => 'asc'));

?>
<?php get_math_header(__('Geometry', 'iii-dictionary')) ?>
<?php get_dict_page_title(__('Geometry', 'iii-dictionary')) ?>

<?php MWHtml::select_math_level_page($levels) ?>

<?php get_math_footer() ?>