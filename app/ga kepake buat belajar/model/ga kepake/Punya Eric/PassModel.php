<?php
class PassModel extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'passmodel';
		const CONST_PASSWORD_DELETE_BARANG = "ericadmin";
		const CONST_PASSWORD_DELETE_NS = "ericadmin";
		const CONST_PASSWORD_DELETE_CUS = "ericadmin";
		const CONST_PASSWORD_DELETE_SUP = "ericadmin";
		const CONST_PASSWORD_DELETE_RS = "ericadmin";
		const CONST_PASSWORD_DELETE_FJ = "ericadmin";
		const CONST_PASSWORD_DELETE_FB = "ericadmin";
	/*--------------------------setter dan getter--------------------------*/
	/*---------------------------public logic------------------------------*/
		public function isPasswordValidForDeleteBarang($textPassword){
			return ($textPassword == self::CONST_PASSWORD_DELETE_BARANG);
		}
		public function isPasswordValidForDeleteNS($textPassword){
			return ($textPassword == self::CONST_PASSWORD_DELETE_NS);
		}
		public function isPasswordValidForDeleteCus($textPassword){
			return ($textPassword == self::CONST_PASSWORD_DELETE_CUS);
		}
		public function isPasswordValidForDeleteSup($textPassword){
			return ($textPassword == self::CONST_PASSWORD_DELETE_SUP);
		}
		public function isPasswordValidForDeleteRS($textPassword){
			return ($textPassword == self::CONST_PASSWORD_DELETE_RS);
		}
		public function isPasswordValidForDeleteFJ($textPassword){
			return ($textPassword == self::CONST_PASSWORD_DELETE_FJ);
		}
		public function isPasswordValidForDeleteFB($textPassword){
			return ($textPassword == self::CONST_PASSWORD_DELETE_FB);
		}
		
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
}
