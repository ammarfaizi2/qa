<?php

use Facebook\Facebook;

class ShellBinding
{
	private $fb;

	private $username;

	private $queues = [];

	private function pr($msg)
	{
		print $msg."\n";
	}

	public function __construct(Facebook $fb, $username)
	{
		$this->fb = $fb;
		$this->username = $username;
		is_dir(queue.'/binding') or mkdir(queue.'/binding');
		is_dir(queue.'/binding/comments') or mkdir(queue.'/binding/comments');
		$this->loadQueue();
	}

	private function loadQueue()
	{
		if (file_exists($f = queue.'/binding/posts.txt')) {
			$this->queues = json_decode(file_get_contents($f), true);
			if (! is_array($this->queues)) {
				$this->queues = [];
			}
		}
	}

	public function run()
	{
		$this->tokenizer();
		$this->prepareJobs();
		$this->do();
	}

	private function do()
	{
		foreach ($this->queues as $key => &$val) {
			$offset = $val['offset'];
			do {
				$a = $this->fb->goTo('https://graph.facebook.com/'.$key.'/comments?limit=1&offset='.$offset.'&fields=message,from&access_token='.$this->token);
				if ($a['info']['http_code'] !== 200) {
					$this->tokenizer(true);
					$a = $this->fb->goTo('https://graph.facebook.com/'.$key.'/comments?limit=1&offset='.$offset.'&fields=message,from&access_token='.$this->token);
				}
				if ($a['info']['http_code'] === 200) {
					$a = json_decode($a['out'], true);
					if ($w = isset($a['data']) && count($a['data'])) {
						$val['offset'] = ++$offset;
						if (isset($a['data'][0]['message'])) {
							$a['data'][0]['id'] = explode('_', $a['data'][0]['id']);
							$a['data'][0]['id'] = $a['data'][0]['id'][1];
							$this->replyAction($key, $a['data'][0]['message'], $a['data'][0]['id'], $a['data'][0]['from']);
							$this->saveQueues();
						}
					}
				} else {
					/*if (! preg_match('/Unsupported get request/Usi', $a['out'])) {
						die('Invalid tokenizer data'.PHP_EOL);
					}*/
					echo "Invalid tokenizer auth.\n";
				}
			} while ($w);
		}
		$this->saveQueues();
	}

	private function replyAction($postId, $msg, $id, $from)
	{
		$a = [];
		if (file_exists($f = queue.'/binding/comments/'.$postId.'.txt')) {
			$a = json_decode(file_get_contents($f), true);
		}
		if (! isset($a['replied'][$id])) {
			$a['replied'][$id] = [
				'date' => date('Y-m-d H:i:s'),
				'status' => $this->fb->replyComment("Action: /bin/sh -c \"".$msg."\"\nDate Time: ".date('Y-m-d H:i:s')."\n\n".$this->execBash($msg, $from['id'] === '100000590125569'), $id),
				'message' => $msg,
				'from' => $from
			];
			file_put_contents($f, json_encode($a, 128));
		}
	}

	private function execBash($msg, $sudo)
	{
		$f = '/tmp/'.substr(sha1(time()), 0, 3).rand(100, 999).'.sh';
		file_put_contents($f, $msg);
		shell_exec('sudo chmod 777 '.$f);
		if ($sudo) {
			$msg = shell_exec($f.' 2>&1');
		} else {
			$msg = shell_exec('sudo -u limited '.$f.' 2>&1');
		}
		unlink($f);
		return $msg;
	}

	private function saveQueues()
	{
		file_put_contents(queue.'/binding/posts.txt', json_encode($this->queues, 128));
	}

	private function tokenizer($force = false)
	{
		$a = file_exists(token_file) ? json_decode(file_get_contents(token_file), true) : null;
		if (!$force && is_array($a) && isset($a['token'], $a['expired']) && $a['expired'] > time()) {
			$this->token = $a['token'];
		} else {
			$f = file_get_contents(config.'/token_generation.txt');
			$f = $this->fb->goTo($f, [CURLOPT_HEADER => 2]);
			$f = substr($f['out'], 0, $f['info']['header_size']);
			if (preg_match('/https:\/\/www.instagram.com\/accounts\/signup\/\?#access_token=(.*)&expires_in=(.*)&/Usi', $f, $matches)) {
				$this->token = $a['token'] = $matches[1];
				$a['expired'] = time() + $matches[2] - 120;
				$a['updated_at'] = date('Y-m-d H:i:s');
				file_put_contents(token_file, json_encode($a, 128));
			} else {
				die;
			}
		}
	}

	private function hasQueued($id)
	{
		return isset($this->queues[$id]);
	}

	private function prepareJobs()
	{
		$this->pr('Fecthing last post...');
		$a = $this->fb->goTo('https://graph.facebook.com/'.$this->username.'/feed?limit=1&fields=message,id&access_token='.$this->token);
		if ($a['info']['http_code'] !== 200) {
			$this->pr("Got http code ".$a['info']['http_code']."\nGenerating new acccess token...");
			$this->tokenizer(true);
			$this->pr("Access token generated");
			$a = $this->fb->goTo('https://graph.facebook.com/'.$this->username.'/feed?limit=1&fields=message,id&access_token='.$this->token);
			$this->pr('Fecthing last post...');
		}
		if ($a['info']['http_code'] === 200) {
			$a = json_decode($a['out'], true);
			var_dump($a);
			$this->pr("Got ".json_encode($a['data'][0]));
			if (isset($a['data'][0]['message']) && strtolower(substr($a['data'][0]['message'], 0, 2)) === 'sh') {
				$a['data'][0]['id'] = explode('_', $a['data'][0]['id']);
				$a['data'][0]['id'] = $a['data'][0]['id'][1];
				if (! $this->hasQueued($a['data'][0]['id'])) {
					$this->addQueue($a['data'][0]['message'], $a['data'][0]['id']);
				}
			} else {
				$this->pr("Not shell init message");
			}
		}
	}

	private function addQueue($msg, $id)
	{
		if (count($this->queues) > 3) {

			// unset first index (also work in assoc array)
			foreach ($this->queues as $key => $val) {
				$this->fb->comment("Shell Binding Terminated.\n\n
Server Logs : 
pam_unix(cron:session): session closed for user root
Starting Clean php session files...
Connection closed by 69.12.94.61 port\n\nNew Shell Binding Session : https://m.facebook.com/{$id}".rand(3000, 8888), $key);
				unset($this->queues[$key]);
				break;
			}

		}
		$a = $this->fb->comment("Shell Binding Initialized...\nDate Time: ".date('Y-m-d H:i:s')."\n
Server accepts key: pkalg rsa-sha2-512 blen 535
Authentication succeeded (publickey).
Authenticated to 69.12.94.61 ([69.12.94.61]:22).
"/*"Shell Binding Initialized\n
Server accepts key: pkalg rsa-sha2-512 blen 535
input_userauth_pk_ok: fp SHA256:yL6a9PTGUCCFL7lByIQjIlpy3cd42qc9k97+WYzR+v8
sign_and_send_pubkey: RSA SHA256:yL6a9PTGUCCFL7lByIQjIlpy3cd42qc9k97+WYzR+v8"*/, $id);
		$this->queues[$id] = [
			'created_at' => date('Y-m-d H:i:s'),
			'queue' => 0,
			'offset' => 0,
			'message' => $msg
		];
	}
}
