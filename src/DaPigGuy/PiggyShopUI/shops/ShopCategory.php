<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

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

    public function getName(): string
    {
        return $this->name;
    }

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

    public function addItem(ShopItem $item): void
    {
        $this->items[] = $item;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        $this->private = $private;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function getImageType(): int
    {
        return $this->imageType;
    }

    public function setImageType(int $imageType): void
    {
        $this->imageType = $imageType;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath): void
    {
        $this->imagePath = $imagePath;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function serialize(): array
    {
        return ["name" => $this->name, "items" => array_map(function (ShopItem $item): array {
            return $item->serialize();
        }, $this->items), "private" => $this->private, "imageType" => $this->imageType, "imagePath" => $this->imagePath];
    }
}