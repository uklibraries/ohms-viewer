OHMS Viewer installation and customization instructions
=======================================================


License Information
-------------------

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License (Version 3) as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.

The OHMS Viewer includes:

    jQuery
    jQuery UI
    Fancybox
    Colorbox
    FlowPlayer
   

Use and modifications of these libraries must comply with their respective licenses.


Requirements
------------

* PHP 5.3+
* Tested with Apache httpd on Linux, but other setups (e.g., Windows/IIS) should also work.

You will need to be able to add and create files on your web server to install the OHMS Viewer.  You can do simple customizations of the site with CSS.  More
complex customizations require knowledge of PHP and JavaScript.

In the instructions below, file references (such as css/viewer.css) refer to the relative path to files from the extracted OHMS Viewer zip file.

Basic installation
------------------

I. Install the Viewer Files

1) Download the OHMS Viewer zip file.

2) Create a subdirectory on the web server to store the OHMS Viewer.  If your web server stores pages in /var/www/html and you want to access the viewer 
   as http://example.com/ohms-viewer/, then you would create the subdirectory "ohms-viewer" in
   /var/www/html/.

3) Extract the OHMS Viewer zip file into the subdirectory you created.


II. Set the values for the configuration file
--------------------------------------------

The next steps will require using a text editing program to create your configuration file for the Viewer. This configuration file allows you to set the background colors, the Usage and Rights statements and images used on the Viewer page. You can refer to the config.example.ini in the Viewer subdirectory "config"as a guide for settings values.

1) Create the configuration file "config.ini". Rename the file "config.template.ini" to "config.ini" in the Viewer subdirectory "config". The reason for this is that if you implement a newer version of the Viewer, your current config.ini file will not be overwritten or replaced (though a future version may require changes to your config.ini).

2) Next, you must configure the OHMS Viewer to access the location for your repository cache files exported from OHMS.  The cache files can be located on the same server, inside the
   Viewer directory, or on another server.  To set the server path to access the cache files:
   
   The config.ini has the property "tmpDir". Change this line to the server directory path for the cache files.

   Examples:

   * Inside the Viewer directory:

               tmpDir = /var/www/html/ohms-viewer/cachefiles

   * On the server:

               tmpDir = /usr/local/share/cachefiles/

   * On another server:

               tmpDir = http://example.com/cachefiles/

   In any case, you must ensure that the location you specify exists and is readable by the OHMS Viewer.
 
3) Set the repository name. Replace the existing entry that states "Your Repository Name" with your repository name as entered in the "repository" data field of your cache files. The names must match exactly (the same uppercase or lowercase letters and any punctation). For example, if your repository's name is "John J. Doe Center, University of Us" then that is what must be entered (without the double quotes).
 
4) The CSS file name (the "css" config property). The default setting is to the "custom_default.css" file located in the "css" subdirectory of the Viewer. We suggest you keep this file name. Edit the custom_default.css values to the background colors you wish to use. This is further discussed in the next installation section.
 
5) Set the location for the footer image (such as a logo) that appears in the footer area of the viewer (the "footerimg" config property). The image must reside in the root directory of your web site.
   An example is that if the image file is "footerimage.jpg" in the "images" subdirectory of your site, you would enter: images/footerimage.jpg .

6) Set the alternate text for the footer image that will appear when a computer pointing device (such as a mouse) hovers over the image or that is read by a screen reader. This is entered for the "footerimgalt" config property.
 
7) Set the contact email address for the "contactemail" config property.
 
8) Set the link to the web site for the repository owner for the "contactlink" config property. This may be the same as the URL for your site hosting the Viewer.
 
9) Set the text for the copyright holder information for the "copyrightholder" config property. If this text will list an entry such as a department or division of an organization (for example "History Department, University of State"), then each entity should be inside the HTML `<span></span>` tags such as:

	 <span>History Department</span><span>University of State</span>
 
10) Set the Open Graph "description" value for the "open_graph_description" config property. The Open Graph protocol provides a way for links placed in social media sites to display thumbnail images, descriptions and titles. The "description" is what will always appear as the description (such as "Our Repository") for every link to interviews hosted by your OHMS Viewer.

11) Set the Open Graph image to use for links placed in social media sites ("open_graph_image" config property). This will be the image seen with links placed in social media site postings (it does not appear on the Viewer page). The image must reside in the root directory of your web site. An example is that if the image is "ourimage.jpg" in the "images" subdirectory of your site, you would enter: images/ourimage.jpg .
  
