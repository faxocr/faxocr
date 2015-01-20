/* jQuery Context Menu
 * Created: Dec 16th, 2009 by DynamicDrive.com.
 * This notice must stay intact for usage 
 * Author: Dynamic Drive at http://www.dynamicdrive.com/
 * Visit http://www.dynamicdrive.com/ for full source code
 */

jQuery.noConflict();

//
// ez-cloud用変数
//
var targetid;
// var ctbl = ['#FFFFFF', '#FFE5e5', '#ffd8d8', '#ffcbcb', '#ffbebe', '#ffb1b1', '#ffa3a3'];
// var sval = 0; // note: the val is referenced from other files.

var jquerycontextmenu = {
	arrowpath: 'image/arrow.gif', //full URL or path to arrow image
	contextmenuoffsets: [1, -1], //additional x and y offset from mouse cursor for contextmenus

	//***** NO NEED TO EDIT BEYOND HERE

	builtcontextmenuids: [], //ids of context menus already built (to prevent repeated building of same context menu)

	positionul: function($, $ul, e) {
		var istoplevel = $ul.hasClass('jqcontextmenu'), //Bool indicating whether $ul is top level context menu DIV
			docrightedge = $(document).scrollLeft() + $(window).width() - 40, //40 is to account for shadows in FF
			docbottomedge = $(document).scrollTop() + $(window).height() - 40,
			x,
			y;

		if (istoplevel) { //if main context menu DIV
			x = e.pageX + this.contextmenuoffsets[0]; //x pos of main context menu UL
			y = e.pageY + this.contextmenuoffsets[1];
			x = (x + $ul.data('dimensions').w > docrightedge) ? docrightedge - $ul.data('dimensions').w : x; //if not enough horizontal room to the ridge of the cursor
			y = (y + $ul.data('dimensions').h > docbottomedge) ? docbottomedge - $ul.data('dimensions').h : y;
		} else { //if sub level context menu UL
			var $parentli = $ul.data('$parentliref'),
				parentlioffset = $parentli.offset();

			x = $ul.data('dimensions').parentliw; //x pos of sub UL
			y = 0;

			x = (parentlioffset.left + x + $ul.data('dimensions').w > docrightedge) ? x - $ul.data('dimensions').parentliw - $ul.data('dimensions').w : x; //if not enough horizontal room to the ridge parent LI
			y = (parentlioffset.top + $ul.data('dimensions').h > docbottomedge) ? y - $ul.data('dimensions').h + $ul.data('dimensions').parentlih : y;
		}
		$ul.css({left: x, top: y});
	},

	showbox: function($, $contextmenu, e) {
		$contextmenu.show();
	},

	hidebox: function($, $contextmenu) {
		$contextmenu.find('ul').andSelf().hide(); //hide context menu plus all of its sub ULs
	},

	buildcontextmenu: function($, $menu) {
		$menu.css({display: 'block', visibility: 'hidden'})
			 .appendTo(document.body);
		$menu.data('dimensions', {
			w: $menu.outerWidth(),
			h: $menu.outerHeight()
		}); //remember main menu's dimensions

		var $lis = $menu.find("ul").parent(); //find all LIs within menu with a sub UL

		$lis.each(function(i) {
			var $li = $(this).css({zIndex: 1000 + i}),
				$subul = $li.find('ul:eq(0)').css({display: 'block'}); //set sub UL to "block" so we can get dimensions

			$subul.data('dimensions', {
				w: $subul.outerWidth(),
				h: $subul.outerHeight(),
				parentliw: this.offsetWidth,
				parentlih: this.offsetHeight
			});
			$subul.data('$parentliref', $li); //cache parent LI of each sub UL
			$li.data('$subulref', $subul); //cache sub UL of each parent LI
			$li.children("a:eq(0)").append( //add arrow images
				'<img src="' + jquerycontextmenu.arrowpath + '" class="rightarrowclass" style="border:0;" />'
			);
			$li.bind('mouseenter', function(e) { //show sub UL when mouse moves over parent LI
				var $targetul = $(this).data('$subulref');

				if ($targetul.queue().length <= 1) { //if 1 or less queued animations
					jquerycontextmenu.positionul($, $targetul, e);
					$targetul.show();
				}
			});
			$li.bind('mouseleave', function(e) { //hide sub UL when mouse moves out of parent LI
				$(this).data('$subulref').hide();
			});
		});
		$menu.find('ul').andSelf().css({display: 'none', visibility: 'visible'}); //collapse all ULs again
		this.builtcontextmenuids.push($menu.get(0).id); //remember id of context menu that was just built
	},

	init: function($, $target, $contextmenu) {
		var field;
		//only bind click event to document once
		if (this.builtcontextmenuids.length === 0) {
			$(document).bind("contextmenu", function(e) {
				// hide all context menus (and their sub ULs)
				// when left mouse button is clicked
				if (e.button === 0) {
					name = document.activeElement.name;
					if (name === 'undefined' || typeof name === 'undefined') {
						// Firefox => typeof name is 'undefined',
						// other browser => typeof name is String
						jquerycontextmenu.hidebox($, $('.jqcontextmenu'));
						if (targetid) {
//							$("#" + targetid).css('background-color', ctbl[sval]);
							SheetCell.clickSelected(targetid, cellBgColorManager);
						}
//						sval = 0;
						targetid = null;
					} else {
						// XXX
						// document.activeElement.value = "";
					}
					field = null;
				}
			});

			// XXX
			/*
			$(document).bind("mouseup", function(e) {
				jquerycontextmenu.hidebox($, $('.jqcontextmenu'));
			});
			*/
		}

		//if this context menu hasn't been built yet
		if (jQuery.inArray($contextmenu.get(0).id, this.builtcontextmenuids) === -1) {
			// 個別のメニューアイテム登録？
			this.buildcontextmenu($, $contextmenu);
			$(document).bind("click", function(e) {
				// hide all context menus (and their sub ULs)
				// when left mouse button is clicked
				if (e.button === 0) {
					jquerycontextmenu.hidebox($, $('.jqcontextmenu'))
				}
			});
		}

		// if $target matches an element within the context menu markup,
		// don't bind oncontextmenu to that element
		if ($target.parents().filter('ul.jqcontextmenu').length > 0) {
			return;
		}

		$target.bind("contextmenu", function(e) {
			var targettd;

			// ターゲット選択時
			jquerycontextmenu.hidebox($, $('.jqcontextmenu'));

			//hide all context menus (and their sub ULs)
			jquerycontextmenu.positionul($, $contextmenu, e);
			jquerycontextmenu.showbox($, $contextmenu, e);

			field = $('#field').focus();
			targettd = $('td[name="' + this.id + '"]');

			if (targettd.length) {
				field.val(targettd.find('div').text());
			} else {
				field.val(this.id);
			}

			field.select();
			targetid = this.id;

			// sval = (defaultbg === '#FFFFFF') ? 0 : 5;
			//SheetFieldProcessor.set($(this));

			return false;
		});
	}
};

jQuery.fn.addcontextmenu = function(contextmenuid) {
	var $ = jQuery;

	return this.each(function() { //return jQuery obj
		var $target = $(this);

		jquerycontextmenu.init($, $target, $('#' + contextmenuid));
	});
};

//Usage: $(elementselector).addcontextmenu('id_of_context_menu_on_page')
