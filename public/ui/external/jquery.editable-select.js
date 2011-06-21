
 /**
 * Copyright (c) 2009 Anders Ekdahl (http://coffeescripter.com/)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Version: 1.3.2
 *
 * Demo and documentation: http://coffeescripter.com/code/editable-select/
 */

(function($) {
 	var instances = [];
	var methods={
		getInstance:function() {	
			return   instances[this.data('editable-selecter')];
		},
		getSelectedItem:function() {
			var sItem={}	
		var inst= instances[this.data('editable-selecter')];
		
		if (inst) {
		//var opt=inst.select_mc[0].options[[(inst.selectedListItem().data('seq_id'))]]
		var opt=null
		for (var i=0;i<inst.select_mc[0].options.length;i++) {
			if (inst.select_mc[0].options[i].text==this.val()) {
			opt=inst.select_mc[0].options[i]
			}
		}
			if (opt) {
				for (var i=0;i<opt.attributes.length;i++) {
					sItem[opt.attributes[i].name]=opt.attributes[i].value
				}
				sItem['value']=opt.value
			}
		}
		return sItem
		},
		init:function(options) { 
return this.each(function() {
	var defaults = { bg_iframe: false,
                     onSelect: false,
                     items_then_scroll: 10,
                     case_sensitive: false
    };
    var settings = $.extend(defaults, options);
    // Only do bg_iframe for browsers that need it
    if(settings.bg_iframe && !$.browser.msie) {
      settings.bg_iframe = false;
    };
    
      var i = instances.length;
       $(this).data('editable-selecter', i);
        instances[i] = new EditableSelect($(this), settings);
         $(this).data('instance', instances[i]);
	})
	  
   
		}
	}
  $.fn.editableSelect = function(method) {
	if ( methods[method] ) {

      return methods[method].apply(this, Array.prototype.slice.call( arguments, 1 ));
    } else if ( typeof method === 'object' || ! method ) {
	return methods.init.apply( this, arguments );
    } else {

    }
  };
var EditableSelect = function(select, settings) {
    this.init(select, settings);
  };
EditableSelect.prototype = {
    settings: false,
    text: false,
    select: false,
    select_width: 0,
    wrapper: false,
    list_item_height: 20,
    list_height: 0,
    list_is_visible: false,
    hide_on_blur_timeout: false,
    bg_iframe: false,
    current_value: '',
    init: function(select, settings) {
      this.settings = settings;
      this.wrapper = $(document.createElement('div'));
      this.wrapper.addClass('editable-select-options');
      this.select_mc = $(select);
      this.text = $('<input type="text">');
      this.text.attr('name', this.select_mc.attr('name'));
	this.select_mc.attr('name',this.select_mc.attr('name')+"_hidden")
      this.text.data('editable-selecter', this.select_mc.data('editable-selecter'));
      // Because we don't want the value of the select when the form
      // is submitted
      this.select_mc.attr('disabled', 'disabled');
      this.text[0].className = this.select_mc[0].className;
      var id = this.select_mc.attr('id');
      if(!id) {
        id = 'editable-select'+ instances.length;
      };
      this.text.attr('id', id);
      this.text.attr('autocomplete', 'off');
      this.text.addClass('editable-select');
      this.select_mc.attr('id', id +'_hidden_select');
      this.select_mc.after(this.text);
      if(this.select_mc.css('display') == 'none') {
        this.text.css('display', 'none');
      }
      if(this.select_mc.css('visibility') == 'hidden') {
        this.text.css('visibility', 'visibility');
      }
      // Set to hidden, because we want to call .show()
      // on it to get it's width but not having it display
      // on the screen
      this.select_mc.css('visibility', 'hidden');
      this.select_mc.hide();
      this.initInputEvents(this.text);
      this.duplicateOptions();
      this.setWidths();
      $(document.body).append(this.wrapper);

      if(this.settings.bg_iframe) {
        this.createBackgroundIframe();
      };
    },
    /**
     * Take the select lists options and
     * populate an unordered list with them
     */
	
    duplicateOptions: function() {
      var context = this;
      var option_list = $(document.createElement('ul'));
      this.wrapper.append(option_list);
      var options = this.select_mc.find('option');
	var o=0 
      options.each(function() {
        if($(this).attr('selected')) {
          context.text.val($(this).val());
          context.current_value = $(this).val();
        };
	
        var li = $('<li>'+ $(this).val() +'</li>');
	li.data("seq_id",o);
        context.initListItemEvents(li);
        option_list.append(li);
	o++
      });
      this.checkScroll();
    },
    /**
     * Check if the list has enough items to display a scroll
     */
    checkScroll: function() {
      var options = this.wrapper.find('li');
      if(options.length > this.settings.items_then_scroll) {
        this.list_height = this.list_item_height * this.settings.items_then_scroll;
        this.wrapper.css('height', this.list_height +'px');
        this.wrapper.css('overflow', 'auto');
      } else {
        this.wrapper.css('height', 'auto');
        this.wrapper.css('overflow', 'visible');
      };
    },
	getOption:function() {
	return this
	},
    addOption: function(value) {
      var li = $('<li>'+ value +'</li>');
      var option = $('<option>'+ value +'</option>');
      this.select_mc.append(option);
      this.initListItemEvents(li);
      this.wrapper.find('ul').append(li);
      this.setWidths();
      this.checkScroll();
    },
    /**
     * Init the different events on the input element
     */
    initInputEvents: function(text) {
      var context = this;
      var timer = false;
      $(document.body).click(
        function() {
          context.clearSelectedListItem();
          context.hideList();
        }
      );
      text.focus(
        function() {
          // Can't use the blur event to hide the list, because the blur event
          // is fired in some browsers when you scroll the list
            if(typeof context.settings.onFocus == 'function') {
            	    context.settings.onFocus.call(context);
            };
          context.showList();
          context.highlightSelected();
        }
      ).click(
        function(e) {
          e.stopPropagation();
          context.showList();
          context.highlightSelected();
        }
      ).keydown(
        // Capture key events so the user can navigate through the list
        function(e) {
          switch(e.keyCode) {
            // Down
            case 40:
              if(!context.listIsVisible()) {
                context.showList();
                context.highlightSelected();
              } else {
                e.preventDefault();
                context.selectNewListItem('down');
              };
              break;
            // Up
            case 38:
              e.preventDefault();
              context.selectNewListItem('up');
              break;
            // Tab
            case 9:
              context.pickListItem(context.selectedListItem());
              break;
            // Esc
            case 27:
              e.preventDefault();
              context.hideList();
              return false;
              break;
            // Enter, prevent form submission
            case 13:
              e.preventDefault();
              var list_item=context.selectedListItem();
              if (list_item.text().indexOf(context.text.val())>-1) {
              context.pickListItem(list_item);
              }else{
                context.hideList();
              }
              return false;
          };
        }
      ).keyup(
        function(e) {
          // Prevent lots of calls if it's a fast typer
          if(timer !== false) {
            clearTimeout(timer);
            timer = false;
          };
          timer = setTimeout(
            function() {
              // If the user types in a value, select it if it's in the list
              if(context.text.val() != context.current_value) {
                context.current_value = context.text.val();
                context.highlightSelected();
              };
            },
            200
          );
        }
      ).keypress(
        function(e) {
          if(e.keyCode == 13) {
            // Enter, prevent form submission
            e.preventDefault();
            return false;
          };
        }
      );
    },
    initListItemEvents: function(list_item) {
      var context = this;
      list_item.mouseover(
        function() {
          context.clearSelectedListItem();
          context.selectListItem(list_item);
        }
      ).mousedown(
        // Needs to be mousedown and not click, since the inputs blur events
        // fires before the list items click event
        function(e) {
          e.stopPropagation();
          context.pickListItem(context.selectedListItem());
        }
      );
    },
    selectNewListItem: function(direction) {
      var li = this.selectedListItem();
      if(!li.length) {
        li = this.selectFirstListItem();
      };
      if(direction == 'down') {
        var sib = li.next();
      } else {
        var sib = li.prev();
      };
      if(sib.length) {
        this.selectListItem(sib);
        this.scrollToListItem(sib);
        this.unselectListItem(li);
      };
    },
    selectListItem: function(list_item) {
      this.clearSelectedListItem();
      list_item.addClass('selected');
    },
    selectFirstListItem: function() {
      this.clearSelectedListItem();
      var first = this.wrapper.find('li:first');
      first.addClass('selected');
      return first;
    },
    unselectListItem: function(list_item) {
      list_item.removeClass('selected');
    },
    selectedListItem: function() {
      return this.wrapper.find('li.selected');
    },
    clearSelectedListItem: function() {
      this.wrapper.find('li.selected').removeClass('selected');
    },
    /**
     * The difference between this method and selectListItem
     * is that this method also changes the text field and
     * then hides the list
     */
    pickListItem: function(list_item) {
    
      if(list_item.length) {
	
		this.text.val(list_item.text());
		this.current_value = this.text.val();
	
      };
      if(typeof this.settings.onSelect == 'function') {
        this.settings.onSelect.call(this, list_item);
      };
      this.hideList();
    },
    listIsVisible: function() {
      return this.list_is_visible;
    },
    showList: function() {
      this.positionElements();
      this.setWidths();
      this.wrapper.show();
      this.hideOtherLists();
      this.list_is_visible = true;
      if(this.settings.bg_iframe) {
        this.bg_iframe.show();
      };
    },
    highlightSelected: function() {
      var context = this;
      var current_value = this.text.val();
      if(current_value.length < 0) {
        if(highlight_first) {
          this.selectFirstListItem();
        };
        return;
      };
      if(!context.settings.case_sensitive) {
        current_value = current_value.toLowerCase();
      };
      var best_candiate = false;
      var value_found = false;
      var list_items = this.wrapper.find('li');
      list_items.each(
        function() {
          if(!value_found) {
            var text = $(this).text();
            if(!context.settings.case_sensitive) {
              text = text.toLowerCase();
            };
            if(text == current_value) {
              value_found = true;
              context.clearSelectedListItem();
              context.selectListItem($(this));
              context.scrollToListItem($(this));
              return false;
            } else if(text.indexOf(current_value) === 0 && !best_candiate) {
              // Can't do return false here, since we still need to iterate over
              // all list items to see if there is an exact match
              best_candiate = $(this);
            };
          };
        }
      );
      if(best_candiate && !value_found) {
        context.clearSelectedListItem();
        context.selectListItem(best_candiate);
        context.scrollToListItem(best_candiate);
      } else if(!best_candiate && !value_found) {
        this.selectFirstListItem();
      };
    },
    scrollToListItem: function(list_item) {
      if(this.list_height) {
        this.wrapper.scrollTop(list_item[0].offsetTop - (this.list_height / 2));
      };
    },
    hideList: function() {
      this.wrapper.hide();
      this.list_is_visible = false;
      if(this.settings.bg_iframe) {
        this.bg_iframe.hide();
      };
    },
    hideOtherLists: function() {
      for(var i = 0; i < instances.length; i++) {
        if(i != this.select_mc.data('editable-selecter')) {
          instances[i].hideList();
        };
      };
    },
    positionElements: function() {
      var offset = this.text.offset();
      offset = { top: offset.top, left: offset.left };
      offset.top += this.text[0].offsetHeight;
      this.wrapper.css({top: offset.top +'px', left: offset.left +'px'});
      // Need to do this in order to get the list item height
      this.wrapper.css('visibility', 'hidden');
      this.wrapper.show();
      this.list_item_height = this.wrapper.find('li')[0].offsetHeight;
      this.wrapper.css('visibility', 'visible');
      this.wrapper.hide();
    },
    setWidths: function() {
      // The text input has a right margin because of the background arrow image
      // so we need to remove that from the width
      this.select_mc.show();
      var width = this.select_mc.width() + 2;
      this.select_mc.hide();
      var padding_right = parseInt(this.text.css('padding-right').replace(/px/, ''), 10);
      this.text.width(width - padding_right);
      this.wrapper.width(width + 2);
      if(this.bg_iframe) {
        this.bg_iframe.width(width + 4);
      };
    },
    createBackgroundIframe: function() {
      var bg_iframe = $('<iframe frameborder="0" class="editable-select-iframe" src="about:blank;"></iframe>');
      $(document.body).append(bg_iframe);
      bg_iframe.width(this.select_mc.width() + 2);
      bg_iframe.height(this.wrapper.height());
      bg_iframe.css({top: this.wrapper.css('top'), left: this.wrapper.css('left')});
      this.bg_iframe = bg_iframe;
    }
  };
  
})(jQuery);
