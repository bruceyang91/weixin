<?php

namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
		//签名验证
		public function index()
		{
			$signature = $_GET["signature"];
	        $timestamp = $_GET["timestamp"];
	        $nonce = $_GET["nonce"];
			$token = 'weixin';
			$echoStr = $_GET["echostr"];
			$tmpArr = array($token, $timestamp, $nonce);
			sort($tmpArr, SORT_STRING);
			$tmpStr = implode( $tmpArr );
			$tmpStr = sha1( $tmpStr );

			if( $tmpStr == $signature && $echoStr )
			{
				echo $echoStr;
				exit;
			}else{
				$this->reponseMsg();
			}
		}

		public function reponseMsg()
		{
			$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
			$postObj = simplexml_load_string($postArr);//xml转对象

			if(strtolower($postObj->MsgType) == 'event')
			{
				//订阅事件回复单图文
				if(strtolower($postObj->Event) == 'subscribe'){
					$arr = array(
						array(
							'title'=>"草泥马",
							'Description' => "草泥马is very cool",
							'picUrl'=>"https://www.baidu.com/s?wd=%E9%87%8D%E9%98%B3%E8%8A%82&tn=SE_pshlcjsy_xef5bmh9",
							'url'=>'https://www.baidu.com',
						)
					);
					$indexModel = new IndexModel();
					$indexModel->responsePicText($postObj,$arr);
				}

				//自定义菜单点击事件
				if(strtolower($postObj->Event)=='click'){
					//eventkey为自定义菜单自定义的key
					if(strtolower($postObj->EventKey)=='item1'){
						$content = "这是item1";
					}
					if(strtolower($postObj->EventKey)=='songs'){
						$content = "这是songs";
					}
					$indexModel = new IndexModel;
					$indexModel->responsetextMsg($postObj,$content);
				}

				//自定义菜单view事件view为跳转
				if(strtolower($postObj->Event)=='view'){
					//因为页面是直接跳转所以没有直接推送
					$content = "view事件的跳转url是".$postObj->EventKey;
					$indexModel = new IndexModel;
					$indexModel->responsetextMsg($postObj,$content);
				}

				//扫描二维码事件 --回复图文消息
				if(strtolower($postObj->Event=='scan')){
					if($postObj->EventKey==2000){//eventkey为生成二维码中设置的一个参数
						//零时二维码
						$QRcode = "临时二维码欢迎你";
					}

					if($postObj->EventKey ==3000){
						//永久二维码
						$QRcode = "永久二维码欢迎你";
					}

					//回复单图文
					$arr = array(
					array(
						'title'=>$QRcode,
						'Description' => "草泥马 is very cool",
						'picUrl'=>"https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png",
						'url'=>'https://www.baidu.com/',
						)
					);
					$indexModel = new IndexModel();
					$indexModel->responsePicText($postObj,$arr);
				}
			}

			if(strtolower($postObj->MsgType) =='text')
			{

				switch(trim($postObj->Content)){
					case 1:
						$content='您输入的数字是2';
					break;
				}

				$indexModel = new IndexModel;
				$indexModel->responsetextMsg($postObj,$content);
			}

			if(strtolower($postObj->MsgType)=='text' && $postObj->Content =='tw1'){
				$content = "哈哈";
				$indexModel = new IndexModel;
				$indexModel->responsetextMsg($postObj,$content);
			}
    	}

    	//获取access_token网页授权session解决办法  memcached mysql都可以
    	public function getWxAccessToken(){
    		if($_SESSION['access_token'] && $_SESSION['expire_time']>time()){
    			return $_SESSION['access_token'];
    		}else{
    			$apptid = "wxbfc75fce7f8240bc";
    			$appsecret = "46117b38b923599c327196e27b199081";
    			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$apptid&secret=$appsecret";
    			$res = $this->http_curl($url,'get','json');
    			$accesstoken = $res['access_token'];
    			$_SESSION['access_token']=$accesstoken;
    			$_SESSION['expire_time'] = time()+7000;
    			return $accesstoken;
    		}
    	}

    	//自定义菜单  json格式直接提交中文会报错用urlencode
    	public function definedMenu(){
    		// echo $access_token = $this->getWxAccessToken().'<br>';
    		$access_token = $this->getWxAccessToken();
    		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$accesstoken;
    		$postJson = array(
    			'button'=>array(
	    			array(
	    				'name'=>urlencode('视频'),
	    				'type'=>urlencode('click'),
	    				'key'=>urlencode('item1'),
	    			),//第一个一级菜单
	    			array(
	    				'name'=>'菜单',
	    				'sub_button'=>array(
	    					array(
	    						'name'=>urlencode("歌曲"),
	    						'type'=>'click',
	    						'key'=>'songs',

	    					),//第一个二级菜单
	    					array(
	    						'name'=>urlencode('优酷'),
	    						'type'=>'view',
	    						'url'=>'http://www.youku.com',
	    					),//第二个二级菜单
	    				),

	    			),//第二个一级菜单
	    			array(
	    				'name'=>urlencode('百度'),
	    				'type'=>'view',
	    				'url'=>'http//www.baidu.com',
	    			)//第三个一级菜单
    			)
    		);
    	    $postJson=urldecode(json_encode($postJson));
    		// echo	 $postJson=json_encode($postJson);
    		$res = $this->http_curl($url,'post','json',$postJson);
    		var_dump($res);
    	}

    	//curl
    	function http_curl($url,$type='get',$res='json',$arr=''){
    		$ch = curl_init();
    		curl_setopt($ch,CURLOPT_URL,$url);
    		curl_setopt($ch,CURLOPT_RETURNTRANSFER);

    		if($type == 'post'){
    			curl_setopt($ch,CURLOPT_POST,1);
    			curl_setopt($ch,CURLOPT_POSTFIELDS,$arr)
    		}

    		$output = curl_exec($ch);
    		curl_close($ch);
    		if($res=='json'){
    			if(curl_errno($ch)){
    				return curl_error($ch);
    			}else{
    				return json_decode($output,true);
    			}
    		}
    	}

    	//获取服务器ip
    	function getwxserverip(){
    		$accesstoken = $this->getWxAccessToken();
    		$url  = "https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=$accesstoken";

    		$serverip=$this->http_curl($url);
    		return $serverip['ip_list'];
    	}

    	//获取jsApi票据
    	function getJsApiTicket(){
    		if($_SESSION['jsapi_ticket_expire_time']>time() && $_SESSION['jsapi_ticket']){
    			$jsapi_ticket =  $_SESSION['jsapi_ticket'];
    		}else{
	    		$access_token = $this->getWxAccessToken();
	    		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=ACCESS_TOKEN&type=jsapi";
	    		$res = $this->http_curl($url);
	    		$jsapi_ticket = $res['ticket'];
    			$_SESSION['jsapi_ticket'] = $jsapi_ticket;
    			$_SESSION['jsapi_ticket_expire_time'] = time()+7000;
    		}

    		return $jsapi_ticket;
    	}

    	//生成16位随机码
    	function getRandCode($num = 16){
    		$array = array(
    			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
    			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
    			'0','1','2','3','4','5','6','7','8','9'
    			);

    		for($i=1;$i<=$num;$i++){
    			$tmpstr = "";
    			$max = count($array);
    			$tmpstr .= $array[mt_rand(0,$max-1)];
    		}
    		return $tmpstr;
    	}

    	//sdk分享
    	function shareWx(){
    		$timestamp = time();
    		$nonceStr = $this->getRandCode();
    		$ticket = $this->getJsApiTicket();
    		$url = "http://www.baidu.com";
    		$signature = "jsapi_ticket=$ticket&noncestr=$nonceStr&url=$url";
    		$signature = sha1($signature);
    		$this->assign('name','bruce');
    		$this->assign('timestamp',$timestamp);
    		$this->assign('nonceStr',$nonceStr);
    		$this->assign('signature',$signature);

    		$this->display('share');
    	}

    	//模板消息接口
    	function sendTemplateMsg(){
    		//模板消息接口设置的是:{{参数.DATA}}
    		$accesstoken = $this->getWxAccessToken();
    		$url ="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$accesstoken";
    		$array = array(){
    			"toUser"=>"ozCMCxNmxUbAp3Cnb9DxpXeqn_JU",
    			"template_id"=>"DmxNSK91rb4SPW9caYMx4YFLK71Q1n2NAJKDtapfO50",
    			"url"=>'http://www.baidu.com',
    			"data"=>array(
    				'name'=array('value'=>'hello','color'=>"#173177"),
    				'money'=>array('value'=>100,'color'=>"#173177"),
    				'date'=array('value'=>date('Y-m-d H:i:s','color'=>'red')),

    			),
    		}

    		$postJson = json_encode($array);
    		$res = $this->http_curl($url,'post','json',$postJson);
    		var_dump($res);
    	}

    	//生成二临时维码接口
    	function getQrCode(){
    		header('content-type:text/html;charset=utf-8');
    		$access_token = $this->getWxAccessToken();
    		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
    		$postArr = array(
    				'expire_seconds'=>'6048400',
    				'action_name'=>'QR_SCENE',
    				'action_info'=>array(
    					'scene'=>array('scene_id'=>2000),
    				),
    		);
    		$postJson = json_encode($postArr);
    		$res = $this->http_curl($url,'post','json',$postJson);
    		// var_dump($res);
    		$ticket = $res['ticket'];
    		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
    		echo "临时二维码";
    		echo "<img src="$url">";
    	}

    	//生成永久二维码
    	function getForeverQrCode(){
    		header('content-type:text/html;charset=utf-8');
    		$access_token = $this->getWxAccessToken();
    		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$access_token";
    		$postArr = array(
    				'action_name'=>'QR_LIMIT_STR_SCENE',
    				'action_info'=>array(
    					'scene'=>array('scene_id'=>3000),
    				),
    			);
    		$postJson = json_encode($postArr);
    		$res = $this->http_curl($url,'post','json',$postJson);
    		// var_dump($res);
    		$ticket = $res['ticket'];
    		$url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
    		echo "永久二维码";
    		echo "<img src="$url">";
    	}

    	//群发接口
    	function sendMsgAll(){
    		//获取全局的注意是全局的access_token
    		$access_token = $this->getWxAccessToken();
    		$url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token=$access_token";
    		//单文本
    		// $array = array(
    		// 	'touser'=>"ozCMCxNmxUbAp3Cnb9DxpXeqn_JU",//微信用户的openid
    		// 	'text'=>array('content'=>"my name is bruceyang"),
    		// 	'msgtype'=>'text'
    		// );
    		// 单图文

			$array = array(
				'touser'=>'ozCMCxNmxUbAp3Cnb9DxpXeqn_JU',
				'mpnews'=>array('media_id'=>''),
				'msgtype'=>'mpnews',
			);
    		$postJson = json_encode($array);
    		$res= $this->http_curl($url,'post','json',$postJson);
    		var_dump($res);
    	}
}