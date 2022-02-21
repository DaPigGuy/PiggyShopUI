<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\utils;

use pocketmine\utils\TextFormat;
use ReflectionClass;

class Utils
{
    private static array $replacements;

    public static function init(): void {
        foreach ((new ReflectionClass(TextFormat::class))->getConstants() as $color => $code) {
            if (is_string($code)) self::$replacements["{" . $color . "}"] = $code;
        }
    }

    public static function translateColorTags(string $message): string
    {
        return str_replace(array_keys(self::$replacements), self::$replacements, $message);
    }
}