<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<div id="help-holder">
  <p class='header1'>Attachments</p>
  <p>It is possible to attach files to your publications in two ways: by uploading them to the server, and as a 'remote' link to a document somewhere on the web. Upon import, the import data is scanned for links to attachments, which are then automatically stored as remote attachments for the imported entries. 'Remote' attachments may at a later time be downloaded to the server and turned into 'server stored attachments' with a single command.</p>
  
  <p class='header1'>Why attachments?</p>
  <p>When building up a bibliography, it is not uncommon that one spends a lot of time tracking down not only the bibliographic information for new entries, but also tracking down the content of the papers themselves. And then, when you have finally obtained the file and printed it out, you forget the print-out somewhere on the plane... and have to do it all again. Or you could of course have attached the electronic version of the paper to your Aigaion database - then at least you can print it out a second time without a long search.</p>
  
  <p class='header1'>Attachments and public access rights</p>
  <p>When you have downloaded a publication, e.g. from the publishers site or from your library, you do not necessarily have the right to make that file public to all the world. It is a good idea not to set the access level of a publication to 'public' unless you are very certain of your right to do so. When in doubt, keep the attachment intern or private, and use the DOI to give anonymous guests access to the publication contents.</p>
      
  <p class='header1'>Remote attachments vs DOI</p>
  <p>A DOI is a Digital Object Identifier. "They are used to provide current information, including where they (or information about them) can be found on the Internet. Information about a digital object may change over time, including where to find it, but its DOI name will not change." (source: www.doi.org).</p>
  <p>When you know the DOI of a paper, you can always find the paper by appending the DOI to the url <code>http://dx.doi.org/</code> This usually leads you to the paper on the site of the publisher. A major advantage of this is that, as opposed to on-server attachments, you do not have to worry about your rights to make the information public: the publisher will have the appropriate mechanisms in place on his site to restrict access to the actual content of the paper to those who have a right to it.</p>
  <p>The difference with remote attachments (URL links) is that a DOI always stays the same, whereas an URL will probably be outdated as soon as the publisher changes his web site.</p>
  
  <p class='header1'>Common problems</p>
  <p>The most common problems and errors with uploading attachments are:
    <ul>
      <li>The server is read-only, or the attachment directory is not writable, so storing uploaded attachment on the server fails</li>
      <li>The PHP settings for the webserver are sometimes inadequate for uploading attachments: 'upload_max_filesize', 'post_max_size' and 'max_execution_time' should all be large enough for uploading attachments of a normal size</li>
      <li>The Aigaion setting 'allowed attachment extensions' does not contain the right extensions</li>
    </ul>
  </p>     
</div>
