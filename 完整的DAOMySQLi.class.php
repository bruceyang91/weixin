<?php


/**
 * 基于MySQLi的数据库操作层对象（DAO）
 */
class DAOMySQLi {
	private $_host;
	private $_port;
	private $_user;
	private $_pw;//password
	private $_dbname;
	private $_charset;
	// 存储当前类唯一实例
	private static $_instance;
	// MySQLi类实例
	private $_mysqli;

	/**
	 * 获得单例对象的方法
	 * @param  array  $option [description]
	 * @return [type]         [description]
	 */
	public static function getSingleton(array $option = array()) {
		// 判断所存储的是否为当前对象的实例
		if (! self::$_instance instanceof self) {
			// 如果不是，说明没有实例化过，则实例化，然后存储。
			self::$_instance = new self($option);
		}
		// 返回所存储当前对象
		return self::$_instance;
	}
	private function __construct(array $option=array()) {
		$this->_initOption($option);
		$this->_initMySQLi();
		$this->_initCharset();
	}
	private function __clone() {
	}

	/**
	 * 初始化配置
	 */
	private function _initOption(array $option=array()) {
		// 优先使用用户传递的参数
		// 如果没有，则使用默认的，默认值从php的配置中获取
		$this->_host = isset($option['host']) ? $option['host'] : ini_get('mysqli.default_host');
		$this->_port = isset($option['port']) ? $option['port'] : ini_get('mysqli.default_port');
		$this->_user = isset($option['user']) ? $option['user'] : ini_get('mysqli.default_user');
		$this->_pw = isset($option['pw']) ? $option['pw'] : ini_get('mysqli.default_pw');
		$this->_dbname = isset($option['dbname']) ? $option['dbname'] : '';
		$this->_charset = isset($option['charset']) ? $option['charset'] : 'utf8';
	}

	/**
	 * 初始化MySQLi对象
	 */
	private function _initMySQLi() {
		// 实例化MySQLi对象
		$this->_mysqli = new MySQLi($this->_host, $this->_user, $this->_pw, $this->_dbname, $this->_port);
		// 是否连接失败
		if ($this->_mysqli->connect_errno) {
			die('MySQL服务器连接失败：' . $this->_mysqli->connect_error);
		}
	}

	/**
	 * 初始化连接字符集
	 */
	private function _initCharset() {
		if(! $this->_mysqli->set_charset($this->_charset)) {
			die('数据库连接字符集设置失败: ' . $this->_mysqli->error);
		}
	}

	/**
	 * 执行SQL
	 * @param  string $sql 待执行的SQL
	 * @return [type]      [description]
	 */
	public function query($sql = '') {
		if (! $result = $this->_mysqli->query($sql)) {
			echo 'SQL执行失败', '<br>';
			echo '错误的SQL：', $sql, '<br>';
			echo '错误消息：', $this->_mysqli->error;
			die;
		}
		return $result;
	}

	public function fetchAll($sql) {
		// 执行SQL
		$result = $this->query($sql);
		// 初始化记录数组
		$rows = array();
		// 依次获取所有的记录
		// 以关联数组形式
		while($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$rows[] = $row;
		}
		// 释放结果集
		$result->free();
		return $rows;
	}
	public function fetchRow($sql) {
		// 执行SQL
		$result = $this->query($sql);
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$result->free();
		return $row ? $row : false;
	}
	public function fetchOne($sql) {
		// 执行SQL
		$result = $this->query($sql);
		$row = $result->fetch_array(MYSQLI_NUM);
		$result->free();
		return $row ? $row[0] : false;
	}
	public function fetchColumn($sql) {
		// 执行SQL
		$result = $this->query($sql);
		// 初始化记录数组
		$column = array();
		// 依次获取所有的记录
		// 以关联数组形式
		while($row = $result->fetch_array(MYSQLI_NUM)) {
			$column[] = $row[0];
		}
		// 释放结果集
		$result->free();
		return $column;
	}
	/**
	 * 转义字符串，并使用引号包裹
	 * @param  string $str 待转义字符串
	 * @return string      转义后字符串
	 */
	public function escapeString($str) {
		return "'" . $this->_mysqli->real_escape_string($str) . "'";
	}

	/**
	 * 获取最后一条插入语句生成的ID
	 * @return [type] [description]
	 */
	public function lastInsertId() {
		return $this->_mysqli->insert_id;
	}
}