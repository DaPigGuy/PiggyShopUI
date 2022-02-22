<?php

declare(strict_types=1);

namespace DaPigGuy\PiggyShopUI\shops;

class ShopSubcategory extends ShopCategory
{
    public ShopCategory $parent;

    public function getParent(): ShopCategory
    {
        return $this->parent;
    }

    public function setParent(ShopCategory $category): void
    {
        $this->parent = $category;
    }
}