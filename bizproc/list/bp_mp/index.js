<<<<<<< HEAD
function bp_mp_init(){
    setInterval(function(){
        var PROPERTY_1846 = $('#PROPERTY_1846');
        if(PROPERTY_1846.is(':visible')){
            PROPERTY_1846.hide();
            var select = $('<select>').addClass('bx-select');
            PROPERTY_1846_items.forEach(function(item){
                select.append(
                    $('<option>').val(item.ID).text(item.NAME).prop('selected', item.NAME == PROPERTY_1846.val())
                );
            });
            select
                .on('change',function(){
                    PROPERTY_1846.val(this.options[this.selectedIndex].textContent)
                })
                .insertBefore(PROPERTY_1846);
        }
    }, 100);
};
function bp_mp_start(){
    var items = $('.bx-odd .bx-checkbox-col input, .bx-even .bx-checkbox-col input').map(function(){
        return this.value;
    }).toArray();
    if(!items.length){
        alert('Процессы не выбраны');    
        return;
    }
}
=======
function bp_mp_init(){
    setInterval(function(){
        var PROPERTY_1846 = $('#PROPERTY_1846');
        if(PROPERTY_1846.is(':visible')){
            PROPERTY_1846.hide();
            var select = $('<select>').addClass('bx-select');
            PROPERTY_1846_items.forEach(function(item){
                select.append(
                    $('<option>').val(item.ID).text(item.NAME).prop('selected', item.NAME == PROPERTY_1846.val())
                );
            });
            select
                .on('change',function(){
                    PROPERTY_1846.val(this.options[this.selectedIndex].textContent)
                })
                .insertBefore(PROPERTY_1846);
        }
    }, 100);
};
function bp_mp_start(){
    var items = $('.bx-odd .bx-checkbox-col input, .bx-even .bx-checkbox-col input').map(function(){
        return this.value;
    }).toArray();
    if(!items.length){
        alert('Процессы не выбраны');    
        return;
    }
}
>>>>>>> e0a0eba79 (init)
$(bp_mp_init);