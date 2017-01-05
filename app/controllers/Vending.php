<?php

class Vending extends BaseController {

	const c_startVendingIsNotAvailable = "Sudah Ada Vending Yang Masih Active";

	public function getIdVendingActiveOrNull(){
		$idUser = $this->getIdUser();

		$vending = new VendingM;
		return $vending->getIdVendingActiveOrNull($idUser);
	}

	public function startVending(){
		$idUser = $this->getIdUser();

		if($this->isStartVendingAvailable($idUser)){
			$vending = new VendingM;
			$vending->insertVending($idUser);
			return $vending->id;
		}else{
			return self::c_startVendingIsNotAvailable;
		}
	}

	private function isStartVendingAvailable($idUser){
		$vending = new VendingM;
		return $vending->isStartVendingAvailable($idUser);
	}

}
