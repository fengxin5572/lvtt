<?php
//dezend by  QQ:2172298892
namespace base;

class Storage
{
	/**
	 * 存储配置
	 * @var array
	 */
	protected $config = array();
	/**
	 * 存储配置名
	 * @var string
	 */
	protected $storage = 'default';
	/**
	 * 驱动对象
	 * @var array
	 */
	static protected $objArr = array();

	public function __construct($storage = 'default')
	{
		if ($storage) {
			$this->storage = $storage;
		}

		$this->config = Config::get('STORAGE.' . $this->storage);
		if (empty($this->config) || !isset($this->config['STORAGE_TYPE'])) {
			throw new \Exception($this->storage . ' storage config error', 500);
		}
	}

	public function __call($method, $args)
	{
		if (!isset(self::$objArr[$this->storage])) {
			$storageDriver = 'base' . '\\storage\\' . ucfirst($this->config['STORAGE_TYPE']) . 'Driver';

			if (!class_exists($storageDriver)) {
				throw new \Exception('Storage Driver \'' . $storageDriver . '\' not found\'', 500);
			}

			self::$objArr[$this->storage] = new $storageDriver($this->config);
		}

		return call_user_func_array(array(self::$objArr[$this->storage], $method), $args);
	}
}


?>
