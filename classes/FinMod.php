<?php
/**
 * Abstract class for inheritance to future modules
 * to process reports from banks
 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
 * @license The MIT License (MIT)
 *
 */
namespace org\dibujo\finance;
{
	/**
	 * Abstract class for inheritance to future modules
	 * to process reports from banks
	 *
	 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
	 * @property-read array $baseAccountNumbers Array of base accounts numbers. Used like a mask to be filtered by
	 * @version FinMod:0.0.1
	 */
	abstract class FinMod implements \Iterator {

		/**
		 * This property contain the raw \SimpleXMLElement of the file
		 * @var \SimpleXMLElement
		 */
		protected $_xml = null;
		/**
		 * A position of the iterator for this class
		 * @var integer
		 */
		protected $_pos = 0;
		/**
		 * Array of base accounts numbers. Used like a mask to be filtered by
		 * @var array
		 */
		protected $_baseAccountNumbers = null;
		/**
		 * The cached number that represents the quantity of rows
		 * @var integer
		 */
		protected $_rowsquant = null;
		/**
		 * Cached data (rows)
		 * @var array
		 */
		protected $_cache = null;

		/**
		 * A Constructor
		 * @param \SimpleXMLElement $xml SimpleXMLElement data for a next processing
		 */
		public function __construct($xml) {
			$this->_xml = $xml;
		}

		/**
		 * Return the row defined by $index
		 *
		 * @param integer $index
		 */
		abstract protected function row($index);

		/**
		 * Method that caches rows to process them faster,
		 * it usually uses only once per instance
		 */
		abstract protected function cacheIt();

		/**
		 * Method returns the quantity of rows
		 * a number of rows is cached by the call of cacheIt
		 */
		abstract protected function quantity();

		/**
		 * @ignore
		 * @see Iterator::rewind()
		 */
		public function rewind() {
	        $this->_pos = 0;
	    }
	    /**
	     * @ignore
	     * @see Iterator::current()
	     */
	    public function current() {
	    	if ($this->_pos < $this->quantity()) {
	        	return $this->row($this->_pos);
	        } else
	        	return null;
	    }
	    /**
	     * @ignore
	     * @see Iterator::key()
	     */
	    public function key() {
	        $var = md5($this->current());
	        return $var;
	    }
	    /**
	     * @ignore
	     * @see Iterator::next()
	     */
	    public function next() {
	    	if ($this->_pos++ < $this->quantity()) {
	        	return $this->current();
	        } else
	        	return null;
	    }
	    /**
	     * @ignore
	     * @see Iterator::valid()
	     */
	    public function valid() {
	    	$res = $this->current();
	        return isset($res);
	    }

	    /**
	     * @ignore
	     */
	    public function __get($name) {
	    	if ($name == "baseAccountNumbers") {
	    		if (!isset($this->_baseAccountNumbers))
	    			$this->cacheIt();
	    		return $this->_baseAccountNumbers;
	    	} else
	    		$this->$name;
	    }
	}
}
