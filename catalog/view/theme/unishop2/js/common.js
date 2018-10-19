/* Aim */
!function(e){function t(t){var n=e(this),i=null,o=[],u=null,r=null,c=e.extend({rowSelector:"> li",submenuSelector:"*",submenuDirection:"right",tolerance:75,enter:e.noop,exit:e.noop,activate:e.noop,deactivate:e.noop,exitMenu:e.noop},t),l=2,f=100,a=function(e){o.push({x:e.pageX,y:e.pageY}),o.length>l&&o.shift()},s=function(){r&&clearTimeout(r),c.exitMenu(this)&&(i&&c.deactivate(i),i=null)},h=function(){r&&clearTimeout(r),c.enter(this),v(this)},m=function(){c.exit(this)},x=function(){y(this)},y=function(e){e!=i&&(i&&c.deactivate(i),c.activate(e),i=e)},v=function(e){var t=p();t?r=setTimeout(function(){v(e)},t):y(e)},p=function(){function t(e,t){return(t.y-e.y)/(t.x-e.x)}if(!i||!e(i).is(c.submenuSelector))return 0;var r=n.offset(),l={x:r.left,y:r.top-c.tolerance},a={x:r.left+n.outerWidth(),y:l.y},s={x:r.left,y:r.top+n.outerHeight()+c.tolerance},h={x:r.left+n.outerWidth(),y:s.y},m=o[o.length-1],x=o[0];if(!m)return 0;if(x||(x=m),x.x<r.left||x.x>h.x||x.y<r.top||x.y>h.y)return 0;if(u&&m.x==u.x&&m.y==u.y)return 0;var y=a,v=h;"left"==c.submenuDirection?(y=s,v=l):"below"==c.submenuDirection?(y=h,v=s):"above"==c.submenuDirection&&(y=l,v=a);var p=t(m,y),b=t(m,v),d=t(x,y),g=t(x,v);return d>p&&b>g?(u=m,f):(u=null,0)};n.mouseleave(s).find(c.rowSelector).mouseenter(h).mouseleave(m).click(x),e(document).mousemove(a)}e.fn.menuAim=function(e){return this.each(function(){t.call(this,e)}),this}}(jQuery);

