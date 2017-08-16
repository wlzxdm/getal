<?php

	set_time_limit(0);
	header('Content-type:text/html;charset=gb2312;');
	require 'simple_html_dom.php';  

	$urls = array();

	// $o1 = new simple_html_dom('http://www.148com.com/html/67/');
	// 主页
	$o1 = file_get_html('http://www.960law.com/Class/43.shtm');

	// 二级进到更多
	$index_urls = $o1->find('.More a');
	foreach ($index_urls as $key => $value) {
		$cnt = 0;
		while($cnt<3 &&($o2 = file_get_html('http://www.960law.com'.$value->href))=== false){$cnt++;}
		
		// 第一页论文链接地址
		$o2_urls = $o2->find('#dlArticle a');
		foreach($o2_urls as $item){
			$urls[] = 'http://www.960law.com'.$item->href;
			echo 'http://www.960law.com'.$item->href . PHP_EOL;
			getcontent('http://www.960law.com'.$item->href);
		}
		
		// 如果有分页循环分页获取其他分页上的链接
		$page = $o2->find('#hlNext');
		
		$o2->clear();
		unset($o2_urls);
		
		if($page){
			checkpage($page[0]->href);
		}else{
			continue;
		}
		
	}

	$o1->clear();
	unset($index_urls);
	echo "get lunwen data is finished ^^^^" . PHP_EOL;

	/**
	* 递归获取所有分页上的论文列表
	*/
	function checkpage($url){
		$cnt = 0;
		while($cnt<3 && ($o3 = file_get_html('http://www.960law.com'.$url)) === false){$cnt++;}
		$o3_urls = $o3->find('#dlArticle a');
		foreach($o3_urls as $item){
			$urls[] = 'http://www.960law.com'.$item->href;
			echo 'http://www.960law.com'.$item->href . PHP_EOL;
			getcontent('http://www.960law.com'.$item->href);
		}
		$page = $o3->find('#hlNext');

		$o3->clear();
		unset($o3_urls);
		if($page){
			checkpage($page[0]->href);
		}
	}

	/**
	* 获取论文内容
	*/
	function getcontent($url){
		$o = file_get_html($url);
		// 分类
		$cate = $o->find('#lbNav a',1)->plaintext;

		// title
		$title = $o->find('#lalTitle')[0]->plaintext;

		// 发布时间
		$time = $o->find('#lbInDate')[0]->plaintext;

		// 来源
		$source = $o->find('#lbSource')[0]->plaintext;

		// 作者
		$author = $o->find('#lbAuthor')[0]->plaintext;

		// 内容
		$content = $o->find('#lalContent')[0]->innertext;

		// 写表
		// insert(array('cate'=>trim($cate),'title'=>trim($title),'content'=>trim($content),'sub_time'=>trim($time),'source'=>trim($source),'author'=>trim($author)));
	}

	function insert($data){
		// 数据库连接信息
		$dbname = 'articlesource';  // 数据库名称
		$user = 'root';     // 用户
		$pass = '123456';     // 密码

		set_time_limit(0);

		try {
			$dsn = "mysql:host=192.168.10.110;dbname=$dbname";
			$db = new PDO($dsn, $user, $pass);

			// echo 'PDO connected success ...' . PHP_EOL;

			
			$db->exec('set names utf8');

			$sql_exists = "select id from lunwen where title = '".$data['title']."';";
			$record = $db->query($sql_exists)->fetch();
			if($record['id']){
				echo $data['title'] . ' record is exists . ' . PHP_EOL;
				return true;
			}

			$sql = "INSERT lunwen(cate,title,content,sub_time,source,author)VALUES('".$data['cate']."','".$data['title']."','".$data['content']."','".$data['sub_time']."','".$data['source']."','".$data['author']."');";
			$db->exec($sql);
        	$id = $db->lastInsertId();
			echo 'inserted success . id = '.$id . ' | title = ' . $data['title'] . PHP_EOL . PHP_EOL;
		}
		catch (PDOException $e) {
			echo $e->getMessage() . "\r\n";
			exit;
		}
	}
?>