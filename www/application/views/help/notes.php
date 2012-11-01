<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="help-holder">
  <p class='header1'>Annotating the bibliography</p>
  <p>Where a publication abstract is a purely descriptive summary of a publication, a annotation can be both descriptive and critical. Annotations are commonly used to:
    <ul>
      <li>place a publication in a context</li>
      <li>describe the relevance of a publication</li>
      <li>summarize the strengths and weaknesses of a publication</li>
    </ul>
  </p>
  <p>Aigaion offers the 'note' facility to create annotations. An annotation might look like the following example:
    <div class="message">
    <span title="Example"><b>[EXA]</b></span> :&nbsp;Extensive evaluation of several featuresets and classifiers. The evaluation confirms the results that have been found in <i><a href='#'>aucouturier:04</a></i>.<br/>
    <ul>
      <li>There seems to be a glass ceiling in classification accuracy.</li>
      <li>The featureset found by Aucouturier indeed represents an optimal set.</li>
    </ul>
    <nobr>
      INCLUDE: MOCKUP OF EDIT AND DELETE LINKS!
    </nobr>
    </div>
  </p>
  
  <p class='header1'>Referencing other publications</p>
  <p>You can reference to other publications by simply using the publications BibTeX cite ID. On displaying the note, the cite ID will be replaced by a link to the corresponding publication.</p>
  
  <p class='header1'>Formatting annotations</p>
  <p>To improve the readability of annotations it is recommended to keep annotations short and to the point. Standard HTML formatting tags can be used to format an annotation. The most common tags are listed here:</p>
  <p align=center>
  <table>
  	<tr>
  		<td>
  			<div class=message>
  			<table>
  				<tr><td><h3>HTML:</h3></td><td><h3>Output:</h3></td></tr>
  				<tr><td><pre>&lt;br/&gt;</pre></td><td valign=top>linebreak</td></tr>
  				<tr><td><pre>&lt;b&gt;boldface text&lt;/b&gt;</pre></td><td valign=top><b>boldface text</b></td></tr>
  				<tr><td><pre>&lt;i&gt;italic text&lt;/i&gt;</pre></td><td valign=top><i>italic text</i></td></tr>
  				<tr><td>
  <pre>&lt;ul&gt;
    &lt;li&gt;the first list item&lt;/li&gt;
    &lt;li&gt;the second list item&lt;/li&gt;
    &lt;li&gt;the third list item&lt;/li&gt;
  &lt;/ul&gt;</pre>
  			</td><td valign=top><ul>
  			  <li>the first list item</li>
  			  <li>the second list item</li>
  			  <li>the third list item</li>
  			</ul>
  			</td></tr>
  			</table>
  			</div>
  		</td>
  	</tr>
  </table>
  </p>
</div>
