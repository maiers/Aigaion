function submitAccessLevel(redirecturl, re,object_type, object_id) {
    var dropdown = $(re+'_al_'+object_type+'_'+object_id);
    window.location.href = (redirecturl+'/'+object_type+'/'+object_id+'/'+re+'/'+dropdown.value);
}
