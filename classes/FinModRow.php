<?php
/**
 * Describes the row with exact data-fields
 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
 * @license The MIT License (MIT)
 *
 */
namespace org\dibujo\finance;
{
	/**
	 * Describes the row with exact data-fields
	 *
	 *
	 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
	 * @version FinModRow:0.0.1
	 *
	 * @property int $internalId
	 * @property string $externalId
	 * @property string $extraExternalId
	 * @property date $docDate
	 * @property double $docSum
	 * @property date $processDate
	 * @property string $fromAcc
	 * @property string $toAcc
	 * @property string $fromBank
	 * @property string $toBank
	 * @property string $fromBic
	 * @property string $toBic
	 * @property string $toCorrAcc
	 * @property string $fromCorrAcc
	 * @property string $toINN
	 * @property string $fromINN
	 * @property string $toKPP
	 * @property string $fromKPP
	 * @property int $paymentOrder
	 * @property int $fromCurrCode
	 * @property int $toCurrCode
	 * @property string $from
	 * @property string $purpose
	 * @property string $depCbc
	 * @property date $depDocDate
	 * @property int $depDrawerStatus
	 * @property string $depOkato
	 * @property string $depPaymentTypeReason
	 * @property int $depPaymentTypeKind
	 * @property string $depTaxPeriod
	 * @property int $depDocNo
	 *
	 */
	class FinModRow {

		/**
		 * Contain raw data in array (fields $key => $val)
		 * @var array
		 */
		private $attributes = null;

		/**
		 * Returns an array with fields
		 *
		 * @return array
		 */
		public static function getAttrs() {
			return array(
				"internalId", "externalId", "extraExternalId",
				"docDate", "docSum", "processDate", "fromAcc",
				"toAcc", "fromBank", "toBank", "fromBic",
				"toBic", "toCorrAcc", "fromCorrAcc", "toINN",
				"fromINN", "toKPP", "fromKPP", "paymentOrder",
				"fromCurrCode", "toCurrCode", "from", "to", "purpose",
				"depCbc", "depDocDate", "depDrawerStatus", "depOkato",
				"depPaymentTypeReason", "depPaymentTypeKind", "depTaxPeriod",
				"depDocNo"
			);
		}

		/**
		 * A Constructor
		 */
		public function __construct() {
			$this->attributes = array();
			foreach (self::getAttrs() as $attr)
			$this->attributes[$attr] = null;
		}

		/**
		 * Check if the $val is in attributes
		 * @param mixed $val
		 * @return boolean
		 */
		protected function isInAttrs($val) {
			if (!isset($this->keysAttrs))
				$this->keysAttrs = array_keys($this->attributes);

			return in_array($val, $this->keysAttrs);
		}

		/**
		 * @ignore
		 * @return string
		 */
	    public function __toString() {
	    	$line = "";
	    	foreach ($this->attributes as $key => $val) {
	    		$line .= "{$key}: \"{$val}\"\n";
	    	}
	    	return $line;
	    }
	    /**
	     * @ignore
	     */
		public function __get($val) {
			if ($this->isInAttrs($val))
				return $this->attributes[$val];
			else
				return $this->$val;
		}
		/**
		 * @ignore
		 */
		public function __set($name, $val) {
			if ($this->isInAttrs($name))
				$this->attributes[$name] = $val;
			else
				$this->$name = $val;
		}
	}
}
