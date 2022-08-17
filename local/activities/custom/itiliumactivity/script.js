$(document).ready(function(){
    $('#td_PARENT').closest('tr').addClass('Show-select Show-TASK');

    var currentType = $('select[name=TYPE]').val();

    $('select[name=TYPE]').on('change', function(){
        $('.Show-select')
            .hide()
            .find('select').attr('disabled', true);
        currentType = $('select[name=TYPE]').val();
        $('.Show-' + currentType)
            .show()
            .find('select').attr('disabled', false);
    });
    $('select[name=TYPE]').trigger('change');

    $('select[name=BUSINESS_SERVICE]').on('change', function(){
        $('.BusinessComponent-select').hide().attr('disabled', true);
        currentBusinessComponent = $('select[name=BUSINESS_SERVICE]').val();
        $('#Component-' + currentBusinessComponent).show().attr('disabled', false);
    });
    $('select[name=BUSINESS_SERVICE]').trigger('change');
    
    $('select[name=TECH_SERVICE]').on('change', function(){
        $('.TechComponent-select').hide().attr('disabled', true);
        currentBusinessComponent = $('select[name=TECH_SERVICE]').val();
        $('#Component-' + currentBusinessComponent).show().attr('disabled', false);
    });
    $('select[name=TECH_SERVICE]').trigger('change');
});