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
$input        = JFactory::getApplication()->input;
$hidemainmenu = $input->getBool('hidemainmenu');
$task         = $input->getCmd('task');

// Prepare Purge url
$uri = JUri::getInstance();

$uri->setVar('varnish', 'purge');

// Filter SEF language from path if set
$path        = $uri->getPath();
$languageTag = JFactory::getLanguage()->getTag();
$languages   = JLanguageHelper::getLanguages('lang_code');

$uri->setPath(str_replace($languages[$languageTag]->sef, '', $path));

// Compile Purge url
$url = $uri->toString();

// Load layout file
require JModuleHelper::getLayoutPath('mod_bytevarnish', $params->get('layout', 'default'));
