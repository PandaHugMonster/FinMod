<?php
/**
 * The base class to process queries and do data-specific actions like getting summary or qunatity.
 * This is the front-end to the data
 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
 * @license The MIT License (MIT)
 *
 */
namespace org\dibujo\finance;
{
	/**
	 * The base class to process queries and do data-specific actions like getting summary or qunatity.
	 * This is the front-end to the data
	 *
	 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
	 * @version FinModDataSheet:0.0.1
	 * @property-read integer $quantity
	 * @property-read double $summary
	 *
	 */
	class FinModDataSheet implements \Iterator {

		/**
		 * A Financial period 3 first months
		 * @var integer
		 */
		const FIN_PERIOD_3 = 3;
		/**
		 * A Financial period 6 first months
		 * @var integer
		 */
		const FIN_PERIOD_6 = 6;
		/**
		 * A Financial period 9 first months
		 * @var integer
		 */
		const FIN_PERIOD_9 = 9;
		/**
		 * A Financial period the year
		 * @var integer
		 */
		const FIN_PERIOD_12 = 12;

		/**
		 * The first quarter
		 * @var integer
		 */
		const QUARTER_FIRST = 1;
		/**
		 * The second quarter
		 * @var integer
		 */
		const QUARTER_SECOND = 2;
		/**
		 * The third quarter
		 * @var integer
		 */
		const QUARTER_THIRD = 3;
		/**
		 * The fourth quarter
		 * @var integer
		 */
		const QUARTER_FOURTH = 4;

		/**
		 * Array of the base account names
		 * @var array
		 */
		protected $baseaccnums = null;

		/**
		 * The array of FinModRow
		 * @var FinModRow[]
		 */
		protected $rows = array();

		/**
		 * A Constructor
		 * @param mixed $accnums Array or raw string of the account numbers or the account number respectively
		 */
		public function __construct($accnums) {
			$this->baseaccnums = $accnums;
		}

		/**
		 * Put the row to the exact position in the Sheet
		 *
		 * @param FinModRow $row
		 * @param integer $index
		 */
		public function put($row, $index = null) {
			if (isset($index) && is_numeric($index))
				$this->rows[$index] = $row;
			else
				$this->rows[] = $row;
		}

		/**
		 * Get the exact FinModRow
		 *
		 * @param integer $index Index of the row
		 * @return \org\dibujo\finance\FinModRow
		 */
		public function get($index) {
			return $this->rows[$index];
		}

		/**
		 * Delete the exact FinModRow
		 *
		 * @param integer $index Index of the row
		 */
		public function del($index) {
			unset($this->rows[$index]);
		}


		/**
		 * Return filtered FinModDataSheet by date
		 *
		 * Return FinModDataSheet filtered by DocDat to process next actions (queries)
		 * <code>$fmds->byDocDate(array("from" => date("01.07.2014"), "to" => date("01.10.2014")));</code>
		 * @param array $params params "from" and "to" acceptable
		 * @return FinModDataSheet
		 */
		public function byDocDate($params = array()) {
			$res = new self($this->baseaccnums);
			foreach ($this->rows as $key => $row) {
				$from = isset($params["from"])?strtotime($params["from"]):null;
				$to = isset($params["to"])?strtotime($params["to"]):null;

				$ddtime = strtotime($row->docDate);

				$both = (isset($from) && isset($to) && $from <= $ddtime && $to >= $ddtime);
				$onlyFrom = (isset($from) && !isset($to) && $from <= $ddtime);
				$onlyTo = (!isset($from) && isset($to) && $to >= $ddtime);
				if ($both || $onlyFrom || $onlyTo)
					$res->put($row, $key);
			}

			return $res;
		}

