<?php
	/***************************************************************
	 * Copyright notice
	 *
	 * (c) 2004 macmade.net
	 * All rights reserved
	 *
	 * This script is part of the TYPO3 project. The TYPO3 project is 
	  * free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 *
	 * The GNU General Public License can be found at
	 * http://www.gnu.org/copyleft/gpl.html.
	 *
	 * This script is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 * GNU General Public License for more details.
	 *
	 * This copyright notice MUST APPEAR in all copies of the script!
	 ***************************************************************/
	
	/** 
	 * Plugin 'FLV Player' for the 'flvplayer' extension.
	 *
	 * @author		Jean-David Gadina (macmade@gadlab.net)
	 * @version		1.0
	 */
	
	/**
	 * [CLASS/FUNCTION INDEX OF SCRIPT]
	 * 
	 * SECTION:		1 - MAIN
	 *      99:		function main($content,$conf)
	 *     132:		function setConfig
	 *     169:		function buildFlashCode
	 *     211:		function writeFlashObjectParams
	 * 
	 *				TOTAL FUNCTIONS: 4
	 */
	
	// Typo3 FE plugin class
	require_once(PATH_tslib.'class.tslib_pibase.php');
	
	// Developer API class
	require_once(t3lib_extMgm::extPath('api_macmade').'class.tx_apimacmade.php');
	
	class tx_flvplayer_pi1 extends tslib_pibase {
		
		
		
		
		
		/***************************************************************
		 * SECTION 0 - VARIABLES
		 *
		 * Class variables for the plugin.
		 ***************************************************************/
		
		// Same as class name
		var $prefixId = 'tx_flvplayer_pi1';
		
		// Path to this script relative to the extension dir
		var $scriptRelPath = 'pi1/class.tx_flvplayer_pi1.php';
		
		// The extension key
		var $extKey = 'flvplayer';
		
		// Upload directory
		var $uploadDir = 'uploads/tx_flvplayer/';
		
		// Version of the Developer API required
		var $apimacmade_version = 1.9;
		
		
		
		
		
		/***************************************************************
		 * SECTION 1 - MAIN
		 *
		 * Functions for the initialization and the output of the plugin.
		 ***************************************************************/
		
		/**
		 * Returns the content object of the plugin.
		 * 
		 * This function initialises the plugin "tx_flvplayer_pi1", and
		 * launches the needed functions to correctly display the plugin.
		 * 
		 * @param		$content			The content object
		 * @param		$conf				The TS setup
		 * @return		The content of the plugin.
		 * @see			setConfig
		 * @see			buildFlashCode
		 */
		function main($content,$conf) {
			
			// New instance of the macmade.net API
			$this->api = new tx_apimacmade($this);
			
			// Set class confArray TS from the function
			$this->conf = $conf;
			
			// Init flexform configuration of the plugin
			$this->pi_initPIflexForm();
			
			// Store flexform informations
			$this->piFlexForm = $this->cObj->data['pi_flexform'];
			
			// Set final configuration (TS or FF)
			$this->setConfig();
			
			// Build content
			$content = $this->buildFlashCode();
			
			// Return content
			return $this->pi_wrapInBaseClass($content);
		}
		
		/**
		 * Set configuration array.
		 * 
		 * This function is used to set the final configuration array of the
		 * plugin, by providing a mapping array between the TS & the flexform
		 * configuration.
		 * 
		 * @return		Void
		 */
		function setConfig() {
			
			// Mapping array for PI flexform
			$flex2conf = array(
				'file' => 'sDEF:file',
				'swfParams.' => array(
					'loop' => 'sFLASH:loop',
					'menu' => 'sFLASH:menu',
					'quality' => 'sFLASH:quality',
					'scale' => 'sFLASH:scale',
					'bgcolor' => 'sFLASH:bgcolor',
					'swliveconnect' => 'sFLASH:swliveconnect',
				),
				'playerParams.' => array(
					'autoStart' => 'sPLAYER:autostart',
					'fullScreen' => 'sPLAYER:fullscreen',
				),
				'width' => 'sFLASH:width',
				'height' => 'sFLASH:height',
				'version' => 'sFLASH:version',
			);
			
			// Ovverride TS setup with flexform
			$this->conf = $this->api->fe_mergeTSconfFlex($flex2conf,$this->conf,$this->piFlexForm);
			
			// DEBUG ONLY - Output configuration array
			#$this->api->debug($this->conf,'FLV Player: configuration array');
		}
		
		/**
		 * Returns the code for the flash file.
		 * 
		 * This function creates a flash plugin object.
		 * 
		 * @return		The complete HTML code used to display the flash file.
		 * @see			writeFlashObjectParams
		 */
		function buildFlashCode() {
			
			// Creating valid pathes for the MP3 player
			$swfPath = str_replace(PATH_site,'',t3lib_div::getFileAbsFileName($this->conf['flvplayer']));
			
			// Autostart (WARNING - Settings looks inverted in the Flash file)
			$autoStart = ($this->conf['playerParams.']['autoStart']) ? '' : '&autoStart=true';
			
			// Allow fullscreen mode
			$fullScreen = ($this->conf['playerParams.']['fullScreen']) ? '&showFs=true' : '';
			
			// File path
			$filePath = str_replace(PATH_site,'/',t3lib_div::getFileAbsFileName($this->uploadDir . $this->conf['file']));
			
			// Add FlashVars param to TS
			$this->conf['swfParams.']['FlashVars'] = 'file=' . $filePath . $autoStart . $fullScreen;
			
			// Add movie param to TS
			$this->conf['swfParams.']['movie'] = $swfPath;
			
			// Storage
			$htmlCode = array();
			
			// Flash code
			$htmlCode[] = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=' . $this->conf['version'] . ',0,0,0" id="' . $this->prefixId . '" width="' . $this->conf['width'] . '" height="' . $this->conf['height'] . '" align="center">';
			$htmlCode[] = $this->writeFlashObjectParams();
			$htmlCode[] = '<embed src="' . $swfPath . '" FlashVars="' . $this->conf['swfParams.']['FlashVars'] . '" swliveconnect="' . $this->conf['swfParams.']['swliveconnect'] . '" loop="' . $this->conf['swfParams.']['loop'] . '" menu="' . $this->conf['swfParams.']['menu'] . '" quality="' . $this->conf['swfParams.']['quality'] . '" scale="' . $this->conf['swfParams.']['scale'] . '" bgcolor="' . $this->conf['swfParams.']['bgcolor'] . '" width="' . $this->conf['width'] . '" height="' . $this->conf['height'] . '" name="' . $this->prefixId . '" align="top" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">';
			$htmlCode[] = '</embed>';
			$htmlCode[] = '</object>';
			
			// Return content
			return implode(chr(10),$htmlCode);
		}
		
		/**
		 * Returns param tags
		 * 
		 * This function creates a param tag for each parameter specified in the
		 * setup field.
		 * 
		 * @return		A param tag for each parameter.
		 */
		function writeFlashObjectParams() {
			
			// Storage
			$params = array();
			
			// Build HTML <param> tags from TS setup
			foreach($this->conf['swfParams.'] as $name => $value) {
				$params[] = '<param name="' . $name . '" value="' . $value . '">';
			}
			
			// Return tags
			return implode(chr(10),$params);
		}
	}
	
	/**
	 * XCLASS inclusion
	 */
	if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flvplayer/pi1/class.tx_flvplayer_pi1.php']) {
		include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flvplayer/pi1/class.tx_flvplayer_pi1.php']);
	}
?>
