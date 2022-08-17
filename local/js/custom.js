setInterval(function(){
    if(typeof BX != "undefined" && typeof BX.Bizproc != "undefined" && typeof BX.Bizproc.showTaskPopup != "undefined" &&  BX.Bizproc.showTaskPopup.toString().indexOf('BX_Bizproc_showTaskPopup_check') == -1){
        BX.Bizproc.showTaskPopup = function(taskId, callback, userId, scope, useIframe){
            var BX_Bizproc_showTaskPopup_check = false;
            var return_url = (window.BX||top.BX).message.SITE_DIR+"company/personal/bizproc/";
            location.href = (window.BX||top.BX).message.SITE_DIR+"company/personal/bizproc/"+taskId+"/?back_url="+encodeURI(return_url);
        };
    }
}, 200);

if (typeof jQuery !== 'undefined') {
    !function(c){jQuery.fn.doubleScroll=function(l){var r={contentElement:void 0,scrollCss:{"overflow-x":"auto","overflow-y":"hidden",height:"20px"},contentCss:{"overflow-x":"auto","overflow-y":"hidden"},onlyIfScroll:!0,resetOnWindowResize:!1,timeToWaitForResize:30};c.extend(!0,r,l),c.extend(r,{topScrollBarMarkup:'<div class="doubleScroll-scroll-wrapper"><div class="doubleScroll-scroll"></div></div>',topScrollBarWrapperSelector:".doubleScroll-scroll-wrapper",topScrollBarInnerSelector:".doubleScroll-scroll"});function t(l,o){var e,r,t;o.onlyIfScroll&&l.get(0).scrollWidth<=l.width()?l.prev(o.topScrollBarWrapperSelector).remove():(0==(e=l.prev(o.topScrollBarWrapperSelector)).length&&(e=c(o.topScrollBarMarkup),l.before(e),e.css(o.scrollCss),c(o.topScrollBarInnerSelector).css("height","20px"),l.css(o.contentCss),r=!1,e.bind("scroll.doubleScroll",function(){r?r=!1:(r=!0,l.scrollLeft(e.scrollLeft()))}),l.bind("scroll.doubleScroll",function(){r?r=!1:(r=!0,e.scrollLeft(l.scrollLeft()))})),t=void 0!==o.contentElement&&0!==l.find(o.contentElement).length?l.find(o.contentElement):l.find(">:first-child"),c(o.topScrollBarInnerSelector,e).width(t.outerWidth()),e.width(l.width()),e.scrollLeft(l.scrollLeft()))}return this.each(function(){var l,o,e=c(this);t(e,r),r.resetOnWindowResize&&(o=function(l){t(e,r)},c(window).bind("resize.doubleScroll",function(){clearTimeout(l),l=setTimeout(o,r.timeToWaitForResize)}))})}}(jQuery);

    $(document).ready(function() {
        if ($.isFunction($.fn.doubleScroll)) {
            $('.bx-bizproc-interface-list').doubleScroll();
        }

        /**
         * Возможность скрыть системные комментарии в задачах
         */
        var hideTasksSystemComments = false;
        if ($('.task-comments-block .feed-com-header').length > 0) {
            $('<a/>', {
                class: 'feed-com-all hide-system-comments',
                html: 'Скрыть системные комментарии'
            }).css({
                'padding-left': '20px',
                'cursor': 'pointer',
            }).on('click', function(){
                $('.mpl-comment-aux').closest('.feed-com-block-cover').hide();
                $('.hide-system-comments').hide();
                $('.show-system-comments').show();
                hideTasksSystemComments = true;
                BX.addCustomEvent('OnUCListWasShown', BX.delegate(function(obj) {
                    if (hideTasksSystemComments) {
                        $('.mpl-comment-aux').closest('.feed-com-block-cover').hide();
                    }
                }, this));
                return false;
            }).appendTo($('.feed-com-header'));

            $('<a/>', {
                class: 'feed-com-all show-system-comments',
                html: 'Вернуть системные комментарии'
            }).css({
                'padding-left': '20px',
                'cursor': 'pointer',
                'display': 'none'
            }).on('click', function(){
                $('.mpl-comment-aux').closest('.feed-com-block-cover').show();
                $('.hide-system-comments').show();
                $('.show-system-comments').hide();
                hideTasksSystemComments = false;
                return false;
            }).appendTo($('.feed-com-header'));
        }

        $('.js-id-wg-optbar-task-control').closest('.task-options-field').hide();
    });

    window.onerror = function (msg, filename, line, col, error) {
        $.post(
            '/local/api/logger/error_js.php',
            {
                message: msg,
                params: {
                    file: filename,
                    line: line,
                    column: col,
                    location: window.location.href,
                    search: window.location.search,
                    userAgent: navigator.userAgent,
                    error: error
                }
            }
        );
    };

    var lastBrowserStats = BX.getCookie('lastBrowserStats'),
        nowBrowserStats = Math.floor(Date.now() / 1000);

    if (isNaN(lastBrowserStats) || lastBrowserStats < nowBrowserStats) {
        $.post(
            '/local/api/logger/browser.php',
            {
                userAgent: navigator.userAgent,
            }
        );
        BX.setCookie('lastBrowserStats', nowBrowserStats+86400, {expires:86400,path:'/'});
    }
}