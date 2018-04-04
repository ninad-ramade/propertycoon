<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Landlord
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
	width:100%;
	border:1px solid #4c0120;
}
th,td
{
	padding:5px 10px;
	border-right:1px solid #4c0120;
	width: 50%;
	text-align:center;
}
@media only screen and (min-width: 600px) {
	table
	{
		width:40%;
		margin: 0 auto;
	}
}
.folio_wrapper
{
	float:left;
	width:100%;
	text-align:center;
	color:#4c0120;
}
.place_img
{
    padding: 10px;
}
.place_address
{
	margin-bottom:10px;
}
.place_wrapper
{
	background-color:white;
	padding:10px 0px;
    border-radius:4px;
    width:100%;
    margin-bottom:10px;
}
</style>
<div class="folio_wrapper">
	<?php foreach($this->properties as $pkey=>$eachprop){
		$place_details=json_decode(file_get_contents("https://maps.googleapis.com/maps/api/place/details/json?placeid=".$eachprop->place_id."&key=AIzaSyCTbismgZ1tAeVHX5Dn9OaF3IRtxz_FPVY"));
		$rating=!empty($place_details->result->rating) ? $place_details->result->rating : 1;
	?>
	<div class="place_wrapper">
		<div class="place_image"><img class="place_img" src="<?php echo $place_details->result->icon; ?>" /></div>
		<div class="place_name"><h3 style="margin-bottom:5px;"><?php echo $place_details->result->name; ?></h3></div>
		<div class="place_address"><?php echo $place_details->result->formatted_address; ?></div>
		<div class="place_value">VALUE OF <?php echo $eachprop->percent.'%'?> OWNED<?php $costs=LandlordHelpersLandlord::getCostbyTypeid($eachprop->place_type_id); 
		$value=round(($costs->cost/100)*$eachprop->percent*$rating);?><h3 style="margin-top:0px;"><?php echo $value.' PRC'; ?></h3></div>
		<div class="place_revenue">
			<table>
			<tr><th>RENT (LAST 24 HOURS)</th><th>CHARGES (LAST 24 HOURS)</th><th>TOTAL EARNINGS</th></tr>
			<tr><td><?php $rent=round(($costs->rent/100)*$eachprop->percent*$rating);$charges=round(($costs->daily_charges/100)*$eachprop->percent*$rating);echo $rent-$charges.' PRC'; ?></td>
			<td><?php echo '-'.$charges.' PRC'; ?></td>
			<td><?php echo !empty($eachprop->earnings) ? $eachprop->earnings.' PRC' : '&ndash;'; ?></td></tr>
			
		</table>
		</div>
	</div>
	<?php } ?>
</div>