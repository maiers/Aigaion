function submitPublicationForm(formname)
{
  $('pubform_authors').value=getAuthors();
  $('pubform_editors').value=getEditors();
  $(formname).submit();
}

function submitPublicationConfirmForm(text)
{
  //document.publicationform.upd_crossref.value=text
  //document.publicationform.submit();
}

function getAuthors() 
{
  var strValues = "";
  var boxLength = $('selectedauthors').length;
  var count = 0;
  if (boxLength != 0) {
    for (i = 0; i < boxLength; i++) {
      if (count == 0) {
        strValues = $('selectedauthors').options[i].value;
      }
      else {
        strValues = strValues + "," + $('selectedauthors').options[i].value;
      }
      count++;
     }
  }
  return strValues;
}

function getEditors() {
  var strValues = "";
  var boxLength = $('selectededitors').length;
  var count = 0;
  if (boxLength != 0) {
    for (i = 0; i < boxLength; i++) {
      if (count == 0) {
        strValues = $('selectededitors').options[i].value;
      }
      else {
        strValues = strValues + "," + $('selectededitors').options[i].value;
      }
      count++;
     }
  }
  return strValues;
}

function addAuthor(text,value)
{
  //document.publicationform.authorsbox.options[document.publicationform.authorsbox.length] = new Option(text,value,false,false);
}

function addEditor(text,value)
{
  //document.publicationform.editorsbox.options[document.publicationform.editorsbox.length] = new Option(text,value,false,false);
}

function clearAuthors()
{
  //for (var i=(document.publicationform.authorsbox.length-1);i>=0;i--)
  {
    //document.publicationform.authorsbox.options[i] = null;
  }
}

function clearEditors()
{
  //for (var i=(document.publicationform.editorsbox.length-1);i>=0;i--)
  {
    //document.publicationform.editorsbox.options[i] = null;
  }
}
