<?php
require 'vendor/autoload.php';
$config = [
	//引擎:file,mysql,memcache,redis
	'driver'    => 'file',
	//session_name
	'name'      => 'hdcmsid',
	//cookie加密密钥
	'secureKey' => 'houdunwang88',
	//有效域名
	'domain'    => '',
	//过期时间 0 会话时间 3600 为一小时
	'expire'    => 0,
	#File
	'file'      => [
		'path' => 'storage/session',
	]
];
\houdunwang\config\Config::set( 'session', $config );
//设置cookie
$config = [
	//密钥
	'key'    => '405305c793179059f8fd52436876750c587d19ccfbbe2a643743d021dbdcd79c',
	//前缀
	'prefix' => 'HOUDUNWANG##'
];
\houdunwang\config\Config::set( 'cookie', $config );
$config = [
	/**
	 * 验证错误显示类型
	 * redirect 直接跳转,会分配$errors到前台
	 * show 直接显示错误信息
	 * default 由开发者自行处理
	 */
	'dispose' => 'redirect',
	//发生错误时的显示模板
	'view'    => 'resource/bug.php'
];
\houdunwang\config\Config::set( 'validate', $config );
\houdunwang\validate\Validate::make( [
	[
		'domain',
		function ( $value ) {
			return $value > 100;
		},
		'域名不能为空',
		3
	]
] );