<<<<<<< HEAD
$(function(){
    if (!window.location.origin) {
        window.location.origin = window.location.protocol + "//" 
            + window.location.hostname 
            + (window.location.port ? ':' + window.location.port : '');
    }

    $(document).on('filesigner_hiden', function(){
        window.parent.postMessage('filesigner_hiden', window.location.origin);
    });
    $(document).on('filesigner_signed', function(){
        window.parent.postMessage('filesigner_signed', window.location.origin);
    });
    if(typeof window.filesignerInit == "undefined"){
        window.parent.postMessage('filesigner_error', window.location.origin);
    }else{
        filesignerInit();
    }
=======
$(function(){
    if (!window.location.origin) {
        window.location.origin = window.location.protocol + "//" 
            + window.location.hostname 
            + (window.location.port ? ':' + window.location.port : '');
    }

    $(document).on('filesigner_hiden', function(){
        window.parent.postMessage('filesigner_hiden', window.location.origin);
    });
    $(document).on('filesigner_signed', function(){
        window.parent.postMessage('filesigner_signed', window.location.origin);
    });
    if(typeof window.filesignerInit == "undefined"){
        window.parent.postMessage('filesigner_error', window.location.origin);
    }else{
        filesignerInit();
    }
>>>>>>> e0a0eba79 (init)
});