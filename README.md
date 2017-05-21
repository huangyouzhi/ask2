# ask2
Ask2V3.5SNS社交版
linux注意权限，根目录config.php,plugin，application文件夹，还有data文件夹，文件夹里全部子文件夹都要777权限。

php安装环境在5.3-5.6之间。

建议用apache或者nginx,当然nginx抗压性最好，也是很多公司和站长最喜欢的。

安装优化教程：
http://www.ask2.cn/note/view/47.html

QQ互联设置教程：
http://www.ask2.cn/article-14496.html

网站加速优化教程：
http://www.ask2.cn/article-14494.html

站点地图教程：
http://www.ask2.cn/article-14439.html



如何设置用户组是否有权限发布文章：
http://www.ask2.cn/article-14497.html

安装完成把install目录删掉！！！


如果安装过程提示config.php不存在，请手动在根目录下创建一个空的config.php文件，linux下设置777权限。
config.php保存了数据库的配置信息，自动写入进去的。




上传完成后如果域名绑定好了，直接访问就行，会自动跳转到安装界面，安装完成会在data目录创建install.lock文件。

安装完成也要记得执行下更新地址：

http://你的网站域名/?update


application/view下存放网站模板

admin文件夹是后台模板
wap结尾的是手机端模板

请选择default模板

上传图片水印设置教程--百度编辑器默认会启用水印功能
https://www.ask2.cn/article-14580.html






