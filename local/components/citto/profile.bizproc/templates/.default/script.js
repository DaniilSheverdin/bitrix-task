(function($){
    jQuery.fn.lightTabs = function(options){
        var createTabs = function(){
            tabs = this;
            i = 0;

            showPage = function(i){
                $(tabs).children("div").children("div").hide();
                $(tabs).children("div").children("div").eq(i).show();
                $(tabs).children("ul").children("li").removeClass("active");
                $(tabs).children("ul").children("li").eq(i).addClass("active");
            }

            showPage(0);

            $(tabs).children("ul").children("li").each(function(index, element){
                $(element).attr("data-page", i);
                i++;
            });

            $(tabs).children("ul").children("li").click(function(){
				showPage(parseInt($(this).attr("data-page")));
				location.hash = this.querySelector('a').getAttribute('href');
                return false;
            });
        };
        return this.each(createTabs);
    };
})(jQuery);

$(document).ready(function(){
	$("#tabs").lightTabs();
	if(location.hash.match(/#tabs-/)){
		$('#tabs a[href="'+location.hash+'"]').parent().click();
	}

	$(document).on('click','.bp-interface-toolbar .bp-context-button', function(e){
		if(~this.getAttribute('href').indexOf('type=my_processes')){
			e.preventDefault();
			e.stopPropagation();
			$('.processes').hide();
			$('.my_processes').show();
			$(this).addClass('active').parent().siblings().find('a').removeClass('active');
			return false;
		}else if(~this.getAttribute('href').indexOf('type=processes')){
			e.preventDefault();
			e.stopPropagation();
			$('.processes').show();
			$('.my_processes').hide();
			$(this).addClass('active').parent().siblings().find('a').removeClass('active');
			return false;
		}
	});
	$(document).on('click','#bizproc_task_list .bp-button', function(e){
		e.preventDefault();
		e.stopPropagation();
		var _this = $(this);
		var _this__href = _this.attr('href');
		if(~_this__href.indexOf('/company/personal/user/')){
			var matches = _this__href.match(/\&ID=([0-9]+)/);
			if(matches){
				location.href = "/company/personal/bizproc/"+matches[1]+"/?back_url="+location.pathname;
			}
		}
		return false;
	});

	if ($('.my_processes').hasClass('trigger-click')) {
		$('.bp-context-button').each(function(){
			if (~this.getAttribute('href').indexOf('type=my_processes')) {
				$(this).trigger('click');
			}
		});
	}
});
