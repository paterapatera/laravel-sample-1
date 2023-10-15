<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Console\Services\CreateDiagrams\DomainMarkdown;

use App\UserInterface\Object\User as ObjectUser;

class CreateDiagrams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-diagrams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ドメインモデル図を作成する';

    public function handle(): void
    {
        $classNames = [
            ObjectUser::class,
        ];
        DomainMarkdown::createDir();
        DomainMarkdown::file(fn ($file) => DomainMarkdown::outputMarkdown($classNames, $file));

        // ファイル内のクラス自動読み込み機能
        // $classFiles = glob(app_path('UserInterface/Object/') . '*.php') ?: [];
        // foreach ($classFiles as $file) {
        //     include_once($file);
        // }
        // $classNames = \Arr::where(get_declared_classes(), fn ($className) => preg_match('/^App\\\\UserInterface\\\\Object\\\\[^\\\\]+$/', $className));
        // var_dump($classNames);
    }
}
