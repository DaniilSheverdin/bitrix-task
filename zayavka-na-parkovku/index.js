<<<<<<< HEAD
$(function(){
     $('.zayavka-na-parkovku').on('submit',function(e){
           e.preventDefault();
           e.stopPropagation();
           
           var _this = $(this);
           _this.find('>.alert').hide();
 
           if(_this.get(0).checkValidity() !== false){
                _this.find('button[type=submit]').prop('disabled',true);
 
                $.ajax({
                     url: _this.attr('action'),
                     data: new FormData(_this.get(0)),
                     processData: false,
                     contentType: false,
                     dataType: 'json',
                     type: 'POST',
                     success: function (resp) {
                          if(resp.code != "OK"){
                               _this.find('>.alert').attr('class','alert alert-danger').html(resp.message).show();
                               return;
                          }
 
                          _this.find('>.alert').attr('class','alert alert-success').html(resp.message).show();
                     }
                }).fail(function(){
                     _this.find('>.alert').attr('class','alert alert-danger').html("Произошла ошибка, попробуйте позже").show();
                }).always(function(){
                     _this.find('button[type=submit]').prop('disabled',false);
                     $('html, body').scrollTop(0);
                })
           }
           _this.addClass('was-validated');
           return false;
      });

      $('[name=SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO]').on('input',function(){
           var val = parseInt($('[name=SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO]').val()) || 0;
           
           if(val){
               val = Math.round(val * 0.2);
           }
           $('.zayavka-na-parkovku__zdanie [name=MAKS1]').val(val);
      });
      
      $('[name=SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO]').on('input',function(){
          var val = parseInt($('[name=SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO]').val()) || 0;
          
          if(val){
              val = Math.round(val * 0.1);
          }
          $('.zayavka-na-parkovku__zdanie [name=MAKS2]').val(val);
     })
 });
 
 function zayavka_na_parkovku__avto_new(){
      var last_item = $('.zayavka-na-parkovku__avto__item').last();
      var new_item = last_item.clone();
      new_item.data('index',parseInt(last_item.data('index')+1));
      new_item.find('input').each(function(){
           var _this = $(this);
           _this.val(null);
           _this
               .attr('name', _this.attr('name').replace('AVTO['+last_item.data('index')+']','AVTO['+new_item.data('index')+']'))
               .attr('name', _this.attr('name').replace('AVTO_STS['+last_item.data('index')+']','AVTO_STS['+new_item.data('index')+']'))
      });
      new_item.insertAfter(last_item);
=======
$(function(){
     $('.zayavka-na-parkovku').on('submit',function(e){
           e.preventDefault();
           e.stopPropagation();
           
           var _this = $(this);
           _this.find('>.alert').hide();
 
           if(_this.get(0).checkValidity() !== false){
                _this.find('button[type=submit]').prop('disabled',true);
 
                $.ajax({
                     url: _this.attr('action'),
                     data: new FormData(_this.get(0)),
                     processData: false,
                     contentType: false,
                     dataType: 'json',
                     type: 'POST',
                     success: function (resp) {
                          if(resp.code != "OK"){
                               _this.find('>.alert').attr('class','alert alert-danger').html(resp.message).show();
                               return;
                          }
 
                          _this.find('>.alert').attr('class','alert alert-success').html(resp.message).show();
                     }
                }).fail(function(){
                     _this.find('>.alert').attr('class','alert alert-danger').html("Произошла ошибка, попробуйте позже").show();
                }).always(function(){
                     _this.find('button[type=submit]').prop('disabled',false);
                     $('html, body').scrollTop(0);
                })
           }
           _this.addClass('was-validated');
           return false;
      });

      $('[name=SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO]').on('input',function(){
           var val = parseInt($('[name=SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO]').val()) || 0;
           
           if(val){
               val = Math.round(val * 0.2);
           }
           $('.zayavka-na-parkovku__zdanie [name=MAKS1]').val(val);
      });
      
      $('[name=SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO]').on('input',function(){
          var val = parseInt($('[name=SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO]').val()) || 0;
          
          if(val){
              val = Math.round(val * 0.1);
          }
          $('.zayavka-na-parkovku__zdanie [name=MAKS2]').val(val);
     })
 });
 
 function zayavka_na_parkovku__avto_new(){
      var last_item = $('.zayavka-na-parkovku__avto__item').last();
      var new_item = last_item.clone();
      new_item.data('index',parseInt(last_item.data('index')+1));
      new_item.find('input').each(function(){
           var _this = $(this);
           _this.val(null);
           _this
               .attr('name', _this.attr('name').replace('AVTO['+last_item.data('index')+']','AVTO['+new_item.data('index')+']'))
               .attr('name', _this.attr('name').replace('AVTO_STS['+last_item.data('index')+']','AVTO_STS['+new_item.data('index')+']'))
      });
      new_item.insertAfter(last_item);
>>>>>>> e0a0eba79 (init)
 }