# global_uniqid_generator 

基于twitter开放的SnowFlake改进的一种通用的全局唯一自增ID生成服务算法实现，适用于中小规模互联网业务。

### 数据结构
```
64bit = 1bit空缺 + 7bit业务编号(128项业务) + 39bit毫秒时间戳(与2017-06-01的差值) + 5bit机器id(32台) + 4bit一级随机数 + 4bit二级随机数 + 4bit三级随机数
```
参考了 https://segmentfault.com/a/1190000007769660 的介绍，对原作者表示感谢。

