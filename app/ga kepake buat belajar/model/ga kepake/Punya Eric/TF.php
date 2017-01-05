<?php

class TF{
	const INT_NO_TIME_DEFINED = -99998;
	const STR_FORMAT_TANGGAL_INDONESIA = "TANGGAL_INDONESIA";
	const STR_FORMAT_TANGGAL_DAN_JAM_INDONESIA = "TANGGAL_INDONESIAV2";
	const STR_FORMAT_BUAT_BACKUP = "BUAT_BACK_UP";

	public function convertTanggal($dateVersion, $strTimeNya){
		return $this->FzyDateVersion($dateVersion, strtotime($strTimeNya));
	}
	public function FzyDateVersion($dateVersion, $timeNya = self::INT_NO_TIME_DEFINED){
		if($dateVersion == 1){
			return $this->getTheDate("Y-m-d H:i:s", $timeNya);
		}else if($dateVersion == 2){
			return $this->getTheDate("Y-m-d", $timeNya);
		}else if($dateVersion == 3){
			return $this->getTheDate(self::STR_FORMAT_TANGGAL_INDONESIA, $timeNya);
		}else if($dateVersion == 4){
			return $this->getTheDate("H:i:s", $timeNya);
		}else if($dateVersion == 5){
			return $this->getTheDate(self::STR_FORMAT_TANGGAL_DAN_JAM_INDONESIA, $timeNya);
		}else if($dateVersion == 6){
			return $this->getTheDate(self::STR_FORMAT_BUAT_BACKUP, $timeNya);
		}
	}
	private function getTheDate($format, $timeNya){
		if($format == self::STR_FORMAT_TANGGAL_INDONESIA){
			if($timeNya == self::INT_NO_TIME_DEFINED){
				return date("d") . " " . $this->getBulanIndonesia(date("m")) . " " . date("Y");
			}else{
				return date("d", $timeNya) . " " . $this->getBulanIndonesia(date("m", $timeNya)) . " " . date("Y", $timeNya);
			}
		}
		else if($format == self::STR_FORMAT_TANGGAL_DAN_JAM_INDONESIA){
			if($timeNya == self::INT_NO_TIME_DEFINED){
				return date("d") . " " . $this->getBulanIndonesia(date("m")) . " " . date("Y") . " " . date("H:i:s");
			}else{
				return date("d", $timeNya) . " " . $this->getBulanIndonesia(date("m", $timeNya)) . " " . date("Y", $timeNya) .  " " . date("H:i:s", $timeNya);
			}
		}
		else if($format == self::STR_FORMAT_BUAT_BACKUP){
			return $this->FzyDateVersion(2) . "_JAM_" . date("H-i-s");
		}
		else{
			if($timeNya == self::INT_NO_TIME_DEFINED){
				return date($format);
			}else{
				return date($format, $timeNya);
			}
		}
	}
	private function getBulanIndonesia($textNya){
		return ($textNya == "01" ? "Januari" : ($textNya == "02" ? "Februari" : ($textNya == "03" ? "Maret" : ($textNya == "04" ? "April" : ($textNya == "05" ? "Mei" : ($textNya == "06" ? "Juni" : ($textNya == "07" ? "Juli" : ($textNya == "08" ? "Agustus" : ($textNya == "09" ? "September" : ($textNya == "10" ? "Oktober" : ($textNya == "11" ? "November" : "Desember")))))))))));
	}
}