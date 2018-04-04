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
			$rent=0;
			$properties=LandlordHelpersLandlord::getPropertyHistorybyUserid($eachp->user_id);
			foreach($properties as $key=>$eachprop)
			{
				$costs=LandlordHelpersLandlord::getCostbyTypeid($eachprop->place_type_id);
				//need to get place data from Google Places API for current ratings for this place
				$place_details=json_decode(file_get_contents("https://maps.googleapis.com/maps/api/place/details/json?placeid=".$eachprop->place_id."&key=AIzaSyCTbismgZ1tAeVHX5Dn9OaF3IRtxz_FPVY"));
				$rating=!empty($place_details->result->rating) ? $place_details->result->rating : 1;
				$rent+=round(($costs->rent/100)*$eachprop->percent*$rating);
			}
			//we have got the rent, lets credit into the wallet
			$folio=new stdClass();
			$folio->folio_id=$eachp->folio_id;
			$folio->wallet_balance=$eachp->wallet_balance+$rent;
			$db=JFactory::getDbo();
			if(!$db->updateObject('#__propertycoon_portfolio',$folio,'folio_id'))
			{
				echo "Could not credit rent into the portfolio";
				//notify admins
			}
			//lets insert in the rent log
			$rent_log=new stdClass();
			$rent_log->user_id=$eachp->user_id;
			$rent_log->rent=$rent;
			$rent_log->created_on=JFactory::getDate()->toSql();
			if(!$db->insertObject('#__propertycoon_rent',$rent_log))
			{
				echo "Could not save the rent log";
				//notify admins
			}
		}
	}
}
