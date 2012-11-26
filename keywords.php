<?php
	require_once 'ITunesTool.php';


	define("APPID", "534966036");

	$itunes = new ITunesTool();
	$resultString = '';
	$keywords_data = file_get_contents("./keywords.txt");
	$keywords_country_array = explode("\n",$keywords_data);
	foreach ($keywords_country_array as $keywords_country) {
		$array = explode(":", $keywords_country);
		if (count($array) == 2) {
			$country = $array[0];
			$keywordsArray = explode(",", $array[1]);
			$resultString.= sprintf("%s:\n",$country);
			foreach ($keywordsArray as $keyword) {
				$keyword = trim($keyword);
				$result = $itunes->getSearchRank(array($country),array(APPID),$keyword);
				$app = $result[0];
				$resultString .= sprintf("%s:rank %s  in %s \n",$keyword,$app['rank'],$app['result_count']);
			}
		}
	}
	var_dump($resultString);
	file_put_contents("result.txt", $resultString);

?>