$(function() {
	if($('.product-layout').length)	{
		select_view();
	}
	
    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я-]+';
    $('[name="firstname"]').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я-]+';
    $('[name="lastname"]').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я-]+';
    $('[name="patronymic"]').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[0-9()+-]+';
    $('[name="telephone"]').mask('hhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[0-9]+';
    $('#input-custom-field12').mask('hh:hh:hhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я-]+';
    $('#input-lastname').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я-]+';
    $('#input-patronymic').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я-]+';
    $('#input-firstname').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[0-9]+';
    $('#input-telephone').mask('+hhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я0-9()+-]+';
    $('#input-password').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-ZА-Яа-я0-9()+-]+';
    $('#input-confirm').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-Z-@+-=.]+';
    $('[name="email"]').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-Z0-9-.]+';
    $('#input-custom-field10').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    $.mask.definitions['h'] = '[a-zA-Z0-9-.]+';
    $('#input-custom-field11').mask('hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh', {
        placeholder: "",
        autoclear: false
    });

    if ($(window).width() < 520) {
        $('#cart .dropdown-menu').css('width', $(window).width() - 38);
    }

    let valueSearch = $("#search input[name='search']").val();
    valueSearch = valueSearch.replace(/\s+/g, '');
    if (valueSearch === "") {
		$('.search-btn').attr('disabled', 'true');
	}

    let valueSearch2 = $("#input-search").val();
	if (valueSearch2) {
		valueSearch2 = valueSearch2.replace(/\s+/g, '');
		if (valueSearch2 === "") {
			$('#button-search').attr('disabled', 'true');
		}
	}

    let valueSearch3 = $("#search3 input[name='search']").val();
    valueSearch3 = valueSearch3.replace(/\s+/g, '');
    if (valueSearch3 === "") {
        $('.search-btn').attr('disabled', 'true');
    }

    $("#search3 input[name='search']").keyup(function () {
        let valueSearch3 = $("#search3 input[name='search']").val();
        valueSearch3 = valueSearch3.replace(/\s+/g, '');
        if (valueSearch3 === "") {
            $('.search-btn').attr('disabled', 'true');
            $('#button-search').attr('disabled', 'true');
        } else {
            $('.search-btn').removeAttr('disabled');
            $('#button-search').removeAttr('disabled');
        }
    });

    $("#search input[name='search']").keyup(function () {
        let valueSearch = $("#search input[name='search']").val();
        valueSearch = valueSearch.replace(/\s+/g, '');
        if (valueSearch === "") {
            $('.search-btn').attr('disabled', 'true');
            $('#button-search').attr('disabled', 'true');
        } else {
            $('.search-btn').removeAttr('disabled');
            $('#button-search').removeAttr('disabled');
		}
    });

    $("#input-search").keyup(function () {
        let valueSearch2 = $("#input-search").val();
        valueSearch2 = valueSearch2.replace(/\s+/g, '');
        if (valueSearch2 === "") {
            $('#button-search').attr('disabled', 'true');
            $('.search-btn').attr('disabled', 'true');
        } else {
            $('#button-search').removeAttr('disabled');
            $('.search-btn').removeAttr('disabled');
        }
    });

    if ($(window).width() > 576) {
        $('.sort-price').addClass('col-xs-6');
        $('.sort-price').removeClass('col-xs-12');
    }

	$('#list-view').on('click', function() {
		list_view();
	});

	$('.all-products').click(function() {
        $('.product-info').show(1000);
        $(this).hide();
        $('.hide-product').show(1500);
    });

    $('.hide-product').click(function() {
        $('.product-info').slice(5).hide(1000);
        $(this).hide();
        $('.all-products').show(1500);
    });

    $('.name-main-category').click(function() {
    	if ($(this).siblings('.new-menu-category').is(':visible')) {
            $(this).siblings('.new-menu-category').hide(300);
            $(this).siblings('.fa').toggleClass('transform');
		} else {
            $('.new-menu-category').hide(300);
            $(this).siblings('.new-menu-category').show(300);
            $('.category-list').find('.fa').removeClass('transform');
            $(this).siblings('.fa').toggleClass('transform');
		}
    });

    $('.category-list .fa').click(function() {
        if ($(this).siblings('.new-menu-category').is(':visible')) {
            $(this).siblings('.new-menu-category').hide(300);
            $(this).toggleClass('transform');
        } else {
            $('.new-menu-category').hide(300);
            $('.category-list').find('.fa').removeClass('transform');
            $(this).siblings('.new-menu-category').show(300);
            $(this).toggleClass('transform');
        }
    });


	$("body").delegate( ".delete-address", "click", function() {
        $(this).closest('.custom-field').remove();
    });

	$('#menu-category .navbar-header').click(function () {
		if ($('.navbar-ex2-collapse').hasClass('in')) {
            $('.navbar-ex2-collapse').removeClass('in');
		} else {
            $('.navbar-ex2-collapse').addClass('in');
		}
    });

    $(window).resize(function(){
       if ($(window).width() < 576) {
       		$('.sort-price').removeClass('col-xs-6');
       		$('.sort-price').addClass('col-xs-12');
	   } else {
           $('.sort-price').addClass('col-xs-6');
           $('.sort-price').removeClass('col-xs-12');
	   }

       if ($(window).width() < 520) {
           $('#cart .dropdown-menu').css('width', $(window).width() - 38);
       }

       if ($(window).width() > 991) {
           $('#menu').css('position', 'relative');
       }
    });

    $("body").delegate(".add-address", "click", function() {
        let div = $(this).closest('.custom-field');
        let name = $(this).data("label");
        let key = parseInt($(this).attr('data-key'), 10) + 1;
        let number = key + 1;
        let placeholder = $(this).data("placeholder");
        $('<div class="form-group custom-field" data-sort="11">\n' +
'                    <label class="col-sm-3 control-label" for="input-custom-field">' + name +'  '+ number + '</label>\n' +
'                      <div class="col-sm-9">\n' +
'                      <textarea name="custom_field[account_edit][15]['+ key +']" rows="5" placeholder="' + placeholder +'" id="input-custom-field" class="form-control"></textarea>\n' +
'                          <button type="button" class="btn btn-danger delete-address">\n' +
'                              <i class="fa fa-times"></i>\n' +
'                          </button>\n' +
'                    </div>\n' +
'                  </div>'
        ).insertBefore(div);
        $(this).attr('data-key', key);
    });

    $("body").delegate( "input[name='quantity']", "change keyup input click", function() {
        if (this.value.match(/[^0-9\.]/g)) {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        }
    });
	
	$('#grid-view').on('click', function() {
		grid_view();
	});



    $('.close-modal').on('click', function() {
        $("#modal-agree").remove();
        let url = $('base').attr('href') + 'index.php?route=common/header/setcookie';
        $.get(url);
    });

    $('.close-alert').on('click', function(){
    	$('.alert').remove();
	})

    $('.close-profile-modal').on('click', function(){
        $('#modal-success').remove();
    })
	
	$('#compact-view').on('click', function() {
		compact_view();
	});
	
	if(uni_live_price) uniLivePrice(); 
	
	$(window).scroll(function(){		
		$(this).scrollTop() > 190 ? $('.fly_callback, .scroll_up').addClass('show') : $('.fly_callback, .scroll_up').removeClass('show');
	});
	
	$('header #phone .dropdown-menu').on('mouseenter', function() {
	    $(this).attr('style', 'display:block');
	}).on('mouseleave',function () {
	    $(this).removeAttr('style');
	});
	
	$(window).resize(function(){
		if($(window).width() <= 992) grid_view();
		m_filter();
	});
	
	/* menu */
	
	$('#menu.menu2 .dropdown-menu').each(function() {
		var menu = $('#menu').offset(), dropdown = $(this).parent().offset(), i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu.menu2').outerWidth());
		if (i > 0) $(this).css('margin-left', '-'+(i+1)+'px');
	});
	
	$('#menu .nav > li.has-children').on('mouseenter', function() {
		$('#menu').addClass('open');
	}).on('mouseleave', function() {
		$('#menu').removeClass('open');
	});
	
	$('#main-content').on('click', function() {
		$('#menu .navbar-collapse').removeClass('in');
	});
	
	$('#menu span.visible-xs').on('click', function() {
		$(this).parent().toggleClass('open');
	});
	
	uniMenuAim();
	
	//if(uni_menu_blur) uniMenuBlur();
	
	/* menu */
	
	if($(window).width() > 768) {
		$('[data-toggle=\'tooltip\']').tooltip({container:'body', trigger:'hover'});
		$(document).ajaxStop(function() {
			$('[data-toggle=\'tooltip\']').tooltip({container:'body', trigger:'hover'});
		});
	}
	
	$('.add_to_cart.disabled').each(function(){
		$(this).attr('disabled', true);
	});
	
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();
		if (element.hasClass('form-group')) element.addClass('has-error');
	});

	$('#language li a').on('click', function(e) {
		e.preventDefault();
		$('#language input[name=\'code\']').attr('value', $(this).attr('data-code'));
		$('#language input[name=\'currency\']').attr('value', $(this).attr('data-currency'));
		$('#language input[name=\'country\']').attr('value', $(this).attr('data-country'));
		$('#language input[name=\'redirect\']').attr('value', $(this).attr('data-redirect'));
		$('#language').submit();
	});
	
	$('#currency li a').on('click', function(e) {
		e.preventDefault();
		$('#currency input[name=\'code\']').attr('value', $(this).attr('data-code'));
		$('#currency').submit();
	});
	
	$('body').on('click', '.search ul li', function () {
		var elem = $(this).parent().parent();
		elem.find('button span').text($(this).text());
		elem.find('input[name=\'filter_category_id\']').val($(this).attr('data-id'));
	});

	$('body').on('click', '.search-btn', function() {
		url = $('base').attr('href') + 'index.php?route=product/search';

		var elem = $(this).parent().parent(), value = elem.find('input[name=\'search\']').val(), filter_category_id = elem.find('input[name=\'filter_category_id\']').val();
		
		if (value) url += '&search=' + encodeURIComponent(value);
		if (filter_category_id > 0) url += '&category_id=' + encodeURIComponent(filter_category_id)+'&sub_category=true';
		url += '&description=true';

		location = url;
	});

	$('body').on('keydown', 'input[name=\'search\']', function(e) {
		if (e.keyCode == 13) {
			$(this).parent().find('.search-btn').click();
		}
	});
	
	$('body').on('input', 'input[name=\'search\']', function(e) {
		$('div input[name=\'search\']').not($(this)).val($(this).val());
	});
	
	$('#search_phrase a').on('click', function() {
		if(uni_live_search) {
			$('#search input[name=\'search\']').val($(this).text()).click();
		} else {
			location = $('base').attr('href') + 'index.php?route=product/search&search='+encodeURIComponent($(this).text());
		}
	});
	
	$('#phone .additional-phone span').on('click', function() {
		$('#phone .additional-phone span').removeClass('selected');
		$(this).addClass('selected');
		$('#phone .show-phone span').text($(this).attr('data-phone'));
	});
	
	$(document).ajaxStop(function() {
		$('.modal').on('hide.bs.modal', function() {
			$(this).attr('class', 'modal fade '+uni_popup_effect_out);
		});
		
		$('.modal').on('hidden.bs.modal', function() {
			$(this).remove();
		});
		
		if($('#column-left #filterpro_box').length || $('#column-left .mfilter-box').length) {
			select_view();
		}
	});
	
	fly_menu_enabled = 0;
});

$(window).load(function() {
	if(uni_change_opt_img) uniChangeProductImg();
	if(uni_notify) uniNotify();
	if(uni_ajax_pagination) uniAjaxPagination();
	if(uni_additional_image) uniAddAdditImg();
	if(uni_showmore) uniShowMore();
	
	uniPopupOptionImg();
});

function deleteModal()
{
    $('#modal-agree').remove();
    $('#modal-agree').remove();
    $('.modal-backdrop').remove();
    $('body').removeClass();
    $('body').removeAttr('style');
}

function list_view() {
	$('.product-grid, .product-price').attr('class', 'product-layout product-list col-xs-12');
	localStorage.setItem('display', 'list');
}

