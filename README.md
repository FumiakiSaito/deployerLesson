# deployer

## 検証内容
* deployerを使用し、Bitbucketのリポジトリからソースをデプロイする。  
* deployerを実行するサーバ == デプロイ先サーバとする。
* 検証環境はVagrant, Ansibleで構築。



##環境

### ホスト側(MAC)

#### 環境構築方法
```
(ホスト側のVagrantfileがあるディレクトリにて実行)
$ vagrant up
```

#### アクセスURL
http://localhost:8100/ or
http://http://192.168.33.99/


#### 共有ディレクトリ
ホスト側のwwwとゲスト側の/var/www/htmlは共有ディレクトリ

***

### ゲスト側(Ubuntu)

#### インストールソフトウェア
* PHP5.5.9
* nginx 1.4.6

#### ドキュメントルート
/var/www/html

#### Nginx
設定ファイル  
/etc/nginx/conf.d/nginx.conf

ログファイル  
/var/log/nginx/[access | error].log

#### deployer

| ファイル|用途|
|:----------|:-------------:|
|/var/www/html/deploy/deploy.php|デプロイ設定ファイル|
|/var/www/html/deploy/deployer.phar|デプロイ実行ファイル|
    

## デプロイ設定

デプロイ設定ファイル  
/var/www/html/deploy/deploy.php

```
<?php

require 'recipe/common.php';

# デプロイ先サーバ設定
localServer('localhost')
	->env('deploy_path', '/var/www/html') # デプロイ先ディレクトリ
	->env('branch', 'master')             # デプロイするブランチ
	->stage('local');


# デプロイ元リポジトリ設定
set('repository', 'https://hoge.git'); # HTTPSの場合、プロンプトでパスワードを入力する必要あり
#set('repository', 'git@hoge.git');    # SSHの場合、鍵設定を行う必要あり

# 何世代残すか
set('keep_releases', 3);

# バージョン間で共有するディレクトリを設定
set('shared_dirs', ['fuel/app/logs']);

# 書き込み権限を変更するディレクトリを設定(エラーとなる。。)
set('writable_dirs', ['fuel/app/logs', 'fuel/app/cache']);

# 処理のグループ化設定
# deployコマンドを実行するとprepareからsymlinkまで実行される
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:symlink',
    'cleanup',
]);

# タスク完了後の処理設定
# ここではdeploy実行後に完了メッセージを出力している
task('deploy:done', function () {
    writeln('Deploy done!');
});
after('deploy', 'deploy:done');
```

コマンド一覧

```
Available commands:
  cleanup             Cleaning up old releases
  current             Show current release.
  help                Displays help for a command
  init                Initialize deployer system in your project.
  list                Lists commands
  rollback            Rollback to previous release
  self-update         Updates deployer.phar to the latest version
  worker              Deployer uses workers for parallel deployment.
 deploy
  deploy:clean        Cleaning up files and/or directories
  deploy:copy_dirs    Copy directories
  deploy:prepare      Preparing server for deploy
  deploy:release      Prepare release
  deploy:shared       Creating symlinks for shared files
  deploy:symlink      Creating symlink to release
  deploy:update_code  Updating code
  deploy:vendors      Installing vendors
  deploy:writable     Make writable dirs
```

※実質、1発実行すればデプロイ作業が完了するコマンドは存在しない。  
それぞれ処理内容が違う為、適切に選択し実行する事で  
デプロイ作業を完了させる仕組みなもよう。  
ただしコマンドをまとめて実行させる事は可能（後述）

##コマンドを実行してみる

###deploy:prepare

```
$ sudo ./deployer.phar deploy:prepare localhost
✔ Executing task deploy:prepare
```

```
vagrant@develop:/var/www/html$ ll
合計 8
drwxr-xr-x 1 www-data www-data  204  5月  7 00:33 ./
drwxr-xr-x 3 root     root     4096  4月 28 22:34 ../
drwxr-xr-x 1 www-data www-data  136  4月 28 22:51 deploy/
-rw-r--r-- 1 www-data www-data   18  4月 27 22:23 index.php
drwxr-xr-x 1 www-data www-data   68  5月  7 00:33 releases/
drwxr-xr-x 1 www-data www-data   68  5月  7 00:33 shared/
```

releases、sharedディレクトリが作成される 

***

###deploy:release
```
$ sudo ./deployer.phar deploy:release localhost
✔ Executing task deploy:release
```

