<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;

class ShopCategory
{
    /**
     * @param ShopItem[] $items
     * @param ShopSubcategory[] $subcategories
     */
    final public function __construct(public string $name, public array $items, public array $subcategories, public bool $private, public int $imageType, public string $imagePath)
    {
        foreach ($this->subcategories as $subcategory) {
            $subcategory->setParent($this);
        }

        $childNode = "piggyshopui.category." . strtolower($name);
        PermissionManager::getInstance()->addPermission(new Permission($childNode, "Allows usage of the " . $name . " shop category"));
        PermissionManager::getInstance()->getPermission("piggyshopui.category")?->addChild($childNode, true);
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

    /**
     * @return ShopSubcategory[]
     */
    public function getSubCategories(): array
    {
        return $this->subcategories;
    }

    public function addSubCategory(ShopSubcategory $category): void
    {
        $this->subcategories[] = $category;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function removeSubCategory(int $index): void
    {
        unset($this->subcategories[$index]);
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
        }, $this->items), "subcategories" => array_map(function (ShopCategory $subcategory): array {
            return $subcategory->serialize();
        }, $this->subcategories), "private" => $this->private, "imageType" => $this->imageType, "imagePath" => $this->imagePath];
    }

    public static function deserialize(array $category): static
    {
        return new static($category["name"], array_map(function (array $item): ShopItem {
            return new ShopItem(ShopCategory::JsonDeserialize($item["item"]), $item["description"], $item["canBuy"] ?? true, $item["buyPrice"], $item["canSell"], $item["sellPrice"], $item["imageType"] ?? -1, $item["imagePath"] ?? "");
        }, $category["items"]), array_map(function (array $subcategory): ShopSubcategory {
            return ShopSubcategory::deserialize($subcategory);
        }, $category["subcategories"] ?? []), $category["private"], $category["imageType"] ?? -1, $category["imagePath"] ?? "");
    }

    public static function jsonDeserialize($item){
        $p = (new LittleEndianNbtSerializer())->read(base64_decode($item))->mustGetCompoundTag();
        return Item::nbtDeserialize($p);
    }
}