<?
include_once("./_common.php");

// SMS 설정값 배열변수
$sms4 = sql_fetch("select * from $g4[sms4_config_table] ");

$err = null;

if (!$sms4[cf_member])
    $err = "문자전송이 허용되지 않았습니다.\\n\\n사이트 관리자에게 문의하여 주십시오.";

if (!$err and !$is_member)
    $err = "로그인 해주세요.";

if (!$err and $member[mb_level] < $sms4[cf_level])
    $err = "회원 $sms4[cf_level] 레벨 이상만 문자전송이 가능합니다.";

// 오늘 문자를 보낸 총 건수
$row = sql_fetch(" select count(*) as cnt from $g4[sms4_member_history_table] where mb_id='$member[mb_id]' and date_format(mh_datetime, '%Y-%m-%d') = '$g4[time_ymd]' ");
$total = $row[cnt];

// 건수 제한
if (!$err and $sms4[cf_day_count] > 0 && $is_admin != 'super') {
    if ($total >= $sms4[cf_day_count]) {
        $err = "하루에 보낼수 있는 문자갯수(".number_format($sms4[cf_day_count])." 건)를 초과하였습니다.";
    }
}

// 포인트 검사
if (!$err and $sms4[cf_point] > 0 && $is_admin != 'super') {
    if ($sms4[cf_point] > $member[mb_point])
        $err = "보유하신 포인트(".number_format($member[mb_point])." 포인트)가 없거나 모자라서\\n\\n문자전송(".number_format($sms4[cf_point])." 포인트)이 불가합니다.\\n\\n포인트를 적립하신 후 다시 시도 해 주십시오.";
}

// 특정회원에게 문자 전송
if ($mb_id) {
    $mb = get_member($mb_id);
    if (!$mb[mb_sms] || !$mb[mb_open]) {
        alert("정보를 공개하지 않았습니다.");
    }
    $hp = $mb[mb_hp];
}

$g4[title] = "문자전송";

$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

include_once("$g4[path]/head.sub.php");
?>

<table border=0 width=100% cellpadding=0 cellspacing=0>
<tr>
    <td width=160 valign=top>

        <div <? if ($err) { echo " onclick=\"sms_error(this, '$err');\""; } ?> >
        <table width=160 align=center border=0 cellpadding=0 cellspacing=0>
        <form action="./write_update.php" onsubmit="return smssend_submit(this)" name="smsform" method="post" autocomplete="off">
        <input type=hidden name=token value='<?=$token?>'>
        <input type=hidden name=mh_hp value=''>
        <colgroup width=50>
        <colgroup width=''>
        <tr>
            <td title='단축키 alt+m' align=center>
                <div style="background-image:url('<?=$g4[path]?>/sms/img/smsbg.gif'); width:160px; height:150px; text-align:center; font-size:11px;">
                <textarea name='mh_message' id='mh_message' class=ed 
                    style="font-family:굴림체; color:#000; line-height:15px; margin:auto; margin-top:30px; margin-bottom:10px; overflow:hidden; width:100px; height:88px; background-color:#88C8F8; text-align:left; word-break:break-all; font-size:9pt; border:0;" cols="16" onkeyup="byte_check('mh_message', 'sms_bytes');" 
                    accesskey="m" itemname='문자메세지'
                    <? if ($err) { echo " disabled "; } ?> ></textarea>
                <div>
                <span id='sms_bytes' align='center'>0</span> / 80 <span style="letter-spacing:-1px;">바이트</span>
                </div>
                </div>
            </td>
        </tr>
        <tr>
            <td title='받는 번호'>
                <div style="letter-spacing:-1px; margin:5px 0 5px 0; color:#777;">받는 번호</div>
                <div style="height:96px; border:1px solid #ccc; overflow:auto;">
                    <table border=0 cellspacing=0 cellpadding=0 width=100%>
                    <colgroup align=center width=20>
                    <colgroup align=center>
                    <? for ($i=1; $i<=50; $i++) { ?>
                    <tr>
                        <td style="font-size:11px; border-right:1px solid #ccc; border-bottom:1px solid #ccc; background-color:#efefef; text-align:center; height:18px;"> 
                            <?=sprintf("%02d", $i)?> 
                        </td>
                        <td style="border-bottom:1px solid #ccc;"> 
                            <input type=text name=numbers style="width:100%; border:0; font-size:11px; font-weight:bold;"<? if ($err) { echo " disabled "; } ?>> 
                        </td>
                    </tr>
                    <? } ?>
                    </table>
                </div> 
            </td>
        </tr>
        <tr>
            <td title='보내는 번호'>
                <div style="letter-spacing:-1px; margin:5px 0 5px 0; color:#777;">보내는 번호</div>
                <input name="mh_reply" type="text" class=ed style="width:100%; font-weight:bold; font-size:11px;" title='회원정보의 휴대폰번호' value='<?=$member[mb_hp]?>' <?if ($is_admin != 'super') {?> readonly onclick="alert('회원정보의 휴대폰번호입니다.\n\n휴대폰번호 변경은 회원 정보수정 메뉴를 이용해주세요.')" <? } ?>>
            </td>
        </tr>
        <tr>
            <td title='예약'>
                <div style="letter-spacing:-1px; margin:5px 0 5px 0; color:#777;">
                    <label for='booking_flag'>예약</label>
                    <input type="checkbox" name="booking_flag" id="booking_flag" value="true" onclick="booking_show()"<? if ($err) { echo " disabled "; } ?>>
                </div>

                <div id='reserved' style='margin-top:5px; margin-bottom:10px; text-align:right;'>
                <select name="mh_by" id="mh_by" style="font-size:7pt; width:37px;" disabled><? for ($i=date("Y"); $i<=date("Y")+1; $i++) { echo "<option value='$i'>".substr($i,-2)."</option>"; } ?></select>년
                <select name="mh_bm" id="mh_bm" style="font-size:7pt; width:37px;" disabled><? for ($i=1; $i<=12; $i++) { if ($i==date('m')) $sel='selected'; else $sel=''; echo "<option value='".sprintf("%02d", $i)."' $sel>".sprintf("%02d", $i)."</option>"; } ?></select>월
                <select name="mh_bd" id="mh_bd" style="font-size:7pt; width:37px;" disabled><? for ($i=1; $i<=31; $i++) { if ($i==date('d')) $sel='selected'; else $sel=''; echo "<option value='".sprintf("%02d", $i)."' $sel>".sprintf("%02d", $i)."</option>"; } ?></select>일<br/>

                <select name="mh_bh" id="mh_bh" style="font-size:7pt; width:37px;" disabled><? for ($i=0; $i<=23; $i++) { if ($i==date('h')) $sel='selected'; else $sel=''; echo "<option value='".sprintf("%02d", $i)."' $sel>".sprintf("%02d", $i)."</option>"; } ?></select>시
                <select name="mh_bi" id="mh_bi" style="font-size:7pt; width:37px;" disabled><? for ($i=0; $i<=59; $i++) { if ($i==date('i')) $sel='selected'; else $sel=''; echo "<option value='".sprintf("%02d", $i)."' $sel>".sprintf("%02d", $i)."</option>"; } ?></select>분
                </div>
            </td>
        </tr>
        <? if (!$err) { ?>
        <tr>
            <td align=center><input type="submit" value="보내기"></td>
        </tr>
        <? } ?>
        </form>
        </table>
        </div>
    </td>
    <td style="padding-left:20px;" valign=top>
        <iframe id=form_list name=form_list src="write_form.php" border=0 frameborder=0 width=100%></iframe>
    </td>
