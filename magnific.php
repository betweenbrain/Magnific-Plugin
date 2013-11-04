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

		$this->app = JFactory::getApplication();
		$this->db  = JFactory::getDBO();
		$this->doc = JFactory::getDocument();
	}

	function onAfterRoute() {

		if ($this->app->isAdmin()) {
			return TRUE;
		}

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