```
vagrant@develop:/var/www/html$ ll
合計 12
drwxr-xr-x 1 www-data www-data  238  5月  7 00:35 ./
drwxr-xr-x 3 root     root     4096  4月 28 22:34 ../
drwxr-xr-x 1 www-data www-data  136  4月 28 22:51 deploy/
-rw-r--r-- 1 www-data www-data   18  4月 27 22:23 index.php
lrwxr-xr-x 1 www-data www-data   37  5月  7 00:35 release -> /var/www/html/releases/20160507003529/
drwxr-xr-x 1 www-data www-data  102  5月  7 00:35 releases/
drwxr-xr-x 1 www-data www-data   68  5月  7 00:33 shared/
vagrant@develop:/var/www/
```

releases配下にyyyymmddhhiissディレクトリが作成され  
そこへのシンボリックリンク(release)が作成される

***

###deploy:update_code

```
$ sudo ./deployer.phar deploy:update_code localhost
➤ Executing task deploy:update_code
✔assword for 'https://FumiakiSaito@bitbucket.org':
```

```
vagrant@develop:/var/www/html$ ll
合計 12
drwxr-xr-x 1 www-data www-data  238  5月  7 00:35 ./
drwxr-xr-x 3 root     root     4096  4月 28 22:34 ../
drwxr-xr-x 1 www-data www-data  136  4月 28 22:51 deploy/
-rw-r--r-- 1 www-data www-data   18  4月 27 22:23 index.php
lrwxr-xr-x 1 www-data www-data   37  5月  7 00:35 release -> /var/www/html/releases/20160507003529/
drwxr-xr-x 1 www-data www-data  102  5月  7 00:35 releases/
drwxr-xr-x 1 www-data www-data   68  5月  7 00:33 shared/
vagrant@develop:/var/www/html$ cd release
vagrant@develop:/var/www/html/release$ ls
README.md  composer.json  fuel  git-clone-all.sh  go-pear.phar  go-pear.php  oil  public  tags
```
release(releases/yyyymmddhhiiss)にソースが展開される

***

###deploy:shared

```
$ sudo ./deployer.phar deploy:shared localhost
✔ Executing task deploy:shared
```

```
vagrant@develop:/var/www/html$ ll
合計 12
drwxr-xr-x 1 www-data www-data  238  5月  7 00:35 ./
drwxr-xr-x 3 root     root     4096  4月 28 22:34 ../
drwxr-xr-x 1 www-data www-data  136  4月 28 22:51 deploy/
-rw-r--r-- 1 www-data www-data   18  4月 27 22:23 index.php
lrwxr-xr-x 1 www-data www-data   37  5月  7 00:35 release -> /var/www/html/releases/20160507003529/
drwxr-xr-x 1 www-data www-data  102  5月  7 00:35 releases/
drwxr-xr-x 1 www-data www-data  102  5月  7 00:41 shared/
vagrant@develop:/var/www/html$ cd shared/fuel/app/logs/
vagrant@develop:/var/www/html/shared/fuel/app/logs$ ls
vagrant@develop:/var/www/html/shared/fuel/app/logs$
```

shared配下にfuel/app/logs  
(deploy.phpのshared_dirsで設定したディレクトリ)  
が作成される

***

###deploy:writable

```
$ sudo ./deployer.phar deploy:writable localhost
➤ Executing task deploy:writable
```

```

  Unable to setup correct permissions for writable dirs.
  You need to configure sudo's sudoers files to not prompt for password,
  or setup correct permissions manually.


  [Symfony\Component\Process\Exception\ProcessFailedException]
  The command "cd /var/www/html/releases/20160507141008 && sudo setfacl -R -m u:"www-data":rwX -m u:`whoami`:rwX fuel/app/logs fuel/app/cache" failed.
  Exit Code: 1(General error)
  Working directory: /var/www/html/deploy
  Output:
  ================
  Error Output:
  ================
  setfacl: fuel/app/logs: サポートされていない操作です
  setfacl: fuel/app/cache: サポートされていない操作です
  setfacl: fuel/app/cache/2016-02-01-2016-04-01.cache: サポートされていない操作です

deploy:writable [-p|--parallel]
```

エラーとなった。  
ACLで権限を付与するコマンドが実行されているが  
ACLを利用するにはファイルシステム設定が必要な為、エラーとなったと思われる。  

機能としてはfuel/app/logs, fuel/app/cache  
(deploy.phpのwritable_dirsで設定したディレクトリ)  
に書き込み権限を与えるものと思われる。  

とりあえずwww-dataが所有者であれば問題ないので放置

***


###deploy:symlink

