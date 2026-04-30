<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
date_default_timezone_set('Asia/Shanghai');

define('DATA_DIR', __DIR__ . '/sop_data');
define('SYS_CONFIG_FILE', DATA_DIR . '/config.json');
define('USER_FILE', DATA_DIR . '/users.json');
define('CATE_1_FILE', DATA_DIR . '/cate1.json');
define('CATE_2_FILE', DATA_DIR . '/cate2.json');
define('SOP_FILE', DATA_DIR . '/sop_list.json');
define('LOG_FILE', DATA_DIR . '/logs.json');

if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
initData();

function getSiteName(){
    $cfg = readJson(SYS_CONFIG_FILE);
    return $cfg['site_name'] ?? 'SOP展示系统';
}
function getGuestOpen(){
    $cfg = readJson(SYS_CONFIG_FILE);
    return isset($cfg['guest_open']) ? $cfg['guest_open'] : 1;
}

function initData()
{
    if (!file_exists(SYS_CONFIG_FILE)) {
        saveJson(SYS_CONFIG_FILE, [
            'site_name' => 'SOP展示系统',
            'guest_open' => 1
        ]);
    }
    if (!file_exists(USER_FILE)) {
        saveJson(USER_FILE, [
            ['id'=>1,'username'=>'admin','pwd'=>password_hash('123456',PASSWORD_DEFAULT),'name'=>'超级管理员','role'=>'admin']
        ]);
    }
    if (!file_exists(CATE_1_FILE)) {
        saveJson(CATE_1_FILE, [
            ['id'=>1,'name'=>'售前部门','sort'=>1],
            ['id'=>2,'name'=>'售后部门','sort'=>2],
            ['id'=>3,'name'=>'打单部门','sort'=>3],
            ['id'=>4,'name'=>'生产部门','sort'=>4],
            ['id'=>5,'name'=>'运营部门','sort'=>5],
            ['id'=>6,'name'=>'财务部门','sort'=>6]
        ]);
    }
    if (!file_exists(CATE_2_FILE)) {
        saveJson(CATE_2_FILE, [
            ['id'=>1,'pid'=>1,'name'=>'售前接待咨询','sort'=>1],
            ['id'=>2,'pid'=>2,'name'=>'售后退换货处理','sort'=>2],
            ['id'=>3,'pid'=>2,'name'=>'售后投诉纠纷处理','sort'=>3],
            ['id'=>4,'pid'=>2,'name'=>'售后产品维修对接','sort'=>4],
            ['id'=>5,'pid'=>3,'name'=>'订单拆分/打单发货','sort'=>5],
            ['id'=>6,'pid'=>4,'name'=>'吸顶灯生产工序','sort'=>6],
            ['id'=>7,'pid'=>4,'name'=>'无主灯生产工序','sort'=>7],
            ['id'=>8,'pid'=>5,'name'=>'店铺日常运营','sort'=>8],
            ['id'=>9,'pid'=>6,'name'=>'财务对账/核算','sort'=>9]
        ]);
    }
    if (!file_exists(SOP_FILE)) {
        saveJson(SOP_FILE, [
            [
                'id'=>1,'cate1'=>2,'cate2'=>2,'title'=>'售后退换货处理标准SOP',
                'content'=>'<p>一、客户申请退换货受理流程</p><p>1. 接收客户诉求，<span style="color:#f00">核对订单与产品信息</span></p>',
                'author'=>'管理员','ctime'=>date('Y-m-d H:i:s'),'utime'=>date('Y-m-d H:i:s')
            ]
        ]);
    }
    if (!file_exists(LOG_FILE)) saveJson(LOG_FILE, []);
}

