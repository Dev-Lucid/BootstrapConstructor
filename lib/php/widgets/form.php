<?php
# Copyright 2013 Mike Thorn (github: WasabiVengeance). All rights reserved.
# Use of this source code is governed by a BSD-style
# license that can be found in the LICENSE file.

class bsc_widget_form extends bsc_widget
{
	function init()
	{
		$this->options['tag'] = 'form';
		$this->option('action','');
		$this->option('method','post');
	}
	
	function render_start($data = array())
	{
		$html = '<form';
		
		if($this->options['method'] != '')
			$html .= ' method="'.$this->options['method'].'"';
		if($this->options['action'] != '')
			$html .= ' action="'.$this->options['action'].'"';
		if($this->events['onsubmit'] != '')
			$html .= ' onsubmit="'.$this->events['onsubmit'].'"';
		
		$html .= '>';
		return $html;
	}
}

?>