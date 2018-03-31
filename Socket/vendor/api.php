<?php
namespace Vendor;

class Api 
{
	
	function __construct(){
		
	}

	public function api($data){

    	//*********获取雅虎接口****************************
		$yql_base_url = "http://query.yahooapis.com/v1/public/yql";

		$yql_query = "select * from yahoo.finance.xchange where pair in ('{$data}')";  
		  
		$yql_query_url = $yql_base_url . "?q=" . urlencode($yql_query);

		$yql_query_url .= "&format=json&diagnostics=true&env=store://datatables.org/alltableswithkeys&callback=";  

		$session = curl_init($yql_query_url);  
		curl_setopt($session, CURLOPT_RETURNTRANSFER,true);      
		$json = curl_exec($session);  
		$json = json_decode($json);


		//********东方财富**********************************
		$ch = curl_init("http://nufm.dfcfw.com/EM_Finance2014NumericApplication/JS.aspx?type=CT&cmd={$data}I0&sty=MPICT&st=z&sr=&p=&ps=&cb=callback_fill&js=&token=049db06d2bc9c947062f56de8b3b5648&0.08389930590501993&callback=callback_fill&_=1498301282312"); 		 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);      
		$res = curl_exec($ch); 
		$tempRes='';
		preg_match('#".+"#', $res,$tempRes);

		// 检测 有没有 返回数据
		if( !is_null($tempRes) && !empty($tempRes) ){
			$tempRes=explode(",",trim($tempRes[0],"\"") ); 

			$json->query->results->kline = array(
				'd' => "http://pifm3.eastmoney.com/EM_Finance2014PictureInterface/Index.aspx?ImageType=KXL&ID={$data}I0&Formula=CCI&unitwidth=-6&type=&token=44c9d251add88e27b65ed86506f6e5da",
				'w' => "http://pifm3.eastmoney.com/EM_Finance2014PictureInterface/Index.aspx?ImageType=KXL&ID={$data}I0&Formula=CCI&unitwidth=-6&type=W&token=44c9d251add88e27b65ed86506f6e5da",
				'm' => "http://pifm3.eastmoney.com/EM_Finance2014PictureInterface/Index.aspx?ImageType=KXL&ID={$data}I0&Formula=CCI&unitwidth=-6&type=M&token=44c9d251add88e27b65ed86506f6e5da",
			);	

		}else{
			$json->query->results->kline=array(
				'd' => "http://hl.anseo.cn/images/ichart.gif?bs={$data}=x",
				'w' => "http://hl.anseo.cn/images/ichart.gif?bs={$data}=x",
				'm' => "http://hl.anseo.cn/images/ichart.gif?bs={$data}=x",
			);			
		}	


		$json->query->results->today = $tempRes;
		
		return $json;
	}

}
