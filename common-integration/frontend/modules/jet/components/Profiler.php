<?php 
namespace frontend\modules\jet\components;
use yii\base\Component;

class Profiler extends component
{
	public static $profiler = [];
	public static $sequence = [];
    public static $level = 0;
    public static $status = true;
    public static $counters = [];
    
    /* Function for adding counter keys */
    public static function addCounter($counter,$description = ''){
    	self::$counters[$counter] = [];
    	self::$counters[$counter]['description'] = $description;
    	self::$counters[$counter]['values'] = [];
    }
	/*
     * function for setting start time of profiler
	 */
	public static function start($code){
		if(!self::$status)
			return;
		self::$profiler[$code] = ['start'=>time()];
		self::$sequence[] = ['data'=>self::$profiler[$code],'type'=>'start','code'=>$code,'level'=>self::$level++]; 
	}

	/*
     * function for pause profiler
	 */
	public static function pause(){

		self::$status = false;
	}

	/*
     * function for resuming profiler
	 */
	public static function resume(){

		self::$status = true;
	}

	public static function pushData($code,$key,$data){
		if(!self::$status)
			return;
		if(isset(self::$counters[$key])){
			if(isset(self::$counters[$key]['values'][$code]))
				self::$counters[$key]['values'][$code] += $data;
			else
				self::$counters[$key]['values'][$code] = $data;
			return;
		}
		self::$profiler[$code]['data'][$key] = $data;
	}
	public static function stop($code){
		if(!self::$status)
			return;
		self::$profiler[$code]['stop'] = time();
		self::$profiler[$code]['time'] = self::$profiler[$code]['stop']-self::$profiler[$code]['start'];
		self::$sequence[] = ['data'=>self::$profiler[$code],'code'=>$code,'type'=>'stop','level'=>--self::$level]; 
	}

	public static function getProfiler(){
		return self::$profiler;
	}

	public static function getSequencialData(){
		return self::$sequence;
	}

	public static function getHtml(){

		$html = '';
		$squences = self::$sequence;
		foreach($squences as $squence){
			$html .= self::prepareHtml($squence);
		
		}
		$script = '<script>
						var $togglerElements = document.getElementsByClassName("toggle-button");
						for(var index in $togglerElements){
							if(typeof $togglerElements[index]!="function"){
								$togglerElements[index].onclick = function(){
									$element = document.getElementById(this.getAttribute("content"));
									if($element.style.display=="none"){
										$element.style.display = "";
									}
									else
									{
										$element.style.display = "none";
									}
								}
							}
						}
					</script>';
		return $html.$script;
	}

	public static function prepareHtml($squence){
		if($squence['type']=='start')
		{
			$html = '<a  class="toggle-button" content="'.$squence['code'].'" href="javascript:void(0);">Show '.$squence['code'].' Details</a>
			<ul id="'.$squence['code'].'" style="display:none;" ><li>'.'<strong>'.$squence['code'].':Started</strong> Execution Time : ('.self::$profiler[$squence['code']]['time'].')';
			if(isset(self::$profiler[$squence['code']]['data'])){
				$html .= '<br>'.self::$profiler[$squence['code']]['data']['msg'];
				if(isset(self::$profiler[$squence['code']]['data']['trace']))
					$html .= '<br><span class="trace" style="display:none;">'.self::$profiler[$squence['code']]['data']['trace'].'<span>';

				if(isset(self::$profiler[$squence['code']]['data']['extraData']))
					$html .= '<br>
					<a  class="toggle-button" content="extradata-'.$squence['code'].'" href="javascript:void(0);">Extra Data</a>
				<span class="extra-data" id="extradata-'.$squence['code'].'" style="display:none;">'.self::$profiler[$squence['code']]['data']['extraData'].'<span>';
			}
			$html .= '</li>';
			return $html;
		}
		if($squence['type']=='stop')
		{
			return '<li><strong>'.$squence['code'].':Ended</strong></li></ul>';
		}
	}


}