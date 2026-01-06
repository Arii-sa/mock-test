
# 勤怠管理アプリ

## 環境構築

### Docker ビルド

1. git clone git@github.com:Arii-sa/mock-test.git
1. docker-compose up -d --build

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;※MySQL は、OS によって起動しない場合があるのでそれぞれの PC に合わせて docker-compose.yml ファイルを編集してください。

### Laravel 環境構築

1. docker-compose exec php bash
1. composer install
1. cp .env.example .env
1. .env ファイルの一部を以下のように編集

```
DB_HOST=mysql
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

6. php artisan key:generate
1. php artisan migrate
1. php artisan db:seed


## メール認証機能の環境構築

本プロジェクトでは、**MailHog** を利用してメール送信・メール認証を確認できるようにしています。

### MailHog 起動
1. docker-compose.yml に MailHog サービスを追加
```
mailhog:
    image: mailhog/mailhog:v1.0.1
    container_name: mock-test-mailhog
    ports:
      - "1025:1025"
      - "8025:8025"
```
1. Docker を再起動
```bash
docker-compose up -d build
```
1. .envファイルの一部を以下のように変更
```
MAIL_MAILER=smtp
MAIL_HOST=mock-test-mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## user のログイン用初期データ

- メールアドレス: user@example.com
- パスワード: password

## admin のログイン用初期データ

- メールアドレス: admin@example.com
- パスワード: password123


## 使用技術

- MySQL 8.0.26
- PHP 8.1.33
- Laravel 8

## URL

- 環境開発: http://localhost/login
- phpMyAdmin: http://localhost:8080/
- mailhog: http://localhost:8025/


## ER図
![alt](ER.drawio.png)


## PHPUnitを利用したテストに関して

1. データベース作成　以下のコマンド:
```
//テスト用データベースの作成
docker-compose exec mysql bash
mysql -u root -p
//パスワードはrootと入力
create database laravel_test;
```

1. configファイルの一部を以下のように変更
```
'mysql_test' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => 'laravel_test',
            'username' => 'root',
            'password' => 'root',
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
```
1. テスト用.envファイル作成
```
//.env.testingの作成
cp .env .env.testing
//.env.testingの編集　それぞれ以下のように変更
APP_ENV=test
APP_KEY=

DB_DATABASE=laravel_test
DB_USERNAME=root
DB_PASSWORD=root
```

1. APP_KEYに新たなテスト用のアプリケーションキーを加えるために以下のコマンドを実行
```
php artisan key:generate --env=testing
```

1. テスト用テーブル作成
```
php artisan migrate --env=testing
```

1. phpunitを以下のように編集
```
<server name="DB_CONNECTION" value="mysql_test"/>
<server name="DB_DATABASE" value="laravel_test"/>
```


