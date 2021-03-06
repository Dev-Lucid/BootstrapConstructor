<?php
# Copyright 2013 Mike Thorn (github: WasabiVengeance). All rights reserved.
# Use of this source code is governed by a BSD-style
# license that can be found in the LICENSE file.

class bsc_widget_button_group extends bsc_widget
{
	function init()
	{
		$this->options['tag'] = 'div';
		$this->option('class','btn-group');
	}
	
	
	function option($name,$value)
	{
		switch($name)
		{
			case 'toggle':
				$this->attributes['data-toggle'] = 'buttons-'.$value;
				break;
			default:
				parent::option($name,$value);
				break;
		}
		return $this;
	}
	
}

?>