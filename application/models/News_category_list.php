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
        if($Section!='all')
        {
            $this->db->where('section_id',$Section);
        }
        
        $res = $this->db->get();
        return $res->result();
    }
}
?>