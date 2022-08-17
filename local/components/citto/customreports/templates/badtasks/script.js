var currentInput = null,
    currentInputid = null;
$(document).ready(function() {
    $('.table-toggle').on('click', function() {
        let id = $(this).attr('data-id');
        $('#' + id).toggleClass('d-none');
    });
    $('[name=NEW_USER]').on('click', function() {
        currentInput = $(this);
        currentInputid = $(this).attr('id');
        $('#NEW_USER_FAKE_selector_content').show();
        $('.user-selector-popup').removeClass('d-none');
        $('.user-selector-popup').css({
            left: $(this).offset().left,
            top: $(this).offset().top + 45
        });
    });
    $('.user-selector-popup .close').on('click', function(){
        $('.user-selector-popup').addClass('d-none');
        $('#NEW_USER_FAKE_selector_content').hide();
    });
});

function hidePopup(val) {
    currentInput.val(val.name);
    $('#' + currentInputid + '_ID').val(val.id);
    $('.user-selector-popup').addClass('d-none');
    $('#NEW_USER_FAKE_selector_content').hide();
}