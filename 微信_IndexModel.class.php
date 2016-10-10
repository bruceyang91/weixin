<?php

	class IndexModel extends Model{
		//回复带图片消息
		public function responsePicText($postObj,$arr){
				$toUser = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$template = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<ArticleCount>.count($arr).</ArticleCount>
					<Articles>";
					foreach($arr as $k=>$v){
						$template .="<item>
						<Title><![CDATA[".$v['title']."]]></Title>
						<Description><![CDATA[".$v['description']."]]></Description>
						<PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
						<Url><![CDATA[".$v['url']."]]></Url>
						</item>";
					}
					$template .="</Articles>
					</xml> ";

				echo sprintf($template,$fromUser,$toUser,$time,'news');
		}

		// 回复文本消息
		public function responsetextMsg($postObj,$content){
					$fromUser = $postObj->ToUserName;
					$toUser = $postObj->FromUserName;
					$time = time();
					$msgtype = 'text';
					$content = $content;
					$template = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[%s]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
					echo sprintf($template,$toUser,$fromUser,$time,$msgtype,$content);
		}
	}