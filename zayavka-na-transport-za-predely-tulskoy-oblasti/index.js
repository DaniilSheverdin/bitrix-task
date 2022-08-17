<<<<<<< HEAD

$(function(){
    var zayavka_container = $('#zayavka-na-transport');
    var zayavka_na_transport_user_seacrh = zayavka_container.find('.zayavka-na-transport-user-seacrh');
    zayavka_container.find('input[name=VREMYA_PODACHI]').mask('00:00');
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

    var usersSearchTimeout = null;
    zayavka_container.find('input[name=FIO]').on('input',function(){
         var _this = $(this);
         if(usersSearchTimeout){
              clearTimeout(usersSearchTimeout);
         }
         usersSearchTimeout = setTimeout(function(){
               $.post(location.href, {'zayavka-na-transport-user-seacrh':_this.val()}, function(data){
                    zayavka_na_transport_user_seacrh.empty();
                    data.forEach(function(data_item){
                         zayavka_na_transport_user_seacrh
                              .append($('<a>')
                              .addClass('btn-link btn-block')
                              .attr({'href': "javascript:void(0)"})
                              .data('id',data_item.ID)
                              .text(data_item.LAST_NAME+" "+data_item.NAME+" "+data_item.SECOND_NAME));
                    });
                    zayavka_na_transport_user_seacrh.show();
               },'json');
         }, 300);
    });
    
    zayavka_na_transport_user_seacrh.on('click','a',function(){
          zayavka_container.find('input[name=FIO]').val(this.textContent);
          zayavka_container.find('input[name=DOLZHNOST]').val("");
          zayavka_container.find('input[name=PODRAZDELENIE]').val("");
          $.post(location.href,{'zayavka-na-transport-user-get':$(this).data('id')},function(resp){
               zayavka_container.find('input[name=DOLZHNOST]').val(resp.DOLZHNOST);
               zayavka_container.find('input[name=PODRAZDELENIE]').val(resp.PODRAZDELENIE);
          },'json')
     });
=======

$(function(){
    var zayavka_container = $('#zayavka-na-transport');
    var zayavka_na_transport_user_seacrh = zayavka_container.find('.zayavka-na-transport-user-seacrh');
    zayavka_container.find('input[name=VREMYA_PODACHI]').mask('00:00');
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

    var usersSearchTimeout = null;
    zayavka_container.find('input[name=FIO]').on('input',function(){
         var _this = $(this);
         if(usersSearchTimeout){
              clearTimeout(usersSearchTimeout);
         }
         usersSearchTimeout = setTimeout(function(){
               $.post(location.href, {'zayavka-na-transport-user-seacrh':_this.val()}, function(data){
                    zayavka_na_transport_user_seacrh.empty();
                    data.forEach(function(data_item){
                         zayavka_na_transport_user_seacrh
                              .append($('<a>')
                              .addClass('btn-link btn-block')
                              .attr({'href': "javascript:void(0)"})
                              .data('id',data_item.ID)
                              .text(data_item.LAST_NAME+" "+data_item.NAME+" "+data_item.SECOND_NAME));
                    });
                    zayavka_na_transport_user_seacrh.show();
               },'json');
         }, 300);
    });
    
    zayavka_na_transport_user_seacrh.on('click','a',function(){
          zayavka_container.find('input[name=FIO]').val(this.textContent);
          zayavka_container.find('input[name=DOLZHNOST]').val("");
          zayavka_container.find('input[name=PODRAZDELENIE]').val("");
          $.post(location.href,{'zayavka-na-transport-user-get':$(this).data('id')},function(resp){
               zayavka_container.find('input[name=DOLZHNOST]').val(resp.DOLZHNOST);
               zayavka_container.find('input[name=PODRAZDELENIE]').val(resp.PODRAZDELENIE);
          },'json')
     });
>>>>>>> e0a0eba79 (init)
})