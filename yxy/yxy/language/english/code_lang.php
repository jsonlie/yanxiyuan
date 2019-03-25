<?php
/**
 * 定义code
 *
 * @author Holyshit
 */
defined('BASEPATH') OR exit('No direct script access allowed');

// 通用，成功
$lang['code_success'] = 0;
$lang['code_fail'] = 100000;        //失败

// 1-19 注册登陆相关
$lang['code_not_login']          = 1;  // 未登录
$lang['code_login_timeout']      = 2;  // 登录超时
$lang['code_login_elsewhere']    = 3;  // 帐号在别的地方登录
$lang['code_user_not_exist']     = 4;  // 用户不存在
$lang['code_incorrect_password'] = 5;  // 密码错误
$lang['code_user_locked']        = 6;  // 账户被锁
$lang['code_user_existed']       = 7;  // 用户已存在
$lang['invite_code_not_exist']       = 8;  // 邀请码不存在


$lang['code_not_wechat_login']     = 10;  // 未用微信登陆
$lang['code_wechat_login_timeout'] = 11;  // 微信登录超时
$lang['code_get_openid_err']       = 12;  // 获取openid失败
$lang['code_get_token_err']        = 13;  // 获取token失败
$lang['code_get_user_info_err']    = 14;  // 获取用户信息失败
$lang['code_get_ticket_err']       = 15;  // 获取票据失败
$lang['code_get_unionid_err']      = 16;  // 获取unionid失败
$lang['code_push_msg_err']         = 17;  // 推送消息失败
$lang['code_qrcode_create_err']    = 18;  // 生成带参数的二维码失败

// 20-69 操作相关
$lang['code_without_permission'] = 20;  // 无权限
$lang['code_timeout']            = 21;  // 超时
$lang['code_captcha_not_match']  = 22;  // 验证码错误
$lang['code_form_validate_err']  = 23;  // 表单验证错误
$lang['code_illegal_operate']    = 24;  // 非法操作
$lang['code_without_param']      = 25;  // 缺少参数
$lang['code_data_abnormal']      = 26;  // 数据异常
$lang['code_capital_not_enough'] = 27;  // 金额不足
$lang['code_processed_suc'] = 28;           // 已处理 - 成功
$lang['code_processed_fail'] = 29;           // 已处理 - 失败
$lang['code_invest_fast'] = 30;         //投资速度太快
$lang['code_processed_full_fail'] = 31; // 满标
$lang['code_cg_fail_time'] = 32; // 授权时间过期
$lang['code_cg_auto_off'] = 33; // 授权被关闭

// 70-79 数据库相关
$lang['code_db_search_err']     = 70;  // 数据库查找数据失败
$lang['code_db_insert_err']     = 71;  // 数据库插入数据失败
$lang['code_db_update_err']     = 72;  // 数据库更新数据失败
$lang['code_db_delete_err']     = 73;  // 数据库删除数据失败
$lang['code_db_search_nothing'] = 74;  // 数据库查找不到数据
$lang['code_db_trans_err']      = 75;  // 数据库事务处理失败

// 80-99 消息相关
$lang['code_send_sms_err'] = 80;   // 发送短信失败
$lang['code_mobile_err'] = 81; //手机号错误

// 100 - 199 安全信息相关
$lang['code_idcard_error'] = 100; //身份证信息错误

// 200 - 300 接口相关
$lang['code_illegal_error'] = 200; //非法请求
$lang['code_api_param_error'] = 201; //请求参数错误
$lang['code_api_token_error'] = 202; //token验证失败
$lang['code_api_timeout'] = 203; //请求超时
$lang['code_api_not_reg'] = 204;    //未注册
$lang['code_api_is_reg'] = 205;    //已注册

//1000 - 1100 充值活动相关
$lang['code_acvitity_user_exist'] = 10001; //已经参加活动
$lang['code_not_recharge'] = 10002; //尚未充值
$lang['code_no_qualification'] = 10003; //不是新用户
// 60000- 其他
$lang['code_curl_err']       = 60000;  // curl错误
$lang['code_escrow_err']     = 60001;  // 第三方资金托管返回错误
$lang['code_pdf_mk_err']     = 60002;  // pdf生成失败
$lang['code_upload_err']     = 60003;  // 上传失败
$lang['code_mkdir_err']      = 60004;  // 创建目录失败
$lang['code_file_not_exist'] = 60005;  // 文件不存在
$lang['code_mbop_err'] = 60006;  //话费或流量充值错误
//小程序抽奖
$lang['code_lottery_submit']       = 2018;  // 已开奖，可以提交表单