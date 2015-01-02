<?php
/**
 * Byte.nl Varnish for Joomla!
 *
 * @author     Perfect Web Team - Sander Potjer <hallo@perfectwebteam.nl>
 * @copyright  Copyright (C) 2015. All rights reserved.
 * @license    GNU Public License version 3 or later
 * @link       http://www.perfectwebteam.nl
 */

defined('_JEXEC') or die;

// Get Variables
$input			= JFactory::getApplication()->input;
$hidemainmenu	= $input->getBool('hidemainmenu');
$task			= $input->getCmd('task');

// Prepare PURGE url
$url = JURI::getInstance();
$url->setVar('varnish','purge');
$url = $url->toString();

// Load layout file
require JModuleHelper::getLayoutPath('mod_bytevarnish', $params->get('layout', 'default'));
