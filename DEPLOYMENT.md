# 部署说明文档

## 🚀 快速部署指南

### 方式一：使用虚拟主机/共享主机

1. **下载并解压**
   - 下载 `ybt_answers.zip` 文件
   - 解压到本地

2. **上传文件**
   - 使用FTP工具（如FileZilla）
   - 将整个 `ybt_answers` 文件夹上传到网站根目录
   - 或者上传到子目录（如 `public_html/ybt/`）

3. **设置权限**
   ```bash
   # Linux主机需要设置写入权限
   chmod 755 ybt_answers/
   chmod 644 ybt_answers/*.php
   ```

4. **访问安装页面**
   ```
   http://你的域名/ybt_answers/install.php
   ```

5. **完成安装**
   - 填写数据库信息
   - 点击"开始安装"
   - **删除install.php文件**

### 方式二：使用VPS/云服务器（Ubuntu/Debian）

#### 1. 安装环境

```bash
# 更新系统
sudo apt update && sudo apt upgrade -y

# 安装Apache、PHP、MySQL
sudo apt install apache2 php php-mysql mysql-server -y

# 安装PHP扩展
sudo apt install php-pdo php-mbstring php-json -y

# 启动服务
sudo systemctl start apache2
sudo systemctl start mysql
sudo systemctl enable apache2
sudo systemctl enable mysql
```

#### 2. 配置MySQL

```bash
# 登录MySQL
sudo mysql -u root -p

# 创建数据库和用户
CREATE DATABASE ybt_answers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ybt_user'@'localhost' IDENTIFIED BY '你的密码';
GRANT ALL PRIVILEGES ON ybt_answers.* TO 'ybt_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. 部署网站

```bash
# 进入Web目录
cd /var/www/html

# 下载项目（假设已上传到服务器）
sudo unzip /path/to/ybt_answers.zip
sudo mv ybt_answers /var/www/html/

# 设置权限
sudo chown -R www-data:www-data /var/www/html/ybt_answers
sudo chmod -R 755 /var/www/html/ybt_answers
```

#### 4. 配置Apache虚拟主机

```bash
# 创建虚拟主机配置
sudo nano /etc/apache2/sites-available/ybt.conf
```

添加以下内容：

```apache
<VirtualHost *:80>
    ServerName ybt.szystudio.cn
    ServerAlias www.ybt.szystudio.cn
    DocumentRoot /var/www/html/ybt_answers
    
    <Directory /var/www/html/ybt_answers>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/ybt_error.log
    CustomLog ${APACHE_LOG_DIR}/ybt_access.log combined
</VirtualHost>
```

启用站点：

```bash
# 启用站点和重写模块
sudo a2ensite ybt.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### 5. 配置HTTPS（推荐）

```bash
# 安装Certbot
sudo apt install certbot python3-certbot-apache -y

# 获取SSL证书
sudo certbot --apache -d ybt.szystudio.cn -d www.ybt.szystudio.cn

# 自动续期
sudo certbot renew --dry-run
```

### 方式三：使用Docker部署

#### 1. 创建Dockerfile

```dockerfile
FROM php:8.1-apache

# 安装扩展
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 启用Apache模块
RUN a2enmod rewrite

# 复制项目文件
COPY ybt_answers/ /var/www/html/

# 设置权限
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80
```

#### 2. 创建docker-compose.yml

```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./ybt_answers:/var/www/html
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=ybt_answers
      - DB_USER=root
      - DB_PASS=rootpassword

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: ybt_answers
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

#### 3. 启动容器

```bash
docker-compose up -d
```

## 🔧 配置优化

### PHP配置优化

编辑 `php.ini`：

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
date.timezone = Asia/Shanghai
```

### MySQL配置优化

编辑 `my.cnf`：

```ini
[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
max_connections = 200
innodb_buffer_pool_size = 256M
```

### Apache性能优化

启用缓存和压缩：

```bash
sudo a2enmod expires
sudo a2enmod deflate
sudo a2enmod headers
sudo systemctl restart apache2
```

## 🔒 安全加固

### 1. 修改管理员密码

编辑 `config.php`，修改默认密码。

### 2. 限制admin目录访问

创建 `admin/.htaccess`：

```apache
# IP白名单（可选）
Order Deny,Allow
Deny from all
Allow from 你的IP地址

# 或使用密码保护
AuthType Basic
AuthName "Admin Area"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

### 3. 配置防火墙

```bash
# 使用UFW
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

### 4. 定期备份

```bash
# 备份数据库
mysqldump -u root -p ybt_answers > backup_$(date +%Y%m%d).sql

# 备份文件
tar -czf ybt_backup_$(date +%Y%m%d).tar.gz /var/www/html/ybt_answers
```

## 📊 性能监控

### 安装监控工具

```bash
# 安装htop
sudo apt install htop

# 安装MySQL监控
sudo apt install mytop
```

### 日志查看

```bash
# Apache访问日志
tail -f /var/log/apache2/access.log

# Apache错误日志
tail -f /var/log/apache2/error.log

# MySQL日志
tail -f /var/log/mysql/error.log
```

## 🐛 故障排除

### 问题1：无法连接数据库

```bash
# 检查MySQL服务
sudo systemctl status mysql

# 检查配置
cat config.php

# 测试连接
php -r "new PDO('mysql:host=localhost;dbname=ybt_answers', 'root', 'password');"
```

### 问题2：500内部服务器错误

```bash
# 检查PHP错误日志
tail -f /var/log/apache2/error.log

# 检查文件权限
ls -la /var/www/html/ybt_answers

# 重启Apache
sudo systemctl restart apache2
```

### 问题3：样式或脚本无法加载

```bash
# 检查.htaccess
cat /var/www/html/ybt_answers/.htaccess

# 确保mod_rewrite已启用
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 📞 技术支持

如遇到部署问题，请检查：

1. PHP版本是否 >= 7.4
2. MySQL版本是否 >= 5.7
3. PDO扩展是否已安装
4. 文件权限是否正确
5. 数据库连接信息是否正确

---

**部署完成后，请访问网站测试所有功能！** ✅
