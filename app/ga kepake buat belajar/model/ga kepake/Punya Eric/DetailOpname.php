<?php
class DetailOpname extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idopname int
		-idbarang int
			(ib)
		-stokakhir int
			(sa)stokakhir dalam bentuk kecil
		-tgl datetime
		-alasan varchar(500)
			(al)alasan boleh null
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'dopname';
		private $barang;
	/*--------------------------setter dan getter--------------------------*/
		public function getAlasan(){
			return $this->alasan;
		}
		public function getJamUbah(){
			$tf = new TF;
			return $tf->FzyDateVersion(4, strtotime($this->tgl));
		}
		public function getStokAkhir(){
			return $this->stokakhir;
		}
		public function getDataBarang(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->barang->getDataForDetailOpname();
			}return self::CONST_ERROR_BARANG;
		}
	/*---------------------------public logic------------------------------*/
		public function doOpname($idOpname, $idBarang, $stokAkhir, $tglJam, $alasan){
			$this->addNew($idOpname, $idBarang, $stokAkhir, $tglJam, $alasan);
			return $this->doMutasiStokOpname();
		}
		public function getDataHistory(){
			return array(
				"0" => $this->getDataBarang(),
				"1" => $this->getStokAkhir(),
				"2" => $this->getJamUbah(),
				"3" => $this->getAlasan(),
			);
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function addNew($idOpname, $idBarang, $stokAkhir, $tglJam, $alasan){
			$this->idopname = $idOpname;
			$this->idbarang = $idBarang;
			$this->stokakhir = $stokAkhir;
			$this->tgl = $tglJam;
			$this->alasan = $alasan;
			$this->save();
		}
		private function getBarang(){
			if($this->barang == null){
				$this->barang = Barang::find($this->idbarang);
				if($this->barang == null) return self::CONST_GET_OBJECT_INSIDE_CLASS_GAGAL;
				else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
			}else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
		}
		private function doMutasiStokOpname(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				$tipeMutasi = DetailMutasi::CONST_TIPE_MUTASI_STOK_OPNAME;
				return $this->barang->doMutasiStok($this->stokakhir, $tipeMutasi, $this->idopname);
			}return self::CONST_ERROR_BARANG;
		}

}
