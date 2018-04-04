<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Missioncontrol
 * @author     Ninad Ramade <ninad.ramade@gmail.com>
 * @copyright  2016 Ninad Ramade
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined ( '_JEXEC' ) or die ();

//JHtml::_ ( 'behavior.keepalive' );
//JHtml::_ ( 'behavior.tooltip' );
//JHtml::_ ( 'behavior.formvalidation' );
JHtml::_ ( 'formbehavior.chosen', 'select' );
JHTML::_ ( 'behavior.calendar' );
// Load admin language file
$lang = JFactory::getLanguage ();
$lang->load ( 'com_landlord', JPATH_SITE );
$doc = JFactory::getDocument ();
$doc->addScript ( JUri::base () . '/media/com_landlord/js/form.js' );
?>
<style>
table
{
	float:left;
	width:100%;
	border:1px solid white;
}
th,td
{
	padding:5px 10px;
	border-right:1px solid white;
	width: 50%;
	text-align:center;
}
@media only screen and (min-width: 600px) {
	table
	{
		width:40%;
	}
}
h3
{
	text-align:left;
}
	.dash_wrapper
	{
		text-align:center;
	}
	.progressbar
	{
		width: 100%;
  		background-color: #ddd;
	}
	.completion
	{
		height: 30px;
		background-color: #4CAF50;
		text-align: center;
		line-height: 30px;
		color: white;
		font-size:16px;
	}
	.next_level
	{
		text-align: right;
    	font-size: 11px;
	}
</style>
<div class="dash_wrapper">
	<div class="profile">
		<h1><?php echo JFactory::getUser()->name; ?></h1>
		</div>
	<div class="property_value"><h3>PROPERTY VALUE</h3>
	<?php 
	$next_level=LandlordHelpersLandlord::getLevelData($this->portfolio->level+1);
	$completion_percent=($this->portfolio->property_value/$next_level->portfolio_value)*100;
	?>
	<div class="progressbar">
			<div class="completion" style="width:<?php echo $completion_percent.'%';?>;"><?php echo $this->portfolio->property_value.' PRC'; ?></div>
		</div>
		<div class="next_level">Need <?php echo $next_level->portfolio_value-$this->portfolio->property_value.' PRC';?> for next Level <?php echo $next_level->level_id; ?></div>
	</div>
	<div class="cash_balance"><h3>CASH BALANCE</h3>
		<?php 
		$current_level=LandlordHelpersLandlord::getLevelData($this->portfolio->level);
		$limit_percent=($this->portfolio->wallet_balance/$current_level->cash_limit)*100;
	?>
	<div class="progressbar">
			<div class="completion" style="width:<?php echo $limit_percent.'%';?>;"><?php echo $this->portfolio->wallet_balance.' PRC'; ?></div>
		</div>
		<div class="next_level">Cash Limit <?php echo $current_level->cash_limit; ?></div>
	</div>
	<div class="revenue">
		<h3>REVENUE</h3>
		<table>
			<tr><th>LAST 24 HOURS</th><th>LAST WEEK</th></tr>
			<tr><td><?php echo $this->last_paid.' PRC'; ?></td><td><?php echo $this->weekly_rent.' PRC'; ?></td></tr>
		</table>
	</div>
</div>