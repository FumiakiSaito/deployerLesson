<?php

/**
 * レシピの読み込み
 */
require 'recipe/common.php';
// 以下のように各種フレームワーク用のレシピが存在する
// FuelPHPは公式ではTODOとなっている為、大元のcommon.phpを読み込む
#require 'recipe/symfony.php'; 
#require 'recipe/laravel.php'; 


/**
 * デプロイ先サーバ設定
 */ 
// 自ホストにデプロイする場合はlocalServerを使用
localServer('localhost')
	->env('deploy_path', '/var/www/html') // デプロイ先ディレクトリ
	->env('branch', 'master')             // デプロイするブランチ
	->stage('local');
// リモートサーバにデプロイする場合は以下のようにする
// 例ではuser,password指定だが鍵指定可能	
#server('prod_1', 'domain.com')
#    ->user('user')
#    ->password('pass')
#    ->env('deploy_path', '/home/www')
#    ->stage('production');



/**
 * デプロイ元リポジトリ設定
 */
set('repository', 'https://FumiakiSaito@bitbucket.org/wiseman_web/gist.git'); // HTTPSの場合、プロンプトでパスワードを入力する必要あり
#set('repository', 'git@bitbucket.org:wiseman_web/gist.git');                 // SSHの場合、鍵設定を行う必要あり

/**
 * 何世代保持するか
 */ 
set('keep_releases', 3);

/**
 * バージョン間で共有するディレクトリを設定
 */
set('shared_dirs', ['fuel/app/logs']);

/**
 * 書き込み権限を変更するディレクトリを設定
 *(エラーとなる。。)
 */
set('writable_dirs', ['fuel/app/logs', 'fuel/app/cache']);

/**
 * 処理のグループ化設定
 * 以下は「deploy」コマンドを実行するとprepareからsymlinkまで実行する設定
 */
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:symlink',
    'cleanup',
]);

/**
 * タスク完了後の処理設定
 * ここではdeploy実行後にメッセージを出力している
 * writelnはタグでメッセージの色を変えられるもよう
 */
task('deploy:done', function () {
    writeln('Deploy done!');
    writeln('<info>Deploy done! 緑で表示</info>');
    writeln('<comment>Deploy done! 黄色で表示</comment>');
    writeln('<error>Deploy done! 白(背景赤)で表示</error>');
});
after('deploy', 'deploy:done');

