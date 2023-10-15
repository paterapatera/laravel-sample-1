<?php

namespace App\Console\Services\CreateDiagrams;

use App\UserInterface\Object\Modeling;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;

class DomainMarkdown
{
    const DIR = 'doc/design/';
    const FILE = 'domain-model.md';

    /**
     * ファイル全体の書き出し
     * 
     * @param resource|false $file
     * @phpstan-ignore-next-line
     */
    static public function outputMarkdown(array $classNames, mixed $file): void
    {
        if (!$file) throw new \RuntimeException('ファイルが見つかりません');

        $refs = array_map(ClassName::toReflectionClass(...), $classNames);
        $names = array_map(DomainMarkdown::toName(...), $refs);

        fwrite($file, "# ドメインモデル図\n");
        foreach ($names as $name) {
            fwrite($file, "## {$name['summery']}\n");
            fwrite($file, "```mermaid\n");
            fwrite($file, "flowchart TD\n");
            self::outputMermaid($name, $file);
            fwrite($file, "```\n");
        }
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

    static public function createDir(): void
    {
        if (!file_exists(base_path(self::DIR))) {
            mkdir(base_path(self::DIR), 0777, recursive: true);
        }
    }

    static public function file(callable $callback): void
    {
        $file = fopen(base_path(self::DIR . self::FILE), 'w');

        try {
            $callback($file);
        } finally {
            if ($file) {
                fclose($file);
            }
        }
    }

    static public function convertFilePath(string $name): string
    {
        return str_replace(['App', '\\'], ['app', '/'], $name);
    }

    /**
     * コメントとクラス情報をドメイン情報に変換
     * 
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
