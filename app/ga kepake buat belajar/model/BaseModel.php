<?php
class BaseModel extends Eloquent{

	/*-----------------------------attribute-------------------------------*/
		public $timestamps = false;
		protected $fzyTimeFormat;

		const Boolean_TRUE = 1;
		const Boolean_FALSE = 0;

		const GetData_TIPEDEFAULT = -1;
		const FormatLike_MENGANDUNG = -1;

		const CONST_RETURN_ERROR_TEXT = "ERROR";

	/*-----------------------------public-------------------------------*/
		public function scopeIdDescending($query){
			return $query->orderBy("id","DESC");
		}
	/*-----------------------------protected-------------------------------*/
		protected function RpNumberFormat($stringNya){
			return number_format($stringNya, 0, ",", ".");
		}
		protected function FzyDateVersion($dateVersion, $timeNya = FzyTimeFormat::INT_NO_TIME_DEFINED){
			if($this->fzyTimeFormat == NULL)$this->fzyTimeFormat = new FzyTimeFormat;
			return $this->fzyTimeFormat->FzyDateVersion($dateVersion, $timeNya);
		}
}