function readJson($file){
    return file_exists($file) ? json_decode(file_get_contents($file),true) : [];
}
function saveJson($file,$data){
    return file_put_contents($file,json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
}
function e($str){
    return htmlspecialchars($str??'',ENT_QUOTES);
}
function addLog($txt){
    $log = readJson(LOG_FILE);
    $u = $_SESSION['user']['name']??'游客';
    array_unshift($log,['time'=>date('Y-m-d H:i:s'),'user'=>$u,'content'=>$txt,'ip'=>$_SERVER['REMOTE_ADDR']]);
    saveJson(LOG_FILE,array_slice($log,0,200));
}
function getCate1Name($id){
    $l=readJson(CATE_1_FILE);
    foreach($l as $v)if($v['id']==$id)return $v['name'];
    return '无';
}
function getCate2Name($id){
    $l=readJson(CATE_2_FILE);
    foreach($l as $v)if($v['id']==$id)return $v['name'];
    return '无';
}
function getCate2ByPid($pid){
    $res=[];$l=readJson(CATE_2_FILE);
    foreach($l as $v)if($v['pid']==$pid)$res[]=$v;
    return $res;
}

$act = $_GET['act'] ?? 'home';
$user = $_SESSION['user'] ?? [];
$SITE_NAME = getSiteName();
$GUEST_OPEN = getGuestOpen();

function isAdmin(){
    global $user;
    return !empty($user) && $user['role']==='admin';
}
function isLogin(){
    global $user;
    return !empty($user);
}
function needLogin(){
    global $GUEST_OPEN;
    if($GUEST_OPEN == 0 && !isLogin()){
        header('Location:?act=login');exit;
    }
}

switch ($act) {
    case 'login':loginPage();break;
    case 'dologin':doLogin();break;
    case 'logout':session_destroy();header('Location:?act=home');exit;
    case 'home':needLogin();homePage();break;
    case 'view':needLogin();viewSop();break;
    case 'search':needLogin();searchSop();break;
    case 'sys_config':sysConfig();break;
    case 'save_config':saveConfig();break;
    case 'user_list':userList();break;
    case 'user_add':userAdd();break;
    case 'user_save':userSave();break;
    case 'user_del':userDel();break;
    case 'cate':cate1List();break;
    case 'cate1_add':cate1Add();break;
    case 'cate1_edit':cate1Edit();break;
    case 'cate1_save':cate1Save();break;
    case 'cate1_del':cate1Del();break;
    case 'cate2_list':cate2List();break;
    case 'cate2_add':cate2Add();break;
    case 'cate2_edit':cate2Edit();break;
    case 'cate2_save':cate2Save();break;
    case 'cate2_del':cate2Del();break;
    case 'sop_list':sopList();break;
    case 'sop_add':sopAdd();break;
    case 'sop_edit':sopEdit();break;
    case 'sop_save':sopSave();break;
    case 'sop_del':sopDel();break;
    case 'log':logList();break;
    default:needLogin();homePage();break;
}

function loginPage(){
    global $SITE_NAME;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>登录 - <?=$SITE_NAME?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{background:#f2f3f5;font-family:Microsoft YaHei;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}
.login-box{width:360px;background:#fff;padding:30px;border-radius:10px;box-shadow:0 0 15px #00000012;}
h2{text-align:center;margin-bottom:25px;color:#333;}
input{width:100%;box-sizing:border-box;padding:12px 15px;border:1px solid #ddd;border-radius:6px;margin:8px 0;}
button{width:100%;padding:12px;background:#2d8cf0;color:#fff;border:none;border-radius:6px;cursor:pointer;}
</style>
</head>
<body>
<div class="login-box">
    <h2>系统登录</h2>
    <input type="text" id="u" placeholder="账号" value="admin">
    <input type="password" id="p" placeholder="密码" value="123456">
    <button onclick="login()">登录</button>
    <div style="text-align:center;margin-top:15px;"><a href="?act=home">返回首页</a></div>
</div>
<script>
function login(){
    fetch('?act=dologin',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'u='+encodeURIComponent(document.getElementById('u').value)+'&p='+encodeURIComponent(document.getElementById('p').value)})
    .then(res=>res.json()).then(ret=>{
        if(ret.code){alert(ret.msg);}else{location.href='?act=home';}
    })
}
</script>
</body>
</html>
<?php
}
function doLogin(){
    $u = trim($_POST['u']??'');
    $p = $_POST['p']??'';
    $users = readJson(USER_FILE);
    foreach($users as $v){
        if($v['username']===$u && password_verify($p,$v['pwd'])){
            $_SESSION['user'] = $v;
            addLog('账号登录：'.$v['username']);
            exit(json_encode(['code'=>0]));
        }
    }
    exit(json_encode(['code'=>1,'msg'=>'账号密码错误']));
}

function adminNav(){
    global $SITE_NAME,$user;
?>
<div style="background:#242933;padding:0 20px;display:flex;align-items:center;justify-content:space-between;color:#fff;line-height:50px;flex-wrap:wrap;position:sticky;top:0;z-index:99;">
    <div style="font-size:18px;font-weight:bold;"><?=$SITE_NAME?></div>
    <div>
        <a href="?act=home" style="color:#fff;margin:0 8px;text-decoration:none;">🏠 首页</a>
        <?php if(isAdmin()):?>
        <a href="?act=sys_config" style="color:#fff;margin:0 8px;text-decoration:none;">站点设置</a>
        <a href="?act=user_list" style="color:#fff;margin:0 8px;text-decoration:none;">用户管理</a>
        <a href="?act=cate" style="color:#fff;margin:0 8px;text-decoration:none;">部门管理</a>
        <a href="?act=cate2_list" style="color:#fff;margin:0 8px;text-decoration:none;">岗位管理</a>
        <a href="?act=sop_list" style="color:#fff;margin:0 8px;text-decoration:none;">SOP管理</a>
        <a href="?act=log" style="color:#fff;margin:0 8px;text-decoration:none;">日志</a>
        <?php endif;?>
        <?php if(isLogin()):?>
        <span style="margin-left:10px;">欢迎：<?=e($user['name'])?>(<?=$user['role']=='admin'?'管理员':'普通用户'?>)</span>
        <a href="?act=logout" style="color:#ff7777;margin-left:10px;text-decoration:none;">退出</a>
        <?php else:?>
        <a href="?act=login" style="color:#fff;margin:0 8px;text-decoration:none;background:#666;padding:6px 12px;border-radius:4px;">登录</a>
        <?php endif;?>
    </div>
</div>
<div style="background:#f5f5f5;padding:10px 20px;border-bottom:1px solid #eee;">
    <form method="get" action="?act=search" style="max-width:600px;margin:0 auto;display:flex;gap:10px;">
        <input type="text" name="kw" placeholder="搜索SOP标题/内容..." style="flex:1;padding:8px;border:1px solid #ddd;border-radius:4px;" required>
        <button type="submit" style="padding:8px 20px;background:#2d8cf0;color:#fff;border:none;border-radius:4px;">搜索</button>
    </form>
</div>
<?php
}

function floatTopBtn(){
?>
<style>
.go-top-left{position:fixed;left:15px;bottom:80px;background:#242933;color:#fff;width:45px;height:45px;text-align:center;line-height:45px;border-radius:50%;cursor:pointer;z-index:999;}
.color-btn{width:24px;height:24px;border:none;border-radius:3px;cursor:pointer;margin:0 2px;}
.tool-bar{padding:8px 10px;background:#f5f5f5;border:1px solid #ddd;border-bottom:none;border-radius:4px 4px 0 0;}
</style>
<div class="go-top-left" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</div>
<?php
}

function leftMenu(){
    $cate1 = readJson(CATE_1_FILE);
?>
<div style="width:240px;background:#fff;border-right:1px solid #eee;min-height:calc(100vh - 102px);padding:15px;position:fixed;left:0;top:102px;">
    <h4 style="margin:0 0 15px 0;border-bottom:1px solid #eee;padding-bottom:10px;">📂 SOP分类菜单</h4>
    <?php foreach($cate1 as $v1):?>
    <div style="margin-bottom:8px;">
        <div style="background:#f5f6f7;padding:10px;border-radius:4px;cursor:pointer;font-weight:bold;" onclick="toggleMenu(<?=$v1['id']?>)">
            ▶ <?=e($v1['name'])?>
        </div>
        <div id="menu_<?=$v1['id']?>" style="display:<?=$v1['id']==2?'block':'none'?>;padding-left:12px;margin-top:5px;border-left:2px solid #2d8cf0;">
            <?php foreach(getCate2ByPid($v1['id']) as $v2):?>
            <div style="margin:6px 0;">
                <div style="color:#666;font-size:14px;cursor:pointer;" onclick="getSopList(<?=$v2['id']?>)">· <?=e($v2['name'])?></div>
            </div>
            <?php endforeach;?>
        </div>
    </div>
    <?php endforeach;?>
</div>
<script>
function toggleMenu(id){
    let box = document.getElementById('menu_'+id);
    let btn = event.target;
    box.style.display = box.style.display==='none'?'block':'none';
    btn.innerHTML = box.style.display==='block' ? '▼ '+btn.innerHTML.replace('▶ ','') : '▶ '+btn.innerHTML.replace('▼ ','');
}
function getSopList(c2){location.href='?act=home&c2='+c2;}
</script>
<?php
}

function sysConfig(){
    if(!isAdmin()){echo '无权限';exit;}
    adminNav();floatTopBtn();
    $cfg = readJson(SYS_CONFIG_FILE);
?>
<div style="padding:20px;max-width:600px;margin:0 auto;">
    <div style="background:#fff;padding:20px;border-radius:8px;">
        <h3>站点设置</h3>
        <form method="post" action="?act=save_config">
            <div style="margin:15px 0;">
                <label>系统标题</label>
                <input type="text" name="site_name" value="<?=e($cfg['site_name'])?>" required style="width:100%;padding:10px;border:1px solid #eee;border-radius:4px;margin-top:5px;">
            </div>
            <div style="margin:15px 0;">
                <label>匿名游客浏览</label>
                <select name="guest_open" style="width:100%;padding:10px;border:1px solid #eee;border-radius:4px;margin-top:5px;">
                    <option value="1" <?=$cfg['guest_open']==1?'selected':''?>>开启（无需登录可查看）</option>
                    <option value="0" <?=$cfg['guest_open']==0?'selected':''?>>关闭（必须登录才能查看）</option>
                </select>
            </div>
            <button type="submit" style="padding:8px 20px;background:#2d8cf0;color:#fff;border:none;border-radius:4px;">保存</button>
        </form>
    </div>
</div>
<?php
}
function saveConfig(){
    if(!isAdmin())exit;
    $data['site_name'] = trim($_POST['site_name']);
    $data['guest_open'] = (int)$_POST['guest_open'];
    saveJson(SYS_CONFIG_FILE,$data);
    addLog('修改系统设置');
    header('Location:?act=sys_config');exit;
}

function userList(){
    if(!isAdmin())exit;
    adminNav();floatTopBtn();
    $users = readJson(USER_FILE);
?>
<div style="padding:20px;max-width:1000px;margin:0 auto;">
    <div style="background:#fff;padding:20px;border-radius:8px;">
        <div style="display:flex;justify-content:space-between;"><h3>用户管理</h3><a href="?act=user_add" style="background:#2d8cf0;color:#fff;padding:6px 12px;border-radius:4px;text-decoration:none;">新增用户</a></div>
        <table width="100%" border="1" cellpadding="10" style="border-collapse:collapse;margin-top:15px;">
            <tr><th>ID</th><th>账号</th><th>昵称</th><th>权限</th><th>操作</th></tr>
            <?php foreach($users as $v):?>
            <tr>
                <td><?=$v['id']?></td>
                <td><?=e($v['username'])?></td>
                <td><?=e($v['name'])?></td>
                <td><?=$v['role']=='admin'?'<span style="color:red">管理员</span>':'<span style="color:green">普通用户</span>'?></td>
                <td><?php if($v['id']!=1):?><a href="?act=user_del&id=<?=$v['id']?>" onclick="return confirm('确定删除？')">删除</a><?php else:?>--<?php endif;?></td>
            </tr>
            <?php endforeach;?>
        </table>
    </div>
</div>
<?php
}
function userAdd(){
    if(!isAdmin())exit;
    adminNav();floatTopBtn();
?>
<div style="padding:20px;max-width:600px;margin:0 auto;">
    <div style="background:#fff;padding:20px;border-radius:8px;">
        <h3>新增用户</h3>
        <form method="post" action="?act=user_save">
            <div style="margin:15px 0;">
                <label>登录账号</label>
                <input type="text" name="username" required style="width:100%;padding:10px;border:1px solid #eee;border-radius:4px;">
            </div>
            <div style="margin:15px 0;">
                <label>登录密码</label>
                <input type="password" name="pwd" required style="width:100%;padding:10px;border:1px solid #eee;border-radius:4px;">
            </div>
            <div style="margin:15px 0;">
                <label>用户昵称</label>
                <input type="text" name="name" required style="width:100%;padding:10px;border:1px solid #eee;border-radius:4px;">
            </div>
            <div style="margin:15px 0;">
                <label>权限角色</label>
                <select name="role" style="width:100%;padding:10px;border:1px solid #eee;border-radius:4px;">
                    <option value="user">普通用户（仅浏览）</option>
                    <option value="admin">管理员（全部权限）</option>
                </select>
            </div>
            <button type="submit" style="padding:8px 20px;background:#2d8cf0;color:#fff;border:none;border-radius:4px;">保存</button>
        </form>
    </div>
</div>
<?php
}
function userSave(){
    if(!isAdmin())exit;
    $users = readJson(USER_FILE);
    $data['id'] = empty($users) ? 1 : max(array_column($users,'id'))+1;
    $data['username'] = trim($_POST['username']);
    $data['pwd'] = password_hash(trim($_POST['pwd']),PASSWORD_DEFAULT);
    $data['name'] = trim($_POST['name']);
    $data['role'] = trim($_POST['role']);
    $users[] = $data;
    saveJson(USER_FILE,$users);
    addLog('新增用户：'.$data['username']);
    header('Location:?act=user_list');exit;
}
function userDel(){
    if(!isAdmin())exit;
    $id = (int)$_GET['id'];
    $users = readJson(USER_FILE);
    foreach($users as $k=>$v)if($v['id']==$id)unset($users[$k]);
    saveJson(USER_FILE,array_values($users));
    header('Location:?act=user_list');exit;
}

function searchSop(){
    adminNav();floatTopBtn();leftMenu();
    $kw = trim($_GET['kw']??'');
    $sopList = readJson(SOP_FILE);
    $res = [];
    foreach($sopList as $v){
        if(stripos($v['title'],$kw)!==false || stripos($v['content'],$kw)!==false) $res[]=$v;
    }
?>
<style>.main-wrap{margin-left:240px;}</style>
<div class="main-wrap">
    <div style="padding:20px;max-width:1200px;margin:0 auto;">
        <div style="background:#e8f4ff;padding:12px;border-radius:6px;margin-bottom:20px;">
            搜索：<?=e($kw)?>，找到 <?=count($res)?> 条
            <a href="?act=home" style="margin-left:15px;color:#2d8cf0;">返回全部</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:15px;">
        <?php if(empty($res)):?>
            <div style="padding:60px 0;text-align:center;color:#999;">无结果</div>
        <?php else:foreach($res as $v):?>
            <div style="background:#fff;padding:16px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                <h3 style="margin:0 0 8px;"><?=e($v['title'])?></h3>
                <div style="font-size:13px;color:#666;margin-bottom:10px;"><?=getCate1Name($v['cate1'])?> / <?=getCate2Name($v['cate2'])?></div>
                <div style="max-height:120px;overflow:hidden;font-size:14px;line-height:1.8;"><?=$v['content']?></div>
                <div style="margin-top:12px;">
                    <a href="?act=view&id=<?=$v['id']?>" style="padding:5px 12px;background:#2d8cf0;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;">查看</a>
                </div>
            </div>
        <?php endforeach;endif;?>
        </div>
    </div>
</div>
<?php
}

function homePage(){
    adminNav();floatTopBtn();leftMenu();
    $c2 = (int)($_GET['c2']??0);
    $sopList = readJson(SOP_FILE);
    if($c2>0){$temp=[];foreach($sopList as $v){if($v['cate2']==$c2)$temp[]=$v;}$sopList=$temp;}
?>
<style>.main-wrap{margin-left:240px;}</style>
<div class="main-wrap">
    <div style="padding:20px;max-width:1200px;margin:0 auto;">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:15px;">
        <?php if(empty($sopList)):?>
            <div style="padding:60px 0;text-align:center;color:#999;">暂无SOP</div>
        <?php else:foreach($sopList as $v):?>
            <div style="background:#fff;padding:16px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                <h3 style="margin:0 0 8px;"><?=e($v['title'])?></h3>
                <div style="font-size:13px;color:#666;margin-bottom:10px;"><?=getCate1Name($v['cate1'])?> / <?=getCate2Name($v['cate2'])?></div>
                <div style="max-height:120px;overflow:hidden;font-size:14px;line-height:1.8;"><?=$v['content']?></div>
                <div style="margin-top:12px;">
                    <a href="?act=view&id=<?=$v['id']?>" style="padding:5px 12px;background:#2d8cf0;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;">查看全文</a>
                    <?php if(isAdmin()):?>
                    <a href="?act=sop_edit&id=<?=$v['id']?>" style="padding:5px 12px;background:#28a745;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;margin-left:5px;">编辑</a>
                    <?php endif;?>
                </div>
            </div>
        <?php endforeach;endif;?>
        </div>
    </div>
</div>
<?php
}

function viewSop(){
    adminNav();floatTopBtn();
    $id = (int)$_GET['id'];
    $sop = readJson(SOP_FILE);
    $info = [];
    foreach($sop as $v) if($v['id']==$id) $info=$v;
    if(empty($info)){echo '<div style="padding:30px;text-align:center;">不存在 <a href="?act=home">返回</a></div>';exit;}
?>
<div style="padding:20px;max-width:900px;margin:0 auto;">
    <div style="background:#fff;padding:30px;border-radius:8px;">
        <h2 style="text-align:center;margin:0 0 20px;"><?=e($info['title'])?></h2>
        <div style="text-align:center;color:#666;margin-bottom:20px;">
            部门：<?=getCate1Name($info['cate1'])?>｜岗位：<?=getCate2Name($info['cate2'])?>
            <?php if(isAdmin()):?><a href="?act=sop_edit&id=<?=$info['id']?>" style="margin-left:20px;color:#28a745;">编辑</a><?php endif;?>
        </div>
        <div style="line-height:2;padding:25px;border:1px solid #eee;border-radius:6px;"><?=$info['content']?></div>
    </div>
</div>
<?php
}

function sopAdd(){
    if(!isAdmin())exit;
    adminNav();floatTopBtn();
    $cate1 = readJson(CATE_1_FILE);
?>
<div style="padding:20px;max-width:1000px;margin:0 auto;">
    <div style="background:#fff;padding:20px;border-radius:8px;">
        <h3>新增SOP</h3>
        <form method="post" action="?act=sop_save">
            <input type="hidden" name="id" value="0">
            <div style="margin:15px 0;">
                <label>部门</label>
                <select name="cate1" id="cate1" onchange="loadCate2()" style="width:100%;padding:8px;">
                    <?php foreach($cate1 as $v):?><option value="<?=$v['id']?>"><?=e($v['name'])?></option><?php endforeach;?>
                </select>
            </div>
            <div style="margin:15px 0;">
                <label>岗位</label>
                <select name="cate2" id="cate2" style="width:100%;padding:8px;">
                    <?php foreach(getCate2ByPid($cate1[0]['id']) as $v):?><option value="<?=$v['id']?>"><?=e($v['name'])?></option><?php endforeach;?>
                </select>
            </div>
            <div style="margin:15px 0;">
                <label>标题</label>
                <input type="text" name="title" required style="width:100%;padding:10px;border:1px solid #eee;border-radius:4px;">
            </div>
            <div style="margin:15px 0;">
                <label>内容</label>
                <div class="tool-bar">
                    <button type="button" onclick="formatBold()"><b>B</b></button>
                    <button type="button" onclick="formatFontColor('#ff0000')" class="color-btn" style="background:#ff0000;"></button>
                    <button type="button" onclick="formatFontColor('#ff6600')" class="color-btn" style="background:#ff6600;"></button>
                    <button type="button" onclick="formatFontColor('#cccc00')" class="color-btn" style="background:#cccc00;"></button>
                    <button type="button" onclick="formatFontColor('#009900')" class="color-btn" style="background:#009900;"></button>
                    <button type="button" onclick="formatFontColor('#0066ff')" class="color-btn" style="background:#0066ff;"></button>
                    <button type="button" onclick="formatFontColor('#9933cc')" class="color-btn" style="background:#9933cc;"></button>
                    <button type="button" onclick="formatFontColor('#333333')" class="color-btn" style="background:#333333;"></button>
                    <button type="button" onclick="clearFormat()" style="margin-left:10px;">清除格式</button>
                </div>
                <div id="editor" style="height:400px;border:1px solid #eee;border-top:none;padding:10px;overflow:auto;" contenteditable="true"></div>
                <textarea name="content" id="content" style="display:none;"></textarea>
            </div>
            <button type="submit" style="padding:10px 30px;background:#2d8cf0;color:#fff;border:none;border-radius:4px;">保存</button>
        </form>
    </div>
</div>
<script>
const cate2Data = <?=json_encode(readJson(CATE_2_FILE),JSON_UNESCAPED_UNICODE)?>;
function loadCate2(){
    let pid = document.getElementById('cate1').value;
    let sel = document.getElementById('cate2');
    sel.innerHTML = '';
    cate2Data.forEach(item=>{if(item.pid==pid) sel.innerHTML += `<option value="${item.id}">${item.name}</option>`;})
}
function formatBold(){document.execCommand('bold',false,null);}
function formatFontColor(color){document.execCommand('foreColor',false,color);}
function clearFormat(){document.execCommand('removeFormat',false,null);}
document.querySelector('form').addEventListener('submit',function(){
    document.getElementById('content').value = document.getElementById('editor').innerHTML;
})
</script>
<?php
}

function sopEdit(){
    if(!isAdmin())exit;
    $id=(int)$_GET['id'];
    $list=readJson(SOP_FILE);
    $info=[];foreach($list as $v)if($v['id']==$id)$info=$v;
    $cate1=readJson(CATE_1_FILE);
    adminNav();floatTopBtn();
?>
<div style="padding:20px;max-width:1000px;margin:0 auto;">
    <div style="background:#fff;padding:20px;border-radius:8px;">
        <h3>编辑SOP</h3>
        <form method="post" action="?act=sop_save">
            <input type="hidden" name="id" value="<?=$info['id']?>">
            <div style="margin:15px 0;">
                <label>部门</label>
                <select name="cate1" id="cate1" onchange="loadCate2()" style="width:100%;padding:8px;">
                    <?php foreach($cate1 as $v):?><option value="<?=$v['id']?>" <?=$info['cate1']==$v['id']?'selected':''?>><?=e($v['name'])?></option><?php endforeach;?>
                </select>
            </div>
            <div style="margin:15px 0;">
                <label>岗位</label>
                <select name="cate2" id="cate2" style="width:100%;padding:8px;">
                    <?php foreach(getCate2ByPid($info['cate1']) as $v):?><option value="<?=$v['id']?>" <?=$info['cate2']==$v['id']?'selected':''?>><?=e($v['name'])?></option><?php endforeach;?>
                </select>
            </div>
            <div style="margin:15px 0;">
                <label>标题</label>
                <input type="text" name="title" value="<?=e($info['title'])?>" required style="width:100%;padding:10px;border:1px solid #eee;border-radius:4px;">
            </div>
            <div style="margin:15px 0;">
                <label>内容</label>
                <div class="tool-bar">
                    <button type="button" onclick="formatBold()"><b>B</b></button>
                    <button type="button" onclick="formatFontColor('#ff0000')" class="color-btn" style="background:#ff0000;"></button>
                    <button type="button" onclick="formatFontColor('#ff6600')" class="color-btn" style="background:#ff6600;"></button>
                    <button type="button" onclick="formatFontColor('#cccc00')" class="color-btn" style="background:#cccc00;"></button>
                    <button type="button" onclick="formatFontColor('#009900')" class="color-btn" style="background:#009900;"></button>
                    <button type="button" onclick="formatFontColor('#0066ff')" class="color-btn" style="background:#0066ff;"></button>
                    <button type="button" onclick="formatFontColor('#9933cc')" class="color-btn" style="background:#9933cc;"></button>
                    <button type="button" onclick="formatFontColor('#333333')" class="color-btn" style="background:#333333;"></button>
                    <button type="button" onclick="clearFormat()" style="margin-left:10px;">清除格式</button>
                </div>
                <div id="editor" style="height:400px;border:1px solid #eee;border-top:none;padding:10px;overflow:auto;" contenteditable="true"><?=$info['content']?></div>
                <textarea name="content" id="content" style="display:none;"></textarea>
            </div>
            <button type="submit" style="padding:10px 30px;background:#2d8cf0;color:#fff;border:none;border-radius:4px;">保存修改</button>
        </form>
    </div>
</div>
<script>
const cate2Data = <?=json_encode(readJson(CATE_2_FILE),JSON_UNESCAPED_UNICODE)?>;
function loadCate2(){
    let pid = document.getElementById('cate1').value;
    let sel = document.getElementById('cate2');
    sel.innerHTML = '';
    cate2Data.forEach(item=>{if(item.pid==pid) sel.innerHTML += `<option value="${item.id}">${item.name}</option>`;})
}
function formatBold(){document.execCommand('bold',false,null);}
function formatFontColor(color){document.execCommand('foreColor',false,color);}
function clearFormat(){document.execCommand('removeFormat',false,null);}
document.querySelector('form').addEventListener('submit',function(){
    document.getElementById('content').value = document.getElementById('editor').innerHTML;
})
</script>
<?php
}

function sopSave(){
    if(!isAdmin())exit;
    $id=(int)$_POST['id'];
    $data['cate1']=(int)$_POST['cate1'];
    $data['cate2']=(int)$_POST['cate2'];
    $data['title']=trim($_POST['title']);
    $data['content']=$_POST['content'];
    $time=date('Y-m-d H:i:s');
    $list=readJson(SOP_FILE);
    if($id==0){
        $newId=empty($list)?1:max(array_column($list,'id'))+1;
        $list[]=['id'=>$newId,'cate1'=>$data['cate1'],'cate2'=>$data['cate2'],'title'=>$data['title'],'content'=>$data['content'],'author'=>'管理员','ctime'=>$time,'utime'=>$time];
        addLog('新增SOP：'.$data['title']);
    }else{
        foreach($list as &$v){
            if($v['id']==$id){
                $v['cate1']=$data['cate1'];$v['cate2']=$data['cate2'];$v['title']=$data['title'];$v['content']=$data['content'];$v['utime']=$time;
            }
        }
        addLog('编辑SOP：'.$data['title']);
    }
    saveJson(SOP_FILE,$list);
    header('Location:?act=sop_list');exit;
}
function sopDel(){
    if(!isAdmin())exit;
    $id=(int)$_GET['id'];
    $list=readJson(SOP_FILE);
    foreach($list as $k=>$v)if($v['id']==$id)unset($list[$k]);
    saveJson(SOP_FILE,array_values($list));
    header('Location:?act=sop_list');exit;
}
function sopList(){
    if(!isAdmin())exit;
    adminNav();floatTopBtn();
    $list=readJson(SOP_FILE);
?>
<div style="padding:20px;max-width:1100px;margin:0 auto;">
    <div style="background:#fff;padding:20px;border-radius:8px;">
        <div style="display:flex;justify-content:space-between;"><h3>SOP管理</h3><a href="?act=sop_add" style="background:#2d8cf0;color:#fff;padding:6px 12px;border-radius:4px;text-decoration:none;">新增</a></div>
        <table width="100%" border="1" cellpadding="10" style="border-collapse:collapse;margin-top:15px;">
        <?php foreach($list as $v):?>
        <tr><td><?=$v['id']?></td><td><?=e($v['title'])?></td><td><?=getCate1Name($v['cate1'])?></td><td><?=getCate2Name($v['cate2'])?></td>
        <td><a href="?act=view&id=<?=$v['id']?>">查看</a> <a href="?act=sop_edit&id=<?=$v['id']?>">编辑</a> <a href="?act=sop_del&id=<?=$v['id']?>" onclick="return confirm('确定删除？')">删除</a></td></tr>
        <?php endforeach;?>
        </table>
    </div>
</div>
<?php
}

function cate1List(){if(!isAdmin())exit;adminNav();floatTopBtn();$list=readJson(CATE_1_FILE);?>
<div style="padding:20px;max-width:900px;margin:0 auto;"><div style="background:#fff;padding:20px;border-radius:8px;">
<div style="display:flex;justify-content:space-between;"><h3>部门管理</h3><a href="?act=cate1_add" style="background:#2d8cf0;color:#fff;padding:6px 12px;border-radius:4px;text-decoration:none;">新增</a></div>
<table width="100%" border="1" cellpadding="10" style="border-collapse:collapse;margin-top:15px;">
<?php foreach($list as $v):?>
<tr><td><?=$v['id']?></td><td><?=e($v['name'])?></td><td><a href="?act=cate1_edit&id=<?=$v['id']?>">编辑</a> <a href="?act=cate1_del&id=<?=$v['id']?>" onclick="return confirm('确定？')">删除</a></td></tr>
<?php endforeach;?>
</table></div></div><?php }

function cate1Add(){if(!isAdmin())exit;adminNav();floatTopBtn();?>
<div style="padding:20px;max-width:600px;margin:0 auto;"><div style="background:#fff;padding:20px;border-radius:8px;">
<form method="post" action="?act=cate1_save">
<input type="text" name="name" placeholder="部门名称" required style="width:100%;padding:10px;margin:10px 0;">
<button type="submit">保存</button></form></div></div><?php }

function cate1Edit(){if(!isAdmin())exit;$id=(int)$_GET['id'];$list=readJson(CATE_1_FILE);foreach($list as $v)if($v['id']==$id)$info=$v;adminNav();floatTopBtn();?>
<div style="padding:20px;max-width:600px;margin:0 auto;"><div style="background:#fff;padding:20px;border-radius:8px;">
<form method="post" action="?act=cate1_save"><input type="hidden" name="id" value="<?=$info['id']?>">
<input type="text" name="name" value="<?=e($info['name'])?>" required style="width:100%;padding:10px;margin:10px 0;">
<button type="submit">保存</button></form></div></div><?php }

function cate1Save(){if(!isAdmin())exit;$id=(int)$_POST['id'];$name=trim($_POST['name']);$list=readJson(CATE_1_FILE);
if($id==0){$newId=count($list)+1;$list[]=['id'=>$newId,'name'=>$name,'sort'=>10];}
else{foreach($list as &$v){if($v['id']==$id)$v['name']=$name;}}
saveJson(CATE_1_FILE,$list);header('Location:?act=cate');exit;}

function cate1Del(){if(!isAdmin())exit;$id=(int)$_GET['id'];$list=readJson(CATE_1_FILE);foreach($list as $k=>$v)if($v['id']==$id)unset($list[$k]);saveJson(CATE_1_FILE,array_values($list));header('Location:?act=cate');exit;}

function cate2List(){if(!isAdmin())exit;adminNav();floatTopBtn();$list=readJson(CATE_2_FILE);?>
<div style="padding:20px;max-width:1000px;margin:0 auto;"><div style="background:#fff;padding:20px;border-radius:8px;">
<div style="display:flex;justify-content:space-between;"><h3>岗位管理</h3><a href="?act=cate2_add" style="background:#2d8cf0;color:#fff;padding:6px 12px;border-radius:4px;text-decoration:none;">新增</a></div>
<table width="100%" border="1" cellpadding="10" style="border-collapse:collapse;margin-top:15px;">
<?php foreach($list as $v):?>
<tr><td><?=$v['id']?></td><td><?=getCate1Name($v['pid'])?></td><td><?=e($v['name'])?></td>
<td><a href="?act=cate2_edit&id=<?=$v['id']?>">编辑</a> <a href="?act=cate2_del&id=<?=$v['id']?>" onclick="return confirm('确定？')">删除</a></td></tr>
<?php endforeach;?>
</table></div></div><?php }

function cate2Add(){if(!isAdmin())exit;adminNav();floatTopBtn();$cate1=readJson(CATE_1_FILE);?>
<div style="padding:20px;max-width:600px;margin:0 auto;"><div style="background:#fff;padding:20px;border-radius:8px;">
<form method="post" action="?act=cate2_save">
<select name="pid" style="width:100%;padding:10px;margin:10px 0;"><?php foreach($cate1 as $v):?><option value="<?=$v['id']?>"><?=e($v['name'])?></option><?php endforeach;?></select>
<input type="text" name="name" required placeholder="岗位名称" style="width:100%;padding:10px;margin:10px 0;">
<button type="submit">保存</button></form></div></div><?php }

function cate2Edit(){if(!isAdmin())exit;$id=(int)$_GET['id'];$list=readJson(CATE_2_FILE);foreach($list as $v)if($v['id']==$id)$info=$v;adminNav();floatTopBtn();?>
<div style="padding:20px;max-width:600px;margin:0 auto;"><div style="background:#fff;padding:20px;border-radius:8px;">
<form method="post" action="?act=cate2_save"><input type="hidden" name="id" value="<?=$info['id']?>">
<select name="pid" style="width:100%;padding:10px;margin:10px 0;"><?php foreach(readJson(CATE_1_FILE) as $v):?><option value="<?=$v['id']?>" <?=$info['pid']==$v['id']?'selected':''?>><?=e($v['name'])?></option><?php endforeach;?></select>
<input type="text" name="name" value="<?=e($info['name'])?>" required style="width:100%;padding:10px;margin:10px 0;">
<button type="submit">保存</button></form></div></div><?php }

function cate2Save(){if(!isAdmin())exit;$id=(int)$_POST['id'];$pid=(int)$_POST['pid'];$name=trim($_POST['name']);$list=readJson(CATE_2_FILE);
if($id==0){$newId=count($list)+1;$list[]=['id'=>$newId,'pid'=>$pid,'name'=>$name,'sort'=>10];}
else{foreach($list as &$v){if($v['id']==$id){$v['pid']=$pid;$v['name']=$name;}}}
saveJson(CATE_2_FILE,$list);header('Location:?act=cate2_list');exit;}

function cate2Del(){if(!isAdmin())exit;$id=(int)$_GET['id'];$list=readJson(CATE_2_FILE);foreach($list as $k=>$v)if($v['id']==$id)unset($list[$k]);saveJson(CATE_2_FILE,array_values($list));header('Location:?act=cate2_list');exit;}

function logList(){if(!isAdmin())exit;adminNav();floatTopBtn();$log=readJson(LOG_FILE);?>
<div style="padding:20px;max-width:1100px;margin:0 auto;"><div style="background:#fff;padding:20px;border-radius:8px;"><h3>操作日志</h3>
<table width="100%" border="1" cellpadding="8" style="border-collapse:collapse;font-size:13px;margin-top:15px;">
<?php foreach($log as $v):?>
<tr><td><?=$v['time']?></td><td><?=e($v['user'])?></td><td><?=e($v['content'])?></td></tr>
<?php endforeach;?>
</table></div></div><?php
}
?>
