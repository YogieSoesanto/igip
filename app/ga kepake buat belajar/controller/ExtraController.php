<?php
class ExtraController extends BaseController {
	public function showViewLandingPage(){
		return View::make("extra.landing");
	}

	public function showViewPertanyaanUmum(){
		return View::make("extra.questions");
	}

	public function showHome(){
		return View::make("extra.homebaru");
	}

	public function showSyaratDanKetentuan(){
		return View::make("extra.syaratdanketentuan");
	}

	public function showOnlyForGuest(){
		return View::make("extra.onlyforguest");
	}

	public function showRouteNotFound(){
		return View::make("extra.routenotfound");
	}

	public function showRouteInternalError(){
		return View::make("extra.routeinternalerror");
	}

	public function showInfoResto(){
		return View::make("extra.inforesto");
	}

	public function showUndianGenerator(){
		return View::make("extra.generator");
	}
	

	

	
}
