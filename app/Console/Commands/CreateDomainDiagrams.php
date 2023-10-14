<?php

namespace App\Console\Commands;

use App\UserInterface\Object\User as ObjectUser;
use Illuminate\Console\Command;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;

class CreateDomainDiagrams extends Command
{
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
        $refs = [
            new \ReflectionClass(ObjectUser::class),
            new \ReflectionClass(User::class),
        ];
        $names = array_map(self::toName(...), $refs);
        self::outputMarkdown($names);
    }


    /** @phpstan-ignore-next-line */
    static public function outputMarkdown(array $names): void
    {
        print_r("# ドメインモデル図\n");
        foreach ($names as $name) {
            print_r("## {$name['summery']}\n");
            print_r("```mermaid\n");
            print_r("flowchart TD\n");
            self::outputMermaid($name);
            print_r("```\n");
        }
    }

    static public function convertFilePath(string $name): string
    {
        return str_replace(['App', '\\'], ['app', '/'], $name);
    }

    /** @phpstan-ignore-next-line */
    static public function outputMermaid(array $name): void
    {
        print_r("{$name['className']}[{$name['summery']}]\n");
        $filePath = self::convertFilePath($name['className']);
        var_dump($filePath);
        print_r("click {$name['className']} \"https://github.com/paterapatera/laravel-sample-1/tree/main/{$filePath}.php\" _blank\n");

        foreach ($name['properties'] as $prop) {
            print_r("{$name['className']} --> {$prop['className']}\n");
        }

        array_map(self::outputMermaid(...), $name['properties']);
    }

    /**
     * @param \ReflectionClass<object> $ref
     * @phpstan-ignore-next-line
     */
    static public function toName(\ReflectionClass $ref): array
    {
        $comment = $ref->getDocComment() ?: '';
        $tags = PhpDocumentor::tags()->with([new Summery()]);
        $parser = new PhpdocParser($tags);
        $meta = $parser->parse($comment);
        $classTree = [
            'summery' => $meta['summery'],
            'className' => $ref->getName(),
        ];
        $properties = collect($ref->getProperties());
        if ($properties->isNotEmpty()) {
            $classTree['properties'] = $properties
                ->filter(Prop::isClass(...))
                ->map(Prop::toReflectionClass(...))
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
        if (!Type::isReflectionNamedType($type)) throw new \Exception('このプロパティはクラスではありません');
        $className = $type->getName();
        if (!ClassName::isClass($className)) throw new \Exception('このプロパティはクラスではありません');
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
