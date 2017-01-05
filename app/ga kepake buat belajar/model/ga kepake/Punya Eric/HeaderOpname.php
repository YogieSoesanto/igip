<?php
class HeaderOpname extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-tgl date
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'opname';

		const CONST_HEADER_TIDAK_DITEMUKAN = "19999";
	/*--------------------------setter dan getter--------------------------*/
	/*---------------------------public logic------------------------------*/
		public function doOpname($textJSONDetailOpname){
			$datas = json_decode($textJSONDetailOpname);

			$tf = new TF;
			$tgl = $tf->FzyDateVersion(2);
			$ho = self::whereRaw("tgl = ?", array($tgl))->first();
			if($ho == null){
				$ho = new HeaderOpname;
				$ho->addNew($tgl);
			}
			$idOpname = $ho->id;
			$tglJam = $tf->FzyDateVersion(1);
			foreach($datas as $data){
				$idBarang = $data->ib;
				$stokAkhir = $data->sa;
				$alasan = $data->al;

				$do = new DetailOpname;
				$do->doOpname($idOpname, $idBarang, $stokAkhir, $tglJam, $alasan);
			}
			
		}

		public function getDataHistory($tglNya){
			$ho = self::whereRaw("tgl = ?", array($tglNya))->first();
			if($ho == NULL)
				return self::CONST_HEADER_TIDAK_DITEMUKAN;
			else{
				$arr = array();
				$dos = DetailOpname::whereRaw("idopname = ?", array($ho->id))->get();
				foreach($dos as $do){
					array_push($arr, $do->getDataHistory());
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
		
}
