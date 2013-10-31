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
		$source     = htmlspecialchars($this->params->get('source'));

		if (!is_array($menuItems)) {
			$menuItems = explode(' ', $menuItems);
		}

		if (in_array($activeItem, $menuItems)) {

			$js = <<<EOT
(function ($) {
	$().ready(function () {
		$.magnificPopup.open({
		  items: {
		    src: '{$source}'
		  },
		  type: 'ajax'
		});
	});
}(jQuery));
EOT;

			$this->doc->addScriptDeclaration($js);
		}
	}
}
