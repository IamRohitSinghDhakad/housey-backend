<?php

class User_model extends CI_Model{

	var $table = USERS;
    var $column_order = array(null, 'user.avatar', 'user.full_name','user.email','user.status');  //set column field database for datatable orderable
    var $table_col= array(null,'user.full_name', 'user.email');
    var $column_search = array('user.full_name', 'user.email'); //set column field database for datatable searchable
    var $order = array('user.userID' => 'DESC');  // default order
    var $where = array('user_type' => 'buyer');
    var $group_by = 'user.userID';

	public function __construct(){
        parent::__construct();
    }

    public function set_data($where=''){
        $this->where = $where;
    }


 	private function posts_get_query(){

        $this->db->select('*');
        $this->db->from($this->table. ' as user');
        $i = 0;

        foreach ($this->column_search as $emp) // loop column 
        {
            if(isset($_POST['search']['value']) && !empty($_POST['search']['value'])){
                $_POST['search']['value'] = $_POST['search']['value'];
            } else
                $_POST['search']['value'] = '';

            if($_POST['search']['value']) // if datatable send POST for search
            {
                if($i===0) // first loop
                {
                    $this->db->group_start();
                    $this->db->like(($emp), $_POST['search']['value']);
                }
                else
                {
                    $this->db->or_like(($emp), $_POST['search']['value']);
                }

                if(count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
            }

            if(!empty($this->group_by)){
                $this->db->group_by($this->group_by);
            }
            
            if(!empty($this->where))
                $this->db->where($this->where); 
               
            //for category filter
            $count_val = count($_POST['columns']);
            for($i=1;$i<=$count_val;$i++){ 


                if(!empty($_POST['columns'][$i]['search']['value'])){ 

                    $this->db->where(array($this->table_col[$i]=>$_POST['columns'][$i]['search']['value'])); 
                }
            }


            if(isset($_POST['order'])) // here order processing
            {
                $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
            } 
            else if(isset($this->order))
            {
                $order = $this->order;
                $this->db->order_by(key($order), $order[key($order)]);
            }
    }

    public function get_list()
    {
        $this->posts_get_query();
        if(isset($_POST['length']) && $_POST['length'] < 1) {
            $_POST['length']= '5';
        } else
        $_POST['length']= $_POST['length'];
        
        if(isset($_POST['start']) && $_POST['start'] > 1) {
            $_POST['start']= $_POST['start'];
        }
        $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered()
    {
        $this->posts_get_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all()
    {
        $this->db->from($this->table);
        return $this->db->count_all_results();
    }

    public function getData($id){
        if(!empty($id)){//user Id
            $data = $this->db->where(array('userID'=>$id,'user_type'=>'buyer'))->get(USERS)->row();
            return $data;
        }else{
            return FALSE;
        }
    }

    function shippingAddress($id){
        if(!empty($id)){//user Id
            $data = $this->db->where(array('user_id'=>$id,'is_default'=>'1'))->get(USER_ADDRESS)->row();
            return $data;
        }else{
            return FALSE;
        }
    }
}
?>