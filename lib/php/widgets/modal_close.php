<?php
# Copyright 2013 Mike Thorn (github: WasabiVengeance). All rights reserved.
# Use of this source code is governed by a BSD-style
# license that can be found in the LICENSE file.

if(!class_exists('bsc_widget_button'))
{
	include(__DIR__.'/button.php');
}

class bsc_widget_modal_close extends bsc_widget_button
{
	function init()
	{
		parent::init();
		$this->option_order = array('label','emphasis','onclick');
		$this->options['label'] = 'Close';
		$this->attribute('data-target','#bsc_modal_root');
		$this->attribute('data-dismiss','modal')->attribute('aria-hidden','true');
	}
}

?>