</tr>
</table>


<script language="JavaScript">
function sms_error(obj, err) {
    alert(err);
    obj.value = '';
}

function smssend_submit(f)
{
    <? if ($err) { ?>
    alert("<?=$err?>");
    return false;
    <? } ?>

    if (!f.mh_message.value)
    {
        alert('보내실 문자를 입력하십시오.');
        f.mh_message.focus();
        return false;
    }

    if (!f.mh_reply.value)
    {
        alert('발신 번호를 입력하십시오.\n\n발신 번호는 회원정보의 핸드폰번호입니다.');
        return false;
    }
    var flag = false;
    var tmp = '';
    for (i=0; i<f.numbers.length; i++) {
        if (f.numbers[i].value.length > 0) {
            flag = true;
            tmp += f.numbers[i].value + ',';
        }
    }
    if (!flag) {
        alert('수신 번호를 하나 이상 입력하십시오.');
        return false;
    }

    f.mh_hp.value = tmp;

    return true;
    //f.submit();    
    //win.focus();
}

function booking_show()
{
    if (document.getElementById('booking_flag').checked) {
        document.getElementById('mh_by').disabled   = false;
        document.getElementById('mh_bm').disabled   = false;
        document.getElementById('mh_bd').disabled   = false;
        document.getElementById('mh_bh').disabled   = false;
        document.getElementById('mh_bi').disabled   = false;
    } else {
        document.getElementById('mh_by').disabled   = true;
        document.getElementById('mh_bm').disabled   = true;
        document.getElementById('mh_bd').disabled   = true;
        document.getElementById('mh_bh').disabled   = true;
        document.getElementById('mh_bi').disabled   = true;
    }
}

function byte_check(mh_message, sms_bytes)
{
    var conts = document.getElementById(mh_message);
    var bytes = document.getElementById(sms_bytes);

    var i = 0;
    var cnt = 0;
    var exceed = 0;
    var ch = '';

    for (i=0; i<conts.value.length; i++) 
    {
        ch = conts.value.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }

    bytes.innerHTML = cnt;

    if (cnt > 80) 
    {
        exceed = cnt - 80;
        alert('메시지 내용은 80바이트를 넘을수 없습니다.\n\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\n\n초과된 부분은 자동으로 삭제됩니다.');
        var tcnt = 0;
        var xcnt = 0;
        var tmp = conts.value;
        for (i=0; i<tmp.length; i++) 
        {
            ch = tmp.charAt(i);
            if (escape(ch).length > 4) {
                tcnt += 2;
            } else {
                tcnt += 1;
            }

            if (tcnt > 80) {
                tmp = tmp.substring(0,i);
                break;
            } else {
                xcnt = tcnt;
            }
        }
        conts.value = tmp;
        bytes.innerHTML = xcnt;
        return;
    }
}

byte_check('mh_message', 'sms_bytes');

<? 
if ($hp) { 
    echo "document.getElementsByName('numbers')[0].value = '$hp'";
} 
?>
</script>

<?
include_once("$g4[path]/tail.sub.php");
?>