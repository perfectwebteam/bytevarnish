<?php
/**
 * Byte.nl Varnish for Joomla!
 *
 * @author     Perfect Web Team - Sander Potjer <hallo@perfectwebteam.nl>
 * @copyright  Copyright (C) 2014. All rights reserved.
 * @license    GNU Public License version 3 or later
 * @link       http://www.perfectwebteam.nl
 */

defined('_JEXEC') or die;

/**
 * Installation Script file
 */
class mod_ByteVarnishInstallerScript
{
	/**
	 * Method to install the extension
	 * $parent is the class calling this method
	 *
	 * @return void
	 */
	function install($parent)
	{
		// Connect with DB
		$db = JFactory::getDbo();

		// Get module ID
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__modules'));
		$query->where($db->quoteName('module') . ' = ' . $db->quote('mod_bytevarnish'));
		$db->setQuery($query);
		$moduleId = $db->loadResult();

		// Query to update module
		$query = $db->getQuery(true);

		$fields = array(
			$db->quoteName('position') . ' = ' . $db->quote('status'),
			$db->quoteName('ordering') . ' = 100',
			$db->quoteName('published') . ' = 1',
			$db->quoteName('showtitle') . ' = 0'
		);

		$conditions = array(
			$db->quoteName('id') . ' = ' . $moduleId
		);

		$query->update($db->quoteName('#__modules'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();

		// Query to update module
		$query = $db->getQuery(true);

		$columns = array('moduleid', 'menuid');
		$values = array($moduleId, 0);

		// Prepare the insert query.
		$query
		    ->insert($db->quoteName('#__modules_menu'))
		    ->columns($db->quoteName($columns))
		    ->values(implode(',', $values));

		$db->setQuery($query);
		$result = $db->execute();

		JFactory::getApplication()->enqueueMessage('Module \'Byte Varnish\' published on position \'status\'', 'message');
	}
}