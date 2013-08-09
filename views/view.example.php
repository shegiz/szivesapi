<?php

/**
 * MyProjectNameHere <http://www.example.com>
 * View Class
 *
 * It is recommended to extend View classes from WWW_Factory in order to 
 * provide various useful functions and API access for the view.
 *
 * @package    Factory
 * @author     DeveloperNameHere <email@example.com>
 * @copyright  Copyright (c) 2012, ProjectOwnerNameHere
 * @license    Unrestricted
 * @tutorial   /doc/pages/guide_mvc.htm
 * @since      1.0.0
 * @version    1.0.0
 */

class WWW_view_example extends WWW_Factory {

	/**
	 * View Controller calls this function as output for page content.
	 * 
	 * This method returns null by default because the API will load the 
	 * result from output buffer, if the API call echoes/prints any output. 
	 * It is recommended for View methods not to return any variable data.
	 *
	 * @param array $input input array from View Controller
	 * @return null
	 */
	public function render($input){
	
		// Getting current view data
		$view=$this->viewData();
		// Getting translations
		$translations=$this->getTranslations();
		// Getting sitemap array
		$sitemap=$this->getSitemap();
		// Getting the entire state data
		$state=$this->getState();
		
		?>
			<div style="padding:30px;width:600px;margin-left:auto;margin-right:auto;">
				<h1 style="font:30px Tahoma; color:##465a9e;padding:30px;">An example API response:</h1>
				<pre>
					<!-- This shows an example API call response -->
					<?=print_r($this->api('example-get'),true)?>
				</pre>
				<h1 style="font:30px Tahoma; color:##465a9e;padding:30px;">Input:</h1>
				<pre>
					<!-- $input is a variable sent to view that contains all the data that is useful when generating views -->
					<?=print_r($input,true)?>
				</pre>
				<h1 style="font:30px Tahoma; color:##465a9e;padding:30px;">View:</h1>
				<pre>
					<!-- $input is a variable sent to view that contains all the data that is useful when generating views -->
					<?=print_r($view,true)?>
				</pre>
				<h1 style="font:30px Tahoma; color:##465a9e;padding:30px;">Sitemap:</h1>
				<pre>
					<!-- $input is a variable sent to view that contains all the data that is useful when generating views -->
					<?=print_r($sitemap,true)?>
				</pre>
				<h1 style="font:30px Tahoma; color:##465a9e;padding:30px;">Translations:</h1>
				<pre>
					<!-- $input is a variable sent to view that contains all the data that is useful when generating views -->
					<?=print_r($translations,true)?>
				</pre>
				<h1 style="font:30px Tahoma; color:##465a9e;padding:30px;">State:</h1>
				<pre>
					<!-- This shows an example API call response -->
					<?=print_r($state,true)?>
				</pre>
			</div>
		<?php
		
		// API Will load result data from output buffer
		return null;
		
	}

}
	
?>