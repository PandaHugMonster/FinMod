<?php
/**
 * Class that helps to parse the data with specified module class
 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
 * @license The MIT License (MIT)
 *
 */
namespace org\dibujo\finance;
use modules\FinModRaif;
{

	/**
	 * Class that helps to parse the data with specified module class
	 *
	 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
	 * @version FinModParser:0.0.1
	 *
	 */
	class FinModParser {

		/**
		 * Contain the name of the module (with full namespace path)
		 * @var string
		 */
		protected $_module = null;
		/**
		 * Contain the name of a file to parse
		 * @var string
		 */
		protected $_filename = null;
		/**
		 * Contain the raw SimpleXMLElement data for a next processing
		 * @var \SimpleXMLElement
		 */
		protected $_xml = null;
		/**
		 * Object-module that was created to process specific files (The reason why modules are needed)
		 * @var \FinMod
		 */
		protected $Module = null;

		/**
		 * A Constructor
		 * @param string $mod The full path to the module-class ("\\org\\dibujo\\finance\\modules\\FinModRaif")
		 * @param string $filename The path to file to process
		 */
		public function __construct($mod, $filename) {
			$this->load($mod, $filename);
		}

		/**
		 * Load the file and create the module-object
		 * @param string $mod The full path to the module-class ("\\org\\dibujo\\finance\\modules\\FinModRaif")
		 * @param string $filename The path to file to process
		 */
		public function load($mod = null, $filename = null) {
			if (isset($mod))
				$this->_module = $mod;

			if (isset($filename))
				$this->_filename = $filename;

			$this->_xml = simplexml_load_file($this->_filename);

			try {
				$mod = $this->_module;
				$this->Module = new $mod($this->_xml);
			} catch (Exception $e) {
				print_r("Module error: {$e}");
			}
		}

		/**
		 * Parse data and return FinModDataSheet object for further queries
		 * @return FinModDataSheet
		 */
		public function parse() {
			$datasheet = new FinModDataSheet($this->Module->baseAccountNumbers);
			foreach ($this->Module as $key => $row)
				$datasheet->put($row);

			return $datasheet;
		}

	}
}