function grid_view() {
	var col_left = $('#column-left').length, col_right =  $('#column-right').length, menu = $('.breadcrumb.col-md-offset-3.col-lg-offset-3').length;

	if ((col_left && col_right) || (col_right && menu)) {
		block_class = 'product-layout product-grid col-xs-12 col-sm-12 col-md-12 col-lg-6 col-xxl-6-1';
	} else if (col_left || col_right || menu) {
		block_class = 'product-layout product-grid col-xs-12 col-sm-6 col-md-4 col-lg-4 col-xxl-5';
	} else {
		block_class = 'product-layout product-grid col-xs-12 col-sm-6 col-md-3 col-lg-3 col-xxl-4';
	}
	
	$('.product-grid, .product-list, .product-price').attr('class', block_class);
	
	$product = $('.product-grid .product-thumb');
	
	autoheight($product.find('.caption > a'));
	autoheight($product.find('.attribute'));
	autoheight($product.find('.description'));
	autoheight($product.find('.option'));
		
	localStorage.setItem('display', 'grid');
}
	
function compact_view() {
	$('.product-list, .product-grid').attr('class', 'product-layout product-price col-xs-12');
	
	if($('.product-price .product-thumb .option > *').length == 0) {
		$('.product-price .product-thumb .option').remove();
		$('.product-price .product-thumb .attribute').addClass('visible-xxl');
	}
	
	localStorage.setItem('display', 'compact');
}

function select_view() {
	if (localStorage.getItem('display') == 'list') {
		list_view();
	} else if (localStorage.getItem('display') == 'compact')  {
		compact_view();
	} else {
		grid_view();
	}
}

/*function uniMenuBlur() {
	
	MenuBlur = function() {
		var uni_blur_blocks = $('#top, header .container:first-child, .menu_links, #main-content, footer'), blur_delay = uni_menu_blur == 1 ? 110 : 10, blur_timer;
		
		$('#menu, #menu.menu2, #menu.menu3').on('mouseenter', function() {
			blur_timer = setTimeout(function() { 
				uni_blur_blocks.addClass('blur');
			}, blur_delay);
		}).on('mouseleave', function() {
			clearTimeout(blur_timer);
			uni_blur_blocks.removeClass('blur');
		});
	}
	
	if($(window).width() > 768)	MenuBlur();
	
	$(window).resize(function(){
		if($(window).width() > 768) MenuBlur();
	});
}*/

function uniMenuAim() {
	
	uniAim = function() {
		$('#menu .nav').menuAim({
			rowSelector:'> li',
			submenuSelector:'*',
			activate:open_menu,
			deactivate:close_menu,
			exitMenu:exit_menu
		});
		
		function open_menu(data) {
			$(data).addClass('open')
		}
	
		function close_menu(data) {
			$(data).removeClass('open')
		}
			
		function exit_menu(data) {
			return true
		}
	}
	
	if($(window).width() > 992) uniAim();
	
	$(window).resize(function(){
		if($(window).width() > 992) uniAim();
	});
}

function uniUpdMenu(menu_block) {
	
	var menu_block = $(menu_block);
	
	updMenu = function() {
		
		var menu_width = menu_block.outerWidth(), menu_child = menu_block.find('> li').not('.additional'), total_width = 0, new_items = '';
		
		if($(window).width() > 992 && $(window).width() < 1600) {
		
			if(menu_block.find('.additional').length) menu_width = menu_width-50
		
			menu_child.each(function() {
				total_width += $(this).outerWidth();
				
				var item = $(this).find('a');
			
				if(total_width > menu_width) {
					new_items += '<li><a href="'+item.attr('href')+'">'+item.html()+'</a></li>';
					$(this).hide();
				} else {
					$(this).show();
				}
			});
		
			if (total_width > menu_width && !menu_block.find('.additional').length) {
				html = '<li class="additional">';
				html += '<button class="btn btn-link dropdown-toggle" data-toggle="dropdown">';
				html += '<span><i class="fa fa-ellipsis-h"></i></span>';
				html += '</button>';
				html += '<ul class="dropdown-menu dropdown-menu-right"></ul>';
				html += '</li>';
			
				menu_block.append(html)
			}
		
			menu_block.find('.additional ul').html(new_items)
		
			if(total_width < menu_width) {
				menu_block.find('.additional').remove();
			}
		} else {
			menu_child.show()
			menu_block.find('.additional').remove();
		}
	}
	
	updMenu();
	
	$(window).resize(function(){
		setTimeout(function() { 
			updMenu();
		}, 50);
	});
}

function uniShowMore() {
	
	if(!$('.product-layout').length || !$('.pagination_wrap .active').length) return
		
	$('.pagination_wrap').before('<div class="show-more" style="margin:10px 0 20px;text-align:center"><button type="button" class="btn btn-lg btn-default"><i class="fa fa-sync-alt"></i><span>'+uni_showmore_text+'</span></button></div>');
	
	$('.show-more .btn').on('click', function() {
		var url = $('.pagination_wrap .active').next().find('a').attr('href');
		
		if(typeof(url) == 'undefined') return;
	
		$.ajax({
			url: url,
			type: 'get',
			dataType: 'html',
			beforeSend: function() {
				//$('html body').append('<div class="full-width-loading"></div>');
				$('.show-more .btn i').addClass('spin');
			},
			success: function(data) {
				$('.products-block .row').append($(data).find('.products-block .row').html());
				$('.pagination_wrap').html($(data).find('.pagination_wrap').html());
			
				if(!$('.pagination_wrap .active').next().find('a').length) $('.show-more').hide();
			
				//$('.full-width-loading').remove();
				$('.show-more .btn i').removeClass('spin');
			}
		});
	});
}

function uniAjaxPagination() {
	$('body').on('click', '.pagination_wrap a', function(e) {
		
		if(!$('.products-block').length) return
		
		e.preventDefault();
	
		var url = $(this).attr('href');
	
		$.ajax({
			url: url,
			type: 'get',
			dataType: 'html',
			beforeSend: function() {
				$('html body').append('<div class="full-width-loading"></div>');
			},
			complete: function() {
				select_view();
				scroll_to('.products-block');
			},
			success: function(data) {
				$('.products-block').html($(data).find('.products-block').html());
				$('.pagination_wrap').html($(data).find('.pagination_wrap').html());
				
				if(!$('.pagination_wrap .active').next().find('a').length) {
					$('.show-more').hide();
				} else {
					$('.show-more').show();
				}
				
				$('.full-width-loading').remove();
			}
		});
	});
}

function autoheight(div) {
	block_height = function() {
		$(div).height('auto');
		var maxheight = 0;
		
		$(div).each(function(){
			if($(this).height() > maxheight) {
				maxheight = $(this).height();
			}
		});
		$(div).height(maxheight);
	}
	
	block_height();
	$(window).resize(block_height);
}

