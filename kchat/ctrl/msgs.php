<?php

/**
* KChat -
* Author Ganesh Kandu
* Contact kanduganesh@gmail.com 
*/

class msgs extends ctrl{
	
	function index($data){
		$this->g($data);
		return $data;
	}
	
	function g($data){
		
		$array = array(
			'title' => "Messages"
		);
		$assets = array(
			"js" => array(
				"assets/emojionearea/emojionearea.min.js",
				"assets/pushjs/push.min.js",
			),
			"css" => array(
				"assets/emojionearea/emojionearea.min.css",
			),
		);
		
		$insert = false;
		if(isset($data['param'][0])){
			$users = array();
			$recid = substr($data['param'][0],6,((strlen($data['param'][0]) - 6)));
			$users[] = $recid;
			$users[] = $data['user']['id'];
			$groupid = array();
			foreach($users as $guser){
				$groupid[$guser] = $guser;
			}
			ksort($groupid);
			$gmd5 = md5(serialize($groupid));
			
			$stmt = $data['pdo']->prepare("SELECT `id` FROM {$this->dbprefix}groups where groupid = :groupid");
			$stmt->execute(array('groupid' => $gmd5));
			$row = $stmt->fetch();
			if(!empty($row['id'])){
				$array['param'][0] = urlencode(base64_encode($row['id']));
			}else{
				$insert = true;
			}
		}
		
		if($insert){
			if((strpos($data['param'][0],'Chat_') == 1) && (strpos($data['param'][0],'K') == 0)){
				
				$group = kchat_rand();
				
				$stmt = $data['pdo']->prepare("INSERT INTO `{$this->dbprefix}groups` (`id`,`groupid`) VALUES (:id,:groupid)");
				$stmt->execute(
					array(
						'id' => $group,
						'groupid' => $gmd5,
					)
				);
				
				foreach($users as $user){
					$stmt = $data['pdo']->prepare("INSERT INTO `{$this->dbprefix}group_users` (`grupid`,`users`) VALUES (:grupid,:users)");
					$stmt->execute(
						array(
							'grupid' => $group,
							'users' => $user,
						)
					);
				}
				$stmt = $data['pdo']->prepare("INSERT INTO `{$this->dbprefix}msgs` (`mid`,`msg`,`grp_id`,`sender_id`) VALUES (1,:msg,:grp_id,:sender_id)");
				$stmt->execute(
					array(
						'msg' => 'You are now connected on KChat',
						'grp_id' => $group,
						'sender_id' => $data['user']['id'],
					)
				);
				$array['param'][0] = urlencode(base64_encode($group));
			}
		}

		$this->load->appendfile($assets);
		$this->load->set($array);
		$this->load->view('header');
		$this->load->view('menu');
		$this->load->view('sitebar');
		$this->load->view('msgs');
		$this->load->view('footer');
		
		return $data;
	}
	
}