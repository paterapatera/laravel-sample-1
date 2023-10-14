<?php

namespace App\Console\Commands;

use App\UserInterface\Object\Modeling;
use App\UserInterface\Object\User as ObjectUser;
use Illuminate\Console\Command;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;

class CreateDomainDiagrams extends Command
{
    const DIR = 'doc/design/';
    const FILE = 'domain-model.md';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-domain-diagrams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle(): void
    {
        self::createDir();
        $file = self::file();
        try {
            $refs = [
                new \ReflectionClass(ObjectUser::class),
                new \ReflectionClass(User::class),
            ];
            $names = array_map(self::toName(...), $refs);
            self::outputMarkdown($names, $file);
        } finally {
            if ($file) {
                fclose($file);
            }
        }
    }


    static public function createDir(): void
    {
        if (!file_exists(base_path(self::DIR))) {
            mkdir(base_path(self::DIR), 0777, recursive: true);
        }
    }

    /**
     * @return resource|false
     */
    static public function file(): mixed
    {
        return fopen(base_path(self::DIR . self::FILE), 'w');
    }

    /**
     * ファイル全体の書き出し
     * 
     * @param resource|false $file
     * @phpstan-ignore-next-line
     */
    static public function outputMarkdown(array $names, mixed $file): void
    {
        if (!$file) throw new \RuntimeException('ファイルが見つかりません');

        fwrite($file, "# ドメインモデル図\n");
        foreach ($names as $name) {
            fwrite($file, "## {$name['summery']}\n");
            fwrite($file, "```mermaid\n");
            fwrite($file, "flowchart TD\n");
            self::outputMermaid($name, $file);
            fwrite($file, "```\n");
        }
    }

    static public function convertFilePath(string $name): string
    {
        return str_replace(['App', '\\'], ['app', '/'], $name);
    }

    /**
     * ドメインごとのMermaidの書き出し
     * 
     * @param resource|false $file
     * @phpstan-ignore-next-line
     */
    static public function outputMermaid(array $name, mixed $file): void
    {
        if (!$file) throw new \RuntimeException('ファイルが見つかりません');

        fwrite($file, "{$name['className']}[{$name['summery']}]\n");
        $filePath = self::convertFilePath($name['className']);
        fwrite($file, "click {$name['className']} \"https://github.com/paterapatera/laravel-sample-1/tree/main/{$filePath}.php\" _blank\n");

        foreach ($name['properties'] as $prop) {
            fwrite($file, "{$name['className']} --> {$prop['className']}\n");
        }

        array_map(
            fn ($nextName) => self::outputMermaid($nextName, $file),
            $name['properties']
        );
    }

    /**
     * @param \ReflectionClass<object> $ref
     * @phpstan-ignore-next-line
     */
    static public function toName(\ReflectionClass $ref): array
    {
        $tags = PhpDocumentor::tags()->with([new Summery()]);
        $parser = new PhpdocParser($tags);

        $comment = $ref->getDocComment() ?: '';
        $meta = $parser->parse($comment);

        $classTree = [
            'summery' => $meta['summery'],
            'className' => $ref->getName(),
            'properties' => [],
        ];
        $properties = collect($ref->getProperties());
        if ($properties->isNotEmpty()) {
            $classTree['properties'] = $properties
                ->filter(Prop::isClass(...))
                ->map(Prop::toReflectionClass(...))
                ->map(self::toName(...))->toArray();
        }
        $attrs = collect($ref->getAttributes(Modeling::class));
        if ($attrs->isNotEmpty()) {
            $classTree['properties'] += $attrs
                ->map(function ($attr) {
                    $className = $attr->newInstance()->className;
                    if (!ClassName::isClass($className)) throw new \Exception('このプロパティはクラスではありません: ' . $className);
                    return new \ReflectionClass($className);
                })
                ->map(self::toName(...))->toArray();
        }
        return $classTree;
    }
}

class Prop
{
    static function isClass(\ReflectionProperty $prop): bool
    {
        return Type::isClass($prop->getType());
    }

    /** @return \ReflectionClass<object> */
    static function toReflectionClass(\ReflectionProperty $prop): \ReflectionClass
    {
        return new \ReflectionClass(self::getClassName($prop));
    }

    /** @return class-string */
    static function getClassName(\ReflectionProperty $prop): string
    {
        $type = $prop->getType();
        if (!Type::isReflectionNamedType($type)) throw new \Exception('このプロパティはクラスではありません: ' . $type);
        $className = $type->getName();
        if (!ClassName::isClass($className)) throw new \Exception('このプロパティはクラスではありません: ' . $className);
        return $className;
    }
}

class Type
{
    /** @phpstan-assert-if-true \ReflectionNamedType $type */
    static function isReflectionNamedType(?\ReflectionType $type): bool
    {
        return $type instanceof \ReflectionNamedType;
    }

    /** @phpstan-assert-if-true \ReflectionNamedType $type */
    static function isClass(?\ReflectionType $type): bool
    {
        if (!$type instanceof \ReflectionNamedType) return false;
        return ClassName::isClass($type->getName());
    }
}

class ClassName
{
    /** @phpstan-assert-if-true class-string $className */
    static function isClass(string $className): bool
    {
        return class_exists($className);
    }
}


/**
 * ユーザー
 */
class User
{
    function __construct(public readonly Contact $contact)
    {
    }
}

/**
 * 連絡先
 */
class Contact
{
    function __construct(public readonly Tel $contact, public readonly Email $email)
    {
    }
}

/**
 * 電話番号
 */
class Tel
{
    function __construct(public readonly string $value)
    {
    }
}

/**
 * メールアドレス
 */
class Email
{
    function __construct(public readonly string $value)
    {
    }
}
