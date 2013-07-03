bsc.widget.dataTable=function(id,url,curPage,maxPage,rowCount,sortCol,sortDir,filters){
	this.id = id;
	this.url = url;
	this.curPage = curPage;
	this.maxPage = maxPage;
	this.rowCount = rowCount;
	this.sortCol = sortCol;
	this.sortDir = sortDir;
	this.filters = filters;
}
bsc.widget.dataTable.objs={};

bsc.widget.dataTable.create=function(id,url,curPage,maxPage,pageSize,sortCol,sortDir,filters){
	bsc.widget.dataTable.objs[id] = new bsc.widget.dataTable(id,url,curPage,maxPage,pageSize,sortCol,sortDir,filters);
}

bsc.widget.dataTable.prototype.applyFilter=function(filterName,formObj){
	formObj = $(formObj);
	if(formObj.attr('type') == 'checkbox'){
		this.filters[filterName].value = (formObj.is(':checked'))?'1':'null';
	}else{
		this.filters[filterName].value = formObj.val();
	}
	
	this.curPage = 0;
	this.refreshData();
}

bsc.widget.dataTable.prototype.changePage=function(newPage){
	alert('test');
	switch(newPage){
		case 'first':
			this.curPage = 0;
			break;
		case 'previous':
			this.curPage--;
			if(this.curPage < 0){
				this.curPage = 0;
				return;
			}
			break;
		case 'next':
			this.curPage++
			if(this.curPage >= this.maxPage){
				this.curPage--;
				return;
			}
			break;
		case 'last':
			if(this.curPage == (this.maxPage - 1)){
				return;
			}
			this.curPage = (this.maxPage - 1);
			break;
		default:
			this.curPage = newPage;
			if(this.curPage >= this.maxPage){
				this.curPage--;
				return;
			}
			break;
	}
	this.refreshData();
}

bsc.widget.dataTable.prototype.changeSort=function(newCol,newDir){
	if(typeof(newCol) == 'object'){
		newDir = newCol[1];
		newCol = newCol[0];
	}
	if(typeof(newDir) == 'undefined'){
		if(newCol == this.sortCol){
			this.sortDir = (this.sortDir == 'asc')?'desc':'asc';
		}else{
			this.sortCol = newCol;
			this.sortDir = 'asc';
		}
	}else{
		this.sortCol = newCol;
		this.sortDir = newDir;
	}
	this.curPage = 0;
	this.refreshData();
}

bsc.widget.dataTable.prototype.changeRowCount=function(newCount){
	this.rowCount = newCount;
	this.curPage = 0;
	this.refreshData();
}

bsc.widget.dataTable.prototype.updateProgress=function(){
	if(this.progressLevel < 100){
		this.progressLevel += 5;
		$('#'+this.id+' > thead > tr.progress > td > div.progress > div.progress-bar').css('width',this.progressLevel+'%');
	}else{
		$('#'+this.id+' > thead > tr.progress > td > span').show();
		
	}
}

bsc.widget.dataTable.prototype.refreshData=function(){
	var data = '&bsc_data_table__'+this.id+'__return_data=yes';
	data += '&bsc_dt__'+this.id+'__page='+this.curPage;
	data += '&bsc_dt__'+this.id+'__row_count='+this.rowCount;
	data += '&bsc_dt__'+this.id+'__sort_column='+this.sortCol;
	data += '&bsc_dt__'+this.id+'__sort_direction='+this.sortDir;
	this.progressLevel = 0;
	$('#'+this.id+' > thead > tr.progress > td > div.progress > div.progress-bar').css('width','0%');
	window.clearInterval(this.progressInterval);
	this.progressInterval=window.setInterval('bsc.widget.dataTable.objs[\''+this.id+'\'].updateProgress();',100);
	$('#'+this.id+' > tbody').hide();
	$('#'+this.id+' > thead > tr.progress').show(500);
	
	for(var key in this.filters){
		if(typeof(this.filters[key].value) != 'object'){
			data += '&bsc_dt__'+key+'='+encodeURIComponent(this.filters[key].value);
		}
	}
	jQuery.ajax(
		this.url,{
			'data':data,
			'dataType':'json',
			'success':this.handleDataUpdate
		}
	);
}

bsc.widget.dataTable.prototype.handleDataUpdate=function(data){
	var table = bsc.widget.dataTable.objs[data.id];
	table.curPage = data.current_page;
	table.maxPage = data.max_page;
	table.rowCount = data.row_count;
	table.sortCol = data.sort_column;
	table.sortDir = data.sort_direction;
	table.updateHeadersPagers();
	table.insertData(data.data);
}

bsc.widget.dataTable.prototype.updateHeadersPagers=function(){
	// set the pager text
	var selector = $('#'+this.id+'-pager-selector');
	if(this.rowCount == 0)
		selector.parent().hide();
	else{
		// adjust the number of pages in the dropdown
		var oldMaxLength = selector.find('ul li').size();
		if(oldMaxLength != this.maxPage){
			//alert('adjust the length');
			newHtml = '';
			var exampleAnchor = selector.find('ul li[data-page="0"] a');
			var template = exampleAnchor.text();
			var onClick = new String(exampleAnchor.attr('onclick'));

			for(var i=0;i<this.maxPage;i++){
				newHtml += '<li role="presentation" data-page="'+i+'">';
				newHtml += '<a role="menuitem" onclick="'+onClick.replace('0',i)+'">';
				newHtml += 'Page '+ (i+1)+' of '+this.maxPage+'</a></li>';
			}
			$('#'+this.id+'-pager-selector ul').html(newHtml);
		}
		selector.children('a').html(selector.find('ul li[data-page="'+this.curPage+'"] a').html()).parent().parent().show();
	}
	
	// disable first/next/previous/last
	
	// update column header sort classes
	$('#'+this.id+' > thead > tr > th').removeClass('bsc-data-table-sort-asc').removeClass('bsc-data-table-sort-desc');
	$('#'+this.id+' > thead > tr > th[data-column="'+this.sortCol+'"]').addClass('bsc-data-table-sort-'+this.sortDir)
	$('#'+this.id+'-sorter option').removeAttr('selected');
	$('#'+this.id+'-sorter').val(this.sortCol+'--'+this.sortDir);
}

bsc.widget.dataTable.prototype.insertData=function(data){
	var html = '';
	window.clearInterval(this.progressInterval);
	if(data.length == 0){
		$('#'+this.id+' > tbody,#'+this.id+' > tfoot').hide();
		$('#'+this.id+' > thead > tr.empty').show();
	}else{
		$('#'+this.id+' > tfoot').show();
		$('#'+this.id+' > thead > tr.empty').hide();
		for(var i=0;i<data.length;i++){
			html += '<tr>';
			for(var j=0;j<data[i].length;j++){
				html += data[i][j];
			}
			html += '</tr>';
		}
		$('#'+this.id+' > tbody').html(html).show();
		$('html, body').animate({
			scrollTop: $('#'+this.id).offset().top
		}, 200);
		$('#'+this.id+' > thead > tr.progress,#'+this.id+' > thead > tr.progress > td > span').hide();
		$('#'+this.id+' > tbody').show(500);
	}
}
