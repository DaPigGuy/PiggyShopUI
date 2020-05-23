<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use pocketmine\item\Item;

class ShopItem
{
    /** @var Item */
    public $item;
    /** @var string */
    private $description;
    /** @var float */
    public $buyPrice;
    /** @var bool */
    public $canSell;
    /** @var float */
    public $sellPrice;

    /** @var int */
    public $imageType;
    /** @var string */
    public $imagePath;

    public function __construct(Item $item, string $description, float $buyPrice, bool $canSell, float $sellPrice, int $imageType, string $imagePath)
    {
        $this->item = $item;
        $this->description = $description;
        $this->buyPrice = $buyPrice;
        $this->canSell = $canSell;
        $this->sellPrice = $sellPrice;
        $this->imagePath = $imagePath;
        $this->imageType = $imageType;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function getBuyPrice(): float
    {
        return $this->buyPrice;
    }

    public function setBuyPrice(float $buyPrice): void
    {
        $this->buyPrice = $buyPrice;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function canSell(): bool
    {
        return $this->canSell;
    }

    public function setCanSell(bool $canSell): void
    {
        $this->canSell = $canSell;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    public function getSellPrice(): float
    {
        return $this->sellPrice;
    }

    public function setSellPrice(float $sellPrice): void
    {
        $this->sellPrice = $sellPrice;
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
        return ["item" => $this->item->jsonSerialize(), "description" => $this->description, "buyPrice" => $this->buyPrice, "canSell" => $this->canSell, "sellPrice" => $this->sellPrice, "imageType" => $this->imageType, "imagePath" => $this->imagePath];
    }
}