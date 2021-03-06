<?php
# Copyright 2013 Mike Thorn (github: WasabiVengeance). All rights reserved.
# Use of this source code is governed by a BSD-style
# license that can be found in the LICENSE file.

# this widget has several dependencies:
#
#	* jQuery
#	* DatabaseManager, or a db collection that supports the same api
#       (you may be able to write a simple wrapper around your db
#        class so that you can use whichever one you want)
#  * lib/js/bsc.widget.dataTable.js
#
#
# You are responsible for making sure that when accessing the 
# url option will actually instantiate this object. 

class bsc_widget_data_table extends bsc_widget
{
	function init()
	{
		$this->option_order = array('id','url','data','sort_column','sort_direction','row_count');
		$this->options['tag'] = 'table';
		$this->options['filters'] = array();
		$this->options['current_page'] = 0;
		$this->options['max_page'] = 0;
		$this->options['row_count_options'] = array(10,50,100,0);
		$this->options['row_count'] = $this->options['row_count_options'][0];
		$this->options['filter_node'] = bsc::td();
		$this->options['pages_before'] = 3;
		$this->options['pages_after']  = 3;
		$this->options['filters'] = array();
		$this->options['empty_title'] = 'No data available.';
		$this->options['empty_filters'] = 'Try removing a filter.';
		$this->options['empty_text'] = '';
		$this->options['formatters'] = array();
		$this->option('class','table table-condensed');
		$this->option('class','table-striped');
		$this->option('class','table-bordered');
		$this->option('class','bsc-data-table');
	}
	
	function send_data()
	{
		if($_REQUEST['bsc_data_table__'.$this->attributes['id'].'__return_data'] == 'yes')
		{
			$this->get_paging_sorting();
			$this->apply_page_sort_filters();
			$out = array(
				'id'=>$this->attributes['id'],
				'sort_column'=>$this->options['sort_column'],
				'sort_direction'=>$this->options['sort_direction'],
				'current_page'=>$this->options['current_page'],
				'row_count'=>$this->options['row_count'],
				'max_page'=>$this->options['max_page'],
				'data'=>$this->render_data('html'),
			);
			
			bsc::log('preparing new datatable data!');
			#sleep(7);
			exit(json_encode($out));
		}
	}
	
	function add_filter($field,$operator,$obj,$trigger=null)
	{
		$name = $this->attributes['id'].'__filter_'.count($this->options['filters']);
		$this->options['filters'][$name] = array(
			'name'=>$name,
			'field'=>$field,
			'operator'=>$operator,
			'value'=>((isset($_REQUEST['bsc_dt__'.$name]) && $_REQUEST['bsc_dt__'.$name]!='' && $_REQUEST['bsc_dt__'.$name]!='null')?$_REQUEST['bsc_dt__'.$name]:null),
		);
		
		if(is_a($obj,'bsc_widget_input_select'))
		{
			$obj->onchange('bsc.widget.dataTable.objs[\''.$this->attributes['id'].'\'].applyFilter(\''.$name.'\',this);');
		}
		else if(is_a($obj,'bsc_widget_input_text'))
		{
			$obj->onkeyup('bsc.widget.dataTable.objs[\''.$this->attributes['id'].'\'].applyDelayedFilter(\''.$name.'\',this);');
		}
		else if(is_a($obj,'bsc_widget_input_checkbox'))
		{
			$obj->onclick('bsc.widget.dataTable.objs[\''.$this->attributes['id'].'\'].applyFilter(\''.$name.'\',this);');
		}
		else
		{
			$obj->$trigger('bsc.widget.dataTable.objs[\''.$this->attributes['id'].'\'].applyFilter(\''.$name.'\',this);');			
		}
		
		$this->options['filter_node']->add(bsc::div()->class('col-lg-4 col-sm-6 col-12')->add($obj));
		return $this;
	}
	
