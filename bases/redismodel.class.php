<?php
/**
 *
 */
class RedisModel
{
  private $rd = null;
  function __construct()
  {
    $params = func_get_args();
    $rd = new Redis();
    call_user_func_array(array($rd, "connect"), $params[0]);
    $this->rd = $rd;
  }

  public function addValue($key,$value)
  {
    $this->rd->set($key,$value);
  }

  public function addCache($tabName, $sql, $data){
    $key=md5($sql);
    if(!$this->rd->exists($key)){
      $this->rd->rpush($tabName,$key);
    }
    $this->rd->set($key,base64_encode(json_encode($data)));
  }

  public function getCache($sql)
  {
    $key=md5($sql);
    $data = $this->rd->get($key);
    if($data){
      $data=json_decode(base64_decode($data),true);
    }
    return $data;
  }

  public function getValue($key)
  {
    return $this->rd->get($key);
  }

  public function isExist($sql)
  {
    $key=md5($sql);
    return $this->rd->exists($key);
  }

  public function delCache($tabName){
    $arList = $this->rd->lrange($tabName, 0 ,200);
    foreach ($arList as $v){
      $this->rd->del($v);
    }
    $this->rd->del($tabName);
    $this->rd->exec();
  }

  public function delAllCache(){
    $this->rd->flushall();
  }

}
