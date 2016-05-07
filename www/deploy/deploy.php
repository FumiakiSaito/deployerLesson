<?php

require 'recipe/common.php';

# デプロイ先サーバ設定
localServer('localhost')
	->env('deploy_path', '/var/www/html') // デプロイ先ディレクトリ
	->env('branch', 'master')             // デプロイするブランチ
	->stage('local');


# デプロイ元リポジトリ設定
set('repository', 'https://hoge.git'); #HTTPSの場合、プロンプトでパスワードを入力する必要あり
#set('repository', 'git@hoge.git');    #SSHの場合、鍵設定を行う必要あり

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