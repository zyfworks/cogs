<?php
require_once("../include/header.php");
gethead(1,"查看比赛","比赛评测");
?>
<div class='container'>
<?php
if ($_POST['do']=="评测选定") {
    echo "<h2>评测选定</h2>";
    $list=$_POST['doit'];
} else if ($_POST['do']=="评测全部") {
    echo "<h2>评测全部</h2>";
    $list=$_POST['doall'];
}
if (!is_array($list))$list=array();
sort($list);
?>

<script language="javascript">
var HTTP;
var list = new Array(<?php 
    $cnt=0;
    foreach($list as $k=>$v) {
        echo "$v,";
        $cnt++;
    }
    echo 0;
?>);
var p=0;
var pmax=<?=$cnt ?>;
var text="";

function createHTTP() {
    if (window.ActiveXObject) {
        HTTP=new ActiveXObject("Microsoft.XMLHTTP");
    } else if (window.XMLHttpRequest) {
        HTTP=new XMLHttpRequest();
    }
}

function doStart(csid) {
    document.getElementById("score"+csid).innerHTML="<span style='background:#FFFF00'>正在评测...</span>";
    document.getElementById("nowp").innerHTML=p+1;
    createHTTP();
    url="compile.php";
    strA = "csid="+csid+"&judger="+"<?=$_POST['judger']?>";
    HTTP.open("POST",url);
    HTTP.setRequestHeader("CONTENT-TYPE","application/x-www-form-urlencoded");
    HTTP.send(strA);
    HTTP.onreadystatechange=Callback;
}

function Callback() {
    if (HTTP.readyState==4) {
        if(HTTP.status==200) {
            var w="";
            text=HTTP.responseText;
            str = text.split("!");
            document.getElementById("score"+list[p]).innerHTML=str[0];
            document.getElementById("result"+list[p]).innerHTML=str[1];
            ++p;
            document.getElementById("progress").style.cssText="width: "+Math.round(p/pmax*100)+"%;";
            if (p<pmax)
                doStart(list[p]);
            else
                Finish();
        }
    }
}

function StartJudge() {
    doStart(list[0]);
    p=0;
    document.getElementById("st").disabled=true;
    document.getElementById("st").value="正在评测...";
    document.getElementById("progressd").className="progress progress-striped progress-warning active";
}

function Finish() {
    document.getElementById("st").disabled=false;
    document.getElementById("st").value="重新评测";
    document.getElementById("progressd").className="progress progress-striped progress-success";
}

</script>


<input name="st" type="button" id="st" value="开始评测" class='btn btn-primary' onclick="StartJudge()" />
  <div id="progressd">
  <div id="progress" class="bar" style="width: 0%;"></div>
  </div>
当前第<span id="nowp">0</span>个 共<?=$cnt ?>个 </p>
<table class='table table-striped table-condensed table-bordered fiexd'>
  <tr>
    <th>CSID</th>
    <th>真实姓名</th>
    <th>题目名</th>
    <th>文件名</th>
    <th>代码</th>
    <th>测试点</th>
    <th>得分</th>
  </tr>
<?php 
    $p=new DataAccess();
    $sql="select compscore.csid,compscore.ctid,userinfo.nickname,userinfo.realname,problem.probname,problem.filename,compscore.lang,compscore.score,compscore.result,compscore.pid,compscore.uid,compscore.ctid from compscore,problem,userinfo where compscore.uid=userinfo.uid and compscore.pid=problem.pid and(";
    foreach($list as $k=>$v)
        $sql.="compscore.csid={$v} or ";
    $sql.="compscore.csid=0) order by compscore.csid asc";
    $cnt=$p->dosql($sql);
    for ($i=0;$i<$cnt;$i++) {
        $d=$p->rtnrlt($i);
?>
  <tr>
    <td><?=$d[csid] ?></td>
    <td><a href="../user/detail.php?uid=<?=$d[uid] ?>" target="_blank"><?=$d[realname] ?></a></td>
    <td><a href="problem.php?pid=<?=$d[pid] ?>&ctid=<?=$d[ctid] ?>" target="_blank"><?=shortname($d[probname]) ?></a></td>
    <td><code><?=$d[filename] ?></code></td>
    <td><a href="code.php?csid=<?=$d[csid] ?>" target="_blank"><?=$STR[lang][$d[lang]] ?></a></td>
    <td id="result<?=$d[csid] ?>"><?=评测结果($d[result], 50) ?></td>
    <td id="score<?=$d[csid] ?>"><?=$d[score] ?></td>
  </tr>
<?php 
    }
?>
</table>
</div>
<?php
    include_once("../include/footer.php");
?>
