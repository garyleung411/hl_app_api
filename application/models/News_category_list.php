<?php
//欄目類別

class News_category_list extends CI_Model
{
	public $tablename = 'section_category_list';
	
    function __construct()
    {
        parent::__construct();
		$this->db = $this->load->database('hl_app',TRUE);
    }
	/**
	*	獲取欄目下的類別
	* 	Section 欄目id
	*/
    public function Get_Cat($Section)
    {
        $this->db->from($this->tablename);
        if($Section!='all'){
            $this->db->where('section_id',$Section);
			$this->db->where('status',1);
        }
        $this->db->order_by('cat_id','asc');
        $res = $this->db->get();
        return $res->result();
    }
    /**
    *   獲取欄目下的類別
    *   Section 欄目id
    */
    public function Check_Cat($Section,$Cat){
        $this->db->from($this->tablename);
        $this->db->where('section_id',$Section);
        $this->db->where('cat_id',$Cat);
		$this->db->where('status',1);
        return $this->db->count_all_results() > 0;
    }

    /**
    *   返回Cat映射id
    *
    */
    public function cat2mapcat($Section,$CatID)
    {
        $this->db->select('mapping_catid');
        $this->db->from($this->tablename);
        $this->db->where('section_id',$Section);
		$this->db->where('status',1);
		$this->db->where('cat_id',$CatID);
        
        $res = $this->db->get();
        return $res->result_array()[0]['mapping_catid'];
    }
	
	public function mapcat2cat($Section,$mapcat)
    {
        $this->db->select('cat_id');
        $this->db->from($this->tablename);
        $this->db->where('section_id',$Section);
		$this->db->where('status',1);
		$this->db->where('mapping_catid',$mapcat);
        
        $res = $this->db->get();
        return $res->result_array()[0]['cat_id'];
    }
}
?>