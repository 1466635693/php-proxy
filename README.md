# php-proxy
纯PHP写的代理程序，用于移动程序抓包

## 应用场景
移动app抓包分析。

优点：
* 不需要安装任何额外的软件，只要有apache就可以
* 可以根据url来自定义返回内容，方便调试

缺点：
* 响应应较慢

## 使用方法
在.htaccess文件里加上以下rewrite配置

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule . /proxy.php [L]

确保proxy.log apache可写。然后就可以通过查看proxy.log内容的方式来查看包内容。
