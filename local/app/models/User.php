<?php
/**
 * User model
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Fat-Free-PHP-Bootstrap-Site
 * @author   Mark Takacs <takacsmark@takacsmark.com>
 * @license  MIT
 * @link     takacsmark.com
 */

 /**
 * User model class
 *
 * @category PHP
 * @package  Fat-Free-PHP-Bootstrap-Site
 * @author   Mark Takacs <takacsmark@takacsmark.com>
 * @license  MIT
 * @link     takacsmark.com
 */
class User extends DB\SQL\Mapper
{
    /**
    * Constructor, maps user table fields to php object
    *
    * @param DB\SQL $db Database connection
    */
    public function __construct(DB\SQL $db)
    {
        parent::__construct($db, 'user');
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
    * @param int $id User id
    *
    * @return none
    */
    public function getById($id)
    {
        $this->load(array('id=?',$id));
    }

    /**
    * Fetch one record by name records
    *
    * @param string $name User name
    *
    * @return none
    */
    public function getByName($name)
    {
        $this->load(array('username=?', $name));
    }

    /**
    * Add a User record
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
    * @param int $id User id
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
    * @param int $id User id
    *
    * @return void
    */
    public function delete($id)
    {
        $this->load(array('id=?',$id));
        $this->erase();
    }
}
