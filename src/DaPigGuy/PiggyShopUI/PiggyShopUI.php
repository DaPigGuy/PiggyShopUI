<?php

namespace DaPigGuy\PiggyShopUI;

use DaPigGuy\PiggyShopUI\economy\EconomyProvider;
use DaPigGuy\PiggyShopUI\economy\EconomySProvider;
use DaPigGuy\PiggyShopUI\shops\ShopCategory;
use DaPigGuy\PiggyShopUI\shops\ShopItem;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

/**
 * Class PiggyShopUI
 * @package DaPigGuy\PiggyShopUI
 */
class PiggyShopUI extends PluginBase
{
    /** @var PiggyShopUI */
    public static $instance;

    /** @var ShopCategory[] */
    public $shopCategories = [];
    /** @var Config */
    public $shopConfig;

    /** @var EconomyProvider */
    public $economyProvider;

    public function onEnable(): void
    {
        self::$instance = $this;

        $this->saveDefaultConfig();

        switch (strtolower($this->getConfig()->getNested("economy.provider"))) {
            case "economys":
            default:
                if ($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") === null) {
                    $this->getLogger()->error("EconomyAPI is required for your selected economy provider.");
                    $this->getServer()->getPluginManager()->disablePlugin($this);
                    return;
                }
                $this->economyProvider = new EconomySProvider();
                break;
        }

        $this->shopConfig = new Config($this->getDataFolder() . "shops.yml");
        foreach ($this->shopCategories as $category) {
            $categoryObject = new ShopCategory($category["name"], array_map(function (array $item) {
                return new ShopItem(Item::jsonDeserialize($item["item"]), $item["buyPrice"], $item["canSell"], $item["sellPrice"]);
            }, $category["items"]), $category["private"]);
        }
    }

    /**
     * @return PiggyShopUI
     */
    public static function getInstance(): PiggyShopUI
    {
        return self::$instance;
    }

    /**
     * @return Config
     */
    public function getShopConfig(): Config
    {
        return $this->shopConfig;
    }

    public function saveToShopConfig(): void
    {
        $this->shopConfig->setAll(array_map(function (ShopCategory $category) {
            return $category->serialize();
        }, $this->shopCategories));
        $this->shopConfig->save();
    }

    /**
     * @return EconomyProvider
     */
    public function getEconomyProvider(): EconomyProvider
    {
        return $this->economyProvider;
    }
}