function fly_menu(product) {
	
	fly_menu_enabled = 1;
	
	$('#menu_wrap').remove();
		
	var menu = $('header #menu'), search = $('header #search').html(), name = $('#product h1').html(), price = $('#product .price').html(), btn = $('#product').find('.add_to_cart');
	var phone = $('header #phone').html(), account = $('#top #account').html(), cart = $('header #cart').html();

	if(product && $('#product').length) {
		html = '<div class="product_wrap col-md-8 col-lg-8 col-xxl-12"><div><div class="product_name">'+name+'</div>';
		html += '<div class="price">'+price+'</div>';
		html += '<button type="button" class="'+btn.attr('class')+'">'+btn.html()+'</button></div></div>';
	} else {
		html = '<div class="menu_wrap col-md-3 col-lg-3 col-xxl-4"><div id="menu">'+menu.html()+'</div></div>';
		html += '<div id="search_w" class="search_wrap col-md-5 col-lg-5 col-xxl-8"><div id="search3">'+search+'</div></div>';
	}
	html += '<div class="phone_wrap col-md-2 col-lg-2 col-xxl-4"><div id="phone">'+phone+'</div></div>';
	html += '<div class="account_wrap col-md-1 col-lg-1 col-xxl-2"><div id="account">'+account+'</div></div>';
	html += '<div class="cart_wrap col-md-1 col-lg-1 col-xxl-2"><div id="cart">'+cart+'</div></div>';
		
	$('html body').append('<div id="menu_wrap"><div class="container"><div class="row">'+html+'</div></div></div>');
	
	if(menu.attr('class').substr(0, 5) == 'menu2') {
		$('#menu_wrap #menu').addClass('menu3');
	}
	
	$('.product_wrap button').click(function() {
		$('#button-cart').click();
	});
			
	if (product) {
		scroll_text('#menu_wrap .product_wrap .product_name', '#menu_wrap .product_wrap .product_name span');
	}
		
	$(document).ajaxStop(function() {
		setTimeout(function() { 
			$('#menu_wrap .product_wrap .price').html($('#product .price').html());
		}, 300);
	});

	$(window).scroll(function(){
		if($(this).scrollTop() > 190) {
			$('#menu, #menu_wrap').addClass('show');
		} else {
			$('#menu, #menu_wrap').removeClass('show');
		}
	});
}

function fly_cart() {
	if(!fly_menu_enabled) {
		$(window).scroll(function(){		
			if($(window).width() > 992) {
				$(this).scrollTop() > 200 ? $('#cart').addClass('fly') : $('#cart').removeClass('fly');
			}
		});
	}
}

function uniAddAdditImg() {
	additImg = function() {
		$('.image a > img').each(function () {
			if ($(this).attr('data-additional')) {
				var img = $(this);
			
				add_image = function() {
					if ($(document).scrollTop() + $(window).height() > img.offset().top && !img.next().hasClass('additional')) {
						img.addClass('main').after('<img src="'+img.attr('data-additional')+'" class="additional img-responsive" title="'+img.attr('alt')+'" />');
					}
				}
			
				add_image();
				$(window).scroll(add_image);
			}		
		});
	}
	
	additImg();
	
	$(document).ajaxStop(function() {
		additImg();
	});
}

function m_filter() {
	if($(window).width() < 767) {
		
		if($('.filter-default').length) {
			$('.filter-default').css('height', $(window).height());
			$('#column-left').after('<div class="filter-default-open">'+$('.filter-default .heading span').text()+'</div');
			
			$('.filter-default-open').on('click', function() {
				$('#column-left, .filter-default-open').addClass('show');
			});
			
			$('.filter-default #button-filter').on('click', function() {
				$('#column-left, .filter-default-open').removeClass('show');
			});
		}
		
		if($('#column-left #filterpro_box').size()) {
			$('#column-left #filterpro_box').css('height', $(window).height());
			$('#column-left').after('<div id="filterpro_box_open">'+$('#filterpro_box .heading span').text()+'</div');
			if(!$('.app_filter').size()) {
			$('#filterpro_box').append('<div style="margin:15px 0; text-align:center"><button class="app_filter btn btn-primary">Применить</button></div>'); 
			}
			$('#filterpro_box_open').on('click', function() {
				$('#column-left, #filterpro_box_open').addClass('show');
			});
			
			$('.app_filter, .clear_filter').on('click', function() {
				$('#column-left, #filterpro_box_open').removeClass('show');
				scroll_to('h1.heading');
			});
		}
	} else {
		$('.app_filter, #filterpro_box_open, #megafilter_box_open, .filter-default-open').remove();
		$('#column-left #filterpro_box, #column-left #megafilter_box').removeAttr('style');
	}
}

function quantity(data, minimum, amount)
{
	var minimum = parseFloat(minimum);

	var input = $(data).attr('class') == 'form-control' ? $(data) : $(data).parent().prev();
	var quantity = parseFloat(input.val()), new_quantity;
	var elem_class = $(data).attr('class').substr(0, 2) == 'fa' ? $(data).attr('class').substr(0, 10) : '';

	if (!quantity) {
        quantity = 0;
	}

	if(elem_class) {
		if(elem_class == 'fa fa-plus') {
			new_quantity = quantity+1;
		} else {
			new_quantity = quantity > minimum ? quantity-1 : quantity;
		}
	} else {
		new_quantity = quantity > minimum ? quantity : minimum;
	}
	if (new_quantity > amount) {
        new_quantity = amount;
	}
	
	if(new_quantity != quantity) {
		input.val(new_quantity);
		input.change();
	}
    setTimeout(function() {
        if ($(window).width() < 520) {
            $('#cart .dropdown-menu').css('width', $(window).width() - 38);
        }
    }, 50);
}

function uniLivePrice() {
	livePrice = function() {
		$('.quantity input, .option input[type="checkbox"], .option input[type="radio"], .option select').on('change', function() { 
			uniChangePrice(this); 
		});
		$('.quantity input').each(function() {
			if($(this).val() > 1) {
				uniChangePrice(this); 
			}
		});
	}
	
	livePrice();
	
	$(document).ajaxStop(function() {
		livePrice();
	});
}

