<?php

/**
* Seller controller
* Handles selller web service request
* version: 1.0 ( 31-01-2020 )
*/

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
class BadgeCount extends Common_Service_Controller {

    protected $database;
    protected $dbname = 'badge_count';

	public function __construct(){
        parent::__construct();
        $serviceAccount = ServiceAccount::fromJsonFile(APPPATH.'lasross-local-2eef5b29b6fd.json');
        $factory = (new Factory)->withServiceAccount($serviceAccount)->withDatabaseUri('https://lasross-local.firebaseio.com')->create();
        $this->database = $factory->getDatabase();
	}

    //Insert and Update action of seller business Information
    function increaseBadgeCount_post(){
       $val = $this->database->getReference($this->dbname)->getSnapshot()->getValue();
       $childVal = $val[17]['count'];
       //pr($val[17]['count']);
       //pr($childVal);

       if(!empty($childVal)){

        //$setChildVal = $childVal+1;
        $child = $this->database->getReference($this->dbname)->getChild(0);
        pr($child );
       }
    }


} //End Class