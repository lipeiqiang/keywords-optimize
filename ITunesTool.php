<?php

require_once('./simple_html_dom.php');



class ITunesTool
{
	private $county_iso2store = '';
	public function __construct()
	{
		$this->county_iso2store = array(
				"ar"=>"143505",
				"au"=>"143460",
				"be"=>"143446",
				"br"=>"143503",
				"ca"=>"143455",
				"cl"=>"143483",
				"cn"=>"143465",
				"co"=>"143501",
				"cr"=>"143494",
				"cz"=>"143489",
				"dk"=>"143458",
				"de"=>"143443",
				"sv"=>"143506",
				"es"=>"143454",
				"fi"=>"143447",
				"fr"=>"143442",
				"gr"=>"143448",
				"gt"=>"143504",
				"hk"=>"143463",
				"hu"=>"143482",
				"in"=>"143467",
				"id"=>"143476",
				"ie"=>"143449",
				"il"=>"143491",
				"it"=>"143450",
				"kr"=>"143466",
				"kw"=>"143493",
				"lb"=>"143497",
				"lu"=>"143497",
				"my"=>"143473",
				"mx"=>"143468",
				"nl"=>"143452",
				"nz"=>"143461",
				"no"=>"143457",
				"at"=>"143445",
				"pk"=>"143477",
				"pa"=>"143485",
				"pe"=>"143507",
				"ph"=>"143474",
				"pl"=>"143478",
				"pt"=>"143453",
				"qa"=>"143498",
				"ro"=>"143487",
				"ru"=>"143469",
				"sa"=>"143479",
				"ch"=>"143459",
				"sg"=>"143464",
				"sk"=>"143496",
				"si"=>"143499",
				"za"=>"143472",
				"lk"=>"143486",
				"se"=>"143456",
				"tw"=>"143470",
				"th"=>"143475",
				"tr"=>"143480",
				"ae"=>"143481",
				"gb"=>"143444",
				"ve"=>"143502",
				"vn"=>"143471",
				"jp"=>"143462",
				"us"=>"143441"
		);
		
	}
	
		

	public function getSearchRank($countryArray,$appIds,$keyword)
	{
		if (count($countryArray) == 0 ||
			      count($appIds) == 0 ||
				        $keyword == '')
			return;
		var_dump($appIds);
		$time = time();
		$array = array();
		foreach ($countryArray as $country)
		{
			$data = $this->searchAppStore($keyword,$country,0);//test

			$appArray = array();
			$resultCount = count($data['items']);
			foreach ($data['items'] as $item)
			{
				$appArray[$item['appId']] = $item;
			}
			for ($i=0; $i < count($appIds) ; $i++) {
				$appId = $appIds[$i];
				if(array_key_exists($appId,$appArray))
				{
					$app = $appArray[$appId];
					$app['country'] = $country;
					$app['result_count'] = $resultCount;
					array_push($array, $app);
				}
				else
				{
					$app['appId'] = $appId;
					$app['country'] = $country;
					$app['result_count'] = $resultCount;
					$app['rank'] = -1;
					array_push($array, $app);				
				}
			}
		}
		return $array;
	}
	
	
	
	//api:搜索Appstore
	public function searchAppStore($keyword,$country='cn',$page = 0)
	{
		$countryId = $this->county_iso2store[$country];
		#echo sprintf("search appstore country %s id=%s",$country,$countryId);
	
		$startIndex = $page * 110;//110 app count one page in appstore,can not be modified!
		$media = "limitedAll";
		$restrict = "false";
		$entity = "software";
	
		$params = array('startIndex' => $startIndex,'entity' => $entity,'media' => $media,'restrict' => $restrict,'term' => $keyword,'page' => $page);
		$url = sprintf("http://ax.search.itunes.apple.com/WebObjects/MZSearch.woa/wa/search?%s",http_build_query($params));
		$header[] = "X-Apple-Tz: 28800";
		$header[] = sprintf("X-Apple-Store-Front: %s-19,12",$countryId);
		$header[] = "User-Agent: iTunes/10.7 (Macintosh; Intel Mac OS X 10.8.2) AppleWebKit/536.26.14";
		$data = $this->curl($url,'',$header);
		$appArray =  $this->parseSearchHtml($data,$country);
		$jsonData  = array('count' => count($appArray),'url' => $url,'country' => $country,'countryId' => $countryId,'page' => $page,'items' => $appArray);
		return $jsonData;
	}
	
	public function curl($url, $post_data = '',$header='')
	{
		$ch = curl_init ( $url );
		if ($post_data)
		{
			curl_setopt ( $ch, CURLOPT_POST, true );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
		}
		if ($header)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}

		curl_setopt ( $ch, CURLOPT_VERBOSE, 0 );
		#    	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt ( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)" );
		curl_setopt ( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($ch, CURLOPT_TIMEOUT, 180);
		curl_setopt($ch, CURLOPT_HEADER, 1);     



		$str = curl_exec ( $ch );
		$status = curl_getinfo ( $ch );
		curl_close ( $ch );
		return $str;
	}


	private function parseSearchHtml($htmlStr,$country)
	{
		$appArray = array();

		$rank = 0;
		$raw =  str_get_html($htmlStr);
		$elementArray = $raw->find('div[class=lockup small detailed option application]');
		foreach ($elementArray as $element)
		{
			$rank ++;
				
			$appId =  $element->attr['adam-id'];
			$appName = '';
			$iconUrl = '';
			$link = '';
			$appGenre = '';


			$imageTagArray = $element->find('img[class=artwork]');
			if (count($imageTagArray) > 0)
			{
				$img = $imageTagArray[0];
				$iconUrl =  $img->attr['src-swap-high-dpi'];
			}

			$liArray =  $element->find('li');


			foreach ($liArray as $li) {

				$liClass = $li->class;
				if ($liClass == 'name')
				{
					$link  = $li->first_child()->href;
					$appName =   $li->first_child()->text();
				}
				else if($liClass == 'genre')
				{
					$appGenre = $li->text();
				}
			}
			$app  = array('appName' => $appName ,
					'appId' => $appId ,
					'iconUrl' => $iconUrl,
					'link' => $link,
					'genreName' => $appGenre,
					'rank' => $rank,
					'country' => $country);

			array_push($appArray, $app);
		}
		return $appArray;

	}
}