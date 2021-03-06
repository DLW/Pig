<?php

//自定义一个单表信息操作类

class Model
{
	//成员属性
	protected $link;//数据库连接属性
	protected $tableName;//数据库表名
	protected $fields = array();//定义表的字段
	protected $pk = "id";//主键叫id
	//添加几个属性
	protected $where = array();//搜索条件
	protected $order = null;//排序条件
	protected $limit = null;//分页条件
	//成员方法

	// 构造方法实现数据库的连接，并初始化表名
	public function __construct($tableName)
	{
		$this->link = @mysqli_connect(HOST,USER,PASS,DBNAME) or die("数据库连接失败");
		mysqli_set_charset($this->link,"utf8");
		$this->tableName = $tableName;
		$this->loadFields();//加载字段
	}

	//加载当前表字段信息的方法
	private function loadFields()
	{
		$sql = "desc {$this->tableName}";
		// die($sql);
		$result = mysqli_query($this->link,$sql);
		//遍历每个字段信息
		while($row = mysqli_fetch_assoc($result)){
			// var_dump($row);
			//获取所有字段的信息
			$this->fields[] = $row['Field'];
			// var_dump($this->fields);
			//获取主键名
			if($row['Key']=="PRI"){
				$this->pk = $row['Field'];
			}
		}
		mysqli_free_result($result);
	}

	//获取所有信息的方法
	public function findAll()
	{
		$sql = "select * from {$this->tableName}";
		$result = mysqli_query($this->link,$sql);
		//解析 所有数据
		$list = mysqli_fetch_all($result,MYSQLI_ASSOC);
		mysqli_free_result($result);

		return $list;
	}

	//获取信息条数的方法
	public function total()
	{
		$sql = "select count(*) from {$this->tableName}";
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
	public function select()
	{
		$sql = "select * from {$this->tableName}";
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

	public function selectconnect()
	{
		$sql = "select * from {$this->tableName}";
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

	//获取指定的单条信息的方法
	public function findone($id)
	{
		$sql = "select * from {$this->tableName} where {$this->pk}={$id}";
		$result = mysqli_query($this->link,$sql);
		//解析单条数据
		$list = mysqli_fetch_assoc($result);
		mysqli_free_result($result);

		return $list;
	}

	//执行添加
	public function add($data=array())
	{
		//var_dump($this->fields);
		//1.若参数无值,则尝试从POSt中获取
		if(empty($data)){
			$data = $_POST;
		}
		// var_dump($data);exit();

		//2.获取有效字段
		$fieldlist = array();
		$valuelist = array();
		foreach($data as $k=>$v){
			//判断是否是有效字段
			if(in_array($k,$this->fields)){
				$fieldlist[] = $k;
				//var_dump($fieldlist);
				$valuelist[] = "'".$v."'";
			}
		}
		// var_dump($fieldlist);
		//拼装sql语句
		$sql = "insert into {$this->tableName}(".implode(",",$fieldlist).") values(".implode(",",$valuelist).")";
		// die($sql);
		//执行添加
		mysqli_query($this->link,$sql);
		//返回结果
		return mysqli_insert_id($this->link);
	}

	//执行修改
	// public function update($id)
	// {
		
	// 	$data=$_POST;
	// 	// var_dump($data);
	// 	$filedlist=array();
	// 	foreach($data as $k=>$v){
	// 		if(in_array($k,$this->fields)){
	// 			$filedlist[]=($k."="."'".$v."'");
	// 			// var_dump($filedlist);
	// 		}
	// 	}
	// 	$sql="update {$this->tablename} set ".implode(",",$filedlist)." where {$this->pk}={$id}";
	// 	mysqli_query($this->link,$sql);
	// 	return mysqli_affected_rows($this->link);
	// }
	public function update($data=array())
	{
		//1.若参数无值,则尝试从POSt中获取
		if(empty($data)){
			$data = $_POST;
		}
		//2.获取有效字段(字段过滤)
		$fieldlist = array();
		foreach($data as $k=>$v){
			//判断是否是有效字段
			if(in_array($k,$this->fields) && $k!=$this->pk){//不是主键
				$fieldlist[] = "{$k}='".$v."'";//name='xiaocang'
			}
			//var_dump($fieldlist);
		}
		// var_dump($fieldlist);
		//拼装sql语句
		$sql = "update {$this->tableName} set ".implode(",",$fieldlist)." where {$this->pk}=".$data[$this->pk];

		//die($sql);//update stu set name='xiaocang',sex='w',age='20',classid='gz27' where id=15
		//执行修改
		mysqli_query($this->link,$sql);
		//返回结果
		return mysqli_affected_rows($this->link);
	}


	//执行删除
	public function del($id)
	{
		$sql = "delete from {$this->tableName} where {$this->pk}={$id}";
		// echo $sql;die();
		mysqli_query($this->link,$sql);
		//返回影响行数
		// var_dump(mysqli_affected_rows($this->link));die();
		return mysqli_affected_rows($this->link);
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


