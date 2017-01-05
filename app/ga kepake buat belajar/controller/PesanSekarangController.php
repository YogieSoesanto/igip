<?php
class PesanSekarangController extends BaseController {
	const INT_DEFAULT_PAGE_KE = 1;
	const INT_DEFAULT_DATA_PER_PAGE = 20;
	const STR_NO_RESTO_FOUND_FOR_THESE_META = "NO RESTO FOUND FOR THESE META";

	public function showSearchEngine(){
		//tampilin halaman pesan otomatis nih
		return View::make("pesansekarang.pesanotomatis");
	}

	public function getIconicRestoByName($name, $pageKe = self::INT_DEFAULT_PAGE_KE, $dataPerPage = self::INT_DEFAULT_DATA_PER_PAGE){
		$dataReturn = array(
			"arrayDataIconic" => $this->getArrayDataIconic($name, $pageKe, $dataPerPage),
			"defaultPathLogo" => Resto::getDefaultPathLogo(),
			"pathLogoForAllResto" => Resto::getPathLogoForAllResto(),
		);
		return json_encode($dataReturn);
		/* defaultPathLogo dan pathLogoForAllResto bakal berulang, jadi ga di dalam arrayDataIconic */
	}

	public function getBestRestoWithoutMeta(){
		
	}

	public function getBestRestoByMeta(){
		//skarang tinggal ambil DATA SEBANYAK N , dan bisa loadmore kan

		/*start ambil dari Input::get*/
		$temp = array();
		array_push($temp, "ayam");array_push($temp, "ayam bakar");array_push($temp, "keju");
		$inputanMeta = json_encode($temp);
		//return $inputanMeta;
		/*end ambil dari Input::get*/
		
		$arrIdMeta = $this->getArrayIDMetaDariInputan($inputanMeta);
		$restos = $this->getAllRestoYangMengandungMeta($arrIdMeta);
		
		if($restos === FALSE){
			return self::STR_NO_RESTO_FOUND_FOR_THESE_META;
		}else{
			foreach($restos as $resto){
				echo $resto->getNilaiRekomendasi($arrIdMeta) . "# Resto : ".$resto->nama."<br/>";
			}
		}
	}

	

	private function getArrayIDMetaDariInputan($inputanMeta){
		$arrId = array();
		$json = json_decode($inputanMeta);
		$byk = sizeof($json);
		for($i=0;$i<$byk;$i++){
			$meta = Meta::whereRaw("nama like ?", array($json[$i]))->first();
			if($meta != NULL){
				array_push($arrId, $meta->id);
			}else{
				Meta::addNew($json[$i]);
			}
		}
		return $arrId;
	}

	private function getAllRestoYangMengandungMeta($arrIdMeta){
		$arrIdRestoHasInserted = array();
		foreach($arrIdMeta as $idMeta){
			$arrIdRestoHasInserted = MetaResto::getIdRestoYangBelumDiArrayUntukMeta($idMeta, $arrIdRestoHasInserted);
		}
		$arrResto = Resto::getAllRestoWithId($arrIdRestoHasInserted);
		return $arrResto;
	}

	/*================================================private=====================================*/
	private function getArrayDataIconic($name, $pageKe, $dataPerPage){
		$skip = ($pageKe-1) * $dataPerPage;
		$restos = Resto::whereRaw("nama like ?", array("%".$name."%"))->skip($skip)->take($dataPerPage)->get();
		$arrIconicData = array();
		foreach($restos as $resto){
			array_push($arrIconicData, $resto->getIconicData());
		}
		return $arrIconicData;
	}
}
