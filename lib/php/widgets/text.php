<?php
# Copyright 2013 Mike Thorn (github: WasabiVengeance). All rights reserved.
# Use of this source code is governed by a BSD-style
# license that can be found in the LICENSE file.

class bsc_widget_text extends bsc_widget
{
	function init()
	{
		$this->option_order = array('text');
		$this->options['tag'] = '';
		$this->options['text'] = '';
	}
	
	function render_start()
	{
		global $__bsc;
		$html = parent::render_start();
		$html .= $this->__translate($this->options['text']);
		return $html;
	}
}

?>