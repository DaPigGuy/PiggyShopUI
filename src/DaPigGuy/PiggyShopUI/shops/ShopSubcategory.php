<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

class ShopSubcategory extends ShopCategory
{
    /** @var ShopCategory */
    public $parent;

    public function getParent(): ShopCategory
    {
        return $this->parent;
    }

    public function setParent(ShopCategory $category): void
    {
        $this->parent = $category;
    }
}