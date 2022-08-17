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