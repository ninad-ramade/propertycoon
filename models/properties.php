<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Missioncontrol
 * @author     Ninad Ramade <ninad.ramade@gmail.com>
 * @copyright  2016 Ninad Ramade
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined ( '_JEXEC' ) or die ();

jimport ( 'joomla.application.component.modellist' );
jimport ( 'joomla.filesystem.file' );

/**
 * Methods supporting a list of Missioncontrol records.
 *
 * @since 1.6
 */
class LandlordModelProperties extends JModelList {
	/**
	 * Constructor.
	 *
	 * @param array $config
	 *        	An optional associative array of configuration settings.
	 *        	
	 * @see JController
	 * @since 1.6
	 */
	public function __construct($config = array()) {
		if (empty ( $config ['filter_fields'] )) {
			$config ['filter_fields'] = array (
			);
		}
		
		parent::__construct ( $config );
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param string $ordering
	 *        	Elements order
	 * @param string $direction
	 *        	Order direction
	 *        	
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.6
	 */
	protected function populateState($ordering = null, $direction = null) {
		// Initialise variables.
		$app = JFactory::getApplication ();
		
		$list = $app->getUserState ( $this->context . '.list' );
		
		if (isset ( $list ['ordering'] )) {
			$this->setState ( 'list.ordering', $list ['ordering'] );
		}
		
		if (isset ( $list ['direction'] )) {
			$this->setState ( 'list.direction', $list ['direction'] );
		}
		
		// List state information.
		parent::populateState ( $ordering, $direction );
	}
	
	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return JDatabaseQuery
	 *
	 * @since 1.6
	 */
	protected function getListQuery() {
		$db = $this->getDbo ();
		$query = $db->getQuery ( true );
		
		return $query;
	}
	
	/**
	 * Method to get an array of data items
	 *
	 * @return mixed An array of data on success, false on failure.
	 */
	public function getItems() {
		$items = parent::getItems ();
		
		return $items;
	}
	
	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 *
	 * @return void
	 */
	protected function loadFormData() {
		$app = JFactory::getApplication ();
		$filters = $app->getUserState ( $this->context . '.filter', array () );
		$error_dateformat = false;
		
		foreach ( $filters as $key => $value ) {
			if (strpos ( $key, '_dateformat' ) && ! empty ( $value ) && $this->isValidDate ( $value ) == null) {
				$filters [$key] = '';
				$error_dateformat = true;
			}
		}
		
		if ($error_dateformat) {
			$app->enqueueMessage ( JText::_ ( "COM_MISSIONCONTROL_SEARCH_FILTER_DATE_FORMAT" ), "warning" );
			$app->setUserState ( $this->context . '.filter', $filters );
		}
		
		return parent::loadFormData ();
	}
	
	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 *
	 * @param string $date
	 *        	Date to be checked
	 *        	
	 * @return bool
	 */
	private function isValidDate($date) {
		$date = str_replace ( '/', '-', $date );
		return (date_create ( $date )) ? JFactory::getDate ( $date )->format ( "Y-m-d" ) : null;
	}
	
	public function buyProperty($data)
	{
		$db=JFactory::getDbo();
		//first get the price of the place type
		$cost_details=LandlordHelpersLandlord::getCostbyType($data['place_type']);
		$purchased_cost=round(($cost_details->cost/100)*$data['rating'])*$data['percent'];
		$purchase_data=new stdClass();
		$purchase_data->user_id=JFactory::getUser()->id;
		$purchase_data->place_id=$data['place_id'];
		$purchase_data->place_type_id=$cost_details->cost_id;
		$purchase_data->type="buy";
		$purchase_data->percent_bought=$data['percent'];
		$purchase_data->amount=$purchased_cost;
		$purchase_data->created_on=JFactory::getDate()->toSql();
		if(!$db->insertObject('#__propertycoon_transactions',$purchase_data))
		{
			$this->setError('Purchase Failed!');
			return false;
		}
		//check in properties
		$property_history=LandlordHelpersLandlord::getPropertyHistory($data['place_id']);
		if(!empty($property_history))
		{
			//update
			$property=new stdClass();
			$property->property_id=$property_history->property_id;
			$property->percent=$property_history->percent+$data['percent'];
			if(!$db->updateObject('#__propertycoon_properties',$property,'property_id'))
			{
				$this->setError('Could not update Property History!');
				return false;
			}
		}
		else
		{
			//insert
			$property=new stdClass();
			$property->user_id=JFactory::getUser()->id;
			$property->place_id=$data['place_id'];
			$property->place_type_id=$cost_details->cost_id;
			$property->percent=$data['percent'];
			if(!$db->insertObject('#__propertycoon_properties',$property))
			{
				$this->setError('Could not save Property History!');
				return false;
			}
		}
		
		//if successful, subtract from portfolio
		$portfolio_data=LandlordHelpersLandlord::getPortfolio();
		$portfolio=new stdClass();
		$portfolio->folio_id=$portfolio_data->folio_id;
		$portfolio->wallet_balance=$portfolio_data->wallet_balance-$purchased_cost;
		$portfolio->property_value=$portfolio_data->property_value+$purchased_cost;
		$portfolio->level=LandlordHelpersLandlord::calculateLevel($portfolio_data->property_value+$purchased_cost);
		if(!$db->updateObject('#__propertycoon_portfolio',$portfolio,'folio_id'))
		{
			$this->setError('Could not update Portfolio!');
			return false;
		}
		return true;
	}
}
