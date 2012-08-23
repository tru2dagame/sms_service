<?php

class LibaMysql {
    private $db;
    private $result = '';

    private $host;
    private $username;
    private $passwd;
    private $dbname;
	private $port;
	private $charset;

    public function __construct($db_config) {
        $this->host = $db_config['host'];
        $this->username = $db_config['user'];
        $this->passwd = $db_config['pass'];
        $this->dbname = $db_config['name'];
		$this->port = $db_config['port'];
		$this->charset = isset($db_config['char']) ? $db_config['char'] : '';
    }

    public function select_db($dbname) {
        $this->dbname = $dbname;
    }

    public function query($query, $result_mode='default', $paramter='') {
		$stime = microtime(TRUE);
        $this->db = new mysqli($this->host, $this->username, $this->passwd, 
                               $this->dbname, $this->port);
        if ($this->db->connect_error) {
			$this->log_error($query);
			$this->db = new mysqli($this->host, $this->username, $this->passwd, 
								   $this->dbname, $this->port);
			if ($this->db->connect_error)
				$this->error($query);
		}
		$this->result = '';
		if ($this->charset)
			$this->db->set_charset($this->charset); 
        $res = $this->db->query($query);
        switch ($result_mode) {
            case '1':
                $this->result = $res->fetch_row();
				if ($this->result)
					$this->result = $this->result[0];
                break;
            case 'array':
                $this->result = $res->fetch_array(MYSQLI_ASSOC);
                break;
    		case 'row':
                $this->result = $res->fetch_row();
				break;
            case 'all':
				while ($row = $res->fetch_array(MYSQLI_ASSOC))
                    $this->result[] = $row;
                break;
            case 'param':
                while ($row = $res->fetch_array(MYSQLI_ASSOC))
                    $this->result[] = $row[$paramter];
                break;
            case 'id':
                $this->result = $this->db->insert_id;
                break;
            default:
                $this->result = $res;
                break;
        }
        $this->close();
		$etime = microtime(TRUE) - $stime;
		global $bbs_total_sqls;
		$bbs_total_sqls[] = array('time' => $etime, 
								  'msg' => $this->host . ':' . $this->port . ' ' . $query);
        return $this->result;
    }

    function nextId($table) {
        $this->query("LOCK TABLES `gbb`.`next_id` WRITE");
        $nextId = $this->query("SELECT `nextId` FROM `gbb`.`next_id` WHERE `tableName`='$table' LIMIT 1", "1");
        $this->query("UPDATE `gbb`.`next_id` SET `nextId`=`nextId`+1 WHERE `tableName`='$table' LIMIT 1");
        $this->query("UNLOCK TABLES");
        if (!$nextId) {
            $this->query("INSERT INTO `gbb`.`next_id` (tableName,nextId) VALUES ('$table','1')");
            $nextId = 1;
        }
        return $nextId;
    }

    private function close() {
        $this->db->close();
    }

	
	private function error($sql) {
		$this->log_error($sql);
		@header("location:http://bbs.liba.com/error.html");
		exit;
	}

}



$database['host'] = SAE_MYSQL_HOST_M;
$database['user'] = SAE_MYSQL_USER;
$database['pass'] = SAE_MYSQL_PASS;
$database['name'] = SAE_MYSQL_DB;
$database['port'] = SAE_MYSQL_PORT;
//$database_verify['char'] = 'gbk';

$db = new LibaMysql($database);