	function get_paging_sorting()
	{
		if(isset($_REQUEST['bsc_dt__'.$this->attributes['id'].'__page']) and is_numeric($_REQUEST['bsc_dt__'.$this->attributes['id'].'__page']))
		{
			$this->options['current_page'] = $_REQUEST['bsc_dt__'.$this->attributes['id'].'__page'];
		}
		if(isset($_REQUEST['bsc_dt__'.$this->attributes['id'].'__sort_column']) and trim($_REQUEST['bsc_dt__'.$this->attributes['id'].'__sort_column']) != '')
		{
			$this->options['sort_column'] = $_REQUEST['bsc_dt__'.$this->attributes['id'].'__sort_column'];
		}
		if(isset($_REQUEST['bsc_dt__'.$this->attributes['id'].'__sort_direction']) and trim($_REQUEST['bsc_dt__'.$this->attributes['id'].'__sort_direction']) != '')
		{
			$this->options['sort_direction'] = $_REQUEST['bsc_dt__'.$this->attributes['id'].'__sort_direction'];
		}
		if(isset($_REQUEST['bsc_dt__'.$this->attributes['id'].'__row_count']) and is_numeric($_REQUEST['bsc_dt__'.$this->attributes['id'].'__row_count']))
		{
			$this->options['row_count'] = $_REQUEST['bsc_dt__'.$this->attributes['id'].'__row_count'];
		}
	}
	
	function apply_page_sort_filters()
	{
		# add limiting and sorting
		$this->options['data']->sort($this->options['sort_column'],$this->options['sort_direction']);
		if($this->options['row_count'] != 0)
		{
			$this->options['data']->page($this->options['current_page'],$this->options['row_count']);
		}
		
		# apply the filters here
		bsc::log('about to loop through filters');
		foreach($this->options['filters'] as $filter)
		{
			bsc::log('filter loop: '.$filter['field']);
			if(!is_null($filter['value']))
			{
				if($filter['operator'] == '%')
				{
					$values = explode(' ',$filter['value']);
					foreach($values as $value)
					{
						$value = trim($value);
						if($value != '')
						{
							$this->options['data']->filter($filter['field'],$filter['operator'],$value);
							
						}
					}
				}
				else
				{
					$this->options['data']->filter($filter['field'],$filter['operator'],$filter['value']);
				}
			}
		}
		
		# load the data and set the max page
		$this->options['data']->load();
		$this->options['max_page'] = $this->options['data']->__sql_max_page;
		#bsc::log(print_r($this->options,true));
	}
	
	function option($name,$value)
	{
		switch($name)
		{	
			case 'formatter':
				$this->formatters[] = $value;
				break;
			case 'empty_title':
			case 'empty_text':
			case 'empty_filter':
				$this->attributes['data-'.str_replace('_','-',$name)] = $value;
				break;
			default:
				parent::option($name,$value);
				break;
		}
		return $this;
	}
	
	function render_start($data)
	{
		$this->get_paging_sorting();
		$this->apply_page_sort_filters();
		
		# render the column widths and start the head section
		$html = parent::render_start($data);
		foreach($this->children as $child)
		{
			$html .= $child->render_width();
		}
		
		$html .= '<thead>';
		
		# if there's some html for filters, put it in a row here
		if(count($this->options['filter_node']->children) > 0)
		{
			$html .='<tr class="filters">';
			$this->options['filter_node']->attributes['colspan'] = (count($this->children));
			$html .= $this->options['filter_node']->render();
			$html .= '</tr>';
		}
		
		# render the column headers
		$html .='<tr class="headers">';
		foreach($this->children as $child)
		{
			$html .= $child->render_header($data);
		}
		$html .= '</tr>';
		
		
		# render the row to display when there's no data
		$html .='<tr class="empty">';
		$html .= '<td colspan="'.(count($this->children)).'">';
		$html .= '<h3>'.$this->options['empty_title'].'</h3>';
		$html .= $this->options[((count($this->options['filters']) == 0)?'empty_text':'empty_filters')];
		$html .= '</td>';
		$html .= '</tr>';
		
		# render the row to display a loading bar
		$html .='<tr class="progress">';
		$html .= '<td colspan="'.(count($this->children)).'">';
		$html .= '<div class="progress progress-striped active"><div class="progress-bar" style="width: 1%"></div></div>';
		$html .= '<span>Sorry, this is taking longer than expected</span>';
		$html .= '</td>';	
		$html .= '</tr>';
		
		
		$html .='</thead>';
		
		
		
		return $html;
	}
	
