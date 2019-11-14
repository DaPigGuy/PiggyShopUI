<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

use DaPigGuy\PiggyShopUI\PiggyShopUI;
use pocketmine\item\Item;

/**
 * Class ShopItem
 * @package DaPigGuy\PiggyShopUI\shops
 */
class ShopItem
{
    /** @var Item */
    public $item;
    /** @var string */
    private $description;
    /** @var int */
    public $buyPrice;
    /** @var bool */
    public $canSell;
    /** @var int */
    public $sellPrice;

    /** @var int */
    public $imageType;
    /** @var string */
    public $imagePath;

    /**
     * ShopItem constructor.
     * @param Item $item
     * @param string $description
     * @param int $buyPrice
     * @param bool $canSell
     * @param int $sellPrice
     * @param int $imageType
     * @param string $imagePath
     */
    public function __construct(Item $item, string $description, int $buyPrice, bool $canSell, int $sellPrice, int $imageType, string $imagePath)
    {
        $this->item = $item;
        $this->description = $description;
        $this->buyPrice = $buyPrice;
        $this->canSell = $canSell;
        $this->sellPrice = $sellPrice;
        $this->imagePath = $imagePath;
        $this->imageType = $imageType;
    }

    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    /**
     * @return int
     */
    public function getBuyPrice(): int
    {
        return $this->buyPrice;
    }

    /**
     * @param int $buyPrice
     */
    public function setBuyPrice(int $buyPrice): void
    {
        $this->buyPrice = $buyPrice;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    /**
     * @return bool
     */
    public function canSell(): bool
    {
        return $this->canSell;
    }

    /**
     * @param bool $canSell
     */
    public function setCanSell(bool $canSell): void
    {
        $this->canSell = $canSell;
        PiggyShopUI::getInstance()->saveToShopConfig();
    }

    /**
     * @return int
     */
    public function getSellPrice(): int
    {
        return $this->sellPrice;
    }

    /**
     * @param int $sellPrice
     */
    public function setSellPrice(int $sellPrice): void
    {
        $this->sellPrice = $sellPrice;
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
        return ["item" => $this->item->jsonSerialize(), "description" => $this->description, "buyPrice" => $this->buyPrice, "canSell" => $this->canSell, "sellPrice" => $this->sellPrice, "imageType" => $this->imageType, "imagePath" => $this->imagePath];
    }
}