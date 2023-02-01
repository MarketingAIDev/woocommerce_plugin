<?php

// if ( ! function_exists('_lang')){
// 	function _lang($string=''){
		
// 		//Get Target language
// 		$target_lang = get_option('language');
// 		$company_id = isset($_GET['token']) ? $_GET['token'] : '';
		
// 		if(company_id() !="" || $company_id != ""){
// 			$target_lang = get_company_option('language');
// 		}
	
		
// 		if($target_lang == ""){
// 			$target_lang = "language";
// 		}
		
// 		if(file_exists(resource_path() . "/language/$target_lang.php")){
// 			include(resource_path() . "/language/$target_lang.php"); 
// 		}else{
// 			include(resource_path() . "/language/language.php"); 
// 		}
		
// 		if (array_key_exists($string,$language)){
// 			return $language[$string];
// 		}else{
// 			return $string;
// 		}
// 	}
// }


if ( ! function_exists('startsWith')){
	function startsWith($haystack, $needle)
	{
		 $length = strlen($needle);
		 return (substr($haystack, 0, $length) === $needle);
	}
}


if ( ! function_exists('create_option')){
	function create_option($table,$value,$display,$selected="",$where=NULL){
		$options = "";
		$condition = "";
		if($where != NULL){
			$condition .= "WHERE ";
			foreach( $where as $key => $v ){
				$condition.=$key."'".$v."' ";
			}
		}

		$query = DB::select("SELECT $value, $display FROM $table $condition");
		foreach($query as $d){
			if( $selected!="" && $selected == $d->$value ){   
				$options.="<option value='".$d->$value."' selected='true'>".ucwords($d->$display)."</option>";
			}else{
				$options.="<option value='".$d->$value."'>".ucwords($d->$display)."</option>";
			} 
		}
		
		echo $options;
	}
}

if( !function_exists('process_string') ){

 function process_string($search_replace,$string){
   $result = $string;
   foreach($search_replace as $key=>$value){
		$result = str_replace($key,$value,$result);
   }
   return $result;
 }

}

if ( ! function_exists('get_table')){
	function get_table($table,$where=NULL) 
	{
		$condition = "";
		if($where != NULL){
			$condition .= "WHERE ";
			foreach( $where as $key => $v ){
				$condition.=$key."'".$v."' ";
			}
		}
		$query = DB::select("SELECT * FROM $table $condition");
		return $query;
	}
}

