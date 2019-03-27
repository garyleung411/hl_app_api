private function list_cast($data){
		$return_data = array();
		$list = array(
			"id"					=> "",
			"title"					=> "",
			"content"				=> "",
			"section"				=> "",
			"cat"					=> "",
			"publish_datetime"		=> "",
			"vdo"					=> "",
			"imgs"					=> array(),
			"writer"				=> array(),
			"layout"				=> "",
		);
		
		foreach($data as $i => $d){
			$tmp = $list;
			foreach($tmp as $k => $v){
				$tmp[$k] = isset($d[$k])?$d[$k]:$v; 
			}
			$return_data[$i] = $tmp;
		}
		return $return_data;
	}
	
	private function detail_cast($data){
		$return_data = array();
		$detail = array(
			"id"					=> "",
			"title"					=> "",
			"content"				=> array(),
			"section"				=> "",
			"cat"					=> "",
			"publish_datetime"		=> "",
			"vdo"					=> "",
			"imgs"					=> array(),
			"writer"				=> array(),
			"layout"				=> "",
			"keyword"				=> array(),
			"topic"					=> array(),
		);
		
		foreach($data as $i => $d){
			$tmp = $detail;
			foreach($tmp as $k => $v){
				$tmp[$k] = isset($d[$k])?$d[$k]:$v; 
			}
			$return_data[$i] = $tmp;
		}
		return $return_data;
	}