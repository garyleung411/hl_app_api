<?php
/**
*   欄目{即時,日報,影片,生活,專欄}
*/
class Section extends CI_Model
{
	public $tablename = 'section_list';
    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('hl_app',TRUE);
    }
	/**
	*	獲取相關欄目信息
	*/
    public function Get_Section($id='all')
    {
        $this->db->from($this->tablename);
        if($id!='all'){
            if(is_array($id)&&count($id)>1){
                $this->db->where_in('section_id',$id);
            }else{
                $this->db->where('section_id',$id);
            }
        }
		$this->db->where('status',1);
        $res = $this->db->get();
        return $res->result();
    }
	 
	/**
	*	獲取欄目下分類信息
	*/
	public function Get_cat_list($sectionID)
	{
		$this->load->model('News_category_list');
		return $this->News_category_list->Get_Cat($sectionID);
		
	}
	
}
?>