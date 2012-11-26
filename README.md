###说明:
1.	把APP的关键词在keywords.txt里面按要求填写。
  例如cn:友录，名片 
   关键词要用英文逗号分开，不要换行，也不要加任何结尾符号。
  多个国家要另起一行填写。
  

2.	keywords.php 里面APPID修改为要搜索的APP的ID。
  define("APPID", "534966036");

3.	命令行执行php keywords.php；
结果会写入result.txt。如果关键词比较多，用时会比较久。