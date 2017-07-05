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

jimport('joomla.log.log');

/**
 * Class PlgSystemByteVarnish
 */
class PlgSystemByteVarnish extends JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * Triggered before Joomla! renders the page
	 */
	public function onBeforeRender()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;

		// Perform these actions in frontend only
		if ($app->isSite())
		{
			// Override Joomla Caching headers
			JResponse::allowCache(true);

			// Is Varnish Cache enabled?
			$enabled = (int) $this->params->get('enabled', 1);

			// If disabled, set header to no-cache and return
			if (!$enabled)
			{
				JResponse::setHeader('Cache-Control', 'no-cache', true);

				return false;
			}

			// Get max-age setting
			$maxage = (int) $this->params->get('maxage', 60);
			$maxage = $maxage * 60;

			// Set caching site headers
			JResponse::setHeader('Cache-Control', 'public, max-age=' . $maxage, true);

			// Check if component is excluded from caching
			$ignorecomponents = $this->params->get('excluded_components', array('com_users'));
			$component        = $input->get('option');

			if (in_array($component, $ignorecomponents))
			{
				JResponse::setHeader('Cache-Control', 'no-cache', true);

				return false;
			}

			// Check if menu-item is excluded from caching
			$ignoremenus = $this->params->get('excluded_menus', array());
			$menu        = $app->getMenu()->getActive();

			if (in_array($menu->id, $ignoremenus))
			{
				JResponse::setHeader('Cache-Control', 'no-cache', true);

				return false;
			}
		}

		// Perform these actions in backend only, and if logged in
		if ($app->isAdmin() && JFactory::getUser()->id)
		{
			$varnish = $input->get('varnish', '');

			// Purge the site cache
			if ($varnish == 'purge')
			{
				$this->purge();
			}
		}

		return;
	}

	/**
	 * Triggered when user logs in, set NO_CACHE cookie
	 */
	public function onUserLogin($user, $options)
	{
		// Set Cookie
		JFactory::getApplication()->input->cookie->set('NO_CACHE', true, time() + 3600, '/');
	}

	/**
	 * Triggered when user logs out, delete NO_CACHE cookie
	 */
	public function onUserLogout($user, $options)
	{
		// Remove Cookie
		JFactory::getApplication()->input->cookie->set('NO_CACHE', false, time() - 3600, '/');
	}

	/**
	 * Triggered after saving content, purge page cache
	 */
	public function onContentAfterSave($context, $item, $isNew)
	{
		// Only continue for com_content & com_menu
		if ($context != 'com_content.article' && $context != 'com_menus.item')
		{
			return true;
		}

		// Stop auto purge if not enabled
		if ($this->params->get('autopurge', 1) == 0)
		{
			return true;
		}

		// Placeholder for ItemIds to purge
		$itemIds = array();

		// The menu
		$menu = JApplication::getInstance('site')->getMenu();

		// Purge com_content
		if ($context == 'com_content.article')
		{
			// Get the menu items
			$items = $menu->getMenu();

			// Always purge homepage
			$itemIds[] = $menu->getDefault()->id;

			// Loop through menu items
			foreach ($items as $item)
			{
				// Get all com_content items
				if (strpos($item->link, 'option=com_content') !== false)
				{
					// Parse the menu link
					parse_str($item->link, $parts);
					$view = $parts['view'];
					$id   = $parts['id'];

					// Retrieve menu items to article
					if (($view == 'article') && ($id == $item->id))
					{
						$itemIds[$item->id] = $view;
					}

					// Retrieve menu items to category of article
					if (($view == 'category') && ($id == $item->catid))
					{
						$itemIds[$item->id] = $view;
					}
				}
			}
		}

		// Purge com_menus
		if ($context == 'com_menus.item')
		{
			// Current menu item
			$itemIds[$item->id] = 'item';

			// Get the childs of the menu item
			$items = $menu->getItems('parent_id', $item->id);

			foreach ($items as $item)
			{
				$itemIds[$item->id] = 'item';
			}
		}

		// Purge the collected menu items
		foreach ($itemIds as $itemId => $type)
		{
			// Purge article view in category
			if ($type == 'category')
			{
				$suffix = '/' . $item->id . '-' . $item->alias;
				$page   = $this->route($itemId, $suffix);
				$this->purge($page);
			}

			// Purge menu item
			$page = $this->route($itemId);
			$this->purge($page);
		}
	}

	/**
	 * Returns the correct site url of a page based on ItemId
	 */
	public function route($itemId, $suffix = '')
	{
		$router = JApplication::getInstance('site')->getRouter();

		// Set URL
		$url = 'index.php?Itemid=' . $itemId;

		// Build route
		$uri = $router->build($url);
		$url = $uri->toString(array('path'));

		// Replace spaces
		$url = preg_replace('/\s/u', '%20', $url);

		// Replace '/administrator'
		$url = str_replace('/administrator', '', $url);

		// Strip .html, just in case
		$url = str_replace('.html', '', $url);
		$url = htmlspecialchars($url);

		// Add suffix if set
		if ($suffix)
		{
			$url = $url . $suffix;
		}

		return $url;
	}

	/**
	 * Purges the Varnish cached for site or specific page only
	 */
	public function purge($page = '')
	{
		// Prepare log file
		JLog::addLogger(
			array(
				'text_file'         => 'plg_bytevarnish.php',
				'text_entry_format' => '{DATETIME} {PRIORITY} {CATEGORY} {MESSAGE}'
			),
			JLog::ALL,
			array('PURGE')
		);

		// Get the host URL
		$host = rtrim(JURI::root(), '/');

		// Set URL for specific page purge
		if ($page)
		{
			$url = $host . $page;
		}

		// General site purge
		if (empty($page))
		{
			$url = $host . '/.*';
		}

		// Perform purge
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PURGE');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$rv = curl_exec($ch);

		// Purge succesful?
		if ($rv !== false)
		{
			// Display success & log
			JLog::add($url . ' PURGED', JLog::INFO, 'PURGE');

			if ($page)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_BYTEVARNISH_MESSAGE_PURGED_PAGE') . ' ' . $url, 'message');
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_BYTEVARNISH_MESSAGE_PURGED_SITE') . ' ' . $host, 'message');
			}
		}
		else
		{
			// Display error & log
			JLog::add($url . ' ERROR: ' . curl_error($ch), JLog::WARNING, 'PURGE');
			JFactory::getApplication()->enqueueMessage(JText::_('PLG_SYSTEM_BYTEVARNISH_MESSAGE_PURGED_ERROR') . ' ' . curl_error($ch), 'error');
		}
	}
}