function uniChangePrice(data) {
	var step = 0, level = 10, this_elem = $(data), elem;
	
	while(step < level) {
		this_elem = this_elem.parent();
		
		if(this_elem.hasClass('product-thumb') || this_elem.hasClass('product-block')){
			elem = this_elem;
			break;
		}
		
		step++;
	}
	
	if(elem) {
		var quantity = elem.find('.quantity input').val() ? elem.find('.quantity input').val() : 1, option_price = 0;
		var elem2 = elem.find('.price'), price = elem2.data('price'), price2 = elem2.data('old-price'), special = elem2.data('special'), special2 = elem2.data('old-special');
		var old_price = price2 ? price2 : price, old_price_elem = elem2.find('.price-old'), old_special = special2 ? special2 : special, new_price_elem = elem2.find('.price-new');
	
		var discounts = elem2.data('discount');
	
		if(discounts && special <= 0) {
			discounts = JSON.parse(discounts.replace(/'/g, '"'));
	
			for (i in discounts) {
				d_quantity = parseFloat(discounts[i]['quantity']);
				d_price = parseFloat(discounts[i]['price']);
		
				if((quantity >= d_quantity) && (d_price < price)) {
					price = d_price;
				}
			}
		}
	
		elem.find('input:checked, option:selected').each(function() {
			if ($(this).data('prefix') == '+') {
				option_price += parseFloat($(this).data('price'));
			}
			if ($(this).data('prefix') == '-') {
				option_price -= parseFloat($(this).data('price'));
			}
			if ($(this).data('prefix') == '*') {
				price *= parseFloat($(this).data('price'));
				special *= parseFloat($(this).data('price'));
			}
			if ($(this).data('prefix') == '/') {
				price /= parseFloat($(this).data('price'));
				special /= parseFloat($(this).data('price'));
			}
		});
	
		new_price = (price + option_price) * quantity;
		new_special = (special + option_price) * quantity;

		if(special <= 0) {
			uniAnimatePrice(old_price, new_price, elem2);
		} else {
			uniAnimatePrice(old_price, new_price, old_price_elem);
			uniAnimatePrice(old_special, new_special, new_price_elem);
		}
	
		elem2.data('old-price', new_price);
		elem2.data('old-special', new_special);
	}
}

function uniAnimatePrice(old_price, new_price, elem){
	if(new_price != old_price) {
		$({val:old_price}).animate({val:new_price}, {
			duration:300,
			step: function(val) {
				elem.text(uniPriceFormat(val));
			}
		});
	}
}

function uniPriceFormat(n) { 
	c = uni_curr_decimals;
	d = uni_curr_decimals_p;
	t = uni_curr_thousand_p;
	s_left = uni_curr_symbol_l;
	s_right = uni_curr_symbol_r;
	i = parseInt((n = Math.abs(n).toFixed(c)), 10) + '';
	j = ((j = i.length) > 3) ? j % 3 : 0; 
		
	return s_left + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '') + s_right; 
}

function add_subscribe(data) {
	
	var form = $(data).parent().parent().parent(), data = form.find('input').serialize(), button = form.find('button');
	
	$('.text-danger, .tooltip').remove();
		
	$.ajax({
		url:'index.php?route=extension/module/uni_subscribe/add',
		type:'post',
		data:data,
		dataType:'json',
		beforeSend: function() {
			button.button('loading');
		},
		complete: function() {
			button.button('reset');
		},
		success: function(json) {
			if (json['error']) {
				form_error('.subscribe', 'email', json['error']);
			}
			
			if (json['alert']) {
				$('.subscribe .subscribe-input > div').addClass('show-pass');
				$('.subscribe .subscribe-input input').attr('disabled', false);
			}

			if (json['success']) {
				$('#modal-subscribe-success').remove();
				
				html  = '<div id="modal-subscribe-success" class="modal fade">';
				html += '<div class="modal-dialog modal-sm '+uni_popup_effect_in+'">';
				html += '<div class="modal-content">';
				html += '<div class="modal-header">';
				html += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
				html += '<h4 class="modal-title">'+json['success_title']+'</h4>';
				html += '</div>';
				html += '<div class="modal-body">'+json['success']+'</div>';
				html += '</div>';
				html += '</div>';
				html += '</div>';
	
				$('html body').append(html);
				$('#modal-subscribe-success').modal('show');
			}
		}
	});
}

function banner_link(url) {
	$('#modal-banner').remove();
	
	$.ajax({
		url: url,
		type: 'get',
		dataType: 'html',
		success: function(data) {
			var data = $(data);
			
			title = data.find('h1.heading').text();
			data.find('h1.heading').remove();
			text = data.find('#content').html();
			
			html  = '<div id="modal-banner" class="modal fade">';
			html += '<div class="modal-dialog '+uni_popup_effect_in+'">';
			html += '<div class="modal-content">';
			html += '<div class="modal-header">';
			html += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			html += '<h4 class="modal-title">'+title+'</h4>';		
			html += '</div>';
			html += '<div class="modal-body">'+text+'</div>';
			html += '</div>';
			html += '</div>';
			html += '</div>';
	
			$('html body').append(html);
			$('#modal-banner').modal('show');
		}
	});
}

function quick_order(id) {
	$.ajax({
		url: 'index.php?route=extension/module/uni_quick_order',
		type:'post',
		data:{'id':id, 'flag':true},
		dataType: 'html',
		success: function(data) {
			$('html body').append(data);
			$('#modal-quick-order').modal('show');
		}
	});
}

function add_quick_order() {
	
	var form = '#modal-quick-order';
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_quick_order/add',
		type: 'post',
		data: $(form+' input, '+form+' textarea, '+form+' select').serialize(),
		dataType: 'json',
		beforeSend: function() {
			$(form+' .add_to_cart').button('loading');
		},
		complete: function() {
			$(form+' .add_to_cart').button('reset');
		},
		success: function(json) {
			$('.text-danger').remove();
				
			$('.form-group').removeClass('has-error');

			if (json['error']) {
				if (json['error']['option']) {
					for (i in json['error']['option']) {							
						
						var element = $('#quick_order #input-option' + i.replace('_', '-'));
						
						if (element.parent().hasClass('input-group')) {
							element.parent().after('<div class="text-danger">'+json['error']['option'][i]+'</div>');
						} else {
							element.after('<div class="text-danger">'+json['error']['option'][i]+'</div>');
						}
					}
				}
			}
			
			if (json['error_c']) {
				for (i in json['error_c']) {
					form_error(form, i, json['error_c'][i]);
				}
			}
		
			if (json['success']) {
				
				var opt = '';
			
				$(form).find('.option input:checked').each(function() {
					opt += $(this).next().text()+' '; 
				});
			
				$(form).find('.option option:selected').each(function() {
					opt += $(this).val()+' '; 
				});
			
				var dataLayer = [];
			
				dataLayer.push({
					'ecommerce':{
						'currencyCode':$(form).find('input[name="currency"]').val(),
						'purchase':{
							'actionField':{
								'id':json['success']['order_id'],
								'goal_id':$(form).find('input[name="goal_id"]').val()
							},
							'products':[{name:$(form).find('.modal-title').text(), variant:opt, price:$(form).find('.price').attr('data-price'), quantity:$(form).find('.quantity input[name="quantity"]').val()}]
						}
					}
				});
				
				$('#quick_order').html('<div class="row"><div class="col-xs-12">'+json['success']['text']+'</div></div>')
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
	});
}

function callback(reason, id) {
	if(typeof(reason) == 'undefined') reason = '';
	if(typeof(id) == 'undefined') id = '';
	
	$.ajax({
		url:'index.php?route=extension/module/uni_request',
		type:'post',
		data:{'reason':reason, 'id':id, 'flag':true},
		dataType:'html',
		success: function(data) {			
			$('html body').append(data);
			$('#modal-request-form').modal('show');
		}
	});
}

function send_callback() {
	
	var form = '#modal-request-form';
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_request/mail',
		type: 'post',
		data: $(form+' input, '+form+' textarea').serialize(),
		dataType: 'json',
		beforeSend: function() {
			$('.callback_button').button('loading');
		},
		complete: function() {
			$('.callback_button').button('reset');
		},
		success: function(json) {	
			if (json['success']) {
				$('.callback').html($('<div class="callback_success">'+json['success']+'</div>').fadeIn());
			}
			
			$(form+' .text-danger').remove();

			if (json['error']) {
				for (i in json['error']) {
					form_error(form, i, json['error'][i]);
				}
			}
		}
	});
}

