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
?>

<div class="btn-group bytevarnishpurge">
	<?php if ($task == 'edit' || $task == 'editA' || $hidemainmenu):?>
		<span class="icon-lightning"></span> Purge Varnish Cache
	<?php else: ?>
		<a href="<?php echo($url); ?>" class="bytevarnishpurge_link">
			<span class="icon-lightning"></span> Purge Varnish Cache
		</a>
	<?php endif;?>
</div>