	function render_children($data)
	{
		return '<tbody>'.$this->render_data('html').'</tbody>';
	}
	
	function render_data($format)
	{
		foreach($this->children as $child)
		{
			$child->escape_label();
		}
		$to_return = '';
		foreach($this->options['data'] as $data_row)
		{
			$row = '';
			if(!is_array($data_row))
			{
				$data_row = $data_row->row_array();
			}
			
			if($format == 'html')
			{
				$row .= '<tr>';
			}
			
			foreach($this->formatters as $formatter)
			{
				$data_row = $formatter($data_row,$format);
			}
			
			foreach($this->children as $child)
			{
				$row .= $child->render($data_row);
			}
			
			if($format == 'html')
			{
				$row .= '</tr>';
			}
			
			$to_return .= $row;
		}
		return $to_return;
	}
	
	function render_end($data)
	{
		$html = '<tfoot>';
		
		$html .= '<tr><td colspan="'.(count($this->children)).'">';
		if(!is_null($this->options['data']->__sql_limit))
		{
			# write the pager
			$html .= '<div class="col-lg-4 col-sm-6">';
			$html .= bsc::pagination(
				$this->options['data']->__sql_max_page,
				$this->options['current_page']
			)
				->id($this->attributes['id'].'-pager')
				->onpagechange('bsc.widget.dataTable.objs[\''.$this->attributes['id'].'\'].changePage')
				->type('selector');
			$html .= '</div>';
			$html .= '<div class="col-lg-4 col-sm-1">&nbsp;</div>';
			$html .= '<div class="col-lg-4 col-sm-5">';
			
			# write a row count selector
			$html .= '<select id="'.$this->attributes['id'].'-rowcount" onchange="bsc.widget.dataTable.objs[\''.$this->attributes['id'].'\'].changeRowCount($(this).val());" class="pull-right">';
			foreach($this->options['row_count_options'] as $option)
			{
				$html .= '<option value="'.$option.'"';
				
				if($option == $this->options['row_count'])
					$html .= ' selected="selected"';
				
				$html .= '>';
				if($option == 0)
					$html .= 'Show all rows';
				else
					$html .= 'Show '.$option.' rows';
				$html .= '</option>';
			}
			$html .= '</select>';
			
			# write sort selector
			$html .= '<select class="bsc-data-table-sorter" id="'.$this->attributes['id'].'-sorter" onchange="bsc.widget.dataTable.objs[\''.$this->attributes['id'].'\'].changeSort(new String($(this).val()).split(\'--\'));">';
			foreach($this->children as $child)
			{
				$html .= '<option value="'.$child->options['name'].'--asc"';
				if($child->options['name'] == $this->options['sort_column'] and $this->options['sort_direction'] == 'asc')
					$html .= ' selected="selected"';
				$html .= '>';
				$html .= 'Sort by: '.$child->attributes['data-label'];
				$html .= '</option>';

				$html .= '<option value="'.$child->options['name'].'--desc"';
				if($child->options['name'] == $this->options['sort_column'] and $this->options['sort_direction'] == 'desc')
					$html .= ' selected="selected"';
				$html .= '>';
				$html .= 'Sort by: '.$child->attributes['data-label'].', Reverse';
				$html .= '</option>';
			}
			$html .= '</select></div>';
		}
		
		$html .= '</td></tr></tfoot>';
		$html .= parent::render_end($data);
		
		
		$js = 'bsc.widget.dataTable.create(';
		$js .= '\''.$this->attributes['id'].'\',';
		$js .= '\''.$this->options['url'].'\',';
		$js .= '\''.$this->options['current_page'].'\',';
		$js .= '\''.$this->options['max_page'].'\',';
		$js .= '\''.$this->options['row_count'].'\',';
		$js .= '\''.$this->options['sort_column'].'\',';
		$js .= '\''.$this->options['sort_direction'].'\',';
		$js .= json_encode($this->options['filters']);
		$js .=');';
		$html .= bsc::call_hook('js',$js);
		
		return $html;
	}
	
	public function reload($name)
	{
		$js = 'bsc.widget.dataTable.objs[\''.$name.'\'].refreshData();';
		bsc::call_hook('js',$js);
		return $js;
	}
}

?>