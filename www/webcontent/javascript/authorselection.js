<?php
  $count = 0;
  echo "var AUTHORIDS = new Array();\n";
  echo "var AUTHORPRIMARY = new Array();\n"; //link to primary author id, or 0
  echo "var AUTHORS = new Array();\n";
  echo "var CLEANAUTHORS = new Array();\n"; //separate authors and clean authors list. One is for searching, the other for showing
  $CI = &get_instance();
  $CI->db->order_by('cleanname');
  $Q = $CI->db->get("author");
  foreach ($Q->result() as $R)  //for each primary author
  {
    
    $author = $CI->author_db->getFromRow($R);
    
    $cleanname = addslashes ($author->cleanname."||".$author->getName('fvl'));
    $name = addslashes ($author->getName('vlf'));
    
    if ($author->synonym_of=='0')
    {
      if ($author->hasSynonyms() && $author->institute!='')
      {
        $name .= ' ('.addslashes ($author->institute).')';
      }
    }
    else
    { //has primary?
      $prim = $CI->author_db->getByID($author->synonym_of);
      if (addslashes ($prim->getName('vlf'))==$name)
      { //same name?
        if ($prim->institute!=$author->institute)
        { //diff institute? add institute
          $name .= ' ('.addslashes ($author->institute).')';
        }
        else if ($prim->email!=$author->email)
        { //diff email? add email
          $name .= ' ('.addslashes ($author->email).')';
        }
    //   same email? add nothing
      }
      else
      { //diff name?
        //add primary name in parenth
        $name.=' ('.addslashes ($prim->getName('vlf')).')';
      }
    } //so: for a synonym, we ALWAYS see why it is a synonym
    
    echo "AUTHORIDS [{$count}] = ".$R->author_id.";";
    echo "CLEANAUTHORS [".$R->author_id."] = '{$cleanname}';\n";
    echo "AUTHORS [".$R->author_id."] = '{$name}';\n";
    echo "AUTHORPRIMARY [".$R->author_id."] = '".$R->synonym_of."';\n";
    
    $count++;
   
  }
?>
function Init ()
{
	AuthorSearch ();
}

function AuthorSearch ()
{
	searchtext = $('authorinputtext').value;
	$('authorinputselect').length = 0;
	for (a=0;a<AUTHORIDS.length; a++) {
		cleanAuthorString = new String (CLEANAUTHORS [AUTHORIDS [a]]);
		authorString = new String (AUTHORS [AUTHORIDS [a]]);
		authorPrim = new String (AUTHORPRIMARY [AUTHORIDS [a]]);
		if (cleanAuthorString.toLowerCase().indexOf(searchtext.toLowerCase ()) != -1)  
		{
			$('authorinputselect').options [$('authorinputselect').length] = new Option (authorString,a,false,false);
			optionelement = Element.extend($('authorinputselect').options [$('authorinputselect').length-1]);
			if (authorPrim=='0')
			{
			  optionelement.addClassName("primaryauthor");
			} 
			else
		  {
			  optionelement.addClassName("synonymauthor");
		  }
		}
	}
//	if ($('authorinputselect').length == 0) {
//	    $('addnewauthorbutton').replace('<div id="addnewauthorbutton">[<a href="#" onclick="AddNewAuthor(); return false;">Create as new name</a>]</div>');
//	} else {
//	    $('addnewauthorbutton').replace('<div id="addnewauthorbutton"></div>');
//	}
}

function AddAuthor()	{  AddWriter ($('selectedauthors'));  }
function AddEditor()	{  AddWriter ($('selectededitors'));  }
function AddWriter (obj)
{
	authorID = AUTHORIDS [$('authorinputselect').options [$('authorinputselect').selectedIndex].value];

	var isNew = true;
	for (var i = 0;  i < obj.length;  i++)  {
		if (obj.options [i].value == authorID)  {
			isNew = false;
			break;
		} else {
			obj.options [i].selected = false;
		}
	}
	if (isNew)  {
		newoption = new Option (AUTHORS [authorID], authorID, false, true);
		obj.options [obj.length] = newoption;
	}
}

function RemoveAuthor()	{  RemoveWriter ($('selectedauthors')); }
function RemoveEditor()	{  RemoveWriter ($('selectededitors')); }
function RemoveWriter (obj)
{
	i = obj.selectedIndex;
	if (i >= 0)  {
		obj.options [i] = null; // obj.length decreases by 1...
		if (i < (obj.length)) {
			// if there is an element on the old position, mark it
			obj.options [i].selected = true;
		} else if (obj.length > 0)  {
			// otherwise it was the lowest element; mark the el. above (if any is left)
			obj.options [i-1].selected = true;
		}
	}
}

function AuthorUp()	{  WriterUp ($('selectedauthors')); }
function EditorUp()	{  WriterUp ($('selectededitors')); }
function WriterUp (obj)
{
	oldAuthorID = obj.options [0].value;
	for (var i = 1;  i < obj.length;  i++)  {
		if (obj.options [i].selected)  {
			obj.options [i-1].text	= AUTHORS [obj.options [i].value];
			obj.options [i-1].value	= obj.options [i].value;
			obj.options [i-1].selected = true;
			obj.options [i].text		= AUTHORS [oldAuthorID];
			obj.options [i].value		= oldAuthorID;
			obj.options [i].selected	= false;
		}
		oldAuthorID = obj.options [i].value;
	}
}

function AuthorDown()	{  WriterDown ($('selectedauthors')); }
function EditorDown()	{  WriterDown ($('selectededitors')); }
function WriterDown (obj)
{
	oldAuthorID = obj.options [obj.length-1].value;
	for (var i = obj.length-2;  i >= 0;  i--)  {
		if (obj.options [i].selected)  {
			obj.options [i+1].text	= AUTHORS [obj.options [i].value];
			obj.options [i+1].value 	= obj.options [i].value;
			obj.options [i+1].selected = true;
			obj.options [i].text		= AUTHORS [oldAuthorID];
			obj.options [i].value		= oldAuthorID;
			obj.options [i].selected	= false;
		}
		oldAuthorID = obj.options [i].value;
	}
}

function AddNewAuthor() 
{
   newname = $('authorinputtext').value.strip();
   if (newname == '') {
    alert('Cannot add empty author name');
    return;
   }
   req = '<?php echo site_url('authors/quickcreate/'); ?>';
   new Ajax.Request(req,{method:'post',postBody:'authorname='+newname,onSuccess:function(request){ShowNewAuthorFromAjaxCall(request)},onFailure:function(request){alert('Error returned while trying to create author: '+request.responseText)}, evalScripts:true});
}

function ShowNewAuthorFromAjaxCall(request) 
{
    newid = request.responseText.strip();
    if (newid=='') {
        alert ('Could not add author '+$('authorinputtext').value);
    } else {
    	AUTHORIDS.unshift(newid);
	    AUTHORS[newid] = $('authorinputtext').value;
        $('authorinputselect').options [$('authorinputselect').length] = new Option ($('authorinputtext').value,0,false,false);
    }
}

function ShowNewAuthor (ID, CleanName)
{
	AUTHORIDS.unshift(ID);
	AUTHORS[ID] = CleanName;
}
