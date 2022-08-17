$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip({
        trigger: 'click',
        placement: 'top',
        html: true
    });

    $('.js-collapse-table').click(function(){
        let id = $(this).attr('data-id'),
            tableId = '#user-table-' + id,
            userTable = $(tableId),
            tr = $(this).closest('tr');
        if (tr.next('tr').attr('id') != tableId) {
            userTable.clone().insertAfter(tr);
            userTable.remove();
        }

        $(tableId).toggleClass('d-none');
        if ($(this).hasClass('ui-btn-icon-add')) {
            $(this).addClass('ui-btn-icon-minus').removeClass('ui-btn-icon-add');
            $(tableId).find('[data-toggle="tooltip"]').tooltip({
                trigger: 'click',
                placement: 'top',
                html: true
            });
        } else {
            $(this).addClass('ui-btn-icon-add').removeClass('ui-btn-icon-minus');
        }
        initSort();
    });

    var initSort = function() {
        $('.sorter')
            .wrapInner('<span title="Сортировка"/>')
            .each(function(){
                var inverse = false;
                $(this).click(function(){
                    var thIndex = $(this).data('index') || $(this).index(),
                        thSorting = $(this).data('class'),
                        reinit = $(this).data('reinit') || false;
                    $(thSorting).find('td').filter(function(){
                        return $(this).index() === thIndex;
                    }).sortElements(function(a, b) {
                        let aText = $(a).data('sorttext') || $.text([a]),
                            bText = $(b).data('sorttext') || $.text([b]);
                        if (aText === bText) {
                            return 0;
                        }
                        return aText > bText ?
                            inverse ? -1 : 1
                            : inverse ? 1 : -1;
                    }, function() {
                        return this.parentNode;
                    });
                    inverse = !inverse;
                    if (reinit) {
                        $('.user-detail-table').addClass('d-none');
                        $('.ui-btn-icon-minus').removeClass('ui-btn-icon-minus').addClass('ui-btn-icon-add');
                    }
                });
            });
    }

    initSort();

    $('.js-task-list').click(function(){
        let ids = $(this).data('id');
        BX.SidePanel.Instance.open(
            '/workgroups/group/612/reporting/?task-list=Y&ids=' + ids,
            {
                cacheable: false,
                allowChangeHistory: false,
                loader: '/local/templates/.default/components/bitrix/socialnetwork.user_menu/.default/images/slider/taskslist.min.svg'
            }
        );

        return false;
    });
});

/**
 * jQuery.fn.sortElements
 * --------------
 * @author James Padolsey (http://james.padolsey.com)
 * @version 0.11
 * @updated 18-MAR-2010
 * --------------
 * @param Function comparator:
 *   Exactly the same behaviour as [1,2,3].sort(comparator)
 *   
 * @param Function getSortable
 *   A function that should return the element that is
 *   to be sorted. The comparator will run on the
 *   current collection, but you may want the actual
 *   resulting sort to occur on a parent or another
 *   associated element.
 *   
 *   E.g. $('td').sortElements(comparator, function(){
 *      return this.parentNode; 
 *   })
 *   
 *   The <td>'s parent (<tr>) will be sorted instead
 *   of the <td> itself.
 */
jQuery.fn.sortElements = (function(){
    
    var sort = [].sort;
    
    return function(comparator, getSortable) {
        
        getSortable = getSortable || function(){return this;};
        
        var placements = this.map(function(){
            
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
                
                // Since the element itself will change position, we have
                // to have some way of storing it's original position in
                // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );
            
            return function() {
                
                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }
                
                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);
                
            };
            
        });
       
        return sort.call(this, comparator).each(function(i){
            placements[i].call(getSortable.call(this));
        });
        
    };
    
})();