		/**
		 * Return filtered FinModDataSheet by month
		 *
		 * Return FinModDataSheet filtered by DocDat with months/month to process next actions (queries)
		 * <code>$fmds->byDocDateMonths("7");</code>
		 * @param mixed $months
		 * @param integer $year
		 * @return \org\dibujo\finance\FinModDataSheet
		 */
		public function byDocDateMonths($months, $year = null) {
			$res = new self($this->baseaccnums);
			$rmonths = array();
			if (!isset($year))
				$year = date("Y");

			if (is_array($months))
				foreach ($months as $k => $m) {
					if (strpos("{$m}", ".") > -1) {
						list($mnum, $ynum) = explode(".", "{$m}");
						$rmonths[$k] = isset($ynum)?$m:"{$m}.{$year}";
					} else
						$rmonths[$k] = "{$m}.{$year}";
				}
			else {
				if (strpos("{$months}", ".") > -1) {
					list($mnum, $ynum) = explode(".", "{$months}");
					$rmonths[$k] = isset($ynum)?"{$months}":"{$months}.{$year}";
				} else
					$rmonths[] = "{$months}.{$year}";
			}

			foreach ($this->rows as $key => $row) {
				$ddtime = date("m.Y", strtotime($row->docDate));


				if (in_array($ddtime, $rmonths))
					$res->put($row, $key);
			}

			return $res;
		}
		/**
		 * Return filtered FinModDataSheet by quarter
		 *
		 * Return FinModDataSheet filtered by DocDat with quarters/quarter to process next actions (queries)
		 * Consts started with QUARTER_
		 * <code>$fmds->byDocDateQuarter(array(1, 2, 3))->quantity;</code>
		 *
		 * @param mixed $quarters
		 * @param integer $year
		 * @return \org\dibujo\finance\FinModDataSheet
		 */
		public function byDocDateQuarter($quarters, $year = null) {
			$_subq = array(
				self::QUARTER_FIRST => 	array(1, 2, 3),
				self::QUARTER_SECOND => array(4, 5, 6),
				self::QUARTER_THIRD => 	array(7, 8, 9),
				self::QUARTER_FOURTH => array(10, 11, 12)
			);
			$res = array();
			if (is_array($quarters)) {
				foreach ($quarters as $quart)
					if (isset($_subq[$quart]))
						$res = array_merge($res, $_subq[$quart]);
			} else if (isset($_subq[$quarters]))
				$res = $_subq[$quarters];

			return $this->byDocDateMonths($res, $year);
		}
		/**
		 * Return filtered FinModDataSheet by fin-period
		 *
		 * Return FinModDataSheet filtered by DocDat with fin-period to process next actions (queries)
		 * Consts started with FIN_PERIOD_
		 * <code>$fmds->byDocDateFinPeriod(FinModDataSheet::FIN_PERIOD_6)->income()->summary;</code>
		 *
		 * @param integer $period
		 * @param integer $year
		 * @return \org\dibujo\finance\FinModDataSheet
		 */
		public function byDocDateFinPeriod($period, $year = null) {
			$_subq = array(
				self::FIN_PERIOD_3 => self::QUARTER_FIRST,
				self::FIN_PERIOD_6 => array(
					self::QUARTER_FIRST, self::QUARTER_SECOND
				), self::FIN_PERIOD_9 => array(
					self::QUARTER_FIRST, self::QUARTER_SECOND, self::QUARTER_THIRD
				), self::FIN_PERIOD_12 => array(
					self::QUARTER_FIRST, self::QUARTER_SECOND,
					self::QUARTER_THIRD, self::QUARTER_FOURTH
				)
			);
			if (isset($_subq[$period]))
				$res = $_subq[$period];

			return $this->byDocDateQuarter($res, $year);
		}
		/**
		 * Return filtered FinModDataSheet by year
		 *
		 * Return FinModDataSheet filtered by DocDat with exact year to process next actions (queries)
		 * This is equivalent of byDocDateFinPeriod(self::FIN_PERIOD_12, $year);
		 *
		 * @param integer $year
		 * @return \org\dibujo\finance\FinModDataSheet
		 */
		public function byDocDateYear($year = null) {
			return $this->byDocDateFinPeriod(self::FIN_PERIOD_12, $year);
		}
		/**
		 * Return filtered FinModDataSheet by CBC
		 *
		 * Return FinModDataSheet filtered by DepCbc with exact cbcs/cbc to process next actions (queries)
		 * @param mixed $cbcs
		 * @return \org\dibujo\finance\FinModDataSheet
		 */
		public function byDepCbc($cbcs) {
			$res = new self($this->baseaccnums);
			if (is_array($cbcs)) {
				foreach ($this->rows as $key => $row) {
					if (in_array($row->depCbc, $cbcs)) {
						$res->put($row, $key);
					}
				}
			} else {
				foreach ($this->rows as $key => $row) {
					if ($row->depCbc == $cbcs) {
						$res->put($row, $key);
					}
				}
			}
			return $res;
		}
		/**
		 * Return filtered FinModDataSheet by Payment Order
		 *
		 * Return FinModDataSheet filtered by PaymentOrder with exact orders/order to process next actions (queries)
		 * @param mixed $order
		 * @return \org\dibujo\finance\FinModDataSheet
		 */
		public function byPaymentOrder($order) {
			$res = new self($this->baseaccnums);
			if (is_array($order)) {
				foreach ($this->rows as $key => $row) {
					if (in_array($row->paymentOrder, $order)) {
						$res->put($row, $key);
					}
				}
			} else {
				foreach ($this->rows as $key => $row) {
					if ($row->paymentOrder == $order) {
						$res->put($row, $key);
					}
				}
			}
			return $res;
		}
		/**
		 * Simple Summary
		 *
		 * Return quantity of money (it sub the money that was send from money that has been earned)
		 * @return integer
		 */
		public function summary() {
			$sum = 0;
			foreach ($this->rows as $row) {
				if (in_array($row->fromAcc, $this->baseaccnums)) {
					$sum -= doubleval($row->docSum);
				} else if (in_array($row->toAcc, $this->baseaccnums)) {
					$sum += doubleval($row->docSum);
				}
			}
			return $sum;
		}
		/**
		 * Get Sheet with only income rows
		 * @return \org\dibujo\finance\FinModDataSheet
		 */
		public function income() {
			$res = new self($this->baseaccnums);

			foreach ($this->rows as $key => $row) {
	// 			print_r($this->baseaccnums);
				if (in_array($row->toAcc, $this->baseaccnums)) {
					$res->put($row, $key);
				}
			}

			return $res;
		}

