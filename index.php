<?php
$ftKey = ftok('/home/share_memory_test','s');
$shmId = shm_attach($ftKey,1000,0777);
if(!shm_has_var($shmId,'test'))shm_put_var($shmId,'test',[]);
$mainPid = posix_getpid();
//进程数
$processNum = 4;
echo "主进程：" . $mainPid . PHP_EOL;
for($i=0;$i<$processNum;$i++){
    $pid = pcntl_fork();
    if($pid==-1){ //进程创建失败
        die('fork child process failure!');
    }
    else if($pid){ //父进程处理逻辑
        //pcntl_wait($status,WUNTRACED);
    } else { //子进程处理逻辑
        //do sth
        $childStart = time();
        $tmp = shm_get_var($shmId,'test');
        shm_put_var($shmId,'test',array_push($tmp,$childStart));
        $childEnd = time();
        $childDiff = $childEnd - $childStart;
        echo "#" . posix_getpid() . "执行完毕，用时：" . $childDiff . "秒" . PHP_EOL;
        //防止出现僵尸进程
        exit(0);
    }
}
//回收子进程
while($processNum>0){
    if(($pid = pcntl_wait($status)) > 0){
        $processNum--;
        echo "#".$pid."退出".PHP_EOL;
    }
}
//获取多进程处理的结果
$result = shm_get_var($shmId,'test');
// 移除共享内存的所有数据
shm_remove($shmId);
// 断开
shm_detach($shmId);