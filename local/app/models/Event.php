<?php
/**
 * Event model
 *
 * PHP version 7
 *
 * @category PHP
 * @package  Fat-Free-PHP-Bootstrap-Site
 * @author   Mark Takacs <takacsmark@takacsmark.com>
 * @license  MIT
 * @link     takacsmark.com
 */

 /**
 * Event model class
 *
 * @category PHP
 * @package  Fat-Free-PHP-Bootstrap-Site
 * @author   Mark Takacs <takacsmark@takacsmark.com>
 * @license  MIT
 * @link     takacsmark.com
 */
class Event extends DB\SQL\Mapper
{
    /**
    * Constructor, maps Event table fields to php object
    *
    * @param DB\SQL $db Database connection
    */
    public function __construct(DB\SQL $db)
    {
        parent::__construct($db, 'event');
    }

    /**
    * Fetch all records
    *
    * @return array
    */
    public function all()
    {
        $this->load();
        return $this->query;
    }

    /**
    * Fetch one record by id records
    *
    * @param int $id Event id
    *
    * @return none
    */
    public function getById($id)
    {
        $this->load(array('id=?',$id));
    }

    /**
    * Add a Event record
    * there are no paramaters, becuase record data is copied from $_POST
    * it's one of the great features of f3
    *
    * @return void
    */
    public function add()
    {
        $this->copyFrom('POST');
        try {
			$this->save();
		}
		catch(Exception $e) {
			// TBD How to handle? Just ignore?
			//echo 'Message: ' .$e->getMessage();
		}    
	}

    /**
    * Edit a specific record
    *
    * @param int $id Event id
    *
    * @return void
    */
    public function edit($id)
    {
        $this->load(array('id=?',$id));
        $this->copyFrom('POST');
        try {
			$this->update();
		}
		catch(Exception $e) {
			// TBD How to handle? Just ignore?
			//echo 'Message: ' .$e->getMessage();
		}
    }

    /**
    * Delete a record
    *
    * @param int $id Event id
    *
    * @return void
    */
    public function delete($id)
    {
        $this->load(array('id=?',$id));
        $this->erase();
    }
}