function login() {
	$.ajax({
		url:'index.php?route=extension/module/uni_login_register',
		type:'post',
		data:{'type':'login', 'flag':true},
		dataType:'html',
		success:function(data) {
			$('html body').append(data);
			$('#modal-login-form').modal('show');
		}
	});
}

function send_login() {
	
	var form = '#modal-login-form';
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_login_register/login',
		type: 'post',
		data: $(form+' input, '+form+' textarea').serialize(),
		dataType: 'json',
		beforeSend: function() {
			$('.login_button').button('loading');
		},
		complete: function() {
			$('.login_button').button('reset');
		},
		success: function(json) {
			if (json['redirect']) {
				if(window.location.pathname == '/logout/') {
					location = json['redirect'];
				} else {
					window.location.reload();
				}
			}
			
			$(form+' .text-danger').remove();
			
			if (json['error']) {
				$('.login_button').before($('<div class="text-danger" style="margin:0 0 15px;">'+json['error']+'</div>'));
			}
		}
	});
}

function register() {
	$.ajax({
		url: 'index.php?route=extension/module/uni_login_register',
		type:'post',
		data:{'type':'register', 'flag':true},
		dataType: 'html',
		success: function(data) {
			$('html body').append(data);
			$('#modal-register-form').modal('show');
		}
	});
}

function send_register() {
	
	var form = '#modal-register-form';
	
	$.ajax({
		url: 'index.php?route=extension/module/uni_login_register/register',
		type: 'post',
		data: $(form+' input, '+form+' textarea').serialize(),
		dataType: 'json',
		beforeSend: function() {
			$('.register_button').button('loading');
		},
		complete: function() {
			$('.register_button').button('reset');
		},
		success: function(json) {				
			if (json['redirect']) {
				location = json['redirect'];
			}
			if (json['appruv']) {
				$('.popup_register').html($('<div class="register_success">'+json['appruv']+'</div>').fadeIn());
			}
			
			$(form+' .text-danger').remove();
			
			if (json['error']) {
				for (i in json['error']) {
					form_error(form, i, json['error'][i]);
				}
			}
		}
	});
}

function form_error(form, input, text) {
	
	var element = $(form+' input[name=\''+input+'\'], '+form+' textarea[name=\''+input+'\'], '+form+' select[name=\''+input+'\']').addClass('input-warning');
	
	if(input == 'confirm' || element.parent().hasClass('input-group') || element.parent().hasClass('input')) {
		element.parent().after('<div class="text-danger">'+text+'</div>');
		$(form+' .input-warning').click(function() {
			$(this).removeClass('input-warning');
			$(this).parent().next('.text-danger').remove();
		});
	} else {
		if (input == 'error_warning') {
            uniCartUpdateOrder();
		}
		element.after('<div class="text-danger">'+text+'</div>');
		$(form+' .input-warning').click(function() {
			$(this).removeClass('input-warning');
			$(this).next('.text-danger').remove();
		});
	}
}

function uniCartUpdateOrder()
{
    $.ajax({
        url: 'index.php?route=checkout/uni_checkout/cart&render=1',
        dataType: 'html',
        success: function(html) {
            $('#unicart').html(html);
            $('.total_checkout h3 span span').html($('.total_table td:last').html());
            if($('#unicart .alert').length) {
                scroll_to('#unicart .alert');
            } else {
                scroll_to('.text-danger');
            }
        }
    });
}

function scroll_to(hash) {		
	var destination = $(hash).offset().top-100;
	$('html, body').animate({scrollTop: destination}, 400);
}

function scroll_text(target_div, target_text) {	
	$(target_div).mouseover(function () {
	    $(this).stop();
	    var boxWidth = $(this).width(), textWidth = $($(target_text), $(this)).width();

	    if (textWidth > boxWidth) {
	        $(target_text).animate({left: -((textWidth+20) - boxWidth)}, 800);
	    }
	}).mouseout(function () {
	    $(target_text).stop().animate({left: 0}, 800);
	});
}

function uniLiveSearch(data) {
	var element = $(data), $parent = element.parent();
	var caret = $(".cat_id .fa-chevron-down");
		
	$('html body *').on('click', function() {
		$('.live-search').hide();
        caret.removeClass('caret-trans');
	});
	
	element.attr('autocomplete', 'off');

	element.on('input click', function() {
		if(!$parent.next().hasClass('live-search')) {
			$parent.after('<div id="live-search" class="live-search"><ul></ul></div>');
		}	
			
		if (element.val().replace(/\s+/g, '').length >= 3) {
			$parent.next().show();
			$.ajax({
				url:'index.php?route=extension/module/uni_live_search&filter_name='+element.val()+'&category_id='+element.parent().find('input[name=\'filter_category_id\']').val(),
				dataType:'html',
				beforeSend: function() {
					$('.live-search ul').html('<li class="loading"></li>');
				},
				success: function(html) {
					$('.live-search ul').html(html);
					$parent.next().show();
                    caret.addClass('caret-trans');
				}
			});
		}
	});
}

function uniNotify() {
	notify = function() {
		$('.product-thumb .add_to_cart.disabled, #product .add_to_cart.disabled, #menu_wrap .add_to_cart.disabled').each(function() {
			var p_id = $(this).attr('class').replace(/\s+/g, '').match(/(\d+)/g);
			$(this).unbind('click').attr('onclick', 'callback("'+uni_notify_text+'", '+p_id+');').removeAttr('disabled').css('cursor', 'pointer');
		});
	}
	
	notify();
	
	$(document).ajaxStop(function() {
		notify();
	});
}

function uniPopupOptionImg() {
	PopupOptionImg = function() {
		if($('.option-image').length) {
			$('.option-image img').each(function() {
			
				var elem = $(this), block = $('<div class="option-image-popup '+elem.attr('data-type')+'"><img src="'+elem.attr('data-thumb')+'" class="img-responsive" /><span>'+elem.attr('alt')+'</span></div>');
		
				elem.on('mouseenter', function() {
					$('html body').append(block);
				
					var offset_top = elem.offset().top-block.outerHeight()-5, offset_left = elem.offset().left+(elem.outerWidth()/2)-(block.outerWidth()/2);
				
					block.attr('style', 'top:'+offset_top+'px;left:'+offset_left+'px');
				
					setTimeout(function() { 
						block.addClass('show');
					}, 50);
				}).on('mouseleave', function() {
					block.removeClass('show');
				
					setTimeout(function() { 
						block.remove();
					}, 150);
				});
			});		
		}
	}
	
	PopupOptionImg();
	
	$(document).ajaxStop(function() {
		PopupOptionImg();
	});
}

function uniChangeProductImg() {
	changeImg = function() {
		$('.product-thumb .option .option-image img').on('click', function() {
			$(this).parent().parent().parent().parent().prev().find('a img:first').attr('src', $(this).attr('data-thumb'));
			$(this).parent().parent().parent().parent().parent().prev().find('a img:first').attr('src', $(this).attr('data-thumb'));
		});
	}
	
	changeImg();
	
	$(document).ajaxStop(function() {
		changeImg();
	});
}

