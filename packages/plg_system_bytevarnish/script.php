<?php
/**
 * Byte.nl Varnish for Joomla!
 *
 * @author     Perfect Web Team - Sander Potjer <hallo@perfectwebteam.nl>
 * @copyright  Copyright (C) 2015-2017. All rights reserved.
 * @license    GNU Public License version 3 or later
 * @link       http://www.perfectwebteam.nl
 */

defined('_JEXEC') or die;

/**
 * Installation Script file
 */
class plgSystemByteVarnishInstallerScript
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

		// Query to update plugin
		$query = $db->getQuery(true);

		$fields = array(
			$db->quoteName('enabled') . ' = 1',
			$db->quoteName('params') . ' = ' . $db->quote('{"enabled":"1","autopurge":"1","maxage":60,"excluded_components":["com_users"]}'),
		);

		$conditions = array(
			$db->quoteName('element') . ' = ' . $db->quote('bytevarnish'),
			$db->quoteName('type') . ' = ' . $db->quote('plugin'),
		);

		$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();

		JFactory::getApplication()->enqueueMessage('Plugin \'Byte Varnish\' is enabled', 'message');
	}
}