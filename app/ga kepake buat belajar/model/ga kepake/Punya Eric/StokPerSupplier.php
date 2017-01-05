<?php
class StokPerSupplier extends Barang{

	/*-----------------------start field dan comment-----------------------
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
	/*--------------------------setter dan getter--------------------------*/
	/*---------------------------public logic------------------------------*/
		public function getDataSPS($idSupplier){
			$barangs = Barang::whereRaw("idsupplier = ?", array($idSupplier))->orderBy("stokkecil","desc")->get();
			$arr = array();
			foreach($barangs as $brg){
				array_push($arr, $brg->getData());
			}
			return $arr;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
}
