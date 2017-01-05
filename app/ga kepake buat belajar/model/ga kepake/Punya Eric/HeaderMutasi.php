<?php
class HeaderMutasi extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-tgl date
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'mutasi';
		const CONST_HEADER_TIDAK_DITEMUKAN = "19999";
	/*--------------------------setter dan getter--------------------------*/
	/*---------------------------public logic------------------------------*/
		public function addPerbedaanStokBarangToMutasiTable($idBarang, $stokAwal, $stokAkhir, $tipeMutasi, $idFaktur){
			$tf = new TF;
			$tgl = $tf->FzyDateVersion(2);
			$hm = self::whereRaw("tgl = ?", array($tgl))->first();
			if($hm == null){
				$hm = new HeaderMutasi;
				$hm->addNew($tgl);
			}
			$dm = new DetailMutasi;
			$dm->addPerbedaanStokBarangToMutasiTable($hm->id, $idBarang, $stokAwal, $stokAkhir, $tipeMutasi, $idFaktur);
		}

		public function getDataHistory($tgl, $idBarang){
			$tgl = $this->getAfterValidateTgl($tgl);
			$HM = self::whereRaw("tgl = ?", array($tgl))->first();

			if($HM == null){
				return self::CONST_HEADER_TIDAK_DITEMUKAN;
			}else{
				$idHM = $HM->id;
				$datas = DetailMutasi::whereRaw("idmutasi = ? and idbarang = ?", array($idHM, $idBarang))->get();

				$arr = array();
				foreach($datas as $data){
					array_push($arr, $data->getData());
				}
				return $arr;
			}
		}


	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function addNew($tglNya){
			$this->tgl = $tglNya;
			$this->save();
		}

		private function getAfterValidateTgl($tgl){
			if($tgl == null || $tgl == ""){
				$tf = new TF;
				return $tf->FzyDateVersion(2);
			}
			else return $tgl;
		}
}
