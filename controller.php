<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Landlord
 * @author     Ninad Ramade <ninad.ramade@gmail.com>
 * @copyright  2018 Ninad Ramade
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Class LandlordController
 *
 * @since  1.6
 */
class LandlordController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean $cachable  If true, the view output will be cached
	 * @param   mixed   $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController   This object to support chaining.
	 *
	 * @since    1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
        $app  = JFactory::getApplication();
        $view = $app->input->getCmd('view', 'properties');
		$app->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}
	public function cronJob()
	{
		//here the rent will be paid to all users for the properties they have bought
		$portfolios=LandlordHelpersLandlord::getAllPortfolios();
		foreach($portfolios as $pkey=>$eachp)
		{
			$final_rent=0;
			$properties=LandlordHelpersLandlord::getPropertyHistorybyUserid($eachp->user_id);
			foreach($properties as $key=>$eachprop)
			{
				$costs=LandlordHelpersLandlord::getCostbyTypeid($eachprop->place_type_id);
				//need to get place data from Google Places API for current ratings for this place
				$place_details=json_decode(file_get_contents("https://maps.googleapis.com/maps/api/place/details/json?placeid=".$eachprop->place_id."&key=AIzaSyCTbismgZ1tAeVHX5Dn9OaF3IRtxz_FPVY"));
				$rating=!empty($place_details->result->rating) ? $place_details->result->rating : 1;
				$charges=round(($costs->daily_charges/100)*$eachprop->percent*$rating);
				$rent=round(($costs->rent/100)*$eachprop->percent*$rating);
				$final_rent+=($rent-$charges);
				//check the cash limit for the level
				$level_data=LandlordHelpersLandlord::getLevelData($eachp->level);
				//lets make final rent to credit in line with the cash limit
				$final_rent=($eachp->wallet_balance+$final_rent)>$level_data->cash_limit ? $level_data->cash_limit-$eachp->wallet_balance : $final_rent;
				$property=new stdClass();
				$property->property_id=$eachprop->property_id;
				$property->earnings=$eachprop->earnings+$final_rent;
				if(!$db->updateObject('#__propertycoon_properties',$property,'property_id'))
				{
					echo "Could not update property";
					//notify admins
				}
			}
			//we have got the rent, lets credit into the wallet
			$db=JFactory::getDbo();
			$folio=new stdClass();
			$folio->folio_id=$eachp->folio_id;
			$folio->wallet_balance=$eachp->wallet_balance+$final_rent;
			if(!$db->updateObject('#__propertycoon_portfolio',$folio,'folio_id'))
			{
				echo "Could not credit rent into the portfolio";
				//notify admins
			}
			//lets insert in the rent log
			$rent_log=new stdClass();
			$rent_log->user_id=$eachp->user_id;
			$rent_log->rent=$final_rent;
			$rent_log->created_on=JFactory::getDate()->toSql();
			if(!$db->insertObject('#__propertycoon_rent',$rent_log))
			{
				echo "Could not save the rent log";
				//notify admins
			}
		}
		echo "All rents paid!";exit;
	}
}
