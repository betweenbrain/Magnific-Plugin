<?php defined('_JEXEC') or die;

/**
 * File       magnific.php
 * Created    10/28/13 4:48 PM
 * Author     Matt Thomas
 * Website    http://betweenbrain.com
 * Email      matt@betweenbrain.com
 * Support    https://github.com/betweenbrain/K2-Redirector/issues
 * Copyright  Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * License    GNU GPL v3 or later
 */

class plgSystemMagnific extends JPlugin {

	function plgSystemMagnific(&$subject, $params) {
		parent::__construct($subject, $params);

		$this->app    = JFactory::getApplication();
		$this->config = JFactory::getConfig();
		$this->db     = JFactory::getDBO();
		$this->doc    = JFactory::getDocument();
	}

	function onAfterRoute() {

		$publish_up   = $this->params->get('publish_up');
		$publish_down = $this->params->get('publish_down');
		$tzoffset     = $this->config->getValue('config.offset');
		$nullDate     = $this->db->getNullDate();

		if ($this->app->isAdmin()) {

			// Check and update date parameter to set time of day
			$setParams = NULL;

			// Append time if not added to publish date
			if (strlen(trim($publish_up)) <= 10) {
				$publish_up .= ' 00:00:00';

				$date       = JFactory::getDate($publish_up, $tzoffset);
				$publish_up = $date->toMySQL();
				$setParams  = TRUE;
			}

			// Handle "never" unpublish date
			if (trim($publish_down) == JText::_('Never') || trim($publish_down) == '') {
				$publish_down = $nullDate;
			} else {
				if (strlen(trim($publish_down)) <= 10) {
					$publish_down .= ' 00:00:00';

					$date         = JFactory::getDate($publish_down, $tzoffset);
					$publish_down = $date->toMySQL();
					$setParams    = TRUE;
				}
			}

			// Update plugin parameters
			if ($setParams) {

				$query = ' SELECT params' .
					' FROM #__plugins' .
					' WHERE element = ' . $this->db->Quote('magnific') . '';
				$this->db->setQuery($query);
				$params = $this->db->loadResult();

				// Check if last_run parameter has been previously saved.
				if (preg_match('/publish_up=[^\n]+/', $params)) {

					// If it has been, update it.
					$params = preg_replace('/publish_up=[^\n]*/', 'publish_up=' . $publish_up, $params);
				}

				// Check if last_run parameter has been previously saved.
				if (preg_match('/publish_down=[^\n]+/', $params)) {

					// If it has been, update it.
					$params = preg_replace('/publish_down=[^\n]*/', 'publish_down=' . $publish_down, $params);
				}

				// Update plugin parameters in database
				$query = 'UPDATE #__plugins' .
					' SET params=' . $this->db->Quote($params) .
					' WHERE element = ' . $this->db->Quote('magnific');
				$this->db->setQuery($query);
				$this->db->query();
			}

			return TRUE;
		}

		$now          = JFactory::getDate()->toUnix();
		$publish_up   = ($publish_up === '') ? 0 : JFactory::getDate($publish_up, $tzoffset)->toUnix();
		$publish_down = ($publish_down === '') ? $now + 1 : JFactory::getDate($publish_down, $tzoffset)->toUnix();

		// Execute plugin only if within published timeframe
		if ($publish_up <= $now && $now < $publish_down) {

			$activeItem = JSite::getMenu()->getActive()->id;
			$menuItems  = $this->params->get('menuItems');

			if (!is_array($menuItems)) {
				$menuItems = explode(' ', $menuItems);
			}

			if (in_array($activeItem, $menuItems)) {

				$popupMode    = $this->params->get('popupMode');
				$cookieExpire = JRequest::getVar('magnificCookie', '', 'COOKIE');

				switch ($popupMode) {
					case 'once':

						if (!$cookieExpire) {
							setCookie('magnificCookie', time() * 60 * 60 * 24 * 365, 0);
							$this->doPopup();
						}

						break;

					case 'daily':
						$now = JFactory::getDate()->toUnix();
						$day = $now * 60 * 60 * 24;

						if (!$cookieExpire || $now - $cookieExpire > $day) {
							setCookie('magnificCookie', time() * 60 * 60 * 24, 0);
							$this->doPopup();
						}

						break;

					case 'load':

						setCookie('magnificCookie', time(), 1);

						$this->doPopup();

						break;
				}
			}
		}
	}

	private function doPopup() {

		$delay     = $this->params->get('delay', 0) * 1000;
		$popupType = $this->params->get('popupType');
		$source    = htmlspecialchars($this->params->get('source'));

		$js = '
					(function ($) {
						$().ready(function () {';

		if ($delay != 0) {
			$js .= '
						setTimeout(function (){';
		}

		$js .= '
					$.magnificPopup.open({
					  items: {
					    src: "' . $source . '"
					  },
					  type: "' . $popupType . '"
					});';

		if ($delay != 0) {
			$js .= '
						}, ' . $delay . ');';
		}

		$js .= '
						});
					}(jQuery));';

		$js = preg_replace(array('/\s{2,}+/', '/\t/', '/\n/'), '', $js);

		$this->doc->addScriptDeclaration($js);

		if (file_exists(JPATH_SITE . '/media/js/jquery.magnific-popup.min.js')) {
			$this->doc->addScript(JURI::base(TRUE) . '/media/js/jquery.magnific-popup.min.js');
		}

		if (file_exists(JPATH_SITE . '/media/css/magnific-popup.css')) {
			$this->doc->addStyleSheet(JURI::base(TRUE) . '/media/css/magnific-popup.css');
		}
	}
}
