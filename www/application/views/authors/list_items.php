<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<ul class='nosymbol'>
<?php
  $initial = '';
  foreach ($authorlist as $author)
  {
    if ($author->cleanname!='' && strtolower($author->cleanname[0])!=$initial) {
        $initial = strtolower($author->cleanname[0]);
        echo '<li><b>'.$author->cleanname[0].'</b></li>';
    }
    $name = $author->getName('vlf');
    if ($author->synonym_of=='0')
    {
      if ($author->hasSynonyms() && $author->institute!='')
      {
        $name .= ' ('.addslashes ($author->institute).')';
      }
    }
    else
    { //has primary?
      $prim = $this->author_db->getByID($author->synonym_of);
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
      $name = '<i>'.$name.'</i>';
    } //so: for a synonym, we ALWAYS see why it is a synonym
        
    echo "  <li>".anchor('authors/show/'.$author->author_id, $name, array('title' => sprintf(__('All information on %s'), $author->cleanname)))."</li>\n";
  }
?>
</ul>
