<?php
/**
 * Class-module for processing report-files
 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
 * @license The MIT License (MIT)
 *
 */
namespace org\dibujo\finance\modules;
use \org\dibujo\finance\FinMod;
use \org\dibujo\finance\FinModRow;
{
	/**
	 * Class-module for processing report-files
	 *
	 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
	 * @version FinModRaif:0.0.1
	 */
	class FinModRaif extends FinMod {

		/**
		 * The string-id or url to identify the file or source correctly
		 * @var string
		 */
		const VALIDITY = "http://bssys.com/upg/response";

		/**
		 * Method that caches rows to process them faster,
		 * it usually uses only once per instance
		 *
		 * @see \org\dibujo\finance\FinMod::cacheIt()
		 */
		protected function cacheIt() {
			$raif = $this->_xml;
			$_c = 0;

			$beep = $beep2 = false;
			if (!isset($this->_rowsquant)) {
				$beep = true;
				$this->_rowsquant = 0;
			}
			if (!isset($this->_baseAccountNumbers)) {
				$beep2 = true;
				$this->_baseAccountNumbers = array();
			}

			foreach ($raif->StatementsRaif->StatementRaif as $statement) {
				if ($beep)
					$this->_rowsquant += count($statement->Docs->TransInfo);

				if ($beep2)
					$this->_baseAccountNumbers[] = (string) $statement["acc"];

				foreach ($statement->Docs->TransInfo as $ti) {
					$row = new FinModRow();
					// HERE
					$map = array(
							"externalId" => $ti["extId"],
							"extraExternalId" => $statement["extId"],
							"docDate" => $ti["docDate"],
							"docSum" => $ti["docSum"],
							"processDate" => $ti["operDate"],
							"fromAcc" => $ti["corrAcc"],
							"toAcc" => $ti["personalAcc"],
							"fromBank" => $ti["bank"],
							"toBank" => $ti["receiverBankName"],
							"fromBic" => $ti["payerBankBic"],
							"toBic" => $ti["corrBIC"],
							"fromCorrAcc" => $ti["payerBankCorrAccount"],
							"toCorrAcc" => $ti["receiverBankCorrAccount"],
							"fromINN" => $ti["personalINN"],
							"toINN" => $ti["receiverINN"],
							"fromKPP" => $ti["personalKPP"],
							"toKPP" => $ti["receiverKPP"],
							"fromCurrCode" => $ti["payerCurrCode"],
							"toCurrCode" => $ti["receiverCurrCode"],
							"paymentOrder" => $ti["paymentOrder"],
							"from" => $ti->PersonalName,
							"to" => $ti["receiptName"],
							"purpose" => $ti->Purpose,
							"depCbc" => $ti->DepartmentalInfo["cbc"],
							"depDocDate" => $ti->DepartmentalInfo["docDate"],
							"depDrawerStatus" => $ti->DepartmentalInfo["drawerStatus"],
							"depOkato" => $ti->DepartmentalInfo["okato"],
							"depPaymentTypeReason" => $ti->DepartmentalInfo["paytReason"],
							"depPaymentTypeKind" => $ti->DepartmentalInfo["taxPaytKind"],
							"depTaxPeriod" => $ti->DepartmentalInfo["taxPeriod"],
							"depDocNo" => $ti->DepartmentalInfo["docNo"]
					);
					foreach ($map as $key => $val)
						$row->$key = $val;

					////
					$this->_cache[$_c++] = $row;
				}
			}
		}

		/**
		 * Return the row defined by $index
		 *
		 * @see \org\dibujo\finance\FinMod::row()
		 * @param integer $index
		 */
		protected function row($index) {
			if (!isset($this->_cache)) {
				$this->cacheIt();
			}
			return $this->_cache[$index];
		}

		/**
		 * Method returns the quantity of rows
		 * a number of rows is cached by the call of cacheIt
		 *
		 * @see \org\dibujo\finance\FinMod::quantity()
		 */
		protected function quantity() {
			$raif = $this->_xml;
			if (!isset($this->_rowsquant)) {
				$this->_rowsquant = 0;
				foreach ($raif->StatementsRaif->StatementRaif as $statement)
					$this->_rowsquant += count($statement->Docs->TransInfo);
			}

			return $this->_rowsquant;
		}


	}
}
