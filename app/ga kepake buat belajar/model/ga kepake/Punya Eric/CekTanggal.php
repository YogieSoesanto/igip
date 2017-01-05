<?php
class CekTanggal extends BaseModel{

	/*-----------------------start field dan comment-----------------------
		-id int
	-------------------------end field dan comment------------------------*/
	/*-----------------------------attribute-------------------------------*/
		protected $table = '';
		const CONST_TANGGAL_MINIMAL_SATU = 1;
		const CONST_TANGGAL_GA_ADA_DATA = 0;
	/*--------------------------setter dan getter--------------------------*/
	/*---------------------------public logic------------------------------*/
		public function ctOpname($tgl){
			$data = HeaderOpname::whereRaw("tgl = ?", array($tgl))->first();
			if($data == NULL)
				return self::CONST_TANGGAL_GA_ADA_DATA;
			else
				return self::CONST_TANGGAL_MINIMAL_SATU;
		}

		public function ctFakturJual($tgl){
			$tglStart = $tgl . " 00:00:00";
			$tglEnd = $tgl . " 23:59:59";
			$data = FakturJual::whereRaw("tanggal >= ? and tanggal <= ?", array($tglStart, $tglEnd))->first();
			if($data == NULL)
				return self::CONST_TANGGAL_GA_ADA_DATA;
			else
				return self::CONST_TANGGAL_MINIMAL_SATU;
		}
		public function ctFakturBeli($tgl){
			$tglStart = $tgl . " 00:00:00";
			$tglEnd = $tgl . " 23:59:59";
			$data = FakturBeli::whereRaw("tanggal >= ? and tanggal <= ?", array($tglStart, $tglEnd))->first();
			if($data == NULL)
				return self::CONST_TANGGAL_GA_ADA_DATA;
			else
				return self::CONST_TANGGAL_MINIMAL_SATU;
		}
		public function coFakturJual($idCustomer){
			$data = FakturJual::whereRaw("idcustomer = ?", array($idCustomer))->first();
			if($data == NULL)
				return self::CONST_TANGGAL_GA_ADA_DATA;
			else
				return self::CONST_TANGGAL_MINIMAL_SATU;
		}
		public function coFakturBeli($idCustomer){
			$data = FakturBeli::whereRaw("idsupplier = ?", array($idCustomer))->first();
			if($data == NULL)
				return self::CONST_TANGGAL_GA_ADA_DATA;
			else
				return self::CONST_TANGGAL_MINIMAL_SATU;
		}
		public function ctMutasi($tgl, $idB){
			$data = HeaderMutasi::whereRaw("tgl = ?", array($tgl))->first();
			if($data == NULL)
				return self::CONST_TANGGAL_GA_ADA_DATA;
			else{
				$dm = DetailMutasi::whereRaw("idmutasi = ? and idbarang = ?", array($data->id, $idB))->first();
				if($dm == NULL)
					return self::CONST_TANGGAL_GA_ADA_DATA;
				else
					return self::CONST_TANGGAL_MINIMAL_SATU;
			}
		}


	/*----------------------------protected logic--------------------------*/
	/*----------------------------private logic----------------------------*/
}
