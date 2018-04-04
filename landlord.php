<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Landlord
 * @author     Ninad Ramade <ninad.ramade@gmail.com>
 * @copyright  2018 Ninad Ramade
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Landlord', JPATH_COMPONENT);
JLoader::register('LandlordController', JPATH_COMPONENT . '/controller.php');


// Execute the task.
$controller = JControllerLegacy::getInstance('Landlord');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
