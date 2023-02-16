<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/ai-commit.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace App;

use App\Exceptions\InvalidJsonFileException;
use App\Exceptions\UnsupportedConfigFileTypeException;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @see https://github.com/hassankhan/config
 */
class ConfigManager extends Repository implements Arrayable, Jsonable, \JsonSerializable
{
    /**
     * @var string
     */
    public const NAME = '.ai-commit.json';

    final public static function load(): void
    {
        resolve('config')->set('ai-commit', self::create());
    }

    public static function create(?array $items = null): self
    {
        if (is_array($items)) {
            return new self($items);
        }

        return self::createFrom(
            ...array_filter([config_path('ai-commit.php'), self::globalPath(), self::localPath()], 'file_exists')
        );
    }

    public static function createFrom(...$files): self
    {
        return new self(self::readFrom(...$files));
    }

    public static function globalPath(string $path = self::NAME): string
    {
        $path = $path ? DIRECTORY_SEPARATOR.$path : $path;
        if (windows_os()) {
            return sprintf('C:\\Users\\%s', get_current_user()).$path;
        }

        return exec('cd ~; pwd').$path;
    }

    public static function localPath(string $path = self::NAME): string
    {
        $cwd = getcwd();
        if (false === $cwd) {
            $cwd = realpath('');
        }

        return $cwd.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    public function toGlobal(int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): void
    {
        $this->toFile(self::globalPath(), $options);
    }

    public function toLocal(int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): void
    {
        $this->toFile(self::localPath(), $options);
    }

    public function toFile(string $file, int $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    {
        $this->forget([
            'generators.openai.completion_parameters.prompt',
            'generators.openai.completion_parameters.user',
        ]);

        return file_put_contents($file, $this->toJson($options));
    }

    public function replaceFrom(string $file): void
    {
        $this->replace(self::readFrom($file));
    }

    public function replace(array $items): void
    {
        $this->items = array_replace_recursive($this->items, $items);
    }

    /**
     * @param string|array<string> $keys
     */
    public function forget($keys): void
    {
        Arr::forget($this->items, $keys);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array<TKey, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_map(static function ($value) {
            if ($value instanceof \JsonSerializable) {
                return $value->jsonSerialize();
            }
            if ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            }
            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->all());
    }

    public function toArray(): array
    {
        return array_map(static function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->all());
    }

    /**
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    protected static function readFrom(...$files): array
    {
        $configurations = array_reduce($files, static function (array $configurations, string $file): array {
            $ext = str(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext->is('php')) {
                $configurations[] = require $file;

                return $configurations;
            }

            if ($ext->is('json')) {
                $configuration = json_decode(file_get_contents($file), true);
                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw InvalidJsonFileException::make($file);
                }

                $configurations[] = $configuration;

                return $configurations;
            }

            throw UnsupportedConfigFileTypeException::make($file);
        }, []);

        return array_replace_recursive(...$configurations);
    }
}
