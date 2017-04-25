<?php

//自定义一个单表信息操作类

class ModelConnect
{
	//成员属性
	protected $link;//数据库连接属性
	//添加几个属性
	protected $where = array();//搜索条件
	protected $order = null;//排序条件
	protected $limit = null;//分页条件
	//成员方法

	// 构造方法实现数据库的连接，并初始化表名
	public function __construct()
	{
		$this->link = @mysqli_connect(HOST,USER,PASS,DBNAME) or die("数据库连接失败");
		mysqli_set_charset($this->link,"utf8");
	}


	//获取信息条数的方法
	public function total($tablename)
	{
		$sql = "select count(*) from {$tablename}";
		//判断封装搜索添加
		if(count($this->where)>0){//说明要封装搜索条件
			$sql .= " where ".implode(" and ",$this->where);
		}
		$result = mysqli_query($this->link,$sql);
		//解析 所有数据
		$res = mysqli_fetch_row($result);
		//var_dump($res);
		mysqli_free_result($result);

		return $res[0];
	}	

	//获取信息(带条件)
	public function select($sqli)
	{
		$sql = $sqli;
		//判断封装搜索添加
		if(count($this->where)>0){//说明要封装搜索条件
			$sql .= " where ".implode(" and ",$this->where);
		}

		//判断排序
		if(!empty($this->order)){
			$sql .= " order by ".$this->order;
		}

		//判断分页
		if(!empty($this->limit)){
			$sql .= " limit ".$this->limit;
		}

		//echo $sql."<br>";
		
		$result = mysqli_query($this->link,$sql);
		//解析 所有数据
		$list = mysqli_fetch_all($result,MYSQLI_ASSOC);
		mysqli_free_result($result);
		$this->where = array();//搜索条件
		$this->order = null;//排序条件
		$this->limit = null;//分页条件
		return $list;
	}	

	//析构方法 关闭数据库
	public function __destruct()
	{
		//判断连接是否为空 然后关闭数据库
		if(!empty($this->link)){
			mysqli_close($this->link);
		}
	}

	//拼装select()条件
	//添加where条件
	public function where($where)
	{
		if(!empty($where)){
			$this->where[] = $where;
		}
		return $this;
	}

	//封装一个排序
	public function order($order)
	{
		$this->order = $order;
		return $this;
	}

	//封装分页
	public function limit($m,$n=0)
	{
		if($n==0){
			$this->limit = $m;//取前多少条 m条
		}else{
			$this->limit = $m.",".$n;
		}
		return $this;
	}
}


