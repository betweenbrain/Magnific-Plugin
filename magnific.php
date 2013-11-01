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
		$delay      = $this->params->get('delay', 0) * 1000;
		$menuItems  = $this->params->get('menuItems');
		$popupType  = $this->params->get('popupType');
		$source     = htmlspecialchars($this->params->get('source'));

		if (!is_array($menuItems)) {
			$menuItems = explode(' ', $menuItems);
		}

		if (in_array($activeItem, $menuItems)) {

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

			$this->doc->addScriptDeclaration($js);

			if (file_exists(JPATH_SITE . '/media/js/jquery.magnific-popup.min.js')) {
				$this->doc->addScript(JURI::base(TRUE) . '/media/js/jquery.magnific-popup.min.js');
			}

			if (file_exists(JPATH_SITE . '/media/css/magnific-popup.css')) {
				$this->doc->addStyleSheet(JURI::base(TRUE) . '/media/css/magnific-popup.css');
			}
		}
	}
}
