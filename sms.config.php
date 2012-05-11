<?
//==============================================================================
// SMS 상수, 변수
//==============================================================================

//------------------------------------------------------------------------------
// SMS 변수 모음 시작
//------------------------------------------------------------------------------
// SMS 디렉토리
$g4[sms]            = "sms";
$g4[sms_path]       = "$g4[path]/$g4[sms]";
$g4[sms_url]        = "$g4[url]/$g4[sms]";

$g4[sms_admin]      = "sms_admin";
$g4[sms_admin_path] = "$g4[path]/$g4[admin]/$g4[sms_admin]";
$g4[sms_admin_url]  = "$g4[url]/$g4[admin]/$g4[sms_admin]";

// SMS 테이블명
$g4[sms4_prefix]            = "sms4_";
$g4[sms4_config_table]      = $g4[sms4_prefix] . "config";
$g4[sms4_write_table]       = $g4[sms4_prefix] . "write";
$g4[sms4_history_table]     = $g4[sms4_prefix] . "history";
$g4[sms4_book_table]        = $g4[sms4_prefix] . "book";
$g4[sms4_book_group_table]  = $g4[sms4_prefix] . "book_group";
$g4[sms4_form_table]        = $g4[sms4_prefix] . "form";
$g4[sms4_form_group_table]  = $g4[sms4_prefix] . "form_group";

$g4[sms4_member_history_table]  = $g4[sms4_prefix] . "member_history";

// Demo 설정
if (file_exists("$g4[path]/DEMO"))
{
    // 받는 번호를 010-000-0000 으로 만듭니다.
    $g4[sms4_demo] = true;

    // 아이코드에 실제로 보내지 않고 가상(Random)으로 전송결과를 저장합니다.
    $g4[sms4_demo_send] = true;
}
?>