Please note that the "title" that appears for links in social media sites using Open Graph is set by the "title" data field in the linked interview cache file.
 
III. Configuring the style values in the CSS file
--------------------------------------------
 
 The file css/custom_default.css contains a base set of style elements for the Viewer you can configure (The file css/viewer.css contains the overall CSS values if you wish to further change settings).  You can edit the  "background" attribute for body, #header, #footer, #audio-panel, or   #subjectPlayer to set the background color.  For example, if you want the footer to have a light red background, you can edit #footer to include the line

    background: #ff0000;
    
IV. Using the Viewer with your interview XML files exported from OHMS
---------------------------------------------------------------------

After installing and configuring the Viewer, you can begin testing and using it immediately. You must have your interview files exported from OHMS in the directory you set for the "tmpDir" configuration property. The URL for using the Viewer would be your web site address and the subdirectory for the Viewer along with the page (viewer.php) that processes the interview file. An example is:

  http://www.myviewerexamplesite.edu/viewer/viewer.php?cachefile=name_of_file.xml
  
If this URL does not load properly or you receive an error message about not finding the interview file, check the following:

  * The subdirectory name where the Viewer is located is correct.
  * The "tmpDir" in the configuration file is correct for the location where you placed your interview files exported from OHMS.
  * The name of the XML file after "cachefile=" in the URL is correct.
  * Check the permissions on the subdirectories for the Viewer and XML interview files to make sure that your web server can read/access files.

 

Extending OHMS Viewer
=======================

  
I. Implementing a different media player for interview files with "Other" as the media file host
--------------------------------------------

You can implement a different media player than the default one for those interviews with "Other" as the media file host.
This requires some programming ability with PHP and JavaScript and is a more complex customization than what is discussed above.

This will entail changing the following files:

	/tmpl/viewer.tmpl.php
	/tmpl/player_other.tmpl.php
	/js/viewer_other.js

We recommend making backups of those files before doing this (or at least keep track of where you stored the Zip file for future use).

The two most likely scenarios for implementing a different player and what we suggest in doing so are:

	A) A media player like Flowplayer (one that uses HTML5 and/or Adobe Flash) that just plays a video or audio file from a direct link.
		1) Install the files for this new player based on the instructions for it. External links to javascript or CSS files are to be placed in /tmpl/viewer.tmpl.php . You may also need to remove any existing links to Flowplayer specific javascript or CSS files in /tmpl/viewer.tmpl.php.
		2) Change tmpl/player_other.tmpl.php and the corresponding javascript file js/viewer_other.js as needed based on the instructions for the new player.
		3) The change that is most crucial is ensuring that the new player can interact with the links (in the transcript and index segments) to the time points in the clip. The javascript code in js/viewer_other.js should provide a guide for doing this along with the documentation for the new player you are implementing.
		4) You may need to adjust the CSS settings for height in player_other.tmpl.php.
		5) Test using the search for transcript and index making sure results are returned, links to results work and that the links for time points in the transcript or index link to the same place in the clip. A new media player may require adjusting how the javascript for searching and playing linked time points works.

	B) A media hosting service that uses an embedded player and IDs for accounts and media files (such as Brightcove or Kaltura).
		1) Install the files for this new player based on the instructions for it. External links to javascript or CSS files are to be placed in /tmpl/viewer.tmpl.php . You may also need to remove any existing links to Flowplayer specific javascript or CSS files in /tmpl/viewer.tmpl.php. 
		2) Use the Brightcove files tmpl/player_brightcove.tmpl.php and the corresponding javascript file js/viewer_brightcove.js as a guide. What is most important is that you reference the "clip_id", "account_id", and "player_id" found in the "mediafile" data element of the interview cache file.
		3) Change tmpl/player_other.tmpl.php and the corresponding javascript file js/viewer_other.js as needed based on the instructions for the new player.
		4) You may need to adjust the CSS settings for height in player_other.tmpl.php.
		5) Test using the search for transcript and index making sure results are returned, links to results work and that the links for time points in the transcript or index link to the same place in the clip. A new media player may require adjusting how the javascript for searching and playing linked time points works.

	   The modifications to these files will require changing settings such as the values of ID fields in the cache file to the corresponding ID names used by the media service, how the javascript is able to interact with the player for linking to time points and the changing CSS for how the media clip is displayed (dimensions). You will also need to check that the functionality for transcript and index searching works correctly.

		