if ( ! function_exists('online_operator')){
	function online_operator()
	{
		$company_id = company_id();
		date_default_timezone_set(get_option('timezone'));
		$now = date("Y-m-d H:i:s");
		$interval = get_option('user_tracking_refresh_rate')+2;
		$query = DB::select("SELECT users.*,departments.department 
		FROM users LEFT JOIN departments ON users.department_id=departments.id
		WHERE users.company_id='$company_id' AND users.last_activity > DATE_SUB('$now', INTERVAL $interval SECOND)");
		return $query;
	}
}

if ( ! function_exists('online_guest')){
	function online_guest()
	{
		$company_id = company_id();
		date_default_timezone_set(get_option('timezone'));
		$now = date("Y-m-d H:i:s");
		$interval = get_option('user_tracking_refresh_rate')+2;
		
		if(\Auth::user()->user_type == "admin" || get_option('allow_department') != "yes"){
			$query = DB::select("SELECT guests.*,COUNT(messages.id) as unread_message,chat_requests.id as chat_request_id,chat_requests.operator_id,chat_requests.status as chat_status,users.name as operator 
			FROM guests JOIN chat_requests ON guests.id= chat_requests.guest_id LEFT JOIN messages ON chat_requests.id=messages.chat_request_id 
			AND messages.status='0' AND messages.sender!='operator' LEFT JOIN users ON chat_requests.operator_id=users.id 
			WHERE guests.company_id='$company_id' AND guests.last_activity > DATE_SUB('$now', INTERVAL $interval SECOND) GROUP BY chat_requests.id");
			return $query;
		}else{
			$department_id = \Auth::user()->department_id;
			
			$query = DB::select("SELECT guests.*,COUNT(messages.id) as unread_message,chat_requests.id as chat_request_id,chat_requests.operator_id,chat_requests.status as chat_status,users.name as operator 
			FROM guests JOIN chat_requests ON guests.id= chat_requests.guest_id LEFT JOIN messages ON chat_requests.id=messages.chat_request_id 
			AND messages.status='0' AND messages.sender!='operator' LEFT JOIN users ON chat_requests.operator_id=users.id 
			WHERE guests.company_id='$company_id' AND guests.last_activity > DATE_SUB('$now', INTERVAL $interval SECOND) AND guests.department_id='$department_id' GROUP BY chat_requests.id");
			return $query;
		}
	}
}



if ( ! function_exists('user_count')){
	function user_count($user_type) 
	{
		$count = \App\User::where("user_type",$user_type)
						->selectRaw("COUNT(id) as total")
						->first()->total;
	    return $count;
	}
}


if ( ! function_exists('get_logo')){
	function get_logo() 
	{
		$logo = get_option("logo");
		if($logo ==""){
			return asset("images/company-logo.png");
		}
		return asset("uploads/$logo"); 
	}
}

if ( ! function_exists('sql_escape')){
	function sql_escape($unsafe_str) 
	{
		if (get_magic_quotes_gpc())
		{
			$unsafe_str = stripslashes($unsafe_str);
		}
		return $escaped_str = str_replace("'", "", $unsafe_str);
	}
}

if ( ! function_exists('get_option')){
	function get_option($name,$default='') 
	{
		$setting = DB::table('settings')->where('name', $name)->get();
	    if ( ! $setting->isEmpty() ) {
		   return $setting[0]->value;
		}
		return $default;

	}
}

if ( ! function_exists('get_company_option')){
	function get_company_option($name, $optional="") 
	{
		$company_id = isset($_GET['token']) ? tricky_decrypt($_GET['token']) : '';
		
		if($company_id == ""){
			$company_id = company_id();
		}
		$setting = DB::table('company_settings')
					->where('name', $name)
					->where('company_id', $company_id)
					->get();
					
	    if ( ! $setting->isEmpty() ) {
		   return $setting[0]->value;
		}
		return $optional;

	}
}


if ( ! function_exists('timezone_list'))
{

 function timezone_list() {
  $zones_array = array();
  $timestamp = time();
  foreach(timezone_identifiers_list() as $key => $zone) {
    date_default_timezone_set($zone);
    $zones_array[$key]['ZONE'] = $zone;
    $zones_array[$key]['GMT'] = 'UTC/GMT ' . date('P', $timestamp);
  }
  return $zones_array;
}

}

if ( ! function_exists('create_timezone_option'))
{

 function create_timezone_option($old="") {
  $option = "";
  $timestamp = time();
  foreach(timezone_identifiers_list() as $key => $zone) {
    date_default_timezone_set($zone);
	$selected = $old == $zone ? "selected" : "";
	$option .= '<option value="'. $zone .'"'.$selected.'>'. 'GMT ' . date('P', $timestamp) .' '.$zone.'</option>';
  }
  echo $option;
}

}


if ( ! function_exists( 'get_country_list' ))
{
    function get_country_list( $old_data='' ) {
		if( $old_data == "" ){
			echo file_get_contents( app_path().'/Helpers/country.txt' );
		}else{
			$pattern='<option value="'.$old_data.'">';
			$replace='<option value="'.$old_data.'" selected="selected">';
			$country_list = file_get_contents( app_path().'/Helpers/country.txt' );
			$country_list = str_replace($pattern,$replace,$country_list);
			echo $country_list;
		}
    }	
}

if ( ! function_exists('decimalPlace'))
{

 function decimalPlace($number){
    return number_format((float)$number, 2);
 }

}


if( !function_exists('load_language') ){
	function load_language($active=''){
		$path = resource_path() . "/language";
		$files = scandir($path);
		$options="";
		
		foreach($files as $file){
		    $name = pathinfo($file, PATHINFO_FILENAME);
			if($name == "." || $name == "" || $name == "language"){
				continue;
			}
			
			$selected = "";
			if($active == $name){
				$selected = "selected";
			}else{
				$selected = "";
			}
			
			$options .= "<option value='$name' $selected>".ucwords($name)."</option>";
		        
		}
		echo $options;
	}
}

if( !function_exists('get_language_list') ){
	function get_language_list(){
		$path = resource_path() . "/language";
		$files = scandir($path);
		$array = array();
		
		foreach($files as $file){
		    $name = pathinfo($file, PATHINFO_FILENAME);
			if($name == "." || $name == "" || $name == "language"){
				continue;
			}
	
			$array[] = $name;
		        
		}
		return $array;
	}
}

if( !function_exists('load_audio') ){
	function load_audio($active=''){
		$path = "sounds";
		$files = scandir($path);
		$options="";
		
		foreach($files as $file){
		    $name = pathinfo($file, PATHINFO_FILENAME);
			if($name == "." || $name == ""){
				continue;
			}
			
			$selected = "";
			if($active == $file){
				$selected = "selected";
			}else{
				$selected = "";
			}
			
			$options .= "<option data-audio=".$file." value='$file' $selected>".ucwords($name)."</option>";
		        
		}
		echo $options;
	}
}

if( !function_exists('load_emboji') ){
	function load_emboji(){
		$path = "emboji";
		$files = scandir($path);
		$results = "";
		
		foreach($files as $file){
		    $name = pathinfo($file, PATHINFO_FILENAME);
			if($name == "." || $name == ""){
				continue;
			}

			$results .= '<div class="col-xs-2 emboji"><img class="s-emboji" src="'.asset("emboji/$file").'"></div>';
		        
		}
		echo $results;
	}
}


if ( ! function_exists('offline_message_count')){
	function offline_message_count() 
	{
		$count = \App\OfflineMessage::where("company_id",company_id())
									->where('status','=','0')->count();
		
		if($count > 0){
			return '<span class="count-badge">'.$count.'</span>';
		}
		
		return '';

	}
}

if ( ! function_exists('company_id')){
	function company_id()
	{
		if(Auth::check()){
			return Auth::user()->company_id; 
		}
		return "";
	}
}

if ( ! function_exists('tricky_encrypt')){
	function tricky_encrypt($number)
	{
		 return ($number * 91378264);
	}
}


if ( ! function_exists('tricky_decrypt')){
	function tricky_decrypt($number)
	{
		 return ($number);
	}
}
