<?php
/**
 * User: Andrey Shamis
 * email: lolnik@gmail.com
 * Date: 06/04/18
 * Time: 15:39
 */

namespace App\Model;

abstract class MineType
{
    public const GOLD_MINE = 1;
    public const WOOD_MINE = 2;
    public const STONE_MINE = 3;
    public const COPPER_MINE = 4;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::GOLD_MINE    => 'Gold',
        self::WOOD_MINE => 'Wood',
        self::STONE_MINE => 'Stone',
        self::COPPER_MINE  => 'Copper',
    ];

    /**
     * @param  string $typeShortName
     * @return string
     */
    public static function getTypeName($typeShortName): string
    {
        if (!isset(static::$typeName[$typeShortName])) {
            return "Unknown type ($typeShortName)";
        }

        return static::$typeName[$typeShortName];
    }

    /**
     * @return array<integer>
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::GOLD_MINE,
            self::WOOD_MINE,
            self::STONE_MINE,
            self::COPPER_MINE
        ];
    }
}