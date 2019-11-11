<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

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

    /**
     * ShopCategory constructor.
     * @param string $name
     * @param array $items
     * @param bool $private
     */
    public function __construct(string $name, array $items, bool $private)
    {
        $this->name = $name;
        $this->items = $items;
        $this->private = $private;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->private;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return ["name" => $this->name, "items" => array_map(function (ShopItem $item) {
            return $item->serialize();
        }, $this->items), "private" => $this->private];
    }
}