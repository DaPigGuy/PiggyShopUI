<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\commands\enum;

use CortexPE\Commando\args\StringEnumArgument;
use DaPigGuy\PiggyShopUI\PiggyShopUI;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use pocketmine\command\CommandSender;

/**
 * Class ShopCategoryEnum
 * @package DaPigGuy\PiggyShopUI\commands\enum
 */
class ShopCategoryEnum extends StringEnumArgument
{
    /**
     * @param string $argument
     * @param CommandSender $sender
     *
     * @return ShopCategory|null
     */
    public function parse(string $argument, CommandSender $sender)
    {
        return PiggyShopUI::getInstance()->getShopCategory($argument);
    }

    /**
     * @return string
     */
    public function getEnumName(): string
    {
        return "category";
    }

    /**
     * @return array
     */
    public function getEnumValues(): array
    {
        return array_map(function (ShopCategory $category): string {
            return $category->getName();
        }, PiggyShopUI::getInstance()->getShopCategories());
    }


    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return "category";
    }
}