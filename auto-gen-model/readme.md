这是一个自动生成模型类的工具

请先下载这个库：[https://github.com/phalcon/phalcon-devtools](https://github.com/phalcon/phalcon-devtools)

1. scripts\Phalcon\Builder\Model.php：  
将代码 `$adapterName = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;`  
改为：
`$adapterName = 'Phalcon\Builder\\' . $adapter;//[SWH|+]`


2. 将我写的Mysql.php文件复制到：scripts\Phalcon\Builder\

3. 将当前文件夹里的所有文件复制到“phalcon devtools”的子文件夹内，该子文件夹名称自己任意取。

4. 修改当前目录下app\config\config.ini文件中的数据库设置。

5. 按需修改app\genModels.php中的设置区变量值。

6. 点击app\genModels.bat文件开始工作吧。