function uniReplaceBtn(){
	changeBtn = function() {
		for(i in uni_incart_products) {
			$('.'+uni_incart_products[i]).html('<i class="'+uni_cart_btn_icon_incart+'"></i><span class="hidden-sm">'+uni_cart_btn_text_incart+'</span>').addClass('in_cart');
		}
	}
	
	changeBtn();
	
	$(document).ajaxStop(function() {
		changeBtn();
	});
}
	
function uniReturnBtn(product_id) {
	var index = uni_incart_products.indexOf(product_id);
	
	if(index != -1) uni_incart_products.splice(index, 1);
	
	$('.'+product_id).html('<i class="'+uni_cart_btn_icon+'"></i><span class="hidden-sm">'+uni_cart_btn_text+'</span>').removeClass('in_cart');
}

(function($){
	var Modules = {
		init:function(options, el) {
            var base = this;
			
			base.$elem = $(el);
			base.$elem2 = $(el).children();
			
			if($('#column-left').find(base.$elem).length || $('#column-right').find(base.$elem).length) {
				options.type = 'carousel';
			}
			
			base.options = $.extend({}, $.fn.uniModules.options, options);
			
			base.load();
        },
		load:function() {
			var base = this;
			
			if(base.$elem.parent().parent().parent().hasClass('tab-content')) {
				var width = base.$elem.parent().parent().parent().width();
			} else {
				var width = base.$elem.width();
			}
		
			width = Math.floor(parseFloat(width))+20
			
			if (base.options.type == 'grid') {					
				base.$elem2.parent().addClass('grid');
				
				var match = -1;
			
				$.each(base.options.items, function(breakpoint) {
					if (breakpoint <= width && breakpoint > match) {
						match = Number(breakpoint);
					}
				});
				
				base.$elem2.children().css('width', Math.floor(width / base.options.items[match]['items']));
			} else {			
				base.$elem2.addClass('owl-carousel').owlCarousel({
					responsive:base.options.items,
					responsiveBaseElement:base.$elem,
					nav:true,
					mouseDrag:false,
					loop:base.options.loop,
					navContainer:base.$elem.parent(),
					navText:['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>']
				});
			}

			base.update();
			base.reload();
		},
		update:function() {
			var base = this, div_arr = base.options.autoheight;
			
			for (i in div_arr) {
				var maxheight = 0, $elem = base.$elem.find('.'+div_arr[i]);
				
				$elem.height('auto');
				
				$.each($elem, function() {
					if($(this).height() > maxheight) {
						maxheight = $(this).height();
					}
				});
				
				$elem.height(maxheight);
			}
			
			base.response();
			base.show();
		},
		show:function() {
			var base = this;
			
			base.$elem.parent().addClass('load-complete');
		},
		response:function () {
            var base = this, lastWindowWidth = $(window).width();

            base.resizer = function () {
                if ($(window).width() !== lastWindowWidth) {
                    base.load();
                }
            };
			
			$(window).resize(function() {
				setTimeout(function() { 
					base.resizer();
				}, 250);
			});
        },
		reload:function() {
			var base = this, div = base.$elem.parent().parent(), modal = div.hasClass('modal-body') ? true : false, tab = div.hasClass('tab-pane') ? div.attr('id') : false;
			
			if(modal) {
				setTimeout(function() { 
					base.update();
				}, 750);
			}
			
			if(tab) {
				$('.nav-tabs li a').on('shown.bs.tab', function (e) {
					if($(this).attr('href') == '#'+tab) {
						setTimeout(function() { 
							base.update();
						}, 350);
					}
				});
			}
		}
	};
	
	$.fn.uniModules = function(options) {		
		return this.each(function () {
            if ($(this).data("uni-modules-init") === true) {
                return false;
            }
			
            $(this).data("uni-modules-init", true);
			
            var module = Object.create(Modules);
            module.init(options, this);
        });
	}
	
	$.fn.uniModules.options = {
		type 	   :'carousel',
		items	   :{0:{items:1},520:{items:2},700:{items:3},940:{items:4},1050:{items:4},1400:{items:5}},
		autoheight :[],
		loop	   :false
	}
})(jQuery);

(function($){
	var Timer = {
		init:function(options, el) {
            var base = this;
			
			base.options = $.extend({}, $.fn.uniTimer.options, options);
			base.days = 24*60*60, base.hours = 60*60, base.minutes = 60;
			
			var year = parseFloat(base.options.date.substr(0, 4)), month = parseFloat(base.options.date.substr(5, 2))-1, day = parseFloat(base.options.date.substr(8, 2));
			
			base.$date = (new Date(year, month, day)).getTime();
			base.$elem = $(el);
			
			if(base.$date > (new Date()).getTime())	{
				base.load();
			}
        },
		load:function() {
			var base = this, i = 4;
			
			html = '<div class="uni-timer">';
			
			for(i in base.options.texts){
				
				if(i > 0) {
					html += '<div class="colon">:</div>';
				}
				
				html += '<div class="digit-group-'+i+'">';
				html += '<span class="digits"><span></span></span><span class="digits"><span></span></span>';
				
				if(!base.options.hideText) {
					html += '<div class="text">'+base.options.texts[i]+'</div>';
				}
				
				html += '</div>';
			}
			
			html += '</div>';
			
			base.$elem.append(html);
			base.$positions = base.$elem.find('.digits');
			base.count();
		},
		count:function() {
			var base = this, left, d, h, m, s;
			
			left = Math.floor((base.$date - (new Date()).getTime())/1000);
			
			left = left > 0 ? left : 0;
			
			d = Math.floor(left / base.days);
			left -= d*base.days;
			h = Math.floor(left / base.hours);
			left -= h*base.hours;
			m = Math.floor(left / base.minutes);
			left -= m*base.minutes;
			s = left;
			
			base.count2(0, 1, d);
			base.count2(2, 3, h);
			base.count2(4, 5, m);
			base.count2(6, 7, s);
			
			if (d == 0) {
				base.hideGroup(0);
			}
			
			if (h == 0) {
				base.hideGroup(1);
			}
			
			setTimeout(function() { 
				base.count();
			}, 1000);
		}, 
		count2:function(minor, major, value) {
			var base = this;
			
			base.switchDigit(base.$positions.eq(minor), Math.floor(value/10)%10);
			base.switchDigit(base.$positions.eq(major), value%10);
		},
		switchDigit:function(position, number) {
			var base = this, digit = position.find('span');
			
			if(position.data('digit') == number){
				return false;
			}
	
			position.data('digit', number);
	
			digit
				.before($('<span>'+number+'</span>'))
				.addClass('out')
				.animate('fast', function(){
					digit.remove();
				});
		},
		hideGroup:function(num) {
			var base = this;
			
			if(base.options.hideIsNull) {
				base.$elem.find('.digit-group-'+num+', .digit-group-'+num+' + .colon').hide();
			}
		}
	}
	
	$.fn.uniTimer = function(options) {		
		return this.each(function () {
            if ($(this).data("uni-timer-init") === true) {
                return false;
            }
			
            $(this).data("uni-timer-init", true);
			
            var timer = Object.create(Timer);
            timer.init(options, this);
        });
	}
	
	$.fn.uniTimer.options = {
		date		:0,
		texts		:['Дней','Часов','Минут','Секунд'],
		hideText	:false,
		hideIsNull	:false
	}
})(jQuery);

