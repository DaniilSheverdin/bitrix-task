<<<<<<< HEAD
$(function(){
    if(typeof window.zayavkaInitiated != "undefined") return;
    window.zayavkaInitiated = true;

    function zayavkaCancel(ID){
        var zayavka_modal = $('#zayavka-na-propusk-cancel');
        zayavka_modal.find('input[name="password"]').val("");
        zayavka_modal.find('input[name="ID"]').val(ID);
        zayavka_modal.modal('show');
    }
    $('#Reports tbody tr').each(function(){
        var _this = $(this);
        var actions = _this.find('td:eq(-1)');
        var action_text = actions.text();
        actions.empty();
        
        if(~action_text.indexOf("#CANCEL#")){
            actions.append(
                $('<button>').attr({type:"button",class:"btn btn-danger btn-sm"}).text("Отменить").click(function(){
                    zayavkaCancel($(this).closest('td').siblings().first().text());
                })
            );
        }
    });

    
    $('#zayavka-na-propusk-cancel form').on('submit',function(e){
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
                  location.reload();
             }).fail(function(){
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
    if(typeof window.zayavkaInitiated != "undefined") return;
    window.zayavkaInitiated = true;

    function zayavkaCancel(ID){
        var zayavka_modal = $('#zayavka-na-propusk-cancel');
        zayavka_modal.find('input[name="password"]').val("");
        zayavka_modal.find('input[name="ID"]').val(ID);
        zayavka_modal.modal('show');
    }
    $('#Reports tbody tr').each(function(){
        var _this = $(this);
        var actions = _this.find('td:eq(-1)');
        var action_text = actions.text();
        actions.empty();
        
        if(~action_text.indexOf("#CANCEL#")){
            actions.append(
                $('<button>').attr({type:"button",class:"btn btn-danger btn-sm"}).text("Отменить").click(function(){
                    zayavkaCancel($(this).closest('td').siblings().first().text());
                })
            );
        }
    });

    
    $('#zayavka-na-propusk-cancel form').on('submit',function(e){
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
                  location.reload();
             }).fail(function(){
                  _this.find('.alert').attr('class','alert alert-danger').html("Произошла ошибка, попробуйте позже").show();
             }).always(function(){
                  _this.find('button[type=submit]').prop('disabled',false);
             })
        }
        _this.addClass('was-validated');
        return false;
   });
>>>>>>> e0a0eba79 (init)
});