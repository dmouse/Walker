<?php
	
	@include ("color.php");

	class walker {
		
		var $ch;
		var $color;
		var $op;
		var $mh;
		var $rq_array;
		
		/*
		* @param $url 
		* 	String url to get information
		*/
		
		public function __construct(){
			
			$this -> ch = curl_init();

			$this -> color = new Colors();
		}

		private function debug($data){
			
			switch($this->op["debug"]){
				case 1:
					echo $this->color->colored("[".$data["name"]."]", "dark_gray", null);
					echo $this->color->colored(" ".$data["message"], "light_gray", null);
					echo "\n";
				break;
				
				case 2:
					echo $this->color->colored("[Debug level 2]", "white", null);
					echo $this->color->colored("[Debug level 2]", "white", null);

				break;

					
			}
		
		}
		
		/*
		 * @param $header 
		 *		Array contain header to send for any request 
		 */
		
		private function _set_headers( $headers = array() ){
			
			//$headers[] = 'X-PHX: true';
			//$headers[] = 'Referer: http://api.twitter.com/p_receiver.html';

			if ($this-> op ["ajax"]){
				$headers[] = 'Accept: application/json, text/javascript, */*';
				$headers[] = 'X-Requested-With: XMLHttpRequest';
				$this -> debug (array("name"=>"Ajax", "message"=>"Activate"));
			}

			if ($this->op ["method"] == "POST"){
				$headers[] = 'Content-type: application/x-www-form-urlencoded';
				$this -> debug (array("name"=>"Method", "message"=>"POST"));
			}
			else
				$this -> debug (array("name"=>"Method", "message"=>"GET"));
	
			$headers[] = 'Accept-Encoding: gzip,deflate';
			$headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
			$headers[] = 'Accept-Language: es-ES,es;q=0.8,en-us;q=0.5,en;q=0.3';
			$headers[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
			$headers[] = 'Keep-Alive: 500';
			$headers[] = 'Connection: Keep-Alive';
			
			$data = array ( 
				"name"=> "Headers",
				"message" => "Send"
			);

			$this -> debug ( $data );

			curl_setopt( $this -> ch, CURLOPT_HTTPHEADER, $headers);
		}
		
		private function _set_user_agent($r = null){
			$ua = array();
			$ua [] = 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.237 Safari/534.10';
			$ua [] = 'Mozilla/5.0 (X11; U; Linux i686; es-MX; rv:1.9.2.14pre) Gecko/20110111 Ubuntu/10.10 (karmic) Namoroka/3.6.14pre';
			$ua [] = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; InfoPath.3; Creative AutoUpdate v1.40.02)';
			$ua [] = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)';
			$ua [] = 'Opera/9.80 (Windows NT 6.1; U; en) Presto/2.5.24 Version/10.54';
			$ua [] = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_6; en-us) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4';

			$this -> debug (array("name"=>"User Agent", "message"=>"Random"));

			curl_setopt( $this -> ch, CURLOPT_USERAGENT, $ua [ ($r ? $r : rand(0,count($ua) - 1 )) ]);
		}
		
		private function _set_url($url){
			curl_setopt ($this -> ch, CURLOPT_URL, $url);
			$this -> debug (array("name"=>"URL", "message"=>$url));
		}
		
		private function _return_transfer($r = false){
			curl_setopt ($this -> ch, CURLOPT_RETURNTRANSFER, $r ? 0:1 );
		}
		
		private function _set_proxy($proxy=null){
			if ($this->op['proxy']){
				curl_setopt ($this->ch, CURLOPT_PROXY, $proxy);
				curl_setopt ($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
				$this -> debug (array("name"=>"Proxy", "message"=> $this->op['proxy'] ));
			}
		}
		
		private function _set_compression($enc = "gzip"){
			curl_setopt ($this->ch, CURLOPT_ENCODING, $enc);
		}
		
		private function _post( $data = array() ){
			$post = "";
			if (isset($data))
				foreach ($data as $i => $d)
					$post .= $i . "=" . $d . "&";
			curl_setopt ($this -> ch, CURLOPT_POST, 1);
			curl_setopt ($this -> ch, CURLOPT_POSTFIELDS, $post);
			$this -> debug (array("name"=>"Payload", "message"=> $post ));
		}

		private function _set_opts($url, $options){
			/* set options */

      $this -> op = $options;

      /*
       *  Url : Url para hacer la petición
       */

      if ( $url )
        $this -> _set_url($url);

      /*
       *  False : User Agent random
       *  0: Chrome 8
       *  1: Firefox Ubuntu 10.10
       *  2: IE 8
       *  3: IE 9
       *  4: Opera 9 
       *  5: Safari 5
       */
      if (isset($options['agent']))
        $this ->  _set_user_agent($options['agent']);
      else
        $this ->  _set_user_agent(); // random

			/*
       *  Headers : Array con los cabezales 
       */
      if (isset($options['headers']) && is_array($options['headers']) )
        $this -> _set_headers($options['headers']);
      else
        $this -> _set_headers(); // se pone el cabezal


      $this -> _return_transfer(); // regresar transferencia?


      /*
       *  Proxy : default none
       */
      if (isset($options['proxy']))
        $this -> _set_proxy($options['proxy']);
      /*else
        $this -> _set_proxy();*/

      $this -> _set_compression();

			(isset($options['method']) == "POST") ?  $this ->  _post($options['data']): false;
      
      
      return $this -> ch;

		}
		
		public function run ($url = null, $options = array()){
		
			$this -> _set_opts($url, $options);
			
			//curl_setopt($this->ch,CURLOPT_NOPROGRESS,false);
			//curl_setopt($this->ch,CURLOPT_PROGRESSFUNCTION,'progress');
			
			//curl_setopt($this->ch,CURLOPT_INTERFACE,'eth2');
				

      $this -> debug (array("name"=>"Exec", "message"=> "OK" ));
      
			//return curl_exec($this->ch); 
			
		}

		public function multi_init(){

			$this -> mh = curl_multi_init();
			
			$this -> rq_array = array();
			
		}
		
		private function _get_key(){
			
			return (string) $this -> ch;
			
		}

		public function multi_opts($url, $ops){

			$this -> ch = curl_init();

			$this -> _set_opts($url, $ops);

			$key = $this -> _get_key();
			
			$this -> rq_array [ $key ] ['ops'] = $ops;
			
			$code = curl_multi_add_handle($this -> mh, $this -> ch);
			
		}
		
		

		public function multi_run(){
			
			do { 
					usleep(10000);
					curl_multi_exec($this -> mh,$running); 
					echo "=";
			} while($running > 0);
			
			
			while($done = curl_multi_info_read($this->mh)){
				$key = (string)$done['handle'];
				
				$data = curl_multi_getcontent($done['handle']);
				
				$this -> rq_array [ $key ]['data'] = $data;
				
		
			}
			
			return $this -> rq_array;
		
		}
		
		function __destruct(){
			curl_close($this->ch);
			curl_multi_close ($this -> mh);
			echo "\n";
			$this -> debug (array("name"=>"+", "message"=>"0xD"));
		}
		
	}
	