// Cart add remove functions
var cart = {
	'add': function(product_id, elem) {
		
		var elem = $(elem), quantity = elem.prev().find('input').val(), options = elem.parent().prev().find('.option');
		
		if (options.children().length) {
			var options_data = options.find('input[type=\'radio\']:checked, input[type=\'checkbox\']:checked, select'), data = options_data.serialize() +'&product_id='+product_id + '&quantity='+(typeof(quantity) != 'undefined' ? quantity : 1);
		} else {
			var data = 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1);
		}
		
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: data,
			dataType: 'json',
			success: function(json) {
				$('.text-danger').remove();

				if (json['redirect'] && !options.children().length) {
					location = json['redirect'];
				}
				
				$('.form-group').removeClass('has-error');

				if (json['error']) {
					if (json['error']['option']) {
						for (i in json['error']['option']) {
							var elem = $('.option #input-option' + i.replace('_', '-')), elem2 = (elem.parent().hasClass('input-group')) ? elem.parent() : elem;
							
							elem2.after('<div class="text-danger">'+json['error']['option'][i]+'</div>');
							$('.option .text-danger').delay(5000).fadeOut();
						}
					}
				}

				if (json['success']) {
					$('.cart'+product_id).empty().html(json['success']);
					$('#cart, .cart_wrap #cart').load('index.php?route=common/cart/info #cart > *');
				}
                setTimeout(function() {
                    if ($(window).width() < 520) {
                        $('#cart .dropdown-menu').css('width', $(window).width() - 38);
                    }
                }, 50);
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	},
	'update': function(key, quantity, product_id, amount, minimum) {
		var cart = $('#cart, .cart_wrap #cart');

		if (quantity > amount) {
            quantity = amount;
		}
        if (quantity < minimum) {
            quantity = minimum;
        }

		cart.attr('class', 'open2');
		
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'quantity['+key+']='+quantity,
			dataType: 'html',
			success: function(data) {
				cart.load('index.php?route=common/cart/info #cart > *');
				cart.attr('class', 'open');
				
				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					$('#content').load('index.php?route=checkout/cart #content > *');
				}
				
				if(typeof(product_id) != 'undefined' && quantity <= 0) {
					uniReturnBtn(product_id);
				}
                setTimeout(function() {
                    if ($(window).width() < 520) {
                        $('#cart .dropdown-menu').css('width', $(window).width() - 38);
                    }
                }, 50);
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	},
	'remove': function(key, product_id) {
		var cart = $('#cart, .cart_wrap #cart');
		
		cart.attr('class', 'open2');
		
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key='+key,
			dataType: 'json',
			success: function(json) {
				cart.load('index.php?route=common/cart/info #cart > *');
				cart.attr('class', 'open');

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					$('#content').load('index.php?route=checkout/cart #content > *');
				}
                deleteModal();
				uniReturnBtn(product_id);
                setTimeout(function() {
                    if ($(window).width() < 520) {
                        $('#cart .dropdown-menu').css('width', $(window).width() - 38);
                    }
                }, 50);
			},
	        error: function(xhr, ajaxOptions, thrownError) {
                deleteModal();
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				$('#cart #cart-total').html(json['total']);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
				
				if (getURLVar('route') == 'checkout/unicheckout') {
					update_checkout();
				}
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success '+uni_popup_effect_in+'"><i class="fa fa-check-circle"></i> '+json['success']+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}

				$('#wishlist-total span').html(json['total']);
				$('#wishlist-total').attr('title', json['total']);
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	},
	'remove': function() {

	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert').remove();

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success '+uni_popup_effect_in+'"><i class="fa fa-check-circle"></i> '+json['success']+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('#compare-total').html('<i class="fas fa-align-right"></i>'+json['total']);
				}
                if (json['danger']) {
                    $('#content').parent().before('<div class="alert alert-danger '+uni_popup_effect_in+'"><i class="fa fa-check-circle"></i> '+json['danger']+' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
			},
	        error: function(xhr, ajaxOptions, thrownError) {
	            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	        }
		});
	},
	'remove': function() {

	}
}

$(document).delegate('.agree', 'click', function(e) {
	e.preventDefault();

	$('#modal-agree').remove();

	var element = this;

	$.ajax({
		url: $(element).attr('href'),
		type: 'get',
		dataType: 'html',
		success: function(data) {
			
			var text = $(data).find('.article_description').length ? $(data).find('.article_description').html() : data;
			
			html  = '<div id="modal-agree" class="modal fade">';
			html += '<div class="modal-dialog '+uni_popup_effect_in+'">';
			html += '<div class="modal-content">';
			html += '<div class="modal-header">';
			html += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			html += '<h4 class="modal-title">'+$(element).text()+'</h4>';
			html += '</div>';
			html += '<div class="modal-body">'+text+'</div>';
			html += '</div';
			html += '</div>';
			html += '</div>';

			$('body').append(html);

			$('#modal-agree').modal('show');
		}
	});
});

function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

$(function () {
    let location = window.location.href;
    $('#menu li').each(function () {
        let link = $(this).find('a').attr('href');
        if (location.indexOf(link) !== -1)
        {
            $(this).addClass('current');
        }
    });

    if (location.indexOf('information/news') !== -1)
    {
        let news = $('#menu li');
        if (news[2]) {
            news[2].classList.add('current');
            news[2].replaceWith(news[2])
		}
    }

    $('#column-profile ul li a').each(function () {
        let link = $(this).attr('href');
        if (location.indexOf(link) !== -1)
        {
            $(this).addClass('current-profile');
        }
    });
});

$(function (){
    let HeaderTop = $('#menu').offset().top;
    $(window).scroll(function(){
        if (window.matchMedia('(max-width: 768px)').matches) {
            if( $(window).scrollTop() > HeaderTop ) {
                $('#menu').css({position: 'fixed', top: '0px'});
            } else {
                $('#menu').css({position: 'relative'});
            }
        }
        if ($('.navbar-collapse[aria-expanded="true"]').length > 0){
            if ($(window).scroll){
                $('.navbar-ex1-collapse').removeClass('in')
            }
		}
    });
});

$(function (){
    $(document).on('mouseenter', ".description, .product-name",  function () {
        var $this = $(this);
        if (this.offsetWidth < this.scrollWidth && !$this.attr('title')) {
            $this.tooltip({
                title: $this.text(),
                placement: "top"
            });
            $this.tooltip('show');
        }
    });
});

$(function() {
    const Accordion = function(el, multiple) {
        this.el = el || {};
        this.multiple = multiple || false;
        let links = this.el.find('.link');
        links.on('click', {el: this.el, multiple: this.multiple}, this.dropdown)
    }
    Accordion.prototype.dropdown = function(e) {
        let $el = e.data.el;
        $this = $(this);
        $next = $this.next();
        $next.slideToggle();
        $this.parent().toggleClass('open');
        if (!e.data.multiple) {
            $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
        };
    }
    var accordion = new Accordion($('#accordion'), false);
});


(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			$(this).on('focus', function() {
				this.request();
			});

			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}

					// Get all the ones with a categories
					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.dropdown-menu').html(html);
			}

			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);