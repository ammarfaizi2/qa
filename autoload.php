<?php

spl_autoload_register(function ($class) {
	$class = str_replace('\\', '/', $class).'.php';
	if (file_exists($f = __DIR__.'/classes/'.$class)) {
		require $f;
	}
	if (file_exists($f = __DIR__.'/fb/src/'.$class)) {
		require $f;
	}
});