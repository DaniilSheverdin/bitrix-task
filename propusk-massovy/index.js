<<<<<<< HEAD
$(function(){
    $('.propusk-massovy').on('submit',function(e){
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
});

function propusk_massovy__avto_new(){
     var last_item = $('.propusk-massovy__avto__item').last();
     var new_item = last_item.clone();
     new_item.data('index',parseInt(last_item.data('index')+1));
     new_item.find('input').val(null).attr('name','AVTO['+new_item.data('index')+'][]');
     new_item.insertAfter(last_item);
}
function propusk_massovy__spl_change(){
     var tr = $(this).closest('tr');
     var val = [];
     tr.find('input.form-control').each(function(){
          var _v = this.value.trim();
          if(_v){
               val.push(_v);
          }
     });
     tr.find('.spisok_lits_str').val(val.join(', '));
     
}
function propusk_massovy__spl_new(){
     $(this).closest('table').find('tbody tr:last-child').clone().appendTo($(this).closest('table').find('tbody')).find('input').val("");
}
function propusk_massovy__spl_del(){
     var tr = $(this).closest('tr');
     if(tr.siblings().length == 0){
          alert("Невозможно удалить последнюю строку");
          return;
     }
     tr.remove();
}
$(function(){
     $('#js-phone').hide();
     $('body').on('change', '[name="VYBOR_ZDANIYA"]', function () {
          $('.propusk-massovy__avto,.propusk-massovy__avto-need').toggleClass('available',(this.options[this.selectedIndex].getAttribute('data-parking') == '1'));
          $('.zayavka-comment').prop('hidden', this.value != '1093');
          if (this.value == '1093') {
               $('#js-phone')
                   .show()
                   .find('input').attr('required', true);
          }
          else {
               $('#js-phone')
                   .hide()
                   .find('input').attr('required', false);
          }
     });
})

$(function(){
     $("#add-mc").on('click', function() {
          let tplMC = $(this).closest('.form-group.row').find('[data-mc=1]').eq(0);
          let edomClone = tplMC.clone();
          tplMC.parent().append(edomClone);
          counterInf($(this));
     });

     var counterInf = function(base) {
          let count = 0;
          base.closest('.form-group.row').find('[data-mc=1]').each(function() {
               let len = $(this).find('input[name="MC_NAIMENOVANIE_MC[]"]').val().length;

               if(len > 0) {
                    count++;
               }
          });

          $('[name="MC_KOLICHESTVO_MC"]').val(count);

          return count;
     }

     $(document).on('keyup', 'input[name="MC_NAIMENOVANIE_MC[]"]', function() {
          counterInf($(this));
     });
=======
$(function(){
    $('.propusk-massovy').on('submit',function(e){
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
});

function propusk_massovy__avto_new(){
     var last_item = $('.propusk-massovy__avto__item').last();
     var new_item = last_item.clone();
     new_item.data('index',parseInt(last_item.data('index')+1));
     new_item.find('input').val(null).attr('name','AVTO['+new_item.data('index')+'][]');
     new_item.insertAfter(last_item);
}
function propusk_massovy__spl_change(){
     var tr = $(this).closest('tr');
     var val = [];
     tr.find('input.form-control').each(function(){
          var _v = this.value.trim();
          if(_v){
               val.push(_v);
          }
     });
     tr.find('.spisok_lits_str').val(val.join(', '));
     
}
function propusk_massovy__spl_new(){
     $(this).closest('table').find('tbody tr:last-child').clone().appendTo($(this).closest('table').find('tbody')).find('input').val("");
}
function propusk_massovy__spl_del(){
     var tr = $(this).closest('tr');
     if(tr.siblings().length == 0){
          alert("Невозможно удалить последнюю строку");
          return;
     }
     tr.remove();
}
$(function(){
     $('#js-phone').hide();
     $('body').on('change', '[name="VYBOR_ZDANIYA"]', function () {
          $('.propusk-massovy__avto,.propusk-massovy__avto-need').toggleClass('available',(this.options[this.selectedIndex].getAttribute('data-parking') == '1'));
          $('.zayavka-comment').prop('hidden', this.value != '1093');
          if (this.value == '1093') {
               $('#js-phone')
                   .show()
                   .find('input').attr('required', true);
          }
          else {
               $('#js-phone')
                   .hide()
                   .find('input').attr('required', false);
          }
     });
})

$(function(){
     $("#add-mc").on('click', function() {
          let tplMC = $(this).closest('.form-group.row').find('[data-mc=1]').eq(0);
          let edomClone = tplMC.clone();
          tplMC.parent().append(edomClone);
          counterInf($(this));
     });

     var counterInf = function(base) {
          let count = 0;
          base.closest('.form-group.row').find('[data-mc=1]').each(function() {
               let len = $(this).find('input[name="MC_NAIMENOVANIE_MC[]"]').val().length;

               if(len > 0) {
                    count++;
               }
          });

          $('[name="MC_KOLICHESTVO_MC"]').val(count);

          return count;
     }

     $(document).on('keyup', 'input[name="MC_NAIMENOVANIE_MC[]"]', function() {
          counterInf($(this));
     });
>>>>>>> e0a0eba79 (init)
});