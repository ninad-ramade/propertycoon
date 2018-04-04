<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Landlord
 * @author     Ninad Ramade <ninad.ramade@gmail.com>
 * @copyright  2018 Ninad Ramade
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('LandlordHelper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_landlord' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'landlord.php');

/**
 * Class LandlordFrontendHelper
 *
 * @since  1.6
 */
class LandlordHelpersLandlord
{
	/**
	 * Get an instance of the named model
	 *
	 * @param   string  $name  Model name
	 *
	 * @return null|object
	 */
	public static function getModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_landlord/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_landlord/models/' . strtolower($name) . '.php';
			$model = JModelLegacy::getInstance($name, 'LandlordModel');
		}

		return $model;
	}

	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

    /**
     * Gets the edit permission for an user
     *
     * @param   mixed  $item  The item
     *
     * @return  bool
     */
    public static function canUserEdit($item)
    {
        $permission = false;
        $user       = JFactory::getUser();

        if ($user->authorise('core.edit', 'com_landlord'))
        {
            $permission = true;
        }
        else
        {
            if (isset($item->created_by))
            {
                if ($user->authorise('core.edit.own', 'com_landlord') && $item->created_by == $user->id)
                {
                    $permission = true;
                }
            }
            else
            {
                $permission = true;
            }
        }

        return $permission;
    }
    public static function getCostbyType($place_type)
    {
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_costs WHERE place_type=".$db->quote($place_type);
    	$db->setQuery($query);
    	return $db->loadObject();
    }
    public static function getCostbyTypeid($place_type_id)
    {
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_costs WHERE cost_id=".$db->quote($place_type_id);
    	$db->setQuery($query);
    	return $db->loadObject();
    }
    public static function getPropertyHistory($place_id,$user_id=null)
    {
    	$user_id=!empty($user_id) ? $user_id : JFactory::getUser()->id;
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_properties WHERE place_id=".$db->quote($place_id)." AND user_id=".$db->quote($user_id);
    	$db->setQuery($query);
    	return $db->loadObject();
    }
    public static function getPropertyHistorybyUserid($user_id=null)
    {
    	$user_id=!empty($user_id) ? $user_id : JFactory::getUser()->id;
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_properties WHERE user_id=".$db->quote($user_id);
    	$db->setQuery($query);
    	return $db->loadObjectList();
    }
    public static function getAllPortfolios()
    {
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_portfolio";
    	$db->setQuery($query);
    	return $db->loadObjectList();
    }
    public static function getPortfolio($user_id=null)
    {
    	$user_id=!empty($user_id) ? $user_id : JFactory::getUser()->id;
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_portfolio WHERE user_id=".$db->quote($user_id);
    	$db->setQuery($query);
    	return $db->loadObject();
    }
    public static function getRentPaidHistory($user_id=null)
    {
    	$user_id=!empty($user_id) ? $user_id : JFactory::getUser()->id;
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_rent WHERE user_id=".$db->quote($user_id);
    	$db->setQuery($query);
    	return $db->loadObjectList();
    }
    public static function getRentPaid($user_id=null)
    {
    	$user_id=!empty($user_id) ? $user_id : JFactory::getUser()->id;
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_rent WHERE user_id=".$db->quote($user_id)." AND created_on>".$db->quote(date('Y-m-d 00:00:00',strtotime(' - 7 Days')))." ORDER BY created_on ASC";
    	$db->setQuery($query);
    	return $db->loadObjectList();
    }
    public static function calculateLevel($value)
    {
    	$db=JFactory::getDbo();
    	$query="SELECT max(level_id) FROM #__propertycoon_levels WHERE portfolio_value<=".$value;
    	$db->setQuery($query);
    	return $db->loadResult();
    }
    public static function getLevelData($level_id)
    {
    	$db=JFactory::getDbo();
    	$query="SELECT * FROM #__propertycoon_levels WHERE level_id=".$db->quote($level_id);
    	$db->setQuery($query);
    	return $db->loadObject();
    }
}
