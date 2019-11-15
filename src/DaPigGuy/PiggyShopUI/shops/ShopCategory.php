<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

/**
 * Class ShopCategory
 * @package DaPigGuy\PiggyShopUI\shops
 */
class ShopCategory
{
    /** @var string */
    public $name;
    /** @var ShopItem[] */
    public $items;
    /** @var bool */
    public $private;

    /** @var int */
    public $imageType;
    /** @var string */
    public $imagePath;

    /**
     * ShopCategory constructor.
     * @param string $name
     * @param array $items
     * @param bool $private
     */
    public function __construct(string $name, array $items, bool $private, int $imageType, string $imagePath)
    {
        $this->name = $name;
        $this->items = $items;
        $this->private = $private;
        $this->imagePath = $imagePath;
        $this->imageType = $imageType;

        $permission = new Permission("piggyshopui.category." . strtolower($name), "Allows usage of the " . $name . " shop category");
        PermissionManager::getInstance()->addPermission($permission);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    /**
     * @return ShopItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param ShopItem $item
     */
    public function addItem(ShopItem $item): void
    {
        $this->items[] = $item;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    /**
     * @param int $index
     */
    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private): void
    {
        $this->private = $private;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    /**
     * @return int
     */
    public function getImageType(): int
    {
        return $this->imageType;
    }

    /**
     * @param int $imageType
     */
    public function setImageType(int $imageType): void
    {
        $this->imageType = $imageType;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    /**
     * @return string
     */
    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     */
    public function setImagePath(string $imagePath): void
    {
        $this->imagePath = $imagePath;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }


    /**
     * @return array
     */
    public function serialize(): array
    {
        return ["name" => $this->name, "items" => array_map(function (ShopItem $item): array {
            return $item->serialize();
        }, $this->items), "private" => $this->private, "imageType" => $this->imageType, "imagePath" => $this->imagePath];
    }
}