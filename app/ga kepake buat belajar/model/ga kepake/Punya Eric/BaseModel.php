<?php
class BaseModel extends Eloquent{

	/*-----------------------------attribute-------------------------------*/
		public $timestamps = false;
		const CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS = "12345";
		const CONST_GET_OBJECT_INSIDE_CLASS_GAGAL = "12346";
		const CONST_INSERT_SUKSES = "1";
		const CONST_INSERT_GAGAL = "0";
		const BOOLEAN_TRUE = "1";
		const BOOLEAN_FALSE = "0";
		const CONST_DELETE_SUKSES = "1";
		const CONST_DELETE_GAGAL = "0";
		const CONST_UPDATE_SUKSES = "1";
		const CONST_UPDATE_GAGAL = "0";
		const FAIL_DELETE_PASSWORD_NOT_MATCH = "999887";
		const CONST_INSERT_GAGAL_NAMA_SUDAH_ADA = "1010";
		const CONST_INSERT_GAGAL_ORANG_SUDAH_ADA = "-9876543";
	/*-----------------------------public-------------------------------*/
		public function scopeIdDescending($query){
			return $query->orderBy("id","DESC");
		}
	/*-----------------------------protected-------------------------------*/
		protected function getString_ConditionsBeginsWith($text){
			return $text . "%";
		}
		protected function getTextRp($stringNya){
			return number_format($stringNya, 0, ",", ".");
		}
}