		/**
		 * Get Sheet with only outcome rows
		 * @return \org\dibujo\finance\FinModDataSheet
		 */
		public function outcome() {
			$res = new self($this->baseaccnums);

			foreach ($this->rows as $key => $row) {
				if (in_array($row->fromAcc, $this->baseaccnums)) {
					$res->put($row, $key);
				}
			}

			return $res;
		}
		/**
		 * Get values of the exact attribute
		 * @param string $attr
		 * @return multitype:string
		 */
		public function getAllValuesOfAttribute($attr) {
			$res = array();
			if (\in_array($attr, FinModRow::getAttrs())) {
				foreach ($this->rows as $key => $row)
					$res[$key] = (string) $row->$attr;
			}
			return $res;
		}


		/**
		 * @ignore
		 * @see Iterator::rewind()
		 */
		public function rewind() {
	        \reset($this->rows);
	    }
	    /**
	     * @ignore
	     * @see Iterator::current()
	     */
	    public function current() {
	        $var = \current($this->rows);
	        return $var;
	    }
	    /**
	     * @ignore
	     * @see Iterator::key()
	     */
	    public function key() {
	        $var = \key($this->rows);
	        return $var;
	    }
	    /**
	     * @ignore
	     * @see Iterator::next()
	     */
	    public function next() {
	        $var = \next($this->rows);
	        return $var;
	    }
	    /**
	     * @ignore
	     * @see Iterator::valid()
	     */
	    public function valid() {
	        $key = \key($this->rows);
	        $var = ($key !== null && $key !== false);
	        return $var;
	    }


	    /**
	     * @ignore
	     */
	    public function __toString() {
	    	$line = "";
	    	foreach ($this as $key => $val) {
	    		$line .= "{$key}) \t {$val}\n";
	    	}
	    	return $line;
	    }
	    /**
	     * @ignore
	     */
	    public function __get($name) {
	    	if ($name == "quantity")
	    		return count($this->rows);
	    	else if ($name == "summary")
	    		return $this->summary();
	    	else
	    		return $this->$name;
	    }

	}
}
