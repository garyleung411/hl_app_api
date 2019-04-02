<?php
/**
*	新聞，文章，廣告
*
*/
class News extends CI_Model
{
	public $year;
    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('daily',TRUE);
		$this->year = date('Y');
    }
	/**
	*	獲取類別下文章列表
	*/
    public function Get_All_New_list($cat=-1,$PageSize=10,$Page=0,$count=FALSE)
    {
    	$this->db = $this->load->database('daily',TRUE);
		if($cat){
			if(!$date = $this->Get_Max_Date($cat,$this->year))
			{
				$date = $this->Get_Max_Date($cat,($this->year-1));
			}
			
			// $this->db->select('nm.title,nm.newsID as id,nm.content,nm.content2,nm.content3,nm.publishDatetime,nm.keyword,nm.videoID,nm.createdBy,dhn.newsCat');
			
			$this->db->from('daily_hl_news as dhn');
			$this->db->join('news_main_'.date('Y',strtotime($date)).' as nm','dhn.newsID = nm.newsID and dhn.year = '.$this->year.', \'inner\'', 'right');
			
			if(is_array($cat)&&count($cat)>0)
			{
				$this->db->where_in('dhn.newsCat',$cat);
				
			}else if($cat!=null&&$cat!='')
			{
				$this->db->where('dhn.newsCat',$cat);
			}
			
			$this->db->where('dhn.status',1);
		
			$this->db->where('publishDatetime >=',$date);
	
			if(!$count){
				$this->db->select('nm.title,nm.newsID as id,nm.content,nm.content2,nm.content3,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,nm.createdBy as writer,dhn.newsCat');
				$this->db->limit($PageSize,$Page*$PageSize);
				$this->db->order_by('nm.publishDatetime','desc');
				$res = $this->db->get();
				return $res->result();
			}else{
				return $this->db->count_all_results();
			}
		}
    }
	public function Get_Max_Date($cat,$year)
	{
		$this->db->select_max('nm.publishDatetime');
			
		$this->db->from('daily_hl_news as dhn');
		$this->db->join("news_main_$year as nm","dhn.newsID = nm.newsID and dhn.year = $year, \'inner\'", 'right');
			
		if(is_array($cat)&&count($cat)>0)
		{
			$this->db->where_in('dhn.newsCat',$cat);
			
		}else if($cat!=null&&$cat!='')
		{
			$this->db->where('dhn.newsCat',$cat);
		}
			
		$this->db->where('dhn.status',1);
			
		$res = $this->db->get();
		return ($res->result()[0]->publishDatetime)?date('Y-m-d',strtotime($res->result()[0]->publishDatetime)):false;
	}

	/**
	*	獲取文章
	*/
    public function Get_New($cat=-1,$id=-1)
    {
    	$this->db = $this->load->database('daily',TRUE);
		if($cat&&$id){
			$this->db->select('nm.title,nm.newsID as id,nm.content,nm.content2,nm.content3,nm.publishDatetime as publish_datetime,nm.keyword,nm.videoID as vdo,nm.createdBy as writer,dhn.newsCat as cat');

			$this->db->from('daily_hl_news as dhn');
			$this->db->join('news_main_'.$this->year.' as nm','dhn.newsID = nm.newsID', 'right');
			
			if(is_array($cat)&&count($cat)>0)
			{
				$this->db->where_in('dhn.newsCat',$cat);
				
			}else if($cat!=null&&$cat!='')
			{
				$this->db->where('dhn.newsCat',$cat);
			}
			
			$this->db->where('dhn.status',1);
			$this->db->where('nm.newsID',(int)$id);
		
			$res = $this->db->get();
			return $res->result();
		}else{
			return false;
		}
    }
}
?>