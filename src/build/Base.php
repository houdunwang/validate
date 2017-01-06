<?php
/** .-------------------------------------------------------------------
 * |  Software: [HDCMS framework]
 * |      Site: www.hdcms.com
 * |-------------------------------------------------------------------
 * |    Author: 向军 <2300071698@qq.com>
 * |    WeChat: aihoudun
 * | Copyright (c) 2012-2019, www.houdunwang.com. All Rights Reserved.
 * '-------------------------------------------------------------------*/
namespace houdunwang\validate\build;

use Closure;
use houdunwang\config\Config;
use houdunwang\session\Session;
use houdunwang\view\View;

/**
 * 表单验证
 * Class Validate
 * @package hdphp\validate
 * @author 向军
 */
class Base extends VaAction {
	//有字段时验证
	const EXISTS_VALIDATE = 1;
	//值不为空时验证
	const VALUE_VALIDATE = 2;
	//必须验证
	const MUST_VALIDATE = 3;
	//值是空时处理
	const VALUE_NULL = 4;
	//不存在字段时处理
	const NO_EXISTS_VALIDATE = 5;
	//扩展验证规则
	protected $validate = [ ];
	//错误信息
	protected $error = [ ];

	/**
	 * 表单验证
	 *
	 * @param $validates 验证规则
	 * @param array $data 数据
	 *
	 * @return $this
	 */
	public function make( $validates, array $data = [ ] ) {
		$data = $data ? $data : $_POST;

		foreach ( $validates as $validate ) {
			//验证条件
			$validate[3] = isset( $validate[3] ) ? $validate[3] : self::MUST_VALIDATE;

			if ( $validate[3] == self::EXISTS_VALIDATE && ! isset( $data[ $validate[0] ] ) ) {
				continue;
			} else if ( $validate[3] == self::VALUE_VALIDATE && empty( $data[ $validate[0] ] ) ) {
				//不为空时处理
				continue;
			} else if ( $validate[3] == self::VALUE_NULL && ! empty( $data[ $validate[0] ] ) ) {
				//值为空时处理
				continue;
			} else if ( $validate[3] == self::NO_EXISTS_VALIDATE && isset( $data[ $validate[0] ] ) ) {
				//值为空时处理
				continue;
			} else if ( $validate[3] == self::MUST_VALIDATE ) {
				//必须处理
			}
			//表单值
			$value = isset( $data[ $validate[0] ] ) ? $data[ $validate[0] ] : '';

			//验证规则
			if ( $validate[1] instanceof Closure ) {
				$method = $validate[1];
				//闭包函数
				if ( $method( $value ) !== true ) {
					$this->error[] = $validate[2];
				}
			} else {
				$actions = explode( '|', $validate[1] );
				foreach ( $actions as $action ) {
					$info   = explode( ':', $action );
					$method = $info[0];
					$params = isset( $info[1] ) ? $info[1] : '';
					if ( method_exists( $this, $method ) ) {
						//类方法验证
						if ( $this->$method( $validate[0], $value, $params, $data ) !== true ) {
							$this->error[] = $validate[2];
						}
					} else if ( isset( $this->validate[ $method ] ) ) {
						$callback = $this->validate[ $method ];
						if ( $callback instanceof Closure ) {
							//闭包函数
							if ( $callback( $validate[0], $value, $params, $data ) !== true ) {
								$this->error[] = $validate[2];
							}
						}
					}
				}
			}
		}

		//验证返回信息处理
		$this->respond( $this->error );

		return $this;
	}

	/**
	 * 验证返回信息处理
	 *
	 * @param $errors
	 */
	public function respond( $errors ) {
		//错误信息记录
		Session::set( 'errors', $errors );
		//验证返回信息处理
		if ( count( $errors ) > 0 ) {
			switch ( Config::get( 'validate.dispose' ) ) {
				case 'redirect':
					echo '<script>location.href="' . $_SERVER['HTTP_REFERER'] . '";</script>';
					exit;
				case 'show':
					View::with('errors',$errors);
					echo View::make(Config::get( 'validate.template' ));
					exit;
				case 'default':
					break;
			}
		}
	}

	/**
	 * 添加验证闭包
	 *
	 * @param $name
	 * @param $callback
	 */
	public function extend( $name, $callback ) {
		if ( $callback instanceof Closure ) {
			$this->validate[ $name ] = $callback;
		}
	}

	/**
	 * 验证判断是否失败
	 * @return bool
	 */
	public function fail() {
		return ! empty( $this->error );
	}

	/**
	 * 获取错误信息
	 * @return array
	 */
	public function getError() {
		return $this->error;
	}
}