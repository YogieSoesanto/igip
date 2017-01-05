<?php
class DetailMutasi extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
		-idmutasi int
		-idbarang int
		-stokawal int
		-stokakhir int
			stokawal dan stokakhir sama2 secara kecil
		-tipemutasi
			untuk tipe Mutasi, lihat CONST_TIPE_MUTASI
		-idfaktur
			untuk idfaktur ini bergantung sama tipemutasi, 
			bisa headerfakturjual, headerfakturbeli, headeropname
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = 'dmutasi';
		const CONST_TIPE_MUTASI_FAKTUR_JUAL = 1;
		const CONST_TIPE_MUTASI_FAKTUR_BELI = 2;
		const CONST_TIPE_MUTASI_STOK_OPNAME = 3;

		const CONST_TIPE_MUTASI_FAKTUR_JUAL_DELETED = 4;
		const CONST_TIPE_MUTASI_FAKTUR_BELI_DELETED = 5;
		const FAKTUR_TIDAK_DITEMUKAN = "-999999";
	/*--------------------------setter dan getter--------------------------*/
		public function getTipeMutasi(){
			return $this->tipemutasi;
		}
		public function getStokAwal(){
			return $this->stokawal;
		}
		public function getStokAkhir(){
			return $this->stokakhir;
		}
		public function getIdFaktur(){
			return $this->idfaktur;
		}
		public function getNamaOrang(){
			$tipe = $this->getTipeMutasi();
			if($tipe == self::CONST_TIPE_MUTASI_STOK_OPNAME){
				return "Admin";
			}
			else if($tipe == self::CONST_TIPE_MUTASI_FAKTUR_JUAL || $tipe == self::CONST_TIPE_MUTASI_FAKTUR_JUAL_DELETED){
				$faktur = FakturJual::find($this->getIdFaktur());
				if($faktur == NULL)return self::FAKTUR_TIDAK_DITEMUKAN;
				return $faktur->getNamaOrang();
			}
			else if($tipe == self::CONST_TIPE_MUTASI_FAKTUR_BELI || $tipe == self::CONST_TIPE_MUTASI_FAKTUR_BELI_DELETED){
				$faktur = FakturBeli::find($this->getIdFaktur());
				if($faktur == NULL)return self::FAKTUR_TIDAK_DITEMUKAN;
				return $faktur->getNamaOrang();
			}
			
		}
	/*---------------------------public logic------------------------------*/
		public function addPerbedaanStokBarangToMutasiTable($idMutasi, $idBarang, $stokAwal, $stokAkhir, $tipeMutasi, $idFaktur){
			$this->idmutasi = $idMutasi;
			$this->idbarang = $idBarang;
			$this->stokawal = $stokAwal;
			$this->stokakhir = $stokAkhir;
			$this->tipemutasi = $tipeMutasi;
			$this->idfaktur = $idFaktur;
			$this->save();
		}
		public function getData(){
			return array(
				"0" => $this->getTipeMutasi(),
				"1" => $this->getStokAwal(),
				"2" => $this->getStokAkhir(),
				"3" => $this->getIdFaktur(),
				"4" => $this->getNamaOrang(),
			);
		}
		public function checkBarangBolehDelete($idnya){
			$byk = DB::table($this->table)->whereRaw("idbarang = ?", array($idnya))->take(1)->count();
			if($byk <= 0)return true;
			return false;
		}
	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
}
