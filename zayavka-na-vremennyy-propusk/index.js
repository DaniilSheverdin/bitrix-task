<<<<<<< HEAD

$(function(){
    var zayavka_container = $('#zayavka-na-vremennyy-propusk-action');
    zayavka_container.find('input[name=VREMYA]').mask('00:00');
    zayavka_container.find('input[name=NOMER_DOCUMENTA]').mask('00 00 000000');

    zayavka_container.on('change','select[name=VID_DOCUMENTA]',function(e){
         var placeholder = this.options[this.selectedIndex].getAttribute('data-placeholder');
         var NOMER_DOCUMENTA = zayavka_container.find('input[name=NOMER_DOCUMENTA]');

         NOMER_DOCUMENTA.attr({
              pattern:       this.options[this.selectedIndex].getAttribute('data-pattern'),
              placeholder:   placeholder,
         })
              .siblings('.invalid-feedback').find('span').text(placeholder?"Формат: "+placeholder:"");
         
         NOMER_DOCUMENTA.unmask();
         if(placeholder){
              NOMER_DOCUMENTA.mask(placeholder);
         }
    });
    zayavka_container.on('submit',function(e){
         e.preventDefault();
         e.stopPropagation();
         
         var _this = $(this);
         _this.find('.alert').hide();

         if(_this.get(0).checkValidity() !== false){
              _this.find('button[type=submit]').prop('disabled',true);
              $.post(_this.attr('action'), _this.serialize(), function(resp){
                   if(resp.code != "OK"){
                        _this.find('.alert').attr('class','alert alert-danger').html(resp.message).show();
                        return;
                   }
                   
                   _this.find('.alert').attr('class','alert alert-success').html(resp.message).show();
              },'json').fail(function(){
                   _this.find('.alert').attr('class','alert alert-danger').html("Произошла ошибка, попробуйте позже").show();
              }).always(function(){
                   _this.find('button[type=submit]').prop('disabled',false);
              })
         }
         _this.addClass('was-validated');
         return false;
    });
=======

$(function(){
    var zayavka_container = $('#zayavka-na-vremennyy-propusk-action');
    zayavka_container.find('input[name=VREMYA]').mask('00:00');
    zayavka_container.find('input[name=NOMER_DOCUMENTA]').mask('00 00 000000');

    zayavka_container.on('change','select[name=VID_DOCUMENTA]',function(e){
         var placeholder = this.options[this.selectedIndex].getAttribute('data-placeholder');
         var NOMER_DOCUMENTA = zayavka_container.find('input[name=NOMER_DOCUMENTA]');

         NOMER_DOCUMENTA.attr({
              pattern:       this.options[this.selectedIndex].getAttribute('data-pattern'),
              placeholder:   placeholder,
         })
              .siblings('.invalid-feedback').find('span').text(placeholder?"Формат: "+placeholder:"");
         
         NOMER_DOCUMENTA.unmask();
         if(placeholder){
              NOMER_DOCUMENTA.mask(placeholder);
         }
    });
    zayavka_container.on('submit',function(e){
         e.preventDefault();
         e.stopPropagation();
         
         var _this = $(this);
         _this.find('.alert').hide();

         if(_this.get(0).checkValidity() !== false){
              _this.find('button[type=submit]').prop('disabled',true);
              $.post(_this.attr('action'), _this.serialize(), function(resp){
                   if(resp.code != "OK"){
                        _this.find('.alert').attr('class','alert alert-danger').html(resp.message).show();
                        return;
                   }
                   
                   _this.find('.alert').attr('class','alert alert-success').html(resp.message).show();
              },'json').fail(function(){
                   _this.find('.alert').attr('class','alert alert-danger').html("Произошла ошибка, попробуйте позже").show();
              }).always(function(){
                   _this.find('button[type=submit]').prop('disabled',false);
              })
         }
         _this.addClass('was-validated');
         return false;
    });
>>>>>>> e0a0eba79 (init)
})