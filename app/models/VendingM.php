<?php
class VendingM extends BaseModel{

	protected $table = 'vending';

	protected $colNames = array(
		"id",
		"iduser",
		"isactive",
	);

	public function getIdVendingActiveOrNull($idUser){
		$res = self::whereRaw( $this->colNames[1] . " = ? and " . $this->colNames[2] . " = 1" , array( $idUser ))->get();
		if($res->isEmpty())
			return NULL;
		else
			return $res;
	}

	public function isStartVendingAvailable($idUser){
		$res = self::whereRaw( $this->colNames[1] . " = ? and " . $this->colNames[2] . " = 1" , array( $idUser ))->get();
		if($res->isEmpty())
			return true;
		else
			return false;
	}

	public function insertVending($idUser){
		$this->iduser = $idUser;
		$this->isactive = 1;
		$this->save();
	}

	public function Get_DataVending_ForSearchVending($idItem){
		$res = self::whereRaw("id in (SELECT distinct idvending from itemvending where iditem = ?)", array($idItem))->get();
		return ($res);
	}

}
