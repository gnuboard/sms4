<?
$sub_menu = "900100";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

check_demo();

$g4[title] = "SMS 기본설정";

$res = get_sock("http://www.icodekorea.com/res/userinfo.php?userid=$cf_id&userpw=$cf_pw");
$res = explode(';', $res);
$userinfo = array(
    'code'      => $res[0], // 결과코드
    'coin'      => $res[1], // 고객 잔액 (충전제만 해당)
    'gpay'      => $res[2], // 고객의 건수 별 차감액 표시 (충전제만 해당)
    'payment'   => $res[3]  // 요금제 표시, A:충전제, C:정액제
);

if ($userinfo[code] == '202')
    alert('아이코드 아이디와 패스워드가 맞지 않습니다.');

if ($userinfo[payment] == 'A')
    $cf_port = 1; // 충전제
else
    $cf_port = 2; // 정액제

if ($cf_member == 'on')
    $cf_member = 1;
else
    $cf_member = 0;

$res = sql_fetch("select * from $g4[sms4_config_table] limit 1");

if (!$res) 
    $sql = "insert into ";
else
    $sql = "update ";

$sql .= "$g4[sms4_config_table] set cf_id='$cf_id', cf_pw='$cf_pw', cf_ip='$cf_ip', cf_port='$cf_port', cf_phone='$cf_phone', cf_register='$cf_register', cf_member='$cf_member', cf_level='$cf_level', cf_point='$cf_point', cf_day_count='$cf_day_count'";

sql_query($sql);

goto_url("./config.php");
?>