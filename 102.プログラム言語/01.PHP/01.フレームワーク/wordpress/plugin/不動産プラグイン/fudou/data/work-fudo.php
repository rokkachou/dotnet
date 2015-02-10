<?php
/*
 * 不動産プラグインデーター設定
 * @package WordPress3.9
 * @subpackage Fudousan Plugin
 * Version: 1.4.5
*/

		//fudo-widget2.php admin_fudou.php

		$work_bukkenshubetsu =
		array(	
			"1101" => array("id" => "1101","name" => "【売地】売地"),
			"1102" => array("id" => "1102","name" => "【売地】借地権譲渡"),
			"1103" => array("id" => "1103","name" => "【売地】底地権譲渡"),
			"1104" => array("id" => "1104","name" => "【売地】建付土地"),
			"1201" => array("id" => "1201","name" => "【売戸建】新築戸建"),
			"1202" => array("id" => "1202","name" => "【売戸建】中古戸建"),
			"1203" => array("id" => "1203","name" => "【売戸建】新築テラス"),
			"1204" => array("id" => "1204","name" => "【売戸建】中古テラス"),
			"1301" => array("id" => "1301","name" => "【売マン】新築マンション"),
			"1302" => array("id" => "1302","name" => "【売マン】中古マンション"),
			"1303" => array("id" => "1303","name" => "【売マン】新築公団"),
			"1304" => array("id" => "1304","name" => "【売マン】中古公団"),
			"1305" => array("id" => "1305","name" => "【売マン】新築公社"),
			"1306" => array("id" => "1306","name" => "【売マン】中古公社"),
			"1307" => array("id" => "1307","name" => "【売マン】新築タウン"),
			"1308" => array("id" => "1308","name" => "【売マン】中古タウン"),
			"1309" => array("id" => "1309","name" => "【売マン】リゾートマン"),
			"1399" => array("id" => "1399","name" => "【売マン】その他"),
			"1401" => array("id" => "1401","name" => "【売建物全部】店舗"),
			"1403" => array("id" => "1403","name" => "【売建物全部】店舗付住宅"),
			"1404" => array("id" => "1404","name" => "【売建物全部】住宅付店舗"),
			"1405" => array("id" => "1405","name" => "【売建物全部】事務所"),
			"1406" => array("id" => "1406","name" => "【売建物全部】店舗・事務所"),
			"1407" => array("id" => "1407","name" => "【売建物全部】ビル"),
			"1408" => array("id" => "1408","name" => "【売建物全部】工場"),
			"1409" => array("id" => "1409","name" => "【売建物全部】マンション"),
			"1410" => array("id" => "1410","name" => "【売建物全部】倉庫"),
			"1411" => array("id" => "1411","name" => "【売建物全部】アパート"),
			"1412" => array("id" => "1412","name" => "【売建物全部】寮"),
			"1413" => array("id" => "1413","name" => "【売建物全部】旅館"),
			"1414" => array("id" => "1414","name" => "【売建物全部】ホテル"),
			"1415" => array("id" => "1415","name" => "【売建物全部】別荘"),
			"1416" => array("id" => "1416","name" => "【売建物全部】リゾートマン"),
			"1420" => array("id" => "1420","name" => "【売建物全部】社宅"),
			"1421" => array("id" => "1421","name" => "【売建物全部】文化住宅"),
			"1499" => array("id" => "1499","name" => "【売建物全部】その他"),
			"1502" => array("id" => "1502","name" => "【売建物一部】店舗"),
			"1505" => array("id" => "1505","name" => "【売建物一部】事務所"),
			"1506" => array("id" => "1506","name" => "【売建物一部】店舗・事務所"),
			"1507" => array("id" => "1507","name" => "【売建物一部】ビル"),
			"1509" => array("id" => "1509","name" => "【売建物一部】マンション"),
			"1599" => array("id" => "1599","name" => "【売建物一部】その他"),
			"3101" => array("id" => "3101","name" => "【賃貸居住】マンション"),
			"3102" => array("id" => "3102","name" => "【賃貸居住】アパート"),
			"3103" => array("id" => "3103","name" => "【賃貸居住】一戸建"),
			"3104" => array("id" => "3104","name" => "【賃貸居住】テラスハウス"),
			"3105" => array("id" => "3105","name" => "【賃貸居住】タウンハウス"),
			"3106" => array("id" => "3106","name" => "【賃貸居住】間借り"),
			"3110" => array("id" => "3110","name" => "【賃貸居住】寮・下宿"),
			"3122" => array("id" => "3122","name" => "【賃貸居住】コーポ"),
			"3123" => array("id" => "3123","name" => "【賃貸居住】ハイツ"),
			"3124" => array("id" => "3124","name" => "【賃貸居住】文化住宅"),
			"3201" => array("id" => "3201","name" => "【賃貸事業】店舗(建全部)"),
			"3202" => array("id" => "3202","name" => "【賃貸事業】店舗(建一部)"),
			"3203" => array("id" => "3203","name" => "【賃貸事業】事務所"),
			"3204" => array("id" => "3204","name" => "【賃貸事業】店舗・事務所"),
			"3205" => array("id" => "3205","name" => "【賃貸事業】工場"),
			"3206" => array("id" => "3206","name" => "【賃貸事業】倉庫"),
			"3207" => array("id" => "3207","name" => "【賃貸事業】一戸建"),
			"3208" => array("id" => "3208","name" => "【賃貸事業】マンション"),
			"3209" => array("id" => "3209","name" => "【賃貸事業】旅館"),
			"3210" => array("id" => "3210","name" => "【賃貸事業】寮"),
			"3211" => array("id" => "3211","name" => "【賃貸事業】別荘"),
			"3212" => array("id" => "3212","name" => "【賃貸事業】土地"),
			"3213" => array("id" => "3213","name" => "【賃貸事業】ビル"),
			"3214" => array("id" => "3214","name" => "【賃貸事業】住宅付店舗(戸建)"),
			"3215" => array("id" => "3215","name" => "【賃貸事業】住宅付店舗(一部)"),
			"3282" => array("id" => "3282","name" => "【賃貸事業】駐車場"),
			"3299" => array("id" => "3299","name" => "【賃貸事業】その他")
		);

		$work_madori =
		array(	
			"0" => array("code" => "10","name" => "R"),
			"1" => array("code" => "20","name" => "K"),
			"2" => array("code" => "25","name" => "SK"),
			"3" => array("code" => "30","name" => "DK"),
			"4" => array("code" => "35","name" => "SDK"),
			"5" => array("code" => "40","name" => "LK"),
			"6" => array("code" => "45","name" => "SLK"),
			"7" => array("code" => "50","name" => "LDK"),
			"8" => array("code" => "55","name" => "SLDK")
		);







	//fudo-functions.php

	$work_gazo =
	array(	
		"smallimg1" => array("name" => "fudoimg1",	 "std" => "1","title" => "画像名1","type" => "text","description" => "",	 "com" => "(間取り)"),
		"imgtype1" =>  array("name" => "fudoimgtype1",	 "std" => "","title" => "画像タイプ1","type" => "select","description" => ""),
		"company1" =>  array("name" => "fudoimgcomment1","std" => "","title" => "説明文1","type" => "text","description" => "<hr />"),

		"smallimg2" => array("name" => "fudoimg2",	"std" => "1","title" => "画像名2","type" => "text","description" => "",	 "com" => "(外観)"),
		"imgtype2" =>  array("name" => "fudoimgtype2","std" => "","title" => "画像タイプ2","type" => "select","description" => ""),
		"company2" =>  array("name" => "fudoimgcomment2","std" => "","title" => "説明文2","type" => "text","description" => "<hr />"),

		"smallimg3" => array("name" => "fudoimg3","std" => "1","title" => "画像名3","type" => "text","description" => "",	 "com" => ""),
		"imgtype3" =>  array("name" => "fudoimgtype3","std" => "","title" => "画像タイプ3","type" => "select","description" => ""),
		"company3" =>  array("name" => "fudoimgcomment3","std" => "","title" => "説明文3","type" => "text","description" => "<hr />"),

		"smallimg4" => array("name" => "fudoimg4","std" => "1","title" => "画像名4","type" => "text","description" => "",	 "com" => ""),
		"imgtype4" =>  array("name" => "fudoimgtype4","std" => "","title" => "画像タイプ4","type" => "select","description" => ""),
		"company4" =>  array("name" => "fudoimgcomment4","std" => "","title" => "説明文4","type" => "text","description" => "<hr />"),

		"smallimg5" => array("name" => "fudoimg5","std" => "1","title" => "画像名5","type" => "text","description" => "",	 "com" => ""),
		"imgtype5" =>  array("name" => "fudoimgtype5","std" => "","title" => "画像タイプ5","type" => "select","description" => ""),
		"company5" =>  array("name" => "fudoimgcomment5","std" => "","title" => "説明文5","type" => "text","description" => "<hr />"),

		"smallimg6" => array("name" => "fudoimg6","std" => "1","title" => "画像名6","type" => "text","description" => "",	 "com" => ""),
		"imgtype6" =>  array("name" => "fudoimgtype6","std" => "","title" => "画像タイプ6","type" => "select","description" => ""),
		"company6" =>  array("name" => "fudoimgcomment6","std" => "","title" => "説明文6","type" => "text","description" => "<hr />"),

		"smallimg7" => array("name" => "fudoimg7","std" => "1","title" => "画像名7","type" => "text","description" => "",	 "com" => ""),
		"imgtype7" =>  array("name" => "fudoimgtype7","std" => "","title" => "画像タイプ7","type" => "select","description" => ""),
		"company7" =>  array("name" => "fudoimgcomment7","std" => "","title" => "説明文7","type" => "text","description" => "<hr />"),

		"smallimg8" => array("name" => "fudoimg8","std" => "1","title" => "画像名8","type" => "text","description" => "",	 "com" => ""),
		"imgtype8" =>  array("name" => "fudoimgtype8","std" => "","title" => "画像タイプ8","type" => "select","description" => ""),
		"company8" =>  array("name" => "fudoimgcomment8","std" => "","title" => "説明文8","type" => "text","description" => "<hr />"),

		"smallimg9" => array("name" => "fudoimg9","std" => "1","title" => "画像名9","type" => "text","description" => "",	 "com" => ""),
		"imgtype9" =>  array("name" => "fudoimgtype9","std" => "","title" => "画像タイプ9","type" => "select","description" => ""),
		"company9" =>  array("name" => "fudoimgcomment9","std" => "","title" => "説明文9","type" => "text","description" => "<hr />"),

		"smallimg10" => array("name" => "fudoimg10","std" => "1","title" => "画像名10","type" => "text","description" => "",	 "com" => ""),
		"imgtype10" =>  array("name" => "fudoimgtype10","std" => "","title" => "画像タイプ10","type" => "select","description" => ""),
		"company10" =>  array("name" => "fudoimgcomment10","std" => "","title" => "説明文10","type" => "text","description" => "<hr />")

	);

	$work_gazo2 =	array();
	for( $i=11; $i<FUDOU_IMG_MAX+1 ; $i++ ){
		$tmp_work_gazo = array(
			"smallimg$i" => array("name" => "fudoimg$i","std" => "1","title" => "画像名$i","type" => "text","description" => "",	 "com" => ""),
			"imgtype$i" =>  array("name" => "fudoimgtype$i","std" => "","title" => "画像タイプ$i","type" => "select","description" => ""),
			"company$i" =>  array("name" => "fudoimgcomment$i","std" => "","title" => "説明文$i","type" => "text","description" => "<hr />"),
		);

		$work_gazo2 = array_merge($work_gazo2, $tmp_work_gazo);
	}


	$work_madorinaiyo =
	array(	
		"madorisyurui1" => array("name" => "madorisyurui1","std" => "","title" => "種類1","type" => "select","description" => ""),
		"madorijyousu1" => array("name" => "madorijyousu1","std" => "","title" => "畳数1","type" => "text","description" => "畳　"),
		"madorikai1" => array("name" => "madorikai1","std" => "","title" => "所在階1","type" => "text","description" => "階　"),
		"madorishitsu1" => array("name" => "madorishitsu1","std" => "","title" => "室数1","type" => "text","description" => "<hr />"),

		"madorisyurui2" => array("name" => "madorisyurui2","std" => "","title" => "種類2","type" => "select","description" => ""),
		"madorijyousu2" => array("name" => "madorijyousu2","std" => "","title" => "畳数2","type" => "text","description" => "畳　"),
		"madorikai2" => array("name" => "madorikai2","std" => "","title" => "所在階2","type" => "text","description" => "階　"),
		"madorishitsu2" => array("name" => "madorishitsu2","std" => "","title" => "室数2","type" => "text","description" => "<hr />"),

		"madorisyurui3" => array("name" => "madorisyurui3","std" => "","title" => "種類3","type" => "select","description" => ""),
		"madorijyousu3" => array("name" => "madorijyousu3","std" => "","title" => "畳数3","type" => "text","description" => "畳　"),
		"madorikai3" => array("name" => "madorikai3","std" => "","title" => "所在階3","type" => "text","description" => "階　"),
		"madorishitsu3" => array("name" => "madorishitsu3","std" => "","title" => "室数3","type" => "text","description" => "<hr />"),

		"madorisyurui4" => array("name" => "madorisyurui4","std" => "","title" => "種類4","type" => "select","description" => ""),
		"madorijyousu4" => array("name" => "madorijyousu4","std" => "","title" => "畳数4","type" => "text","description" => "畳　"),
		"madorikai4" => array("name" => "madorikai4","std" => "","title" => "所在階4","type" => "text","description" => "階　"),
		"madorishitsu4" => array("name" => "madorishitsu4","std" => "","title" => "室数4","type" => "text","description" => "<hr />"),

		"madorisyurui5" => array("name" => "madorisyurui5","std" => "","title" => "種類5","type" => "select","description" => ""),
		"madorijyousu5" => array("name" => "madorijyousu5","std" => "","title" => "畳数5","type" => "text","description" => "畳　"),
		"madorikai5" => array("name" => "madorikai5","std" => "","title" => "所在階5","type" => "text","description" => "階　"),
		"madorishitsu5" => array("name" => "madorishitsu5","std" => "","title" => "室数5","type" => "text","description" => "<hr />"),

		"madorisyurui6" => array("name" => "madorisyurui6","std" => "","title" => "種類6","type" => "select","description" => ""),
		"madorijyousu6" => array("name" => "madorijyousu6","std" => "","title" => "畳数6","type" => "text","description" => "畳　"),
		"madorikai6" => array("name" => "madorikai6","std" => "","title" => "所在階6","type" => "text","description" => "階　"),
		"madorishitsu6" => array("name" => "madorishitsu6","std" => "","title" => "室数6","type" => "text","description" => "<hr />"),

		"madorisyurui7" => array("name" => "madorisyurui7","std" => "","title" => "種類7","type" => "select","description" => ""),
		"madorijyousu7" => array("name" => "madorijyousu7","std" => "","title" => "畳数7","type" => "text","description" => "畳　"),
		"madorikai7" => array("name" => "madorikai7","std" => "","title" => "所在階7","type" => "text","description" => "階　"),
		"madorishitsu7" => array("name" => "madorishitsu7","std" => "","title" => "室数7","type" => "text","description" => "<hr />"),

		"madorisyurui8" => array("name" => "madorisyurui8","std" => "","title" => "種類8","type" => "select","description" => ""),
		"madorijyousu8" => array("name" => "madorijyousu8","std" => "","title" => "畳数8","type" => "text","description" => "畳　"),
		"madorikai8" => array("name" => "madorikai8","std" => "","title" => "所在階8","type" => "text","description" => "階　"),
		"madorishitsu8" => array("name" => "madorishitsu8","std" => "","title" => "室数8","type" => "text","description" => "<hr />"),

		"madorisyurui9" => array("name" => "madorisyurui9","std" => "","title" => "種類9","type" => "select","description" => ""),
		"madorijyousu9" => array("name" => "madorijyousu9","std" => "","title" => "畳数9","type" => "text","description" => "畳　"),
		"madorikai9" => array("name" => "madorikai9","std" => "","title" => "所在階9","type" => "text","description" => "階　"),
		"madorishitsu9" => array("name" => "madorishitsu9","std" => "","title" => "室数9","type" => "text","description" => "<hr />"),

		"madorisyurui10" => array("name" => "madorisyurui10","std" => "","title" => "種類10","type" => "select","description" => ""),
		"madorijyousu10" => array("name" => "madorijyousu10","std" => "","title" => "畳数10","type" => "text","description" => "畳　"),
		"madorikai10" => array("name" => "madorikai10","std" => "","title" => "所在階10","type" => "text","description" => "階　"),
		"madorishitsu10" => array("name" => "madorishitsu10","std" => "","title" => "室数10","type" => "text","description" => "<hr />"),
	);


	$work_setsubi =
	array(	
		//条件
		"10001" => array("code" => "10001","name" => "楽器相談可"),
		"10002" => array("code" => "10002","name" => "楽器不可"),
		"10101" => array("code" => "10101","name" => "事務所可"),
		"10202" => array("code" => "10202","name" => "事務所不可"),
		"10301" => array("code" => "10301","name" => "２人入居可"),
		"10302" => array("code" => "10302","name" => "２人入居不可"),
		"10401" => array("code" => "10401","name" => "男性限定"),
		"10402" => array("code" => "10402","name" => "女性限定"),
		"10501" => array("code" => "10501","name" => "単身者限定"),
		"10502" => array("code" => "10502","name" => "単身者希望"),
		"10503" => array("code" => "10503","name" => "単身者不可"),
		"10601" => array("code" => "10601","name" => "法人限定"),
		"10602" => array("code" => "10602","name" => "法人希望"),
		"10603" => array("code" => "10603","name" => "法人不可"),
		"10701" => array("code" => "10701","name" => "学生限定"),
		"10702" => array("code" => "10702","name" => "学生歓迎"),
		"10801" => array("code" => "10801","name" => "高齢者限定"),
		"10802" => array("code" => "10802","name" => "高齢者歓迎"),
		"10901" => array("code" => "10901","name" => "ペット対応"),
		"10902" => array("code" => "10902","name" => "ペット可能"),
		"10903" => array("code" => "10903","name" => "ペット不可"),
		"11101" => array("code" => "11101","name" => "公庫利用可"),
		"11201" => array("code" => "11201","name" => "手付金保証有"),
		"11301" => array("code" => "11301","name" => "定期借家権"),
		"11401" => array("code" => "11401","name" => "保証付住宅"),
		"11501" => array("code" => "11501","name" => "保証人要"),
		"11502" => array("code" => "11502","name" => "保証人不要"),
		"11701" => array("code" => "11701","name" => "特定優良賃貸住宅"),
		"11901" => array("code" => "11901","name" => "家賃保障付"),
		"25001" => array("code" => "25001","name" => "敷金礼金0"),
		"23601" => array("code" => "23601","name" => "角部屋"),
		"26002" => array("code" => "26002","name" => "角地"),
		"11001" => array("code" => "11001","name" => "建築条件付"),
		"11002" => array("code" => "11002","name" => "建築条件無"),
		"12201" => array("code" => "12201","name" => "分譲賃貸"),
		"24001" => array("code" => "24001","name" => "住宅性能保証付"),
		"12301" => array("code" => "12301","name" => "マンスリー可"),		//410
		"25002" => array("code" => "25002","name" => "未入居"),
		"25003" => array("code" => "25003","name" => "リノベーション"),
		"25004" => array("code" => "25004","name" => "ルームシェア・ハウス"),


		//キッチン
		"20701" => array("code" => "20701","name" => "ガスコンロ"),
		"20702" => array("code" => "20702","name" => "電気コンロ"),
		"20703" => array("code" => "20703","name" => "IHコンロ"),
		"20801" => array("code" => "20801","name" => "一口コンロ"),
		"20802" => array("code" => "20802","name" => "二口コンロ"),
		"20803" => array("code" => "20803","name" => "三口コンロ"),
		"20804" => array("code" => "20804","name" => "四口以上コンロ"),
		"20901" => array("code" => "20901","name" => "システムキッチン"),
		"24501" => array("code" => "24501","name" => "カウンターキッチン"),
		"25501" => array("code" => "25501","name" => "ごみ出し24時間OK"),	//410
		"26001" => array("code" => "26001","name" => "食器洗い乾燥機"),		//410
		"26101" => array("code" => "26101","name" => "ディスポーザー"),		//410
		"22901" => array("code" => "22901","name" => "冷蔵庫"),


		//バス・トイレ
		"21001" => array("code" => "21001","name" => "給湯"),
		"21101" => array("code" => "21101","name" => "追い焚き"),
		"21201" => array("code" => "21201","name" => "洗髪洗面化粧台"),
		"25701" => array("code" => "25701","name" => "独立洗面台"),		//410
		"20301" => array("code" => "20301","name" => "バス専用"),
		"20302" => array("code" => "20302","name" => "バス共同"),
		"20303" => array("code" => "20303","name" => "バスなし"),
		"20601" => array("code" => "20601","name" => "シャワー"),
		"24601" => array("code" => "24601","name" => "浴室乾燥機"),
		"26006" => array("code" => "26006","name" => "ユニットバス"),
		"25801" => array("code" => "25801","name" => "浴室TV"),			//410
		"25901" => array("code" => "25901","name" => "浴室1.6x2.0m以上"),	//410
		"20401" => array("code" => "20401","name" => "トイレ専用"),
		"20402" => array("code" => "20402","name" => "トイレ共同"),
		"20403" => array("code" => "20403","name" => "トイレなし"),
		"20501" => array("code" => "20501","name" => "バス・トイレ別"),
		"24201" => array("code" => "24201","name" => "温水洗浄便座"),


		//冷暖房
		"21301" => array("code" => "21301","name" => "冷房"),
		"21302" => array("code" => "21302","name" => "暖房"),
		"21303" => array("code" => "21303","name" => "石油暖房"),
		"21304" => array("code" => "21304","name" => "エアコン"),
		"23701" => array("code" => "23701","name" => "床暖房"),

		//収納
		"21401" => array("code" => "21401","name" => "トランクルーム"),
		"21501" => array("code" => "21501","name" => "床下収納"),
		"21601" => array("code" => "21601","name" => "W.INクローゼット"),
		"21701" => array("code" => "21701","name" => "ロフト付き"),
		"21801" => array("code" => "21801","name" => "室内洗濯機置き場"),
		"21802" => array("code" => "21802","name" => "洗濯機置き場有"),


		//放送・通信
		"21901" => array("code" => "21901","name" => "CATV"),
		"22001" => array("code" => "22001","name" => "CSアンテナ"),
		"22101" => array("code" => "22101","name" => "BSアンテナ"),
		"22201" => array("code" => "22201","name" => "有線放送"),
		"23402" => array("code" => "23402","name" => "高速インターネット"),
		"23403" => array("code" => "23403","name" => "光ファイバー"),
		"26003" => array("code" => "26003","name" => "地デジ対応"),
		"26301" => array("code" => "26301","name" => "インターネット利用料無料"),	//410
		"26401" => array("code" => "26401","name" => "CATV利用料無料"),			//410
		"23401" => array("code" => "23401","name" => "インターネット有"),

		//セキュリティ
		"22301" => array("code" => "22301","name" => "オートロック"),
		"23801" => array("code" => "23801","name" => "TVドアホン"),
		"26004" => array("code" => "26004","name" => "セキュリティシステム"),
		"26201" => array("code" => "26201","name" => "防犯カメラ"),			//410
		"26005" => array("code" => "26005","name" => "カードキーシステム"),


		//ガス水道
		"20101" => array("code" => "20101","name" => "都市ガス"),
		"20102" => array("code" => "20102","name" => "プロパンガス"),
		"20199" => array("code" => "20199","name" => "その他ガス"),
		"20001" => array("code" => "20001","name" => "公営水道"),
		"20002" => array("code" => "20002","name" => "井戸"),
		"20099" => array("code" => "20099","name" => "その他水道"),
		"20201" => array("code" => "20201","name" => "排水下水"),
		"20202" => array("code" => "20202","name" => "排水浄化槽"),
		"20203" => array("code" => "20203","name" => "排水汲取"),
		"20299" => array("code" => "20299","name" => "排水その他"),


		//その他
		"22401" => array("code" => "22401","name" => "エレベータ"),
		"23001" => array("code" => "23001","name" => "宅配ボックス"),
		"25005" => array("code" => "25005","name" => "駐車場有"),
		"23101" => array("code" => "23101","name" => "駐輪場"),
		"23201" => array("code" => "23201","name" => "バイク置き場"),
		"22501" => array("code" => "22501","name" => "専用庭"),
		"22601" => array("code" => "22601","name" => "出窓"),
		"22701" => array("code" => "22701","name" => "バルコニー"),
		"22801" => array("code" => "22801","name" => "フローリング"),
		"23301" => array("code" => "23301","name" => "タイル貼り"),
		"23501" => array("code" => "23501","name" => "フリーアクセス"),
		"23901" => array("code" => "23901","name" => "二世帯住宅"),
		"24101" => array("code" => "24101","name" => "バリアフリー"),
		"24301" => array("code" => "24301","name" => "デザイナーズ"),
		"24401" => array("code" => "24401","name" => "オール電化"),
		"12001" => array("code" => "12001","name" => "満室賃貸中"),

		"24701" => array("code" => "24701","name" => "外断熱"),		//410
		"24801" => array("code" => "24801","name" => "耐震構造"),	//410
		"24901" => array("code" => "24901","name" => "免震構造"),	//410
		"25012" => array("code" => "25012","name" => "制震構造"),	//410
		"25101" => array("code" => "25101","name" => "自由設計対応"),	//410
		"25201" => array("code" => "25201","name" => "住宅性能評価書"),	//410
		"25301" => array("code" => "25301","name" => "キッズルーム"),	//410
		"25401" => array("code" => "25401","name" => "フロントサービス"),	//410
		"25601" => array("code" => "25601","name" => "家具・家電付"),	//410
	);
