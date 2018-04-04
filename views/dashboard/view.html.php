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

jimport ( 'joomla.application.component.view' );

/**
 * View to edit
 *
 * @since 1.6
 */
class LandlordViewDashboard extends JViewLegacy {
	protected $state;
	protected $item;
	protected $form;
	protected $params;
	protected $canSave;
	
	/**
	 * Display the view
	 *
	 * @param string $tpl
	 *        	Template name
	 *        	
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null) {
		$mainframe = JFactory::getApplication ();
		$user = JFactory::getUser ();
		$model = $this->getModel ();
		$this->state = $this->get ( 'State' );
		$this->params = $mainframe->getParams ( 'com_landlord' );
		$this->portfolio = LandlordHelpersLandlord::getPortfolio();
		$this->rent=LandlordHelpersLandlord::getRentPaid();
		foreach($this->rent as $key=>$each)
		{
			$this->weekly_rent+=$each->rent;
			$this->last_paid=$each->rent;
		}
		if (count ( $errors = $this->get ( 'Errors' ) )) {
			throw new Exception ( implode ( "\n", $errors ) );
		}
		
		$this->_prepareDocument ();
		
		parent::display ( $tpl );
	}
	
	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function _prepareDocument() {
		$app = JFactory::getApplication ();
		$menus = $app->getMenu ();
		$title = null;
		
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive ();
		
		if ($menu) {
			$this->params->def ( 'page_heading', $this->params->get ( 'page_title', $menu->title ) );
		} else {
			$this->params->def ( 'page_heading', JText::_ ( 'COM_MISSIONCONTROL_DEFAULT_PAGE_TITLE' ) );
		}
		
		$title = $this->params->get ( 'page_title', '' );
		
		if (empty ( $title )) {
			$title = $app->get ( 'sitename' );
		} elseif ($app->get ( 'sitename_pagetitles', 0 ) == 1) {
			$title = JText::sprintf ( 'JPAGETITLE', $app->get ( 'sitename' ), $title );
		} elseif ($app->get ( 'sitename_pagetitles', 0 ) == 2) {
			$title = JText::sprintf ( 'JPAGETITLE', $title, $app->get ( 'sitename' ) );
		}
		
		$this->document->setTitle ( $title );
		
		if ($this->params->get ( 'menu-meta_description' )) {
			$this->document->setDescription ( $this->params->get ( 'menu-meta_description' ) );
		}
		
		if ($this->params->get ( 'menu-meta_keywords' )) {
			$this->document->setMetadata ( 'keywords', $this->params->get ( 'menu-meta_keywords' ) );
		}
		
		if ($this->params->get ( 'robots' )) {
			$this->document->setMetadata ( 'robots', $this->params->get ( 'robots' ) );
		}
	}
}