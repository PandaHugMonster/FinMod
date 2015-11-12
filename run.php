#!/usr/bin/php
<?php
/**
 * Run-file for development process
 * @author PandaHugMonster <ivan.ponomarev.pi@gmail.com>
 * @license The MIT License (MIT)
 *
 */
	$mod = "FinModRaif";
	include_once "classes/FinMod.php";
	include_once "classes/FinModRow.php";
	include_once "classes/FinModDataSheet.php";
	include_once "classes/modules/{$mod}.php";
	include_once "classes/FinModParser.php";

	use org\dibujo\finance\FinModParser;
	use org\dibujo\finance\modules\FinModRaif;
	use org\dibujo\finance\FinModRow;
	use org\dibujo\finance\FinModDataSheet;

// 	$filename = "xmls/finacc_report_05-10.2014.xml";
 	$filename = "xmls/finacc_report_27-03.2015.xml";

	if (file_exists($filename)) {
		$finmodp = new FinModParser("\\org\\dibujo\\finance\\modules\\{$mod}", $filename);
		$finmodp = $finmodp->parse();

		$cbcs = array(
// 			"18210102010011000110", // Налог на доходы физических лиц с доходов, источником которых является налоговый агент
			"39310202090071000160", // Оплата 2.9% ФСС
			"39310202050071000160", // Оплата 0.2% ФСС
			"39210202010061000160", // ПФР страховая часть
			"39210202101081011160", // ФФОМС
// 			"18210501011011000110" 	// Авансовый платёж по УСН6
		);

		//foreach ($finmodp as $t)
// 			echo $finmodp;
// 		$finmodp->
// 		echo $finmodp->quantity;
// 		echo $finmodp->byDocDateMonths("7");
// 		echo $finmodp->byDocDateQuarter()->quantity;
// 		echo $finmodp->byDocDateQuarter(FinModDataSheet::QUARTER_THIRD);
// 		echo $finmodp->byDocDateFinPeriod(FinModDataSheet::FIN_PERIOD_6)->quantity;
// 		echo $finmodp->byDocDateFinPeriod(FinModDataSheet::FIN_PERIOD_6)->summary;
// 		echo $finmodp->byDocDateFinPeriod(FinModDataSheet::FIN_PERIOD_9)->income()->summary;
		$res = 0;
		$quarts = [
			FinModDataSheet::QUARTER_FIRST,
			FinModDataSheet::QUARTER_SECOND,
			FinModDataSheet::QUARTER_THIRD,
			FinModDataSheet::QUARTER_FOURTH
		];
		$prevInc = 0;
		foreach ($quarts as $quart) {

			$income = $finmodp->byDocDateQuarter($quart, 2014)->income()->summary;
			$prevInc += $income;
			$paysout = abs($finmodp->byDocDateQuarter($quart, 2014)->byDepCbc($cbcs)->outcome()->summary);

			$fullTax = $income * 6 / 100;
			$halfTax = floor($fullTax / 2);

			$resQ[$quart] = $fullTax - ($halfTax > $paysout?$paysout:$halfTax);
			$res += $resQ[$quart];

			$paysout2 = abs($finmodp->byDocDateQuarter($quart + 1, 2014)->byDepCbc(["18210501011011000110"])->outcome()->summary);

//			echo "Квартал [{$quart}]: {$resQ[$quart]}\t";
//   			echo "Доход квартал [{$quart}]: {$prevInc} ({$income})\n";
			if ($paysout2 > 0) {
//				echo " | (Уплачено: {$paysout2})";
				$res -= $paysout2;
			}
//			echo " | [Полный налог: {$fullTax}; Выплаты: {$paysout}; Весь доход: {$income}]\n";

		}

// 		echo "Сумма всех налогов: {$res}\n";

// 		echo $finmodp->byDocDateQuarter(FinModDataSheet::QUARTER_SECOND, 2014)->byDepCbc($cbcs)->summary;
// 		echo $finmodp;
		$sum = $i = 0;
		foreach ($finmodp->byDocDateYear(2014)->outcome() as $row) {
			if ($row->to == 'Пономарев Иван Андреевич') {
				$sum += $row->docSum;
				$i++;
				echo "{$i}: {$row->docSum} руб. \t\t{$row->purpose} [{$row->docDate}]\n";
			}
		}

		echo $sum;

//		echo $finmodp->byDepCbc($cbcs)->quantity;
// 		echo $finmodp->byDepCbc($cbcs)->summary;
// 		echo $finmodp->byDepCbc($cbcs)->summary;
// 		print_r(FinModRow::getAttrs());

// 		echo $finmodp->byDepCbc($cbcs)->byDocDateFinPeriod(FinModDataSheet::FIN_PERIOD_9)->outcome()->quantity;
// 		echo $finmodp->byDepCbc($cbcs)->byDocDateQuarter(FinModDataSheet::QUARTER_THIRD)->outcome();
// 		echo $finmodp->byDepCbc($cbcs)->byDocDateQuarter(FinModDataSheet::QUARTER_THIRD)->outcome()->summary;
// 		echo $finmodp->byDepCbc($cbcs)->byDocDateFinPeriod(FinModDataSheet::FIN_PERIOD_12)->outcome()->summary;

	} else
		exit("Не смог открыть файл: {$filename}");

	echo "\n";
