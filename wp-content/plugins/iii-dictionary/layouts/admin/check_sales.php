<?php
	$report = MWDB::get_total_sales();
	/*
	echo "<pre>";
	var_dump($report);
	echo "</pre>";
	die();
	*/
?>
<?php get_dict_header('Check Sales') ?>
<?php get_dict_page_title('Check Sales', 'admin-page') ?>

	<div class="row">
		<div class="col-xs-12 box box-sapphire">
			<table class="table table-striped table-style1 text-center">
				<thead>
					<th></th>
					<th>Total Sales</th>
					<th>This Month</th>
					<th>Last Month</th>
					<th>2M ago</th>
					<th>3M ago</th>
					<th>4M ago</th>
					<th>6M ago</th>
					<th>Below 6M</th>
				</thead>
				<tbody>
					<tr>
						<td></td>
						<td><?php echo number_format($report['total']['all']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['all']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['all']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['all']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['all']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['all']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['all']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['all']->amount, 1) ?></td>
					</tr>
					<tr>
						<td colspan="9">&nbsp;</td>
					</tr>
					<tr>
						<td>Teacher's Tool</td>
						<td><?php echo number_format($report['total']['teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['teacher']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Elementary Dictionary</td>
						<td><?php echo number_format($report['total']['elementary']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['elementary']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['elementary']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['elementary']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['elementary']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['elementary']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['elementary']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['elementary']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Intermediate Dictionary</td>
						<td><?php echo number_format($report['total']['intermediate']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['intermediate']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['intermediate']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['intermediate']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['intermediate']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['intermediate']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['intermediate']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['intermediate']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Medical Dictionary</td>
						<td><?php echo number_format($report['total']['medical']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['medical']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['medical']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['medical']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['medical']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['medical']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['medical']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['medical']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Collegiate Dictionary</td>
						<td><?php echo number_format($report['total']['collegiate']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['collegiate']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['collegiate']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['collegiate']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['collegiate']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['collegiate']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['collegiate']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['collegiate']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>All Dictionaries</td>
						<td><?php echo number_format($report['total']['alldic']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['alldic']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['alldic']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['alldic']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['alldic']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['alldic']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['alldic']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['alldic']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>E Learner's Dictionary</td>
						<td><?php echo number_format($report['total']['learner']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['learner']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['learner']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['learner']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['learner']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['learner']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['learner']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['learner']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>SAT Preparation - Grammar</td>
						<td><?php echo number_format($report['total']['sat']['grammar']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['sat']['grammar']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['sat']['grammar']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['sat']['grammar']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['sat']['grammar']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['sat']['grammar']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['sat']['grammar']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['sat']['grammar']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>SAT Preparation - Writing</td>
						<td><?php echo number_format($report['total']['sat']['writing']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['sat']['writing']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['sat']['writing']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['sat']['writing']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['sat']['writing']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['sat']['writing']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['sat']['writing']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['sat']['writing']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>SAT Preparation - Test</td>
						<td><?php echo number_format($report['total']['sat']['sat_test']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['sat']['sat_test']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['sat']['sat_test']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['sat']['sat_test']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['sat']['sat_test']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['sat']['sat_test']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['sat']['sat_test']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['sat']['sat_test']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Point Purchase</td>
						<td><?php echo number_format($report['total']['point']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['point']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['point']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['point']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['point']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['point']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['point']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['point']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Self-study</td>
						<td><?php echo number_format($report['total']['self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['self_study']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Math Self-study</td>
						<td><?php echo number_format($report['total']['math_self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['math_self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['math_self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['math_self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['math_self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['math_self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['math_self_study']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['math_self_study']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Math Teacher Tool</td>
						<td><?php echo number_format($report['total']['math_teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['math_teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['math_teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['math_teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['math_teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['math_teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['math_teacher']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['math_teacher']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Math SATI</td>
						<td><?php echo number_format($report['total']['sati']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['sati']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['sati']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['sati']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['sati']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['sati']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['sati']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['sati']['pre']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Math SATI - Test</td>
						<td><?php echo number_format($report['total']['sati']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['sati']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['sati']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['sati']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['sati']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['sati']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['sati']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['sati']['test']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Math SATII</td>
						<td><?php echo number_format($report['total']['satii']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['satii']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['satii']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['satii']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['satii']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['satii']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['satii']['pre']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['satii']['pre']->amount, 1) ?></td>
					</tr>
					<tr>
						<td>Math SATII - Test</td>
						<td><?php echo number_format($report['total']['satii']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['this_month']['satii']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['last_month']['satii']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['2m_ago']['satii']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['3m_ago']['satii']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['4m_ago']['satii']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['6m_ago']['satii']['test']->amount, 1) ?></td>
						<td><?php echo number_format($report['below_6m']['satii']['test']->amount, 1) ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

<?php get_dict_footer() ?>