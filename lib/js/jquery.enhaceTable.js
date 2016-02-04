/**
*
*/
jQuery.fn.enhanceTable = function(options) {
	var defaults = {
		classHeader	: "ui-state-default",
		classDefault	: "ui-widget-content",
		classHover	: "ui-state-hover",
		classActive	: "ui-state-active",
		classFocus	: "uix-state-focused",
		classPrefix	: "uix",
		singleSelect	: true
	};
	var o = {};
	$.extend(o,defaults,options);
	var _lastSelectedRowIdx = -1;

	var _row_hoverOn  = function() { $(this).addClass(o.classHover); }
	var _row_hoverOff = function() { $(this).removeClass(o.classHover); }

	var _row_blur = function() {
		$(this).removeClass(o.classFocus);
	}
	var _row_focus = function() {
		$(this).parent().children("tr."+o.classFocus).removeClass(o.classFocus);
		$(this).addClass(o.classFocus);
	}

	var _row_click_single_select = function() {
		if( $(this).hasClass(o.classActive) ) {
			$(this).removeClass(o.classActive);
		}
		else {
			//$(this).parent().children("tr."+o.classActive).removeClass(o.classActive);
			$(this).siblings("."+o.classActive).removeClass(o.classActive);
			$(this).addClass(o.classActive);
		}
		$(this).focus();
	}

	/**
	* 	Handles all keyboard events for single row selection
	*/
	var _row_keyup_single_select = function(evt){
		switch( evt.which )
		{
		// Space
		case 32:
			$(this).siblings("."+o.classActive).removeClass(o.classActive);
			$(this).addClass(o.classActive).focus();
			break;
		// Up
		case 38:
			$(this).prev().focus();
			break;
		// Down
		case 40:
			$(this).next().focus();
			break;
		// Page Up
		case 33:
			$(this).prevAll().last().focus();
			break;
		// Page Down
		case 34:
			$(this).nextAll().last().focus();
			break;
		}
	}

	var _row_click_multi_select    = function(e) {
		// Select row range
		if( e.shiftKey && _lastSelectedRowIdx!=-1 ) {
			var ibeg = _lastSelectedRowIdx;
			var iend = this.rowIndex;
			if( ibeg<iend )
				$(this).prevUntil("tr[rowIndex="+ibeg+"]").toggleClass(o.classActive);
			else
				$(this).nextUntil("tr[rowIndex="+ibeg+"]").toggleClass(o.classActive);
			$(this).toggleClass(o.classActive);
			_lastSelectedRowIdx = this.rowIndex;
		}
		// Select single row
		else {
			_lastSelectedRowIdx = this.rowIndex;
			$(this).toggleClass(o.classActive);
		}
		$(this).focus();
	}

	/**
	* 	Handles all keyboard events for multirow selection
	*/
	var _row_keyup_multi_select = function(evt) {
		_lastSelectedRowIdx = -1;
		//console.log( evt.which );
		switch( evt.which )
		{
		// Space
		case 32:
			_lastSelectedRowIdx = this.rowIndex;
			$(this).toggleClass(o.classActive).focus();
			break;
		// Up
		case 38:
			if( evt.shiftKey )
				$(this).prev().toggleClass(o.classActive).focus();
			else
				$(this).prev().focus();
			break;
		// Down
		case 40:
			if( evt.shiftKey )
				$(this).toggleClass(o.classActive).next().focus();
			else
				$(this).next().focus();
			break;
		// Page Up
		case 33:
			if( evt.shiftKey )
				$(this).toggleClass(o.classActive)
					.prevAll().toggleClass(o.classActive)
					.last().focus();
			else
				$(this).prevAll().last().focus();
			break;
		// Page Down
		case 34:
			_lastSelectedRowIdx = -1;
			if( evt.shiftKey )
				$(this).toggleClass(o.classActive)
					.nextAll().toggleClass(o.classActive)
					.last().focus();
			else
				$(this).nextAll().last().focus();
			break;
		}
		return false;
	}

	return this.each(function(){
		$(this)
			//.attr("cellpadding","0")
			//.attr("cellspacing","0")
			.addClass(o.classPrefix+"-table");
		$("thead tr",this).addClass(o.classHeader);
		$("thead tr th:first",this).addClass(o.classPrefix+"-alpha");
		$("thead tr th:last",this).addClass(o.classPrefix+"-omega");
		$("tbody tr",this).each(function() {
			var rowElem = this;
			if( rowElem.rowIndex%2 )
				$(rowElem).addClass(o.classPrefix+"-even");
			else
				$(rowElem).addClass(o.classPrefix+"-odd");

			$("td:first",rowElem).addClass(o.classPrefix+"-alpha");
			$("td:last",rowElem).addClass(o.classPrefix+"-omega");
			$(rowElem)
				.attr("tabindex","0")
				.addClass(o.classDefault)
				.css({
					"-moz-user-select":"none",
					"-webkit-user-select":"none",
					"-khtml-user-select":"none"
				})
				.children("td").attr("unselectable","on"); // iexplorer :-/

			$(rowElem)
				.hover( _row_hoverOn, _row_hoverOff )
				.click( o.singleSelect?_row_click_single_select:_row_click_multi_select )
				.blur(_row_blur)
				.focus(_row_focus)
				.keyup( o.singleSelect?_row_keyup_single_select:_row_keyup_multi_select );
		});
	});
};
