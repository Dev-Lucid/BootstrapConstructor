<?php
# Copyright 2013 Mike Thorn (github: WasabiVengeance). All rights reserved.
# Use of this source code is governed by a BSD-style
# license that can be found in the LICENSE file.

class bsc_widget_jumbotron extends bsc_widget
{
	function init()
	{
		$this->option_order = array('heading','tagline','level');
		$this->options['tag'] = 'div';
		$this->option('heading','');
		$this->option('level',1);
		$this->option('tagline','');
		$this->option('class','jumbotron');
	}

	
	function render_start($data = array())
	{			
		$html = parent::render_start($data);

		if($this->options['heading'] != '')
			$html .= bsc::heading($this->options['heading'])->level($this->options['level']);
		if($this->options['tagline'] != '')
			$html .= bsc::paragraph($this->options['tagline']);
		
		$html .= '<p>';
		
		return $html;
	}
	
	function render_end($data = array())
	{			
		$html = '</p>';	
		$html .= parent::render_end($data);
		return $html;
	}
		
}

?>