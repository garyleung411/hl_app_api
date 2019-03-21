<?php
/**
*	推薦
*/
class Recommend extends CI_Model
{
	public $tablename = 'app_highlight';
    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('hl_app',TRUE);
    }
    public function Get_All_Recommend()
    {
        // var_dump($this->tablename);
        $this->db->from($this->tablename);
        $res = $this->db->get();
        return $res->result();
    }
}
?>