```
$ sudo ./deployer.phar deploy:symlink localhost
✔ Executing task deploy:symlink
```

```
vagrant@develop:/var/www/html$ ll
合計 12
drwxr-xr-x 1 www-data www-data  238  5月  7 01:11 ./
drwxr-xr-x 3 root     root     4096  4月 28 22:34 ../
lrwxr-xr-x 1 www-data www-data   37  5月  7 01:11 current -> /var/www/html/releases/20160507003529/
drwxr-xr-x 1 www-data www-data  136  4月 28 22:51 deploy/
-rw-r--r-- 1 www-data www-data   18  4月 27 22:23 index.php
drwxr-xr-x 1 www-data www-data  102  5月  7 00:35 releases/
drwxr-xr-x 1 www-data www-data  102  5月  7 00:41 shared/
```

releaseがcurrentに置き換わる

***


###cleanup

```
$ sudo ./deployer.phar cleanup localhost
✔ Executing task cleanup
```

```
vagrant@develop:/var/www/html$ cd releases/
vagrant@develop:/var/www/html/releases$ ll
合計 0
drwxr-xr-x 1 www-data www-data 170  5月  7 16:30 ./
drwxr-xr-x 1 www-data www-data 238  5月  7 16:29 ../
drwxr-xr-x 1 www-data www-data 476  5月  7 16:28 20160507162635/
drwxr-xr-x 1 www-data www-data 476  5月  7 16:28 20160507162828/
drwxr-xr-x 1 www-data www-data 476  5月  7 16:29 20160507162911/
```

releases配下の各バージョンディレクトリが保存分を残して削除される  
(deploy.phpのkeep_releasesで設定した値)  

***

###rollback

```
$ sudo ./deployer.phar rollback localhost
✔ Executing task rollback
```

```
vagrant@develop:/var/www/html$ ll
合計 12
drwxr-xr-x 1 www-data www-data  238  5月  7  2016 ./
drwxr-xr-x 3 root     root     4096  5月  7 02:26 ../
lrwxr-xr-x 1 www-data www-data   37  5月  7  2016 current -> /var/www/html/releases/20160507162828/
drwxr-xr-x 1 www-data www-data  136  4月 28 22:51 deploy/
-rw-r--r-- 1 www-data www-data   18  4月 27 22:23 index.php
drwxr-xr-x 1 www-data www-data  102  5月  7  2016 releases/
drwxr-xr-x 1 www-data www-data   68  5月  7 16:21 shared/
```

currentのリンク先が1世代戻る。  
/var/www/html/releases/20160507162911であったのが20160507162828に戻った。  
/var/www/html/releases/20160507162911は物理的に削除された。  
(リンク先だけ変わるわけでない)  

連続して実行すると実行回数分戻った。  
管理バージョン数以上、実行すると「もう戻せない」旨のメッセージが出力される

```
$ sudo ./deployer.phar rollback localhost
➤ Executing task rollback
No more releases you can revert to.
✔ Ok
```

***


###deploy
(これは正規のコマンドでなく、deploy.phpのtaskで各コマンドをグループ化しdeployという名前を付けたもの)

```
$ sudo ./deployer.phar deploy localhost
✔ Executing task deploy:prepare
✔ Executing task deploy:release
➤ Executing task deploy:update_code
✔assword for 'https://FumiakiSaito@bitbucket.org':
✔ Executing task deploy:symlink
✔ Executing task cleanup
➤ Executing task deploy:done
Deploy done!
✔ Ok
```


1. deploy:prepare  
2. deploy:release  
3. deploy:update_code  
4. deploy:symlink 
5. cleanup 
6. 完了メッセージ出力 

が順番に実行される 

***


##所感

sharedディレクトリは各バージョン間で共有したいファイルを管理する為に使用すると思われる。  
例)  Fuelのログ出力先に設定すればリリース/ロールバックしてもログ出力先は同じ  
ログに関しては/var/logに出力したり、fluentdで集約するのであれば特に不要。

導入する場合はcurrentディレクトリ(リンク)が作成されるので  
fuelphpのサービスであればcurrent/publicをドキュメントルートに設定する必要あり。

`deploy:symlink`を実行しなければreleaseディレクトリ(リンク)が最新ソースとなる為、
release/publicをバーチャルホスト設定/IP制限等しておけば、本番環境での事前テストもできそう。

今回の検証ではdeployer実行サーバ==デプロイ先サーバだったが  
リモートにデプロイする事が可能なので、デプロイサーバを1つ立て  
そこから各サービスのデプロイを行う運用がいいのかもしれない。
