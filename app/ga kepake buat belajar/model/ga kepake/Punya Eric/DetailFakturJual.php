<?php
class DetailFakturJual extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idfakturjual int
		-idbarang int
			(ib)
		-qsk int
			(qsk) qsk = qty secara kecil, kalo qty secara besar adanya di function getQTYSecaraBesar
		-hargajual int
			(hj)
		-discjual float
			(dj)
		-ishargaforkecil tinyint 
			(ihfk) karena ishargaforkecil tinyint, defaultnya selalu 0 / BOOLEAN_FALSE
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'dfakturjual';
		private $barang;

		const CONST_ERROR_BARANG = "ERROR # Barang Tidak ditemukan";
		const CONST_HARGA_FOR_KECIL = 1;
		const CONST_HARGA_FOR_BESAR = 0;
	/*--------------------------setter dan getter--------------------------*/
		public function getNamaSatuanKecil(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->barang->getNamaSatuanKecil();
			}return self::CONST_ERROR_BARANG;
		}
		public function getNamaSatuanBesar(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->barang->getNamaSatuanBesar();
			}return self::CONST_ERROR_BARANG;
		}
		public function getQTYSecaraBesar(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->barang->getQTYSecaraBesarByQSK($this->getQTYSecaraKecil());
			}return self::CONST_ERROR_BARANG;
		}
		public function getRatioSatuanKecil(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->barang->getRatioSatuanKecil();
			}return self::CONST_ERROR_BARANG;
		}
		public function getQTYSecaraKecil(){
			return ($this->qsk == "" ? "0" : $this->qsk);
		}
		public function getHargaJual(){
			return ($this->hargajual == "" ? "0" : $this->hargajual);
		}
		public function getDiscJual(){
			return ($this->discjual == "" ? "0" : $this->discjual);
		}
		public function getIsHargaForKecil(){
			return $this->ishargaforkecil;
		}
		public function getNamaBarang(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				return $this->barang->getNama();
			}return self::CONST_ERROR_BARANG;
		}
		public function getNamaSatuanTerpakai(){
			if($this->getIsHargaForKecil() == self::CONST_HARGA_FOR_KECIL){
				return $this->getNamaSatuanKecil();
			}else{
				//return pasti self::CONST_HARGA_FOR_BESAR
				return $this->getNamaSatuanBesar();
			}
		}
		
	/*---------------------------public logic------------------------------*/
		public function getDataBuatBarang(){
			$arr = array(
				"0" => $this->getHargaJual(),
				"1" => $this->getDiscJual(),
				"2" => $this->getIsHargaForKecil()
			);
			return $arr;
		}
		public function addDetailAndDoMutasiStok($idFaktur, $jsonDetailFaktur){
			$datas = json_decode($jsonDetailFaktur);
			if(sizeof($datas) <= 0)
				return self::CONST_INSERT_GAGAL;

			$dfjs = array();

			foreach($datas as $data){
				$idBarang = $data->ib;
				$qtySecaraKecil = $data->qsk;
				$hargaJual = $data->hj;
				$discJual = $data->dj;
				$isHargaForKecil = $data->ihfk;
				$dfj = new DetailFakturJual;
				$retNya = $dfj->addNew($idFaktur, $idBarang, $qtySecaraKecil, $hargaJual, $discJual, $isHargaForKecil);
				if($retNya == self::CONST_INSERT_GAGAL)
					return self::CONST_INSERT_GAGAL;

				array_push($dfjs, $dfj);
			}
			foreach($dfjs as $dfj){
				$dfj->doMutasiStokFakturJual();
			}
			return self::CONST_INSERT_SUKSES;
		}
		public function addNew($idFaktur, $idBarang, $qtySecaraKecil, $hargaJual, $discJual, $isHargaForKecil){
			try{	
				$this->idfakturjual = $idFaktur;
				$this->idbarang = $idBarang;
				$this->qsk = $qtySecaraKecil;
				$this->hargajual = $hargaJual;
				$this->discjual = $discJual;
				$this->ishargaforkecil = $isHargaForKecil;
				$this->save();
				return self::CONST_INSERT_SUKSES;
			}
			catch(Exception $e){
			}
			return self::CONST_INSERT_GAGAL;
		}
		public function getSubTotal(){
			$st = 0;
			if($this->getIsHargaForKecil() == self::BOOLEAN_TRUE){
				$st = $this->getHargaJual() * $this->getQTYSecaraKecil();
			}else{
				$st = $this->getHargaJual() * $this->getQTYSecaraBesar();
			}
			$st = $st * (100 - $this->getDiscJual()) / 100;
			return $st;
		}
		public function getDataHistory(){
			$arr = array(
				"0" => $this->getNamaBarang(),
				"1" => $this->getTextRp($this->getQTYSecaraKecil()),
				"2" => $this->getNamaSatuanKecil(),
				"3" => $this->getQTYSecaraBesar(),
				"4" => $this->getNamaSatuanBesar(),
				"5" => $this->getTextRp($this->getHargaJual()),
				"6" => $this->getDiscJual(),
				"7" => $this->getIsHargaForKecil(),
				"8" => $this->getTextRp($this->getSubTotal()),
			);
			return $arr;
		}
		public function getDataRecreate(){
			$arr = array(
				"0" => $this->idbarang,
				"1" => $this->getQTYSecaraKecil(),
				"2" => $this->getQTYSecaraBesar(),
				"3" => $this->getHargaJual(),
				"4" => $this->getDiscJual(),
				"5" => $this->getIsHargaForKecil(),
				"6" => $this->getNamaBarang(),
				"7" => $this->getNamaSatuanBesar(),
				"8" => $this->getNamaSatuanKecil(),
			);
			return $arr;
		}
		public function doMutasiStokFakturJualDeleted(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				$tipeMutasi = DetailMutasi::CONST_TIPE_MUTASI_FAKTUR_JUAL_DELETED;
				return $this->barang->doMutasiStok($this->qsk, $tipeMutasi, $this->idfakturjual);
			}return self::CONST_ERROR_BARANG;
		}
		public function getDataDetailPrint(){
			return array(
				"0" => $this->getNamaBarang(),
				"1" => $this->getQtyLengkap(),
				"2" => $this->getHargaForDetailPrint(),
				"3" => $this->getDiscountForDetailPrint(),
				"4" => $this->getSubTotalForDetailPrint(),
				"5" => $this->getQtyLengkapForPrint()
			);
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
		private function getBarang(){
			if($this->barang == null){
				$this->barang = Barang::find($this->idbarang);
				if($this->barang == null) return self::CONST_GET_OBJECT_INSIDE_CLASS_GAGAL;
				else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
			}else return self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS;
		}
		private function doMutasiStokFakturJual(){
			if($this->getBarang() == self::CONST_GET_OBJECT_INSIDE_CLASS_SUCCESS){
				$tipeMutasi = DetailMutasi::CONST_TIPE_MUTASI_FAKTUR_JUAL;
				return $this->barang->doMutasiStok($this->qsk, $tipeMutasi, $this->idfakturjual);
			}return self::CONST_ERROR_BARANG;
		}
		private function getQtyLengkap(){
			return $this->getQTYSecaraBesar() . "" . $this->getNamaSatuanBesar() 
			. " = " . $this->getQTYSecaraKecil() . "" . $this->getNamaSatuanKecil();
		}
		private function getQtyLengkapForPrint(){
			return $this->getQTYSecaraBesar() . "" . $this->getNamaSatuanBesar() 
			. " @ " . $this->getRatioSatuanKecil() . "" . $this->getNamaSatuanKecil();	
		}
		private function getHargaForDetailPrint(){
			return $this->getTextRp($this->getHargaJual()) . " / " . $this->getNamaSatuanTerpakai();
		}
		private function getDiscountForDetailPrint(){
			return $this->getDiscJual() . " %";
		}
		private function getSubTotalForDetailPrint(){
			return "Rp " . $this->getTextRp($this->getSubTotal());
		}
}