$(function(){
    /*
    var bizproc_observer = null;
    var bizproc_table = $('#workarea-content .bizproc-task-table');
    var bizproc_change_callback = function(mutationsList, observer){
        $('.bizproc-input-t--S_employee>td>div[id$="_res"]').each(function(){
            if(bizproc_observer !== null){
                bizproc_observer.disconnect();
            }
            this.childNodes.forEach(function(cc_val){
                if(cc_val.nodeType != Node.TEXT_NODE) return;
                if(!cc_val.textContent.match(/\[[0-9]+\]/ig)) return;
                cc_val.textContent = cc_val.textContent.replace(/\[[0-9]+\]/ig, '');
            });
            if(bizproc_observer !== null){
                bizproc_observer.observe(bizproc_table.get(0), {childList: true,subtree: true});
            }
        });
    };
    if (typeof window.MutationObserver != "undefined"){
        bizproc_observer = new MutationObserver(bizproc_change_callback);
        bizproc_observer.observe(bizproc_table.get(0), {childList: true,subtree: true});
    } else {
        setInterval(bizproc_change_callback, 1000);
    }
    */

	var mfs_pmessage = BX.PopupWindowManager.create("mfs_pmessage", null, {
		content: "Превышен максимальный размер загружаемого файла (10 мегабайт)",
		darkMode: true,
		autoHide: true
	});

    $('#id_bpriact_SOTRUD').closest('tr').hide();
    $('#id_bpriact_COMMENTMC').closest('tr').hide();
    $('#id_bpriact_SOTRUDNIK').closest('tr').hide();

    $('body').on('change', '#id_bpriact_SAVEARM', function(){
        console.log($(this).val());
        if($(this).val() === "v1")
        {
            $('#id_bpriact_SOTRUD').closest('tr').show();
        }
        else
        {
            $('#id_bpriact_SOTRUD').closest('tr').hide();
        }
    });
    $('body').on('change', '#id_bpriact_SAVEMC', function(){ 
        if($(this).val() === "v2")
        {
            $('#id_bpriact_COMMENTMC').closest('tr').show();
        }
        else
        {
            $('#id_bpriact_COMMENTMC').closest('tr').hide();
        }
    });
    $('body').on('change', '#id_bpriact_SDACHAARM', function(){
        if($(this).val() === "v2")
        {
            $('#id_bpriact_SOTRUDNIK').closest('tr').show();
        }
        else
        {
            $('#id_bpriact_SOTRUDNIK').closest('tr').hide();
        }
    });


    $('.bp-task-block form input[type=submit], .bp-task-block form button[type=submit]').each(function(){
        var _this = $(this);
        _this.data('innercont', _this.text());
    })

	$(document).on('change', '.bp-task-block form input[type=file]', function(){
		var file_inputs		= $('.bp-task-block form input[type=file]');
		var _sb				= $('.bp-task-block form input[type=submit][name=approve], .bp-task-block form button[type=submit][name=approve]');
		var max_size_file	= {size:0};
		
		file_inputs.each(function(){
			for(var file_indx in this.files){
				if(this.files[file_indx].size > max_size_file.size){
					max_size_file = this.files[file_indx];
				}
			}
		});
		
		if(max_size_file.size && max_size_file.size/1024/1024 > 10){
			_sb.prop('disabled', true).text("Размер файла ("+max_size_file.name+") более 10 мегабайт");
			mfs_pmessage.show();
		}else{
			_sb.prop('disabled', false).text(_sb.data('innercont'));
		}
	});

    /**
     * Multiselect
     */
    if ($("select[multiple]").length > 0) {
        $("head")
            .append('<link href="/local/css/bootstrap-plugin/bootstrap-select.min.css" type="text/css" rel="stylesheet">');

        $("body")
            .append('<script type="text/javascript" src="/local/js/bootstrap-plugin/bootstrap-select.min.js"></script>');

        $("select[multiple]").each(function() {
            $(this).addClass('selectpicker form-control');
            $(this).selectpicker();
        });
    }
});