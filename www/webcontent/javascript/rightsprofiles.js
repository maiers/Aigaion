function deselectAllRights() {
    var rights = $$('.rightbox');
    for(var c = 0; c < rights.length; c++)
    {
        $(rights[c]).checked=false;
    }
}
function selectAllRights() {
    var rights = $$('.rightbox');
    for(var c = 0; c < rights.length; c++)
    {
        $(rights[c]).checked=true;
    }
}
function deselectProfile() {
    var dropdown = $('uncheckrightsprofile');
    if (dropdown.value=='')return;
    var rights = $$('.'+dropdown.value);
    for(var c = 0; c < rights.length; c++)
    {
        $(rights[c]).checked=false;
    }
}
function selectProfile() {
    var dropdown = $('checkrightsprofile');
    if (dropdown.value=='')return;
    var rights = $$('.'+dropdown.value);
    for(var c = 0; c < rights.length; c++)
    {
        $(rights[c]).checked=true;
    }
}
function restoreRights() {
    var rights = $$('.rightbox_on');
    for(var c = 0; c < rights.length; c++)
    {
        $(rights[c]).checked=true;
    }
    var rights = $$('.rightbox_off');
    for(var c = 0; c < rights.length; c++)
    {
        $(rights[c]).checked=false